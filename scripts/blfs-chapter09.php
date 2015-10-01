#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '9';
$CHAPTERS      = 'Chapter 9';
$START_PACKAGE = 'apr';
$STOP_PACKAGE  = 'xapian-core';

$renames = array();
$ignores = array();

//$current="nspr"; // For debugging

$regex = array();
$regex[ 'clucene-core'  ] = "/^.*Download clucene-core-([\d\.]+).tar.*$/";
$regex[ 'expat'         ] = "/^.*Download expat-([\d\.]+).tar.*$/";
//$regex[ 'libesmtp'      ] = "/^.*libesmtp-([\d\.]+).tar.*$/";
$regex[ 'libzeitgeist'  ] = "/^.*Latest version is ([\d\.]+)\s*$/";
$regex[ 'mozjs'         ] = "/^.*mozjs(\d[\d\.]+\d).tar.*$/";
$regex[ 'mozjs1'        ] = "/^.*mozjs-(\d[\d\.]+\d).tar.*$/";
$regex[ 'libiodbc'      ] = "/^.*Download libiodbc-(\d[\d\.]+\d).tar.*$/";
$regex[ 'libical'       ] = "/^.*v(\d[\d\.]+\d).*$/";
$regex[ 'xapian-core'   ] = "/^.*is (\d[\d\.]+\d),.*$/";

$sf = 'sourceforge.net';

