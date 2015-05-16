#! /usr/bin/php
<?php

$CHAPTER=9;
$START_PACKAGE='apr';
$STOP_PACKAGE='xapian-core';

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 'clucene-core'  ] = "/^.*Download clucene-core-([\d\.]+).tar.*$/";
$regex[ 'expat'         ] = "/^.*Download expat-([\d\.]+).tar.*$/";
$regex[ 'libesmtp'      ] = "/^.*libesmtp-([\d\.]+).tar.*$/";
$regex[ 'libzeitgeist'  ] = "/^.*Latest version is ([\d\.]+)\s*$/";
$regex[ 'mozjs'         ] = "/^.*mozjs(\d[\d\.]+\d).tar.*$/";
$regex[ 'mozjs1'        ] = "/^.*mozjs-(\d[\d\.]+\d).tar.*$/";
$regex[ 'libiodbc'      ] = "/^.*Download libiodbc-(\d[\d\.]+\d).tar.*$/";
$regex[ 'libical'       ] = "/^.*v(\d[\d\.]+\d).*$/";
$regex[ 'xapian-core'   ] = "/^.*is (\d[\d\.]+\d),.*$/";

//$current="talloc";

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
        'replace' => "http://www.stafford.uklinux.net/libesmtp/download.html" ),

 array( 'pkg'     => 'libusb',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/libusb/files/libusb-1.0" ),

 array( 'pkg'     => 'libusb-compat',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/libusb/files/libusb-compat-0.1" ),

 array( 'pkg'     => 'nspr',
        'match'   => '^.*$', 
        'replace' => "ftp://ftp.mozilla.org/pub/mozilla.org/nspr/releases" ),

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

 array( 'pkg'     => 'xapian-core',
        'match'   => '^.*$', 
        'replace' => "http://xapian.org" ),

 array( 'pkg'     => 'talloc',
        'match'   => '^.*$', 
        'replace' => "https://www.samba.org/ftp/talloc" ),
);

function find_max( $lines, $regex_match, $regex_replace )
{
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );
     if ( "x$slice" == "x$line" ) continue;  // Numbers and whitespace 
//echo "slice=$slice\n";
     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function find_even_max( $lines, $regex_match, $regex_replace )
{
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     if ( "x$slice" == "x$line" && ! preg_match( "/[\d\.]+/", $slice ) ) continue; 
     
     // Skip odd numbered minor versions
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0" );
     if ( $minor % 2 == 1 ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top

  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function http_get_file( $url )
{
  exec( "curl -L -s -m30 -A Firefox/22.0 $url", $dir );
  $s   = implode( "\n", $dir );
  $dir = strip_tags( $s );
  return explode( "\n", $dir );
}

function max_parent( $dirpath, $prefix )
{
  // First, remove a directory
  $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
  $position = strrpos( $dirpath, "/" );
  $dirpath  = substr ( $dirpath, 0, $position );

  $lines = http_get_file( $dirpath );

  $regex_match   = "#${prefix}[\d\.]+/#";
  $regex_replace = "#^(${prefix}[\d\.]+)/.*$#";
  $max           = find_max( $lines, $regex_match, $regex_replace );

  return "$dirpath/$max"; 
}

function get_packages( $package, $dirpath )
{
  global $exceptions;
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

      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );

      if ( $book_index == "libsigc++" ) 
        $dir = find_max(      $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );
      else
        $dir = find_even_max( $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );

      $dirpath .= "/$dir/";
    }

    if ( $book_index == "nspr" )
    {
        // Get the max directory and adjust the directory path
        exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
        $dir = find_max( $lines, "/v[\d\.]+.*/", "/^.*v([\d\.]+).*/" );
        $dirpath .= "/v$dir/src";
        exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
    }

    // Get listing
    if ( substr( $dirpath, -1 ) != "/" ) $dirpath .= "/";
    exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
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

      if ( $book_index == "libsigc++" ) 
         $dir = find_max(      $lines1, '/^\s+[\d\.]+\/.*$/', '/^\s+([\d\.]+)\/.*$/' );
      else
         $dir = find_even_max( $lines1, '/^\s+[\d\.]+\/.*$/', '/^\s+([\d\.]+)\/.*$/' );

      $dirpath .= "/$dir";
    }

    // Customize http directories as needed
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
       
        //if ( $book_index == "libatomic_ops" )
        //  $ver = preg_replace( "/_/", ".", $ver );

        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "boost_" )
  {
    $dir   = find_max( $lines, '/\d\.\d\d/', '/^\s*([\d\.]+)\s*$/' );
//print_r($lines);
    $lines = http_get_file( "$dirpath/$dir" );
//echo "dirpath/dir=$dirpath/$dir\n";
//print_r($lines);

    return find_max( $lines, '/^.*boost_[\d_]+.tar.*$/', '/^.*boost_([\d_]+).tar.*$/' );
  }

  if ( $book_index == "enchant" )
  {
    $dir   = find_max( $lines, '/^\s*[\d\.]+\/.*$/', '/^\s*([\d\.]+)\/.*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
  }

  if ( $book_index == "icu4c" )
  {
    $dir = max_parent( $dirpath, "" );
    $lines = http_get_file( "$dir" );
    return find_max( $lines, '/^.*icu4c-.*-src.tgz.*$/', '/^.*icu4c-([\d_]+)-src.*$/' );
  }

  if ( $book_index == "json-c" )
  {
      $url = "https://s3.amazonaws.com/json-c_releases";
      exec( "curl -L -s $url", $data );

      $xml_parser = xml_parser_create();
      xml_parse_into_struct( $xml_parser, $data[1], $values );

      foreach ( $values as $v )
        if ( $v[ 'tag' ] == "KEY" ) array_push( $lines, $v[ 'value' ] );

      return find_max( $lines, '/^.*json-c.*.tar.*$/', '/^.*json-c-([\d\.]+).tar.*$/' );
  }

  if ( $book_index == "libdbusmenu-qt" )
    return find_max( $lines, '/^.*libdbusmenu-qt [\d\.]+.*$/', '/^.*libdbusmenu-qt ([\d\.]+).*$/' );

  if ( $book_index == "libsigc++" )
    return find_max( $lines, '/^.*libsigc.*[\d\.]+.*$/', '/^.*libsigc\+\+-([\d\.]+).tar.*$/' );

  if ( $book_index == "libtasn" )
    return find_max( $lines, '/^.*libtasn[\d\.-]+.*$/', '/^.*libtasn([\d\.-]+).tar.*$/' );

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

//print_r($lines);
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
   $match = array();

   $match[ 0 ] = array( 'pkg'   => 'libatomic_ops', 
                        'regex' => "/\D*(\d.*\d[a-z]{0,1})\D*$/" );

   $match[ 1 ] = array( 'pkg'   => 'icu4c', 
                        'regex' => "/^.*icu4c-([\d_]+)-src.*$/" );

   $match[ 2 ] = array( 'pkg'   => 'libxml2', 
                        'regex' => "/libxml2-([\d\.]+).*$/" );

   foreach( $match as $m )
   {
      $pkg = $m[ 'pkg' ];
      if ( preg_match( "/$pkg/", $line ) ) 
         return $m[ 'regex' ];
   }

   return "/\D*(\d.*\d)\D*$/";
}

