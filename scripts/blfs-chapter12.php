#! /usr/bin/php
<?php

$CHAPTER=12;
$START_PACKAGE='acpid';
$STOP_PACKAGE='zip';

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 'acpid'   ] = "/^.*Download acpid-(\d[\d\.]+\d).tar.*$/";
$regex[ 'fcron'   ] = "/^.*Stable release fcron (\d[\d\.]+\d)*$/";
$regex[ 'hdparm'  ] = "/^.*Download hdparm-(\d[\d\.]+\d).tar.*$/";
$regex[ 'ibus'    ] = "/^.*ibus-(\d[\d\.]+\d).tar.*$/";
$regex[ 'strigi'  ] = "/^(\d[\d\.]+\d) .*$/";
$regex[ 'sysstat' ] = "/^.*sysstat-(\d[\d\.]+\d).tar.*$/";
$regex[ 'p7zip_'  ] = "/^.*Download p7zip_(\d[\d\.]+\d)_src.*$/";

// p7zip_ is screwed up on SF.  wget fetch is different from lins or other browser

$sf = 'sourceforge.net';

#$$current="p7zip_";

$url_fix = array (

   array( 'pkg'     => 'hdparm',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/hdparm/files" ),
   
   array( 'pkg'     => 'heirloom',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/heirloom/files/heirloom" ),
   
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
          'replace' => "http://$sf/projects/acpid2/files" ),
   
   array( 'pkg'     => 'p7zip_',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/p7zip/files" ),

   array( 'pkg'     => 'fcron',
          'match'   => '^.*$', 
          'replace' => "http://fcron.free.fr" ),

   array( 'pkg'     => 'gpm',
          'match'   => '^.*$', 
          'replace' => "http://www.ar.linux.it/pub/gpm" ),
);

function find_max( $lines, $regex_match, $regex_replace )
{
  global $book_index;
  $a = array();
  foreach ( $lines as $line )
  {
     // Ensure we skip verbosity of NcFTP
     if ( ! preg_match( $regex_match,   $line ) ) continue; 

     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     // Numbers and whitespace
     if ( "x$slice" == "x$line" && 
          ! preg_match( "/^\d[\d\.]*/", $slice ) ) continue; 

     // Skip minor versions in the 90s
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0.0" );
     if ( $minor >= 90  &&  $book_index != "dbus-glib" ) continue;
     if ( $minor >= 90  &&  $book_index != "gpm" ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function find_even_max( $lines, $regex_match, $regex_replace )
{
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     if ( "x$slice" == "x$line" && ! preg_match( "/^[\d\.]+/", $slice ) ) continue; 
     
     // Skip odd numbered minor versions
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0" );
     if ( $minor % 2 == 1 ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top

  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function http_get_file( $url )
{
  exec( "curl -L -s -m30 $url", $dir );
  $s   = implode( "\n", $dir );
  $dir = strip_tags( $s );
  return explode( "\n", $dir );
}

function max_parent( $dirpath, $prefix )
{
  // First, remove a directory
  $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
  $position = strrpos( $dirpath, "/" );
  $dirpath  = substr ( $dirpath, 0, $position );

  $lines = http_get_file( $dirpath );

  $regex_match   = "#${prefix}[\d\.]+/#";
  $regex_replace = "#^(${prefix}[\d\.]+)/.*$#";
  $max           = find_max( $lines, $regex_match, $regex_replace );

  return "$dirpath/$max"; 
}

function get_packages( $package, $dirpath )
{
  global $exceptions;
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

// p7zip_ is screwed up on SF.  wget fetch is different from lins or other browser
  if ( $book_index == "p7zip_" ) return "check manually";

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
      //exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $lines = http_get_file( "$dirpath/" );
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
      //exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $lines = http_get_file( "$dirpath/" );
      $dir = find_max( $lines, "/\d[\d\.]+/", "/(\d[\d\.]+)/" );
      $dirpath .= "/$dir";
    }

    // Get listing
    //exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
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

  if ( $book_index == "at_" )
    return find_max( $lines, '/at_/', '/^.*at_([\d\.]+).orig.tar.*$/' );

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

  if ( $book_index == "unzip" ||
       $book_index == "zip"  )
    return find_max( $lines, "/$book_index/", "/^.* $book_index(\d\d).tgz.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
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

function get_current()
{
   global $vers;
   global $book;

   $wget_file = "/home/bdubbs/public_html/blfs-book-xsl/wget-list";

   $contents = file_get_contents( $wget_file );
   $wget  = explode( "\n", $contents );

   foreach ( $wget as $line )
   {
      if ( $line == "" ) continue;

      $file = basename( $line );
      $url  = dirname ( $line );
      $file = preg_replace( "/\.tar\..z.*$/", "", $file ); // Remove .tar.?z*$
      $file = preg_replace( "/\.tar$/",       "", $file ); // Remove .tar$
      $file = preg_replace( "/\.gz$/",        "", $file ); // Remove .gz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      if ( preg_match( "/patch$/", $file ) ) continue;     // Skip patches

      $pattern = get_pattern( $line );

      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-
      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      $basename = ( $basename == "hd" ) ? "hd2u" : $basename;

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );
   }
}

function html()
{
   global $CHAPTER;
   global $book;
   global $date;
   global $vers;

   $leftnav = file_get_contents( 'leftnav.html' );

   $f = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
                      'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>BLFS Chapter $CHAPTER Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>\n";

$f .= $leftnav;

$f .= "<h1>BLFS Chapter $CHAPTER Package Currency Check</h1>
<h2>As of $date GMT</h2>

<table>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
  
      $name = $pkg;
      if ( $pkg == "at_"      ) $name = 'at';
      if ( $pkg == "udisks1"  ) $name = 'udisks2';
      if ( $pkg == "sg"       ) $name = 'sg3_utils';
      if ( $pkg == "unrarsrc" ) $name = 'unrar';
      if ( $pkg == "p7zip_"   ) $name = 'p7zip';


      $f .= "<tr><td>$name</td>";
      $f .= "<td>$v</td>";
      $f .= "<td>${vers[ $pkg ]}</td>";
      $f .= "<td class='center'>$flag</td></tr>\n";
   }

   $f .= "</table>
</body>
</html>\n";

   file_put_contents( "/home/bdubbs/public_html/chapter$CHAPTER.html", $f );
}

get_current();  // Get what is in the book

$start = false;

// Get latest version for each package 
foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 

   if ( $book_index == $START_PACKAGE ) $start = true;
   if ( ! $start ) continue;

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   echo "book index: $book_index  bver=$bver url=$url \n";

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;

   // Stop at the end of the chapter 
   if ( $book_index == $STOP_PACKAGE ) break; 
}

html();  // Write html output
?>