$url_fix = array (
 array( //'pkg'     => 'gnome',
        'match'   => '^ftp:\/\/ftp.gnome',
        'replace' => "http://ftp.gnome" ),

 array( 'pkg'     => 'boost_',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/boost/files/boost" ),

 array( 'pkg'     => 'clucene-core',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/clucene/files" ),

 array( 'pkg'     => 'enchant',
        'match'   => '^.*$',
        'replace' => "http://www.abisource.com/downloads/enchant" ),

 array( 'pkg'     => 'expat',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/expat/files/expat" ),

 array( 'pkg'     => 'libiodbc',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/iodbc/files" ),

 array( 'pkg'     => 'gamin',
        'match'   => '^.*$',
        'replace' => "https://people.gnome.org/~veillard/gamin/sources" ),

 array( 'pkg'     => 'libatomic_ops',
        'match'   => '^.*$',
        'replace' => "http://www.ivmaisoft.com/_bin/atomic_ops" ),

 array( 'pkg'     => 'libdbusmenu-qt',
        'match'   => '^.*$',
        'replace' => "https://launchpad.net/libdbusmenu-qt/trunk" ),

 array( 'pkg'     => 'libesmtp',
        'match'   => '^.*$',
        'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/libesmtp/" ),

 array( 'pkg'     => 'libusb',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/libusb/files/libusb-1.0" ),

 array( 'pkg'     => 'libusb-compat',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/libusb/files/libusb-compat-0.1" ),

 array( 'pkg'     => 'openobex',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/openobex/files/openobex" ),

 array( 'pkg'     => 'qjson',
        'match'   => '^.*$',
        'replace' => "http://$sf/projects/qjson/files/qjson" ),

 array( 'pkg'     => 'libzeitgeist',
        'match'   => '^.*$',
        'replace' => "https://launchpad.net/libzeitgeist" ),

 array( 'pkg'     => 'libdaemon',
        'match'   => '^.*$',
        'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/libdaemon" ),

 array( 'pkg'     => 'wv',
        'match'   => '^.*$',
        'replace' => "http://www.abisource.com/downloads/wv" ),

 array( 'pkg'     => 'libical',
        'match'   => '^.*$',
        'replace' => "https://github.com/libical/libical/releases" ),

 array( 'pkg'     => 'nspr',
        'match'   => '^.*$',
        'replace' => "https://ftp.mozilla.org/pub/nspr/releases/" ),

 array( 'pkg'     => 'xapian-core',
        'match'   => '^.*$',
        'replace' => "http://xapian.org" ),

 array( 'pkg'     => 'talloc',
        'match'   => '^.*$',
        'replace' => "https://www.samba.org/ftp/talloc" ),

 array( 'pkg'     => 'icu4c',
        'match'   => '^.*$',
        'replace' => "http://download.icu-project.org/files/icu4c" ),

 array( 'pkg'     => 'json-c',
        'match'   => '^.*$',
        'replace' => "https://s3.amazonaws.com/json-c_releases" ),
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
    if ( $book_index == "glib"      ||
         $book_index == "glibmm"    ||
         $book_index == "gmime"     ||
         $book_index == "json-glib" ||
         $book_index == "libglade"  ||
         $book_index == "libgsf"    ||
         $book_index == "libIDL"    ||
         $book_index == "libsigc++" ||
         $book_index == "libcroco"  ||
         $book_index == "ptlib"     ||
         $book_index == "gobject-introspection" )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );

      $lines = http_get_file( "$dirpath/" );

      //if ( $book_index == "libsigc++" )
      //  $dir = find_max(      $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );
      //else
        $dir = find_even_max( $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );

      $dirpath .= "/$dir/";
    }

    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
    // glib type packages
    if ( $book_index == "glib"      ||
         $book_index == "glibmm"    ||
         $book_index == "gmime"     ||
         $book_index == "json-glib" ||
         $book_index == "libglade"  ||
         $book_index == "libgsf"    ||
         $book_index == "libIDL"    ||
         $book_index == "libsigc++" ||
         $book_index == "libcroco"  ||
         $book_index == "ptlib"     ||
         $book_index == "gobject-introspection" )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines1 = http_get_file( $dirpath );

      //if ( $book_index == "libsigc++" )
      //   $dir = find_max(      $lines1, '/^\s+[\d\.]+\/.*$/', '/^\s+([\d\.]+)\/.*$/' );
      //else
         $dir = find_even_max( $lines1, '/^\s+[\d\.]+\/.*$/', '/^\s+([\d\.]+)\/.*$/' );

      $dirpath .= "/$dir";
    }

    // Customize http directories as needed
    if ( $book_index != "json-c")
    {
      $lines = http_get_file( $dirpath );
      if ( ! is_array( $lines ) ) return $lines;
    }

    if ( $book_index == "nspr" )
    {
      // Get the max directory and return numerical value
      $lines = http_get_file( $dirpath );
      $dir = find_max( $lines, "/v\d/", "/^.*v([\d\.]+).*/" );
      return $dir;
    }
  } // End fetch

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

  if ( $book_index == "boost_" )
  {
    $dir   = find_max( $lines, '/\d\.\d\d/', '/^\s*([\d\.]+)\s*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
    return find_max( $lines, '/^.*boost_[\d_]+.tar.*$/', '/^.*boost_([\d_]+).tar.*$/' );
  }

  if ( $book_index == "enchant" )
  {
    $dir   = find_max( $lines, '/^\s*[\d\.]+\/.*$/', '/^\s*([\d\.]+)\/.*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
  }

  if ( $book_index == "icu4c" )
  {
    $dir   = find_max( $lines, '/\d+\.\d/', '/^(\d+\.\d+)\/.*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
    return find_max( $lines, '/icu4c/', '/^.*icu4c-([\d_]+)-src.*$/' );
  }

  if ( $book_index == "json-c" )
  {
    exec( "curl -L -s $dirpath", $data ); // wget doesn't seem to work here
    $xml_parser = xml_parser_create();
    xml_parse_into_struct( $xml_parser, $data[1], $values );

    $lines = array();

    foreach ( $values as $v )
      if ( $v[ 'tag' ] == "KEY" ) array_push( $lines, $v[ 'value' ] );

    return find_max( $lines, '/^.*json-c.*.tar.*$/', '/^.*json-c-([\d\.]+).tar.*$/' );
  }

  if ( $book_index == "libdbusmenu-qt" )
    return find_max( $lines, '/^.*libdbusmenu-qt [\d\.]+.*$/',
                             '/^.*libdbusmenu-qt ([\d\.]+).*$/' );

  if ( $book_index == "libsigc++" )
    return find_max( $lines, '/^.*libsigc.*[\d\.]+.*$/', '/^.*libsigc\+\+-([\d\.]+).tar.*$/' );

  if ( $book_index == "libusb" )
    return find_max( $lines, '/^.*libusb-[\d\.]+.*$/', '/^.*libusb-([\d\.]+)$/' );

  if ( $book_index == "libusb-compat" )
    return find_max( $lines, '/^.*compat-[\d\.]+$/', '/^.*compat-([\d\.]+)$/' );

  if ( $book_index == "libxml2" )
    return find_max( $lines, '/libxml2/', '/^.*libxml2-([\d\.-]+).tar.*$/' );

  if ( $book_index == "xmlts" )
    return find_max( $lines, '/^.*Conformance Test Suite [\d]+.*$/',
                             '/^.*Conformance Test Suite ([\d]+).*$/' );

  if ( $book_index == "libpaper_" )
    return find_max( $lines, '/libpaper/', '/.*libpaper_([\d\.]+\+nmu\d).tar.*$/' );

  if ( $book_index == "libatomic_ops" )
    return find_max( $lines, '/tar/', '/.*ops-([\d\.]+).tar.*$/' );

  if ( $book_index == "openobex" )
    return find_max( $lines, '/\d\./', '/^\s*([\d\.]+)\s*$/' );

  if ( $book_index == "qjson" )
    return find_max( $lines, '/\d\./', '/^\s*([\d\.]+)\s*$/' );

  if ( $book_index == "slib" )
    return find_max( $lines, '/slib/', '/^.*slib-(\d[a-z]\d+).tar.*$/' );

  if ( $book_index == "wv" )
    return find_max( $lines, '/.*/', '/^([\d\.]+).*$/' );

  if ( $book_index == "grantlee" )
    return find_max( $lines, "/$package/", "/^.*$package-(0[\d\.]*\d)\.tar.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)

  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific pattern matches for extracting book versions
   $match = array(

      array( 'pkg'   => 'libatomic_ops',
             'regex' => "/\D*(\d.*\d[a-z]{0,1})\D*$/" ),

      array( 'pkg'   => 'icu4c',
             'regex' => "/^.*icu4c-([\d_]+)-src.*$/" ),

      array( 'pkg'   => 'libtasn1',
             'regex' => "/libtasn1-([\d\.]+).*$/" ),

      array( 'pkg'   => 'libxml2',
             'regex' => "/libxml2-([\d\.]+).*$/" ),
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
