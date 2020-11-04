#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '17';
$CHAPTERS      = 'Chapters 17-19';
$START_PACKAGE = 'c-ares';
$STOP_PACKAGE  = 'procmail';

$renames = array();

$ignores = array();
$ignores[ 'libnl-doc' ] = '';

//$current="lynx";

$regex = array();
$regex[ 'serf'          ] = "/^.*Serf is ([\d\.]+\d).*$/";
$regex[ 'libevent'      ] = "/^.*release-(\d[\d\.]*)-stable.*$/";
//$regex[ 'libnsl'        ] = "/^.*libnsl ([\d\.]+\d)*$/";
$regex[ 'rpcsvc-proto'  ] = "/^.*Version ([\d\.]+\d)*$/";

$url_fix = array (

   array( //'pkg'     => 'gnome',
          'match'   => '^ftp:\/\/ftp.gnome',
          'replace' => "http://ftp.gnome" ),

   array( 'pkg'     => 'alpine',
          'match'   => '^.*$',
          'replace' => "http://repo.or.cz/alpine.git/shortlog" ),

   array( 'pkg'     => 'c-ares',
          'match'   => '^.*$',
          'replace' => "https://github.com/c-ares/c-ares/releases" ),

   array( 'pkg'     => 'w3m',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/w3m/files" ),

   array( 'pkg'     => 'fetchmail',
          'match'   => '^.*$',
          'replace' => "https://sourceforge.net/projects/fetchmail/files" ),

   array( 'pkg'     => 'links',
          'match'   => '^.*$',
          'replace' => "http://links.twibright.com/download" ),

   array( 'pkg'     => 'serf',
          'match'   => '^.*$',
          'replace' => "http://serf.apache.org/download" ),

   array( 'pkg'     => 'libpsl',
          'match'   => '^.*$',
          'replace' => "https://github.com/rockdaboot/libpsl/releases" ),

   array( 'pkg'     => 'libtirpc',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/libtirpc/files/libtirpc" ),

   array( 'pkg'     => 'libevent',
          'match'   => '^.*$',
          'replace' => "https://github.com/libevent/libevent/releases" ),

   array( 'pkg'     => 'geoclue',
          'match'   => '^.*$',
          'replace' => "https://gitlab.freedesktop.org/geoclue/geoclue/tags" ),

   array( 'pkg'     => 'libpcap',
          'match'   => '^.*$',
          'replace' => "http://www.tcpdump.org/#latest-release" ),

   array( 'pkg'     => 'mutt',
          'match'   => '^.*$',
          'replace' => "http://www.mutt.org/download.html" ),

   array( 'pkg'     => 'neon',
          'match'   => '^.*$',
          'replace' => "https://github.com/notroj/neon/releases/" ),

   array( 'pkg'     => 'nghttp2',
          'match'   => '^.*$',
          'replace' => "https://github.com/nghttp2/nghttp2/releases/" ),

   array( 'pkg'     => 'libndp',
          'match'   => '^.*$',
          'replace' => "https://github.com/jpirko/libndp/releases" ),

   array( 'pkg'     => 'lynx',
          'match'   => '^.*$',
          'replace' => "http://invisible-mirror.net/archives/lynx/tarballs" ),

   array( 'pkg'     => 'libnl',
          'match'   => '^.*$',
          'replace' => "https://github.com/thom311/libnl/releases" ),

   array( 'pkg'     => 'libnsl',
          'match'   => '^.*$',
          'replace' => "https://github.com/thkukuk/libnsl/releases" ),

   array( 'pkg'     => 'rpcsvc-proto',
          'match'   => '^.*$',
          'replace' => "https://github.com/thkukuk/rpcsvc-proto/releases" ),

   array( 'pkg'     => 'procmail',
          'match'   => '^.*$',
          'replace' => "http://ftp.osuosl.org/pub/blfs/conglomeration/procmail" ),
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
      $dir      = find_even_max( $lines, '/^.* [\d\.]+$/', '/^.* ([\d\.]+)$/' );
      $dirpath .= "/$dir/";
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

    // lynx - really has odd name format
    if ( $book_index == "lynx" )
    {
      $lines = http_get_file( $dirpath );
      $max = find_max( $lines, "/rel/", "/^.*lynx(\d[\d\.]+rel\.\d).tar.*$/" );
      return $max;
    }

    $lines = http_get_file( $dirpath );
    if ( ! is_array( $lines ) ) return $lines;
  } // End fetch
//print_r($lines);
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

  if ( $book_index == "alpine" )
    return find_max( $lines, '/New version/', '/^.*New version ([\d\.]+).*$/' );

  if ( $book_index == "c-ares" )
    return find_max( $lines, '/c-ares/', '/^.*c-ares-([\d\.]+).tar.*$/' );

  if ( $book_index == "curl" )
    return find_max( $lines, '/^\d/', '/^([\d\.]+) .*$/' );

  if ( $book_index == "libndp" )
    return find_max( $lines, '/v\d/', '/^.*v([\d\.]+)$/' );

  if ( $book_index == "heirloom-mailx" )
    return find_max( $lines, '/orig/', '/^.*_([\d\.]+)\.orig.*$/' );

  if ( $book_index == "libpcap" )
    return find_max( $lines, '/libpcap-[\d\.]+/', '/^.*libpcap-([\d\.]+).tar.*$/' );

  if ( $book_index == "mutt" )
    return find_max( $lines, '/mutt-/', '/^.*mutt-([\d\.]+).tar.*$/' );

  if ( $book_index == "neon" )
    return find_max( $lines, '/\d\./', '/^.* ([\d\.]+\d).*$/' );

  if ( $book_index == "nghttp2" )
    return find_max( $lines, '/nghttp2/', '/^.*nghttp2 v([\d\.]+).*$/' );

  if ( $book_index == "geoclue" )
    return find_max( $lines, '/Release/', '/^.*Release ([\d\.]+).*$/' );

  if ( $book_index == "procmail" )
    return find_max( $lines, '/procmail/', '/^.*procmail-([\d\.]+)\.tar.*$/' );

  // libtirpc  (sourceforge is inconsistent here)
  // Trying a constant '1' now (2020-07-26)
  if ( $book_index == "libtirpc" )
    return find_max( $lines, '/^\s*1/', '/^\s*(\d\.[\d\.]+) .*$/' );
    //return find_max( $lines, '/^\s*\d/', '/^\s*(\d\.[\d\.]+) .*$/' );

  if ( $book_index == "fetchmail" )
    return find_max( $lines, '/fetchmail-/', '/^.*fetchmail-(\d\.[\d\.rc]+).tar.*$/' );

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

     array( 'pkg'   => 'nghttp',
            'regex' => "/nghttp2-(\d[\d\.]+)/" ),
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
