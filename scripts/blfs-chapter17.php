#! /usr/bin/php
<?php

$CHAPTER=17;
$START_PACKAGE='curl';
$STOP_PACKAGE='re-alpine';

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 're-alpine' ] = "/^.*Download re-alpine-(\d[\d\.]+\d).tar.*$/";
//$regex[ 'mailx'     ] = "/^.*Download mailx-(\d[\d\.]+\d).tar.*$/";
$regex[ 'w3m'       ] = "/^.*Download w3m-(\d[\d\.]+\d).tar.*$/";
$regex[ 'serf'      ] = "/^.*serf-([\d\.]*).tar.*$/";
$regex[ 'neon'      ] = "/^.*Source code: neon-(\d[\d\.]*).tar.*$/";
$regex[ 'geoclue'   ] = "/^.*geoclue-(\d[\d\.]+).tar.*$/";
$regex[ 'libevent'  ] = "/^.*release-(\d[\d\.]*)-stable.*$/";
//$regex[ 'mutt'      ] = "/^.*development.*(\d[\d\.]*)-stable.tar.*$/";

$sf = 'sourceforge.net';

//$current="heirloom-mailx_";

$url_fix = array (

   array( //'pkg'     => 'gnome',
          'match'   => '^ftp:\/\/ftp.gnome', 
          'replace' => "http://ftp.gnome" ),

   array( 'pkg'     => 're-alpine',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/re-alpine/files" ),

   //array( 'pkg'     => 'mailx',
   //       'match'   => '^.*$', 
   //       'replace' => "http://$sf/projects/heirloom/files" ),

   array( 'pkg'     => 'w3m',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/w3m/files" ),

   array( 'pkg'     => 'links',
          'match'   => '^.*$', 
          'replace' => "http://links.twibright.com/download" ),

   array( 'pkg'     => 'serf',
          'match'   => '^.*$', 
          'replace' => "https://code.google.com/p/serf" ),

   array( 'pkg'     => 'libtirpc',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/libtirpc/files/libtirpc" ),

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
          'replace' => "http://libndp.org" ),

   array( 'pkg'     => 'lynx',
          'match'   => '^.*$', 
          'replace' => "ftp://lynx.isc.org" ),

);
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
function find_max( $lines, $regex_match, $regex_replace )
{
  global $book_index;
  $a = array();
  foreach ( $lines as $line )
  {
     // Ensure we skip verbosity of NcFTP
     if ( ! preg_match( $regex_match,   $line ) ) continue; 
     if (   preg_match( "/NcFTP/",      $line ) ) continue;
     if (   preg_match( "/Connecting/", $line ) ) continue;
     if (   preg_match( "/Current/",    $line ) ) continue;

     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     // Numbers and whitespace
     if ( "x$slice" == "x$line" && ! preg_match( "/^\d[\d\.P-]*$/", $slice ) ) continue; 

     // Skip minor versions in the 90s (most of the time)
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0.0" );
     if ( $minor      >= 90  &&  
          $book_index != "dhcpcd" ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
  return ( isset( $a[0] ) ) ? $a[0] : 0;
}
//----------------------------------------------------------
function find_even_max( $lines, $regex_match, $regex_replace )
{
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     if ( "x$slice" == "x$line" && ! preg_match( "/^[\d\.]+/", $slice ) ) continue; 
     
     // Skip odd numbered minor versions
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0" );
     if ( $minor % 2 == 1  ) continue;
     if ( $minor     >= 90 ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
  return ( isset( $a[0] ) ) ? $a[0] : 0;
}
//===========================================
function http_get_file( $url )
{
  exec( "curl -L -s -m30 -A Firefox/22.0 $url", $dir );
  $s   = implode( "\n", $dir );
  $dir = strip_tags( $s );
  return explode( "\n", $dir );
}
//=====================================================
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
/////////////////////////////////////////////////////////////////
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
    if ( $book_index == "glib-networking" ||
         $book_index == "libsoup"          )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $dir      = find_even_max( $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );
      $dirpath .= "/$dir/";
    }

    // lynx - really has odd name format
    if ( $book_index == "lynx" )
    {
      $lines = http_get_file( $dirpath );
      $dir   = find_max( $lines, "/lynx/", "/^.*(lynx\d[\d\.]+).*$/" );
      $dirpath .= "/$dir/";
      $lines = http_get_file( "$dirpath" );
      $max = find_max( $lines, "/rel/", "/^.*lynx(\d[\d\.]+rel\.\d).tar.*$/" );
      return $max;
    }

    // Get listing
    $lines   = http_get_file( "$dirpath/" );
//print_r($lines);
    //exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
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

    if ( $book_index == "libndp" )
    {
      exec( "curl -L -s -m30 -A Firefox/22.0 $dirpath", $lines );  
    }
    else
    {
      $lines = http_get_file( $dirpath );
      if ( ! is_array( $lines ) ) return $lines;
    }
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

  if ( $book_index == "heirloom-mailx_" )
    return find_max( $lines, '/orig/', '/^.*_([\d\.]+)\.orig.*$/' );

  if ( $book_index == "libpcap" )
    return find_max( $lines, '/libpcap-[\d\.]+/', '/^.*libpcap-([\d\.]+).tar.*$/' );

  if ( $book_index == "mutt" )
    return find_max( $lines, '/mutt-/', '/^.*mutt-([\d\.]+).tar.*$/' );

  // libtirpc  (sourceforge is inconsistent here)
  if ( $book_index == "libtirpc" )
    return find_max( $lines, '/^\s*0\.[\d\.]+\s*$/', '/^\s*(0\.[\d\.]+)\s*$/' );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}
//********************************************************
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

      $file = basename( $line );
      $url  = dirname ( $line );
      $file = preg_replace( "/\.tar\..z.*$/", "", $file ); // Remove .tar.?z*$
      $file = preg_replace( "/\.tar$/",       "", $file ); // Remove .tar$
      $file = preg_replace( "/\.gz$/",        "", $file ); // Remove .gz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      if ( preg_match( "/patch$/", $file         ) ) continue; // Skip patches
      if ( preg_match( "/rpcnis-headers/", $file ) ) continue; // Don't want this

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-
      
      if ( preg_match( "/^w3m/", $file ))
         $basename = "w3m";
      else 
         $basename = strstr( $file, $version, true );
      
      $basename = rtrim( $basename, "-" );

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      //if ( preg_match( "/yasm/", $line ) ) break;
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
<title>BLFS Chapters $CHAPTER-19 Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>\n";

$f .= $leftnav;

$f .= "<h1>BLFS Chapters $CHAPTER-19 Package Currency Check</h1>
<h2>As of $date GMT</h2>

<table>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
  
      $name = $pkg;
      //if ( $pkg == "net-tools-CVS_"  ) $name = 'net-tools';
      //if ( $pkg == "wireless_tools." ) $name = 'wireless_tools';
      //if ( $pkg == "whois_"          ) $name = 'whois';

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

   // Skip things we don't want
   //if ( preg_match( "/rpcnis-headers/", $pkg ) ) continue;

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   echo "book index: $book_index  bver=$bver url=$url \n";

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;

   // Stop at the end of the chapter 
   if ( $book_index == $STOP_PACKAGE ) break; 
}

html();  // Write html output
?>
