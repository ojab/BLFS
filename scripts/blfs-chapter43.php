#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '43';
$CHAPTERS      = 'Chapters 43-45';
$START_PACKAGE = 'abiword';
$STOP_PACKAGE  = 'xscreensaver';

$renames = array();
$ignores = array();
$ignores[ 'xorg-server' ] = '';

$libreoffice = array();

//$current="rox-filer";

$regex = array();
$regex[ 'inkscape'     ] = "/^.*Latest.*(\d[\d\.]+\d).*$/";
$regex[ 'gnucash'      ] = "/^.*Download gnucash-(\d[\d\.]+\d).tar.*$/";
$regex[ 'pidgen'       ] = "/^.*Download pidgin-(\d[\d\.]+\d).*$/";
$regex[ 'fontforge-dist'] = "/^.*fontforge-dist-(20\d+).tar.*$/";
$regex[ 'xscreensaver' ] = "/^.*xscreensaver-(\d[\d\.]+\d).tar.*$/";
$regex[ 'tigervnc'     ] = "/^.*TigerVNC (\d[\d\.]+\d)$/";
$regex[ 'transmission' ] = "/^.*release version.*(\d[\d\.]+\d).*$/";
$regex[ 'xarchiver'    ] = "/^.*Download xarchiver-(\d[\d\.]+\d).tar.*$/";

$url_fix = array (

   array( 'pkg'     => 'gnucash',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/gnucash/files" ),

   array( 'pkg'     => 'gnucash-docs',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/gnucash/files/gnucash-docs" ),

   array( 'pkg'     => 'libreoffice',
          'match'   => '^.*$',
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'libreoffice-help',
          'match'   => '^.*$',
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'libreoffice-dictionaries',
          'match'   => '^.*$',
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'libreoffice-translations',
          'match'   => '^.*$',
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'gimp',
          'match'   => '^.*$',
          'replace' => "http://download.gimp.org/pub/gimp" ),

   array( 'pkg'     => 'gimp-help',
          'match'   => '^.*$',
          'replace' => "http://download.gimp.org/pub/gimp/help" ),

   array( 'pkg'     => 'balsa',
          'match'   => '^.*$',
          'replace' => "https://pawsa.fedorapeople.org/balsa/" ),

   array( 'pkg'     => 'gparted',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/gparted/files/gparted" ),

   array( 'pkg'     => 'inkscape',
          'match'   => '^.*$',
          'replace' => "https://launchpad.net/inkscape" ),

   array( 'pkg'     => 'pidgin',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/pidgin/files" ),

   array( 'pkg'     => 'rox-filer',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/rox/files/rox" ),

   array( 'pkg'     => 'tigervnc',
          'match'   => '^.*$',
          'replace' => "https://github.com/TigerVNC/tigervnc/releases" ),

   array( 'pkg'     => 'xchat',
          'match'   => '^.*$',
          'replace' => "http://xchat.org/files/source" ),

   array( 'pkg'     => 'fontforge-dist',
          'match'   => '^.*$',
          'replace' => "https://github.com/fontforge/fontforge/releases" ),

   array( 'pkg'     => 'xscreensaver',
          'match'   => '^.*$',
          'replace' => "http://www.jwz.org/xscreensaver/download.html" ),

   array( 'pkg'     => 'transmission',
          'match'   => '^.*$',
          'replace' => "https://www.transmissionbt.com/download" ),

   array( 'pkg'     => 'xarchiver',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/xarchiver/files" ),

   array( 'pkg'     => 'rxvt-unicode',
          'match'   => '^.*$',
          'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/rxvt-unicode" ),

);

function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;
  global $libreoffice;

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
    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
     if ( $package == "seamonkey"   ||
          $package == "firefox"     ||
          $package == "thunderbird" )
     {
         $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
         $position = strrpos( $dirpath, "/" );
         $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
         $position = strrpos( $dirpath, "/" );
         $dirpath  = substr ( $dirpath, 0, $position );  // Up 2
         $dirpath .= "/";

         $dirs = http_get_file( $dirpath );

         if ( $package == "seamonkey" )
            return find_max( $dirs, "/\d\./", "/^.*\t(\d\.\d+)\/.*$/" );
         else
            return find_max( $dirs, "/\d/", "/^.*(\d{2}[\.\d]+)\/.*/" );
     }

     if ( preg_match( "/abiword/", $dirpath ) )
     {
        $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
        $position = strrpos( $dirpath, "/" );
        $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
        $position = strrpos( $dirpath, "/" );
        $dirpath  = substr ( $dirpath, 0, $position );  // Up 2
     }

     if ( $book_index == "gnucash-docs" )
     {
        $dirs     = http_get_file( $dirpath );
        $dir      = find_max( $dirs, "/^\s+\d\./", "/^\s+(\d\.[\d\.]+)$/" );
        $dirpath .= "/$dir/";
     }

     if ( $book_index == "xchat" )
     {
        $dirs     = http_get_file( $dirpath );
        $dir      = find_max( $dirs, "/^\s*\d\./", ":^\s*(\d\.[\d\.]+)/.*$:" );
        $dirpath .= "/$dir/";
     }

     if ( preg_match( "/^libre/", "$package" ) )
     {
        if ( count( $libreoffice ) == 0 )
        {
           $dirs        = http_get_file( $dirpath );
           $dir         = find_max( $dirs, "/\d\./", "/^.*;([\d\.]+)\/.*$/" );
           $dirpath     = "http://download.documentfoundation.org/libreoffice/src/$dir";
           $libreoffice = http_get_file( $dirpath );
        }

        return find_max( $libreoffice, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
     }

     if ( $package == "gimp" )
     {
         $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
         $dirs     = http_get_file( "$dirpath/" );
         $dir      = find_even_max( $dirs, "/v\d\./", "/^.*(v\d\.[\d\.]+).*$/" );
         $dirpath .= "/$dir/";
     }

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

  if ( preg_match( "/inkscape/", "$dirpath" ) )
      return find_max( $lines, "/\d\./", "/^.* (\d[\d\.]+\d)$/" );

  if ( preg_match( "/abiword/", "$dirpath" ) )
      return find_max( $lines, "/^\d/", "/^([\d\.]+).*$/" );

  if ( $package == "balsa" )
      return find_max( $lines, "/^.*balsa-/", "/^.*balsa-([\d\.]+).*$/" );

  if ( $package == "gparted" )
      return find_max( $lines, "/$package/", "/^.*$package-([\d\.]+).*$/" );

  if ( $package == "rox-filer" )
      return find_max( $lines, "/rox\/files/", "/^.*rox\/(\d\.[\d\.]+)\/.*$/" );

  if ( $package == "tigervnc" )
      return find_max( $lines, "/^\s*\d\./", "/^\s*(\d\.[\d\.]+).*$/" );

  if ( $package == "xdg-utils" )
      return find_max( $lines, "/$package/", "/^$package-(\d\.[\d\.]+).tar.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.ta.*$/" );
  return $max;
}

function get_pattern( $line )
{
   $match = array(
     //array( 'pkg'   => 'gimp-help',
     //       'regex' => "/^.*gimp-help-(\d[\d\.]+).*$/" ),
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
