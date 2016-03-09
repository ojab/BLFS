#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '39';
$CHAPTERS      = 'Chapters 39-43';
$START_PACKAGE = 'lxmenu-data';
$STOP_PACKAGE  = 'qupzilla';

$renames = array();
$renames[ 'libfm'  ] = 'libfm-extra';
$renames[ 'libfm1' ] = 'libfm';

$ignores = array();

//$current="kguiaddons";  // Debug

$regex = array();
$regex[ 'libfm'   ] = "/^.*Download libfm-(\d[\d\.]+\d).tar.*$/";
$regex[ 'libfm1'  ] = "/^.*Download libfm-(\d[\d\.]+\d).tar.*$/";

$url_fix = array (
   array( 'pkg'     => 'lxmenu-data',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/lxmenu-data%20%28desktop%20menu%29" ),

   array( 'pkg'     => 'lxde-icon-theme',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXDE%20Icon%20Theme" ),

   array( 'pkg'     => 'menu-cache',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/menu-cache" ),

   array( 'pkg'     => 'libfm',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/pcmanfm/files" ),

   array( 'pkg'     => 'libfm1',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/pcmanfm/files" ),

   array( 'pkg'     => 'lxpanel',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXPanel%20%28desktop%20panel%29" ),

   array( 'pkg'     => 'lxappearance',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXAppearance" ),

   array( 'pkg'     => 'lxpolkit',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXPolkit" ),

   array( 'pkg'     => 'lxsession',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXSession%20%28session%20manager%29" ),

   array( 'pkg'     => 'lxde-common',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/lxde-common%20%28default%20config%29" ),

   array( 'pkg'     => 'lxappearance-obconf',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXAppearance%20Obconf" ),

   array( 'pkg'     => 'lxinput',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXInput%20%28Kbd%20and%20amp_%20mouse%20config%29/" ),

   array( 'pkg'     => 'gpicview',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/GPicView%20%28image%20Viewer%29/0.2.x" ),

   array( 'pkg'     => 'lxrandr',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXRandR%20%28monitor%20config%20tool%29" ),

   array( 'pkg'     => 'lxshortcut',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXShortcut%20%28edit%20app%20shortcut%29" ),

   array( 'pkg'     => 'lxtask',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXTask%20%28task%20manager%29" ),

   array( 'pkg'     => 'lxterminal',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lxde/files/LXTerminal%20%28terminal%20emulator%29" ),

   array( 'pkg'     => 'pcmanfm',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/pcmanfm/files/PCManFM%20%2B%20Libfm%20%28tarball%20release%29/PCManFM" ),

   array( 'pkg'     => 'qtermwidget',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/qtermwidget/releases" ),

   array( 'pkg'     => 'qterminal',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/qterminal/releases" ),

   array( 'pkg'     => 'QScintilla-gpl',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/pyqt/files/QScintilla2" ),

   array( 'pkg'     => 'qupzilla',
          'match'   => '^.*$', 
          'replace' => "https://github.com/QupZilla/qupzilla/tags" ),
);

function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

  if ( $book_index == 'obconf-qt' ||
       $book_index == 'juffed' )
    return 'check manually';

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
  else if ( $book_index != "midori_"      &&
            $book_index != "lxmenu-data"  &&
            $book_index != "menu-cache"   &&
            $book_index != "pcmanfm"      &&
            $book_index != "libfm"        &&
            $book_index != "libfm1"       &&
            $book_index != "lxpanel"      &&
            $book_index != "lxappearance" &&
            $book_index != "lxpolkit"     &&
            $book_index != "lxsession"    &&
            $book_index != "lxde-common"  &&
            $book_index != "gpicview"     &&
            $book_index != "lxinput"      &&
            $book_index != "lxrandr"      &&
            $book_index != "lxshortcut"   &&
            $book_index != "lxtask"       &&
            $book_index != "lxterminal"   &&
            $book_index != "lxappearance-obconf"  &&
            $book_index != "lxde-icon-theme" &&
            $book_index != "qtermwidget"     &&
            $book_index != "qterminal"       &&
            $book_index != "qupzilla"        &&
            $book_index != "kguiaddons"      &&
            $book_index != "solid"           &&
            $book_index != "kwindowsystem"   &&
            $book_index != "libkscreen"      &&
            $book_index != "QScintilla-gpl" 
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
     if ( $book_index == "menu-cache" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/ 1\./", "/^.* (1[\d\.]+).*$/" );
       $dirpath .= "/$dir";
     }

     if ( $book_index == "lxsession" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXSession \d/", "/^.*(LXSession [\d\.x]+).*$/" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxde-common" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXDE[ -]Common/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxinput" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXInput/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxrandr" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXRandR \d/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxshortcut" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXShortcut \d/i", "/^.*(LX.* [\d\.]+).*$/i" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxtask" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXTask \d/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxterminal" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXTerminal \d/i", "/^.*(LX.* [\d\.]+).*$/i" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxpanel" )
     {
       $lines1   = http_get_file( "$dirpath" );
       $dir      = find_max( $lines1, "/LXPanel 0/", "/^.*(LXPanel [\d\.x]+).*$/" );
       $d        = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "kguiaddons"    ||
          $book_index == "kwindowsystem" ||
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
       $book_index == "solid"         ||
       $book_index == "libkscreen"    ) 
  {
    $max = find_max( $lines, "/\d.\d/", "/^.*;([\d\.]*\d)\/.*$/" );

    if ( $book_index == "libkscreen" ) return $max;
    return $max . ".0";  // Add .0 to version
  }

  if ( $book_index == "lxde-icon-theme" )
    return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).*$/" );

  if ( $book_index == "QScintilla-gpl" )
    return find_max( $lines, "/QScintilla-/", "/^.*QScintilla-([\d\.]*\d).*$/" );

  if ( $book_index == "qupzilla" )
    return find_max( $lines, "/v\d/", "/^.*v([\d\.]*\d).*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)

  return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
}

function get_pattern( $line )
{
   $match = array(
     //array( 'pkg'   => '.*xfce', 
     //       'regex' => "/^.*xfce.*-(\d[\d\.]+).*$/" ),
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
