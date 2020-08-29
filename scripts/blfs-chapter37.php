#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '37';
$CHAPTERS      = 'Chapters 37-38';
$START_PACKAGE = 'libxfce4util';
$STOP_PACKAGE  = 'lxterminal';

$renames = array();
$renames[ 'libfm'  ] = 'libfm-extra';
$renames[ 'libfm1' ] = 'libfm';

$ignores = array();
//$renames[ 'libfm1' ] = '';

$regex = array();
//$regex[ 'libfm'       ] = "/^.*Download libfm-([\d\.]+).tar.*$/";

//$current="obconf-qt";

$url_fix = array (

   array( 'pkg'     => 'gpicview',
          'match'   => '^.*$', 
          'replace' => 
             "https://sourceforge.net/projects/lxde/files/GPicView%20%28image%20Viewer%29/0.2.x"),

   array( 'pkg'     => 'xfce4-pulseaudio-plugin',
          'match'   => '^.*$', 
          'replace' => "https://gitlab.xfce.org/panel-plugins/xfce4-pulseaudio-plugin/-/tags/"),

   array( 'pkg'     => 'lxappearance',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/lxde/files/LXAppearance"),

   array( 'pkg'     => 'lxappearance-obconf',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/lxde/files/LXAppearance%20Obconf/"),

   array( 'pkg'     => 'lxde-common',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/lxde/files/lxde-common%20%28default%20config%29"),

   array( 'pkg'     => 'lxinput',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/lxde/files/LXInput%20%28Kbd%20and%20amp_%20mouse%20config%29"),

   array( 'pkg'     => 'lxrandr',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/lxde/files/LXRandR%20%28monitor%20config%20tool%29"),

   array( 'pkg'     => 'lxtask',
          'match'   => '^.*$', 
          'replace' => 
             "https://sourceforge.net/projects/lxde/files/LXTask%20%28task%20manager%29"),

   array( 'pkg'     => 'lxterminal',
          'match'   => '^.*$', 
          'replace' => 
             "https://sourceforge.net/projects/lxde/files/LXTerminal%20%28terminal%20emulator%29"),

   array( 'pkg'     => 'lxsession',
          'match'   => '^.*$', 
          'replace' =>
             "https://sourceforge.net/projects/lxde/files/LXSession%20%28session%20manager%29"),

   array( 'pkg'     => 'lxmenu-data',
          'match'   => '^.*$', 
          'replace' =>
             "https://sourceforge.net/projects/lxde/files/lxmenu-data%20%28desktop%20menu%29"),

   array( 'pkg'     => 'libfm',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/pcmanfm/files" ),

   array( 'pkg'     => 'libfm1',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/pcmanfm/files" ),

   array( 'pkg'     => 'menu-cache',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/lxde/files/menu-cache" ),

   array( 'pkg'     => 'lxpanel',
          'match'   => '^.*$', 
          'replace' => 
              "https://sourceforge.net/projects/lxde/files/LXPanel%20%28desktop%20panel%29" ),

   array( 'pkg'     => 'pcmanfm',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/pcmanfm/files/PCManFM%20%2B%20Libfm%20%28tarball%20release%29/PCManFM" ),

   array( 'pkg'     => 'qtermwidget',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/qtermwidget/releases" ),

   array( 'pkg'     => 'qterminal',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lxde/qterminal/releases" ),

   array( 'pkg'     => 'QupZilla',
          'match'   => '^.*$', 
          'replace' => "https://github.com/QupZilla/qupzilla/releases" ),

   array( 'pkg'     => 'xfce4-xkb-plugin',
          'match'   => '^.*$', 
          'replace' => "http://archive.xfce.org/src/panel-plugins/xfce4-xkb-plugin/0.7" ),

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
     // All ftp enties for this chapter
     $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
     $position = strrpos( $dirpath, "/" );
     $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
     $dirs     = http_get_file( "$dirpath/" );
  
     if ( $book_index == "libwnck" ||
          $book_index == "gtksourceview" )
        $dir = find_even_max( $dirs, "/ 2\./", "/^.* (2[\d\.]+)$/" );
     
     else if ( $book_index == "vte" )
        $dir = "0.28";
     
     else if ( $book_index == "libunique" )
        $dir = "1.1";
     
     else
        $dir = find_even_max( $dirs, "/^\d/", "/^([\d\.]+).*$/" );
     
     $dirpath .= "/$dir/";

    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else if ( $book_index == "menu-cache" )
  {
    $dirs = http_get_file( "$dirpath/" );    
    $dir = find_max ( $dirs, "/^\s*\d/", "/^\s*(\d\.[\d\.x]+)\s+.*$/" );
    $dirpath .= "/$dir";
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/menu-cache/", "/^.*menu-cache-([\d\.]+).tar.*/" );
    return $ver;
  }
  else if ( $book_index == "libfm1" )
  {
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/libfm/", "/^.*libfm-([\d\.]+).tar.*/" );
    return $ver;
  }
  else if ( $book_index == "pcmanfm" )
  {
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/pcmanfm/", "/^.*pcmanfm-([\d\.]+).tar.*/" );
    return $ver;
  }
  else if ( $book_index == "lxappearance" )
  {
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxappearance/", "/^.*lxappearance-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "lxappearance-obconf" )
  {
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxappearance/", "/^.*lxappearance-obconf-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "lxpanel" )
  {
    $dirs = http_get_file( "$dirpath/" );    
    $dir = find_max ( $dirs, "/LXPanel/", "/^.*(LXPanel\s+[\d\.x]+)\s+\d.*$/" );
    $dir = preg_replace( "/ /", '%20', $dir );
    $dirpath .= "/$dir";
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxpanel/", "/^.*lxpanel-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "lxinput" )
  {
    $dirs = http_get_file( "$dirpath/" );    
    $dir = find_max ( $dirs, "/LXInput/", "/^.*(LXInput\s*[\d\.x]+) .*$/" );
    $dir = preg_replace( "/ /", '%20', $dir );
    $dirpath .= "/$dir";
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxinput/", "/^.*lxinput-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "lxrandr" )
  {
    $dirs = http_get_file( "$dirpath/" );    
    $dir = find_max ( $dirs, "/LXRandR/", "/^.*(LXRandR\s+[\d\.x]+).*$/" );
    $dir = preg_replace( "/ /", '%20', $dir );
    $dirpath .= "/$dir";
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxrandr/", "/^.*lxrandr-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "lxtask" )
  {
    $dirs = http_get_file( "$dirpath/" );    
    $dir = find_max ( $dirs, "/LXTask/", "/^\s*(LXTask\s+[\d\.x]+)\s+.*$/" );
    $dir = preg_replace( "/ /", '%20', $dir );
    $dirpath .= "/$dir";
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxtask/", "/^.*lxtask-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "lxterminal" )
  {
    $dirs = http_get_file( "$dirpath/" );    
    $dir = find_max ( $dirs, "/LXTerminal/", "/^\s*(LXTerminal\s+[\d\.x]+)\s+.*$/" );
    $dir = preg_replace( "/ /", '%20', $dir );
    $dirpath .= "/$dir";
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxterminal/", "/^.*lxterminal-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "lxsession" )
  {
    $dirs = http_get_file( "$dirpath/" );    
    $dir = find_max ( $dirs, "/LXSession/", "/^.*(LXSession\s+[\d\.x]+)\s+.*$/" );
    $dir = preg_replace( "/ /", '%20', $dir );
    $dirpath .= "/$dir";
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/lxsession/", "/^.*lxsession-([\d\.]+).tar.*$/" );
    return $ver;
  }
  if ( $book_index == "lxde-common" )
  {
    $dirs = http_get_file( "$dirpath/" );
    $dir = find_max( $dirs, "/common/", "/^.*common ([\d\.]+\d).*$/" );
    $dirpath .= "/lxde-common%20$dir";
    $lines = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/common/", "/^.*common-([\d\.]+).tar.*$/" );
    return $ver;
  }

  else if ( $book_index == "gpicview" )
  {
    //$dirs = http_get_file( "$dirpath/" );    
    //$dir = find_max ( $dirs, "/^\s*\d/", "/^\s*(\d\.[\d\.x]+) .*$/" );
    //$dir = preg_replace( "/ /", '%20', $dir );
    //$dirpath .= "/$dir";
    $lines = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/gpicview/", "/^.*gpicview-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "qtermwidget" )
  {
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/qtermwidget/", "/^\s+qtermwidget-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "qterminal" )
  {
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/qterminal/", "/^\s+qterminal-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "QupZilla" )
  {
    $lines    = http_get_file( "$dirpath/" );
    $ver = find_max( $lines, "/QupZilla/", "/^\s+QupZilla-([\d\.]+).tar.*$/" );
    return $ver;
  }
  else if ( $book_index == "xfce4-pulseaudio-plugin" )
  {
    $lines    = http_get_file( "$dirpath" );
    $ver = find_max( $lines, "/xfce4-pulseaudio-plugin/", 
                             "/^.*xfce4-pulseaudio-plugin-([\d\.]+).*$/" );
    return $ver;
  }
  else if ( $book_index != "lxmenu-data"  &&
            $book_index != "libfm"        &&
            $book_index != "libfm1"       &&
            $book_index != "vte"          &&
            $book_index != "libwnck"      &&
            $book_index != "xfce4-xkb-plugin" ) // http
  {
     // Most http enties for this chapter
     $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
     $position = strrpos( $dirpath, "/" );
     $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
     $dirs     = http_get_file( "$dirpath/" );

     if ( ( preg_match( "/xf/",  $package ) ||
            preg_match( "/exo/", $package ) 
          ) &&
          $book_index != "xfburn" 
        )
       $dir = find_even_max( $dirs, "/^\d/", "/^([\d\.]+)\/.*$/" );
     else
       $dir = find_max     ( $dirs, "/^\d/", "/^([\d\.]+)\/.*$/" );

     $dirpath .= "/$dir";
     $lines    = http_get_file( "$dirpath/" );

     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  // Others
  else 
  {
     $lines = http_get_file( "$dirpath/" );
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

  if ( $book_index == "lxpanel" )
  {
    $ver = find_max( $lines, "/LXPanel/", "/^.*LXPanel ([\d\.]+\d).*$/" );
    return $ver;
  }

  if ( $book_index == "lxde-icon-theme" )
    return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).*$/" );

  if ( $book_index == "xfce4-terminal" ||
       $book_index == "tumbler"         )
    return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).*$/", TRUE );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
}

function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array(
     array( 'pkg'   => '.*xfce', 
            'regex' => "/^.*xfce.*-(\d[\d\.]+).*$/" ),

     array( 'pkg'   => 'xfwm', 
            'regex' => "/^.*xfwm4-(\d[\d\.]+).*$/" ),
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
