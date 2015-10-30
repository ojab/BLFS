#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '12';
$CHAPTERS      = 'Chapter 12';
$START_PACKAGE = 'acpid';
$STOP_PACKAGE  = 'zip';

$renames = array();
//$renames[ 'at_'      ] = 'at';
$renames[ 'udisks1'  ] = 'udisks2';
$renames[ 'sg'       ] = 'sg3_utils';
$renames[ 'unrarsrc' ] = 'unrar';
//$renames[ 'p7zip_'   ] = 'p7zip';

$ignores = array();

//$current="at";

$regex = array();
$regex[ 'acpid'   ] = "/^.*Download acpid-(\d[\d\.]+\d).tar.*$/";
$regex[ 'fcron'   ] = "/^.*Stable release fcron (\d[\d\.]+\d)*$/";
$regex[ 'hdparm'  ] = "/^.*Download hdparm-(\d[\d\.]+\d).tar.*$/";
$regex[ 'ibus'    ] = "/^.*ibus-(\d[\d\.]+\d).tar.*$/";
$regex[ 'strigi'  ] = "/^(\d[\d\.]+\d) .*$/";
$regex[ 'sysstat' ] = "/^.*sysstat-(\d[\d\.]+\d).tar.*$/";

$sf = 'sourceforge.net';

$url_fix = array (

   array( 'pkg'     => 'hdparm',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/hdparm/files" ),
   
   array( 'pkg'     => 'heirloom',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/heirloom/files/heirloom" ),
   
   array( 'pkg'     => 'ibus',
          'match'   => '^.*$', 
          'replace' => "https://code.google.com/p/ibus" ),
   
   array( 'pkg'     => 'mc',
          'match'   => '^.*$', 
          'replace' => "http://ftp.osuosl.org/pub/midnightcommander" ),
   
   array( 'pkg'     => 'sysstat',
          'match'   => '^.*$', 
          'replace' => "http://sebastien.godard.pagesperso-orange.fr/download.html" ),
   
   array( 'pkg'     => 'unrarsrc',
          'match'   => '^.*$', 
          'replace' => "ftp://ftp.rarlab.com/rar" ),
   
   array( 'pkg'     => 'unzip',
          'match'   => '^.*$', 
          'replace' => "ftp://ftp.info-zip.org/pub/infozip/src" ),
   
   array( 'pkg'     => 'acpid',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/acpid2/files" ),
   
   array( 'pkg'     => 'p7zip',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/p7zip/files/p7zip/" ),

   array( 'pkg'     => 'fcron',
          'match'   => '^.*$', 
          'replace' => "http://fcron.free.fr" ),

);

function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

  // Fix up directory path
  foreach ( $url_fix as $u )
  {
     $replace = $u[ 'replace' ];
     $match   = $u[ 'match'   ];

     if ( isset( $u[ 'pkg' ] ) )
     {
        if ( $package == $u[ 'pkg' ] )
        {
           $dirpath = preg_replace( "/$match/", "$replace", $dirpath );
           break;
        }
     }
     else
     {
        if ( preg_match( "/$match/", $dirpath ) )
        {
           $dirpath = preg_replace( "/$match/", "$replace", $dirpath );
           break;
        }
     }
  }

  // Check for ftp
  if ( preg_match( "/^ftp/", $dirpath ) ) 
  { 
    // glib type packages
    if ( $book_index == "gtk-doc" )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( "$dirpath/" );
      $dir      = find_even_max( $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );
      $dirpath .= "/$dir";
    }

    // babl and similar
    if ( $book_index == "rarian" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( "$dirpath/" );
      $dir      = find_max( $lines, "/\d[\d\.]+/", "/(\d[\d\.]+)/" );
      $dirpath .= "/$dir";
    }

    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
    // Customize http directories as needed
     $lines = http_get_file( $dirpath );

     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  if ( isset( $regex[ $package ] ) )
  {
     // Custom search for latest package name
     foreach ( $lines as $l )
     {
        if ( preg_match( '/^\h*$/', $l ) ) continue;
        $ver = preg_replace( $regex[ $package ], "$1", $l );
        if ( $ver == $l ) continue;

        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "apache-ant" )
    return find_max( $lines, '/apache-ant/', '/^.*apache-ant-([\d\.]+)-src.tar.*$/' );

  if ( $book_index == "at" )
    return find_max( $lines, '/orig/', '/^.*at_([\d\.]+).orig.tar.*$/' );

  if ( $book_index == "dbus" )
    return find_even_max( $lines, '/dbus/', '/^.*dbus-(\d[\d\.]*\d).tar.*$/' );

  if ( $book_index == "colord" )
    return find_even_max( $lines, '/colord/', '/^.*colord-(\d[\d\.]*\d).tar.*$/' );

  if ( $book_index == "fcron" )
    return find_max( $lines, '/fcron/', '/^.*fcron-(\d[\d\.]*\d).src.tar.*$/' );

  if ( $book_index == "raptor2" )
    return find_max( $lines, '/raptor/', '/^.*raptor2-([\d\.]*\d).tar.*$/' );

  if ( $book_index == "heirloom" )
    return find_max( $lines, '/\d{6}/', '/^.*(\d{6})\h*$/' );

  if ( $book_index == "udisks1" )
    return find_max( $lines, '/udisks/', '/^.*udisks-(\d[\d\.]*\d).tar.*$/' );

  if ( $book_index == "p7zip" )
  {
    return find_max( $lines, '/\d\./', '/^\s*([\d\.]+)\s*$/' );
  }

  if ( $book_index == "unzip" ||
       $book_index == "zip"  )
    return find_max( $lines, "/$book_index/", "/^.* $book_index(\d\d).tgz.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/", TRUE );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific pattern matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'sg', 
            'regex' => "/sg3_utils-([\d\.]+)/" ),

     array( 'pkg'   => 'p7zip', 
            'regex' => "/p7zip_([\d\.]+).*/" ),

     array( 'pkg'   => 'raptor2', 
            'regex' => "/raptor2-([\d\.]+).*/" ),
   );

   foreach( $match as $m )
   {
      $pkg = $m[ 'pkg' ];
      if ( preg_match( "/$pkg/", $line ) ) 
         return $m[ 'regex' ];
   }

   return "/\D*(\d.*\d)\D*$/";
}

get_current();  // Get what is in the book

// Get latest version for each package 
foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   echo "book index: $book_index $bver $url\n";

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;
}

html();  // Write html output
?>
