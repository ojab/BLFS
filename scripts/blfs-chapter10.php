#! /usr/bin/php
<?php

$CHAPTER=10;
$START_PACKAGE='aalib';
$STOP_PACKAGE='qpdf';

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 'aalib'         ] = "/^.*Download aalib-([\d\.]+rc\d).tar.*$/";
//$regex[ 'exiv2'         ] = "/^.*Exiv2 (\d[\d\.]*\d) released.*/";
$regex[ 'freetype'      ] = "/^.*Download freetype-(\d[\d\.]*\d).tar.*/";
$regex[ 'jasper'        ] = "/^.*JasPer version (\d[\d\.]+\d) source.*current.*$/";
$regex[ 'lcms2'         ] = "/^.*Download lcms2-([\d\.]+\d).tar.*$/";
$regex[ 'libexif'       ] = "/^.*Download libexif-(\d[\d\.]+\d).*$/";
//$regex[ 'libjpeg-turbo' ] = "/^.*Download libjpeg-turbo-official-(\d[\d\.]+\d)-.*$/";
$regex[ 'graphite2'     ] = "/^.*Download graphite2-(\d[\d\.]+\d).tgz.*$/";
$regex[ 'libmng'        ] = "/^.*Download libmng-(\d[\d\.]+\d).tar.*$/";
$regex[ 'libpng'        ] = "/^.*Download libpng-(\d[\d\.]+\d).tar.*$/";
$regex[ 'LibRaw'        ] = "/^.*LibRaw-(\d[\d\.]+\d).tar.*$/";
//$regex[ 'libwebp'       ] = "/^.*libwebp-(\d[\d\.]+\d).tar.*$/";
$regex[ 'openjpeg1'     ] = "/^.*Download openjpeg-([\d\.]+\d).*$/";
$regex[ 'poppler'       ] = "/^.*poppler-([\d\.]+\d).tar.*$/";
$regex[ 'popplerdata'   ] = "/^.*poppler-data([\d\.]+\d).tar.*$/";
$regex[ 'qpdf'          ] = "/^.*Download qpdf-([\d\.]+\d).tar.*$/";

$sf = 'sourceforge.net';

//$current="openjpeg1";

