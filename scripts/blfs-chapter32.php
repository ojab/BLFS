#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '32';
$CHAPTERS      = 'Chapters 32-33';
$START_PACKAGE = 'libxfce4util';
$STOP_PACKAGE  = 'xfce4-notifyd';

$renames = array();
$renames[ 'midori_' ] = 'midori_';

$ignores = array();

$regex = array();
$regex[ 'midori_' ] = "/^.*midori_(\d[\d\.]*\d)_all.*$/";

//$current="midori_";

$url_fix = array (

   array( 'pkg'     => 'midori_',
          'match'   => '^.*$', 
          'replace' => "http://www.midori-browser.org/download/source" ),

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
  else if ( $book_index != "midori_"  &&
            $book_index != "xfce4-xkb-plugin" ) // http
  {
     // Most http enties for this chapter
     $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
     $position = strrpos( $dirpath, "/" );
     $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
     $dirs     = http_get_file( "$dirpath/" );

     if ( preg_match( "/xf/", $package )  &&
          $book_index != "xfburn" )
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
     if ( $book_index == "midori_" )
     {
       exec( "curl -L -s -m30 $dirpath", $lines );
     }
     else
     {
       $lines = http_get_file( "$dirpath/" );
     }
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

  if ( $book_index == "lxmenu-data" )
  {
    $ver = find_max( $lines, "/$package/", "/^.*$package ([\d\.]*\d).*$/" );
    return $ver;
  }

  if ( $book_index == "lxpanel" )
  {
    $ver = find_max( $lines, "/LXPanel/", "/^.*LXPanel ([\d\.]+\d).*$/" );
    return $ver;
  }

  if ( $book_index == "lxde-icon-theme" )
    return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).*$/" );

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
