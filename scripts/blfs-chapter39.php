#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '39';
$CHAPTERS      = 'Chapters 39-43';
$START_PACKAGE = 'lxqt-build-tools';
$STOP_PACKAGE  = 'qterminal';

$renames = array();
//$renames[ 'libfm'  ] = 'libfm-extra';

$ignores = array();

//$current="lxqt-powermanagement";  // Debug

$regex = array();
//$regex[ 'libfm'   ] = "/^.*Download libfm-(\d[\d\.]+\d).tar.*$/";

$url_fix = array (
   array( 'pkg'     => 'lxqt-build-tools',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-build-tools/releases" ),

   array( 'pkg'     => 'libsysstat',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/libsysstat/releases" ),

   array( 'pkg'     => 'liblxqt',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/liblxqt/releases" ),

   array( 'pkg'     => 'libqtxdg',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/libqtxdg/releases" ),

   array( 'pkg'     => 'libfm-qt',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/libfm-qt/releases" ),

   array( 'pkg'     => 'lxqt-about',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-about/releases" ),

   array( 'pkg'     => 'lxqt-admin',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-admin/releases" ),

   array( 'pkg'     => 'lxqt-common',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-common/releases" ),

   array( 'pkg'     => 'lxqt-config',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-config/releases" ),

   array( 'pkg'     => 'lxqt-globalkeys',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-globalkeys/releases" ),

   array( 'pkg'     => 'lxqt-notificationd',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-notificationd/releases" ),

   array( 'pkg'     => 'lxqt-policykit',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-policykit/releases" ),

   array( 'pkg'     => 'lxqt-powermanagement',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-powermanagement/releases" ),

   array( 'pkg'     => 'lxqt-session',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-session/releases" ),

   array( 'pkg'     => 'lxqt-panel',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-panel/releases" ),

   array( 'pkg'     => 'lxqt-runner',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-runner/releases" ),

   array( 'pkg'     => 'pcmanfm-qt',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/pcmanfm-qt/releases" ),

   array( 'pkg'     => 'lximage-qt',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lximage-qt/releases" ),

   array( 'pkg'     => 'obconf-qt',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/obconf-qt/releases" ),

   array( 'pkg'     => 'pavucontrol-qt',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/pavucontrol-qt/releases" ),



//////

   array( 'pkg'     => 'qtermwidget',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/qtermwidget/releases" ),

   array( 'pkg'     => 'qterminal',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/qterminal/releases" ),

   array( 'pkg'     => 'lxqt-l10n',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-l10n/releases" ),

   array( 'pkg'     => 'lxqt-qtplugin',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/lxqt-qtplugin/releases" ),
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
     // All ftp enties for this chapter N/A for this chapter
  }
  else if ( $book_index != "lxqt-build-tools"  &&
            $book_index != "libsysstat"   &&
            $book_index != "libqtxdg"     &&
            $book_index != "liblxqt"      &&
            $book_index != "libfm-qt"     &&
            $book_index != "lxqt-about"   &&
            $book_index != "lxqt-admin"   &&
            $book_index != "lxqt-common"  &&
            $book_index != "lxqt-config"  &&
            $book_index != "lxqt-globalkeys"      &&
            $book_index != "lxqt-notificationd"   &&
            $book_index != "lxqt-policykit"       &&
            $book_index != "lxqt-powermanagement" &&
            $book_index != "lxqt-qtplugin"        && 
            $book_index != "lxqt-session" &&
            $book_index != "lxqt-l10n"    &&
            $book_index != "lxqt-panel"   &&
            $book_index != "lxqt-runner"  &&
            $book_index != "pcmanfm-qt"   &&
            // kf5
            $book_index != "kwindowsystem"   &&
            $book_index != "kidletime"       &&
            $book_index != "solid"           &&
            $book_index != "kguiaddons"      &&
            $book_index != "kwayland"        &&
            // plasma
            $book_index != "libkscreen"      &&
            // apps
            $book_index != "lximage-qt"      &&
            $book_index != "obconf-qt"       &&
            $book_index != "pavucontrol-qt"  &&
            $book_index != "qtermwidget"     &&
            $book_index != "qterminal"      
            ) // http
  {
     // Most http enties for this chapter
     $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
     $position = strrpos( $dirpath, "/" );
     $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
     $dirs     = http_get_file( "$dirpath/" );

     if ( preg_match( "/xf/", $package ) )
       $dir = find_even_max( $dirs, "/^\d/", "/^([\d\.]+)\/.*$/" );
     else
       $dir = find_max     ( $dirs, "/^\d/", "/^([\d\.]+)\/.*$/" );

     $dirpath .= "/$dir";
     $lines    = http_get_file( "$dirpath/" );

     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  else // http 
  {
     if ( $book_index == "kguiaddons"    ||
          $book_index == "kwindowsystem" ||
          $book_index == "kidletime"     ||
          $book_index == "solid"         ||
          $book_index == "libkscreen"    ) 
     {
       $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
       $position = strrpos( $dirpath, "/" );
       $dirpath = substr ( $dirpath, 0, $position );  // Up 1
     }

     $lines = http_get_file( "$dirpath" );
  }

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

  if ( $book_index == "kguiaddons"    ||
       $book_index == "kwindowsystem" ||
       $book_index == "kidletime"     ||
       $book_index == "solid"         ||
       $book_index == "libkscreen"    ) 
  {
    $max = find_max( $lines, "/\d.\d/", "/^.*;([\d\.]*\d)\/.*$/" );

    if ( $book_index == "libkscreen" ) return $max;
    return $max . ".0";  // Add .0 to version
  }

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)

  return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
}

function get_pattern( $line )
{
   $match = array(
     array( 'pkg'   => 'lxqt-l10n', 
            'regex' => "/^.*lxqt-l10n-(\d[\d\.]+).*$/" ),
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

   echo "book index: $book_index $bver $url \n";

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;
}

html();  // Write html output
?>
