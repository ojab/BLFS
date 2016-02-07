#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '17';
$CHAPTERS      = 'Chapters 17-19';
$START_PACKAGE = 'curl';
$STOP_PACKAGE  = 're-alpine';

$renames = array();
$ignores = array();
$ignores[ 'rpcnis-headers' ] = '';

//$current="serf";

$regex = array();
$regex[ 're-alpine' ] = "/^.*Download re-alpine-(\d[\d\.]+\d).tar.*$/";
$regex[ 'w3m'       ] = "/^.*Download w3m-(\d[\d\.]+\d).tar.*$/";
$regex[ 'serf'      ] = "/^.*Serf is ([\d\.]+\d).*$/";
$regex[ 'neon'      ] = "/^.*Source code: neon-(\d[\d\.]*).tar.*$/";
$regex[ 'geoclue'   ] = "/^.*geoclue-(\d[\d\.]+).tar.*$/";
$regex[ 'libevent'  ] = "/^.*release-(\d[\d\.]*)-stable.*$/";

$url_fix = array (

   array( //'pkg'     => 'gnome',
          'match'   => '^ftp:\/\/ftp.gnome',
          'replace' => "http://ftp.gnome" ),

   array( 'pkg'     => 're-alpine',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/re-alpine/files" ),

   array( 'pkg'     => 'w3m',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/w3m/files" ),

   array( 'pkg'     => 'links',
          'match'   => '^.*$',
          'replace' => "http://links.twibright.com/download" ),

   array( 'pkg'     => 'serf',
          'match'   => '^.*$',
          'replace' => "http://serf.apache.org/download" ),

   array( 'pkg'     => 'libtirpc',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/libtirpc/files/libtirpc" ),

   array( 'pkg'     => 'libevent',
          'match'   => '^.*$',
          'replace' => "https://github.com/nmathewson/Libevent/releases" ),

   array( 'pkg'     => 'geoclue',
          'match'   => '^.*$',
          'replace' => "https://launchpad.net/geoclue/trunk" ),

   array( 'pkg'     => 'libpcap',
          'match'   => '^.*$',
          'replace' => "http://www.tcpdump.org/#latest-release" ),

   array( 'pkg'     => 'mutt',
          'match'   => '^.*$',
          'replace' => "http://www.mutt.org/download.html" ),

   array( 'pkg'     => 'libndp',
          'match'   => '^.*$',
          'replace' => "https://github.com/jpirko/libndp/releases" ),

   array( 'pkg'     => 'lynx',
          'match'   => '^.*$',
          'replace' => "ftp://lynx.isc.org/lynx/tarballs/" ),

   array( 'pkg'     => 'libnl',
          'match'   => '^.*$',
          'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/libnl3/" ),

   array( 'pkg'     => 'libnl-doc',
          'match'   => '^.*$',
          'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/libnl3/" ),

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
    // glib type packages
    if ( $book_index == "glib-networking" ||
         $book_index == "libsoup"          )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( "$dirpath/" );
      $dir      = find_even_max( $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );
      $dirpath .= "/$dir/";
    }

    // lynx - really has odd name format
    if ( $book_index == "lynx" )
    {
      $lines = http_get_file( $dirpath );
      $max = find_max( $lines, "/rel/", "/^.*lynx(\d[\d\.]+rel\.\d).tar.*$/" );
      return $max;
    }

    // Get listing
    $lines   = http_get_file( "$dirpath/" );
  }
  else // http
  {
    // Customize http directories as needed
    if ( $book_index == "glib-networking" ||
         $book_index == "libsoup"          )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines1   = http_get_file( $dirpath );
      $dir      = find_even_max( $lines1, '/^\s*[\d\.]+\/.*$/', '/^\s*([\d\.]+).*$/' );
      $dirpath .= "/$dir/";
    }

    $lines = http_get_file( $dirpath );
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

        //if ( $book_index == "exiv" ) $ver = "2-$ver";

        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "libndp" )
    return find_max( $lines, '/v\d/', '/^.*v([\d\.]+)$/' );

  if ( $book_index == "heirloom-mailx" )
    return find_max( $lines, '/orig/', '/^.*_([\d\.]+)\.orig.*$/' );

  if ( $book_index == "libpcap" )
    return find_max( $lines, '/libpcap-[\d\.]+/', '/^.*libpcap-([\d\.]+).tar.*$/' );

  if ( $book_index == "mutt" )
    return find_max( $lines, '/mutt-/', '/^.*mutt-([\d\.]+).tar.*$/' );

  // libtirpc  (sourceforge is inconsistent here)
  if ( $book_index == "libtirpc" )
    return find_max( $lines, '/^\s*1\.[\d\.]+\s*$/', '/^\s*(1\.[\d\.]+)\s*$/' );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'w3m',
            'regex' => "/w3m-(\d[\d\.]+)/" ),
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
