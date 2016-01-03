#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '32';
$CHAPTERS      = 'Chapters 32-34';

//$current="libkcddb";

function get_packages( $package, $dirpath )
{
  global $book_index;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

  $lines = http_get_file( "$dirpath" );
  return find_max( $lines, "/\d\./", "/^.*;(\d[\d\.]*)\/.*$/" );
}

$file        = "$WGET_DIR/kde/krameworks5.html";
$line        = exec( "grep 'frameworks.*http' $file" );
$kf5_version = preg_replace( '/^.*(\d\.\d+).*$/', "$1", $line );

$book[ 'kf5'      ] = array( 'basename' => 'kf5',
                             'url'      => 'http://download.kde.org/stable/frameworks',
                             'version'  => $kf5_version );

$file             = "$WGET_DIR/kde/kf5-apps.html";
$line             = exec( "grep 'konsole' $file" );
$kf5_apps_version = preg_replace( '/^.*-(\d[\.\d]+).*$/', "$1", $line );

$book[ 'kf5-apps' ] = array( 'basename' => 'kf5-apps',
                             'url'      => 'http://download.kde.org/stable/applications',
                             'version'  => $kf5_apps_version );

$file           = "$WGET_DIR/kde/plasma-all.html";
$line           = exec( "grep 'plasma.*http' $file" );
$plasma_version = preg_replace( '/^.*\/(\d[\.\d]+)".*$/', "$1", $line );

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
