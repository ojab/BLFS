#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '32';
$CHAPTERS      = 'Chapters 32-34';

$renames = array();
$ignores = array();

//$current="libkcddb";

function get_packages( $package, $dirpath )
{
  global $book_index;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

  $lines = http_get_file( "$dirpath" );
  return find_max( $lines, "/\d\./", "/^.*;(\d[\d\.]*)\/.*$/" );
}

$d = getenv( 'BLFS_DIR' );
$BLFS_DIR = ($d) ? $d : '.';

$file        = "$BLFS_DIR/packages.ent";
$line        = exec( "grep 'kf5-short-version' $file" );
$kf5_version = preg_replace( '/^.*"(\d[\.\d]+)".*$/', "$1", $line );

$book[ 'kf5'      ] = array( 'basename' => 'kf5',
                             'url'      => 'http://download.kde.org/stable/frameworks',
                             'version'  => $kf5_version );

$line             = exec( "grep ' kf5apps-version ' $file" );
$kf5_apps_version = preg_replace( '/^.*"(\d[\.\d]+)".*$/', "$1", $line );

$book[ 'kf5-apps' ] = array( 'basename' => 'kf5-apps',
                             'url'      => 'http://download.kde.org/stable/applications',
                             'version'  => $kf5_apps_version );

$line           = exec( "grep 'plasma5-version' $file" );
$plasma_version = preg_replace( '/^.*"(\d[\.\d]+)".*$/', "$1", $line );

$book[ 'plasma5'  ] = array( 'basename' => 'plasma5',
                             'url'      => 'http://download.kde.org/stable/plasma',
                             'version'  => $plasma_version );

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
