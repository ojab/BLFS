#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '43';
$CHAPTERS      = 'Chapters 41-43';
$START_PACKAGE = 'abiword';
$STOP_PACKAGE  = 'xscreensaver';

$renames = array();
$ignores = array();
$ignores[ 'xorg-server'       ] = '';
$ignores[ 'chromium-launcher' ] = '';
$ignores[ 'gimp-help'         ] = '';
$ignores[ 'flash_player_ppapi_linux.x' ] = '';
$ignores[ 'flash_player_ppapi_linux.i' ] = '';

$libreoffice = array();

//$current="xscreensaver";

$regex = array();
$regex[ 'inkscape'     ] = "/^.*Download Inkscape (\d[\d\.]+\d).*$/";
$regex[ 'chromium'      ] = "/^pkgver=(\d[\d\.]+\d).*$/";
$regex[ 'gnucash'       ] = "/^.*Download gnucash-(\d[\d\.]+\d).tar.*$/";
$regex[ 'midori'        ] = "/^.*midori_(\d[\d\.]*\d)_all.*$/";
$regex[ 'pidgin'        ] = "/^.*Download.*pidgin-(\d[\d\.]+\d)-.*$/";
$regex[ 'fontforge-dist'] = "/^.*fontforge-dist-(20\d+).tar.*$/";
$regex[ 'xscreensaver'  ] = "/^.*xscreensaver-(\d[\d\.]+\d).tar.*$/";
$regex[ 'tigervnc'      ] = "/^.*TigerVNC (\d[\d\.]+\d)$/";
$regex[ 'transmission'  ] = "/^.*Transmission (\d[\d\.]+\d).*$/";

$url_fix = array (

   array( 'pkg'     => 'chromium',
          'match'   => '^.*$',
          //'replace' => "https://googlechromereleases.blogspot.com/" ),
          'replace' => "https://git.archlinux.org/svntogit/packages.git/plain/trunk/PKGBUILD?h=packages/chromium" ),

   array( 'pkg'     => 'firefox',
          'match'   => '^.*$',
          'replace' => "https://archive.mozilla.org/pub/firefox/releases/" ),

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

   array( 'pkg'     => 'midori',
          'match'   => '^.*$', 
          'replace' => "http://www.midori-browser.org/download/source" ),

   array( 'pkg'     => 'gimp',
          'match'   => '^.*$',
          'replace' => "http://download.gimp.org/pub/gimp" ),

//   array( 'pkg'     => 'gimp-help',
//          'match'   => '^.*$',
//          'replace' => "http://download.gimp.org/pub/gimp/help" ),

   array( 'pkg'     => 'balsa',
          'match'   => '^.*$',
          'replace' => "https://pawsa.fedorapeople.org/balsa/" ),

   array( 'pkg'     => 'gparted',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/gparted/files/gparted" ),

   array( 'pkg'     => 'inkscape',
          'match'   => '^.*$',
          'replace' => "https://inkscape.org/en/release/" ),

   array( 'pkg'     => 'pidgin',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/pidgin/files" ),

   array( 'pkg'     => 'QupZilla',
          'match'   => '^.*$', 
          'replace' => "https://github.com/QupZilla/qupzilla/tags" ),

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
          'replace' => "https://github.com/transmission/transmission/releases" ),

   array( 'pkg'     => 'xarchiver',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/xarchiver/files" ),
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
    if ( $package == "epiphany" )
    {
       $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
       $position = strrpos( $dirpath, "/" );
       $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
       $dirs     = http_get_file( "$dirpath/" );

       $dir = find_even_max( $dirs, "/\d$/", "/^.* ([\d\.]+)$/" );

       $dirpath .= "/$dir/";
    }

    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
     if ( $package == "seamonkey"   ||
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
            return find_max( $dirs, "/\d\./", "/^.*\t(\d\.[\d\.]+\d)\/.*$/" );
         else
            return find_max( $dirs, "/\d/", "/^.*(\d{2}\.[\.\d]+)\/.*/" );
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

     if ( $book_index == "midori" )
       exec( "curl -L -s -m30 $dirpath", $lines );
     else
       $lines = http_get_file( "$dirpath" );

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

  if ( $package == "inkscape" )
  {
    $max = find_max( $lines, "/Latest/", "/^.*Inkscape (\d[\d\.]+\d).*$/" );

    // Upstrem's website info does not match tarball verion for non point versions
    $dots = 0;

    for ( $i = 0; $i < strlen($max); $i++ ) 
    {
      if ( $max[$i] == '.' ) $dots++;
    }

    if ( $dots < 2 ) $max .= '.0';

    return $max;
  }

  if ( preg_match( "/firefox/", "$dirpath" ) )
      return find_max( $lines, "/^\s+[\d\.]+esr/", "/^\s+([\d\.]+)esr\/.*$/" );

  if ( preg_match( "/abiword/", "$dirpath" ) )
      return find_max( $lines, "/^\d/", "/^([\d\.]+).*$/" );

  if ( $package == "balsa" )
      return find_max( $lines, "/^.*balsa-/", "/^.*balsa-([\d\.]+).*$/" );

  if ( $package == "gparted" )
      return find_max( $lines, "/$package/", "/^.*$package-([\d\.]+).*$/" );

  if ( $book_index == "QupZilla" )
    return find_max( $lines, "/v\d/", "/^.*v([\d\.]*\d).*$/" );

  if ( $package == "rox-filer" )
      return find_max( $lines, "/^\s*\d/", "/^\s*(\d\.[\d\.]+)\s+.*$/" );

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
