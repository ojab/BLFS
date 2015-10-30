#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '29';
$CHAPTERS      = 'Chapter 29';
$START_PACKAGE = 'konsole';
$STOP_PACKAGE  = 'gwenview';

$renames = array();
$ignores = array();

$kde_ver   = "";
$kde_lines = "";

$regex = array();
//$regex[ 'agg'      ] = "/^.*agg-(\d[\d\.]+\d).tar.*$/";

//$current="libkcddb";

$url_fix = array (

//   array( 'pkg'     => 'shared-desktop-ontologies',
//          'match'   => '^.*$', 
//          'replace' => "http://$sf/projects/oscaf/files" ),
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
    // No ftp for kde apps
    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
     if ( $package == "konsole"          ||
          $package == "kdeplasma-addons" ||
          $package == "kate"             ||
          $package == "ark"              ||
          $package == "kmix"             ||
          $package == "kdepim"           ||
          $package == "kdepim-runtime"   ||
          $package == "gwenview" ) return "check manually";

     if ( ! is_array($kde_lines) )
     {
       $dirpath   = "http://download.kde.org/stable/applications/";
       $lines     = http_get_file( $dirpath );
       $kde_ver   = find_max( $lines, "/1\d/", "/^.*;(1[\d\.]+\d)\/.*$/" );
       $kde_lines = http_get_file( "$dirpath/$kde_ver/src" );
     }

     if ( ! is_array( $kde_lines ) ) return $lines;
     return find_max( $kde_lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  } // End fetch

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

function get_pattern( $line )
{
   // Set up specific pattern matches for extracting book versions

   $match = array(
     array( 'pkg'   => 'libkexiv', 
            'regex' => "/^.*libkexiv2-(\d[\d\.]+).*$/" ),
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