function get_current()
{
   global $vers;
   global $book;

   $wget_file = "/home/bdubbs/public_html/blfs-book-xsl/wget-list";

   $contents = file_get_contents( $wget_file );
   $wget  = explode( "\n", $contents );

   foreach ( $wget as $line )
   {
      if ( $line == "" ) continue;
      if ( preg_match( "/patch$/", $line ) ) continue;     // Skip patches

      $file = basename( $line );
      $url  = dirname ( $line );
      $file = preg_replace( "/\.tar\..z.*$/", "", $file ); // Remove .tar.?z*$
      $file = preg_replace( "/\.tar$/",       "", $file ); // Remove .tar$
      $file = preg_replace( "/\.gz$/",        "", $file ); // Remove .gz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      if ( $basename == "v" ) $basename = "libical";


      $index = $basename;

      while ( isset( $book[ $index ] ) ) $index .= "1";

      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/^wv/", $line ) ) break;
   }
}

function html()
{
   global $CHAPTER;
   global $book;
   global $date;
   global $vers;

   $leftnav = file_get_contents( 'leftnav.html' );

   $f = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
                      'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>BLFS Chapter $CHAPTER Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<div id='top'>
<h1>BLFS Chapter $CHAPTER Package Currency Check</h1>
<h2>As of $date GMT</h2>
</div>

<table id='table'>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
  
      $name = $pkg;
      if ( $pkg == "boost_"    ) $name = 'boost';
      if ( $pkg == "libpaper_" ) $name = 'libpaper';

      $f .= "<tr><td>$name</td>";
      $f .= "<td>$v</td>";
      $f .= "<td>${vers[ $pkg ]}</td>";
      $f .= "<td class='center'>$flag</td></tr>\n";
   }

   $f .= "</table>
</body>
</html>\n";

   file_put_contents( "/home/bdubbs/public_html/chapter$CHAPTER.html", $f );
}

get_current();  // Get what is in the book

$start = false;

// Get latest version for each package 
foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 

   if ( $book_index == $START_PACKAGE ) $start = true;
   if ( ! $start ) continue;

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   echo "book index: $book_index  bver=$bver url=$url \n";

   //$v = get_packages( $base, $url );
   $v = get_packages( $book_index, $url );

   $vers[ $book_index ] = $v;

   // Stop at the end of the chapter 
   if ( $book_index == $STOP_PACKAGE ) break; 
}

html();  // Write html output
?>