$url_fix = array (

 array( //'pkg'     => 'gnome',
        'match'   => '^ftp:\/\/ftp.gnome', 
        'replace' => "http://ftp.gnome" ),

 array( 'pkg'     => 'aalib',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/aa-project/files" ),

 array( 'pkg'     => 'freetype',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/freetype/files/freetype2" ),

 array( 'pkg'     => 'freetype-doc',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/freetype/files/freetype-docs" ),

 array( 'pkg'     => 'giflib',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/giflib/files/giflib-5.x" ),

 array( 'pkg'     => 'imlib2',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/enlightenment/files/imlib2-src" ),

 array( 'pkg'     => 'jasper',
        'match'   => '^.*$', 
        'replace' => "http://www.ece.uvic.ca/~frodo/jasper/#download" ),

 array( 'pkg'     => 'lcms',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/lcms/files/lcms" ),

 array( 'pkg'     => 'lcms2',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/lcms/files/lcms" ),

 array( 'pkg'     => 'libexif',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/libexif/files/libexif" ),

 array( 'pkg'     => 'libjpeg-turbo',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/libjpeg-turbo/files" ),

 array( 'pkg'     => 'graphite2',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/silgraphite/files" ),

 array( 'pkg'     => 'libmng',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/libmng/files" ),

 array( 'pkg'     => 'libpng',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/libpng/files" ),

 array( 'pkg'     => 'libwebp',
        'match'   => '^.*$', 
        'replace' => "http://downloads.webmproject.org/releases/webp/index.html" ),

 array( 'pkg'     => 'qpdf',
        'match'   => '^.*$', 
        'replace' => "http://$sf/projects/qpdf/files" ),

 array( 'pkg'     => 'LibRaw',
        'match'   => '^.*$', 
        'replace' => "http://www.libraw.org/download" ),

 array( 'pkg'     => 'exiv2',
        'match'   => '^.*$', 
        'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/exiv2" ),

 array( 'pkg'     => 'openjpeg',
        'match'   => '^.*$', 
        'replace' => "http://sourceforge.net/projects/openjpeg.mirror/files" ),

 array( 'pkg'     => 'openjpeg1',
        'match'   => '^.*$', 
        'replace' => "http://sourceforge.net/projects/openjpeg.mirror/files" ),

 array( 'pkg'     => 'ijs',
        'match'   => '^.*$', 
        'replace' => "https://www.openprinting.org/download/ijs/download/" ),

);

function find_max( $lines, $regex_match, $regex_replace )
{
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
     if ( "x$slice" == "x$line" && ! preg_match( "/^\d[\d\.]*/", $slice ) ) continue; 

     // Skip minor versions in the 90s (most of the time)
     list( $major, $minor, $micro, $rest ) = explode( ".", $slice . ".0.0.0.0" );
     if ( $micro >= 80 ) continue;

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
//echo "start $url " . strftime("%c") . "\n";
  if (  ! preg_match( '/ijs/', $url ) )
    exec( "curl -L -s -tlsv1 -m30 -A Firefox $url", $dir );
  else
    exec( "wget -q --no-check-certificate -O - $url", $dir );

  //exec( "curl -L -s -m30 -1 $url", $dir );
//echo "stop " . strftime("%c") . "\n";
//print_r($dir);
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
    // babl and similar
    if ( $book_index == "babl"  ||
         $book_index == "gegl"  ||
         $book_index == "libart_lgpl" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines = http_get_file( $dirpath );
      $dir = find_max( $lines, "/\d[\d\.]+/", "/(\d[\d\.]+)/" );
      $dirpath .= "/$dir/";
    }

    // Get listing
//echo "dirpath=$dirpath\n";
    if ( $book_index == "tiff" ) { $dirpath .= "/"; }
    $lines = http_get_file( $dirpath );
    //exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
//print_r($lines);
  }
  else // http
  {
    // glib type packages
    if ( $book_index == "librsvg" )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up one
      $lines = http_get_file( $dirpath );
      $dir      = find_even_max( $lines, '/^\s+[\d\.]+\//', '/^\s+([\d\.]+)\/.*$/' );
      $dirpath .= "/$dir/";
    }

    // Customize http directories as needed
    $lines = http_get_file( $dirpath );
    if ( ! is_array( $lines ) ) return $lines;
//echo "dirpath=$dirpath\n";
//print_r($lines);
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

  if ( $book_index == "freetype-doc" )
  {
    $dir   = find_max( $lines, '/\d\./', '/^\s*([\d\.]+)\s*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
  }

  if ( $book_index == "libjpeg-turbo" )
  {
    return find_max( $lines, '/\d\./', '/^\s*([\d\.]+)\s*$/' );
  }

  if ( $book_index == "openjpeg" )
  {
    return find_max( $lines, '/^\s*1[\d\.]/', '/^.*(1\.[\d\.]+).*$/' );
  }

  // imlib
  if ( $book_index == "imlib2" )
  {
    $dir   = find_max( $lines, '/^\s*[\d\.]+\s*$/', '/^\s*([\d\.]+)\s*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
    return find_max( $lines, "/imlib/", "/^.*$package-([\d\.]*\d).tar.*$/" );
  }

  // lcms (actually lcms 1.xx)
  if ( $book_index == "lcms" )
  {
    return find_max( $lines, '/1\.[\d\.]+/', '/^\s*(1[\d\.]+)\s*$/' );
  }

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
      array( 'pkg'   => 'libatomic_ops', 
             'regex' => "/\D*(\d.*\d[a-z]{0,1})\D*$/" ),

      array( 'pkg'   => 'lcms2', 
             'regex' => "/\D*lcms2-([\d\.]+)\D*$/" ),

      array( 'pkg'   => 'exiv2', 
             'regex' => "/\D*exiv2-([\d\.]+)\D*$/" ),

      array( 'pkg'   => 'imlib2', 
             'regex' => "/\D*imlib2-([\d\.]+)\D*$/" ),

      array( 'pkg'   => 'graphite2', 
             'regex' => "/\D*graphite2-([\d\.]+)\D*$/" ),
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

      if ( preg_match( "/patch$/", $file ) ) continue;     // Skip patches

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/^qpdf/", $line ) ) break;
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
<h1>BLFS Chapter $CHAPTER Package Currency Check</h1>
<h2>As of $date GMT</h2>

<table>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
      //$flag = ( $vers[ $pkg ] <  $v ) ? "D" : $flag;
      //if ( $vers[ $pkg ] == "0" ) $flag = "";
  
      $name = $pkg;
      if ( $pkg == "openjpeg1" ) $name = 'openjpeg2';

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
