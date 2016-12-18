#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '5';
$CHAPTERS      = 'Chapter 5';
$START_PACKAGE = 'btrfs-progs-v';
$STOP_PACKAGE  = 'xfsprogs';

$renames = array();
$renames[ 'btrfs-progs-v' ] = 'btrfs-progs';
$renames[ 'LVM2.'         ] = 'LVM2';
$ignores = array();

//$current="sshfs";   // For debugging

$regex = array();
$regex[ 'ntfs-3g_ntfsprogs' ] = "/^.*Stable Source Release ([\d\.]+).*$/";

$sf = 'sourceforge.net';

$url_fix = array (

 array( 'pkg'     => 'fuse',
        'match'   => '^.*$', 
        'replace' => "https://github.com/libfuse/libfuse/releases" ),

 array( 'pkg'     => 'jfsutils',
        'match'   => '^.*$', 
        'replace' => "http://jfs.sourceforge.net/jfs_lr.html" ),

 array( 'pkg'     => 'ntfs-3g_ntfsprogs',
        'match'   => '^.*$', 
        'replace' => 'http://www.tuxera.com/community/ntfs-3g-download' ),

 array( 'pkg'     => 'gptfdisk',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/gptfdisk/files/gptfdisk/" ),

 array( 'pkg'     => 'sshfs',
        'match'   => '^.*$', 
        'replace' => "https://github.com/libfuse/sshfs/releases" ),

 array( 'pkg'     => 'reiserfsprogs',
        'match'   => '^.*$', 
        'replace' => "https://www.kernel.org/pub/linux/kernel/people/jeffm/reiserfsprogs/" ),
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

  if ( preg_match( "/ftp/", $dirpath ) ) $dirpath .= "/";
  $lines = http_get_file( "$dirpath" );

  if ( ! is_array( $lines ) ) return $lines;

  if ( isset( $regex[ $package ] ) )
  {
     // Custom search for latest package name
     foreach ( $lines as $l )
     {
        $ver = preg_replace( $regex[ $package ], "$1", $l );
        if ( $ver == $l ) continue;
        
        if ( $package == 'krb' ) $ver = "5-$ver";
        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "btrfs-progs-v" )
    return find_max( $lines, "/$package/", "/^.*$package([\d\.]*\d).tar.*$/" );

  if ( $book_index == "LVM2." )
    return find_max( $lines, "/$package/", "/^.*$package([\d\.]*\d).tgz.*$/" );

  if ( $book_index == "gptfdisk" )
  {
    $dir = find_max( $lines, '/\d\./', '/^.*gptfdisk\/([\d\.]+)\/.*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
  }

  if ( $book_index == "mdadm" )
  {
    $max = find_max( $lines, '/mdadm-[\d\.]+/', '/^.*mdadm-([\d\.]+).tar.*$/' );
    return $max;
  }

  if ( $book_index == "jfsutils" )
  {
//print_r($lines);
    $max = find_max( $lines, '/release/', '/^.*release ([\d\.]+).*$/' );
    return $max;
  }

  if ( $book_index == "reiserfsprogs" )
  {
    $max = find_max( $lines, '/^v[\d\.]+.*$/', '/^v([\d\.]+).*$/' );
    return $max;
  }

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array( 
      array( 'pkg'   => 'ntfs', 
             'regex' => "/ntfs-3g_ntfsprogs-(\d.*\d)\D*$/" ),

      array( 'pkg'   => 'LVM2', 
             'regex' => "/LVM2.(\d.*\d)\D*$/" ),
   );

   foreach( $match as $m )
   {
      $pkg = $m[ 'pkg' ];
      if ( preg_match( "/$pkg/", $line ) ) 
         return $m[ 'regex' ];
   }

   return "/\D*(\d.*\d)\D*$/";
}


get_current();  // Get what is in the book (in include file)

// Get latest version for each package 
foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

echo "book index: $book_index $bver $url\n";
   $v = get_packages( $base, $url );

   $vers[ $book_index ] = $v;
}

html();  // Write html output
?>
