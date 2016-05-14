#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '29';
$CHAPTERS      = 'Chapters 29-30';
$START_PACKAGE = 'extra-cmake-modules';
$STOP_PACKAGE  = 'libdbusmenu-qt';

$renames = array();
$ignores = array();

//$current="libdbusmenu-qt";

$kde_ver   = "";
$kde_lines = "";

$regex = array();
//$regex[ 'agg'      ] = "/^.*agg-(\d[\d\.]+\d).tar.*$/";

$url_fix = array (
  array( 'pkg'     => 'extra-cmake-modules',
         'match'   => '^.*$', 
         'replace' => "http://download.kde.org/stable/frameworks" ),

  array( 'pkg'     => 'libdbusmenu-qt',
         'match'   => '^.*$', 
       'replace' => "http://archive.ubuntu.com/ubuntu/pool/main/libd/libdbusmenu-qt/" ),
);

function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;
  global $kde_ver;
  global $kde_lines;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

  # ftp.kde.org seems to be down
  if ( preg_match( "/ftp:..ftp.kde.org/", $dirpath ) )
    $dirpath = preg_replace( "/ftp:..ftp.kde.org.pub.kde/",  
                             "http://download.kde.org", $dirpath );

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
    if ( $book_index == "polkit-qt-1" )
    {
      $lines = http_get_file( "$dirpath" );
      return find_max( $lines, "/$book_index/", "/^.*$book_index-([\d\.]+).tar.*$/" );
    }

    if ( $book_index == "phonon-backend-vlc" ||
         $book_index == "phonon"             ||
         $book_index == "phonon-backend-gstreamer" )
    {
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up 1
      $lines = http_get_file( "$dirpath" );
      return find_max( $lines, "/\d\./", "/^.*;([\d\.]+)\/.*$/" );
    }

     if ( $book_index == "extra-cmake-modules" )
     {
        $lines = http_get_file( "$dirpath" );
        $max = find_max( $lines, "/5/", "/^.*(5[\d\.]+)\/.*$/" );
        return $max . ".0";
     }

     if ( $book_index == "libdbusmenu-qt" )
     {
        $lines = http_get_file( "$dirpath" );
        $max = find_max( $lines, "/libdbusmenu-qt/", 
                                 "/^.*libdbusmenu-qt_([\d\.]+.*)\.orig.*$/" );
        return $max;
     }

     if ( ! is_array($kde_lines) )
     {
       // All http for kde
       /*
       $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
       $position = strrpos( $dirpath, "/" );
       $dirpath  = substr ( $dirpath, 0, $position ); // Up 1
       $position = strrpos( $dirpath, "/" );
       $dirpath  = substr ( $dirpath, 0, $position ); // Up 2
       */
       $dirpath="http://download.kde.org/stable/applications/";

       $lines = http_get_file( $dirpath );
       $kde_ver = find_max( $lines, "/1\d\./", "/^.*;(1[\d\.]+\d)\/.*$/" );
       $kde_lines = http_get_file( "$dirpath$kde_ver/src/" );
     }

     return find_max( $kde_lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
     //if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

function get_pattern( $line )
{
   global $start;

   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(

     array( 'pkg'   => 'polkit-qt',
            'regex' => "/^.*polkit-qt-1-(\d[\d\.]+).*$/" ),

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
