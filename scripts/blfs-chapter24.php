#! /usr/bin/php
<?php

$CHAPTER=24;
$START_PACKAGE='util-macros';
$STOP_PACKAGE='xinit';

$freedesk = "http://xorg.freedesktop.org/releases/individual";
$sf       = 'sourceforge.net';

$book = array();
$book_index = 0;

$vers     = array();
$proto    = array();
$apps     = array();
$libs     = array();
$fonts    = array();
$driverss = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 'libvdpau-va-gl'  ] = "/^.*version (\d[\d\.]+\d).*$/";

//$current="libvdpau-va-gl";

$url_fix = array (

   array( 'pkg'     => 'xf86-input-wacom',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/linuxwacom/files/xf86-input-wacom" ),

   array( 'pkg'     => 'libvdpau-va-gl',
          'match'   => '^.*$', 
          'replace' => "https://github.com/i-rinat/libvdpau-va-gl/releases" ),

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
     list( $major, $minor, $micro, $patch, $rest ) = explode( ".", $slice . ".0.0.0.0" );
     if ( $micro >= 90  || $patch >=90 ) continue;

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
   exec( "curl -L -s $url", $dir );
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
  global $freedesk;
  global $proto;
  global $apps;
  global $libs;
  global $fonts;
  global $drivers;

  if ( isset( $current ) && $book_index != "$current" ) return 0;
  if ( $dirpath == "" ) return "";

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
    // MesaLib
    if ( $book_index == "mesa" )
    {
      // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines ); 

      // The directory is not always the same as the package
      $max = find_max( $lines, "/^\d+/", "/^(\d+[\d\.]+)$/" );
      $split = explode( ".", $max );
      if ( count( $split ) < 3 ) $max .= ".0";

      return $max;
    }

     if ( $dirpath == "ftp://ftp.x.org/pub/individual/driver" )
     {
       if ( count( $drivers ) == 0 )
          exec( "echo 'ls -1;bye' | ncftp $dirpath", $drivers );

       $lines = $drivers;
     }
     else
    // Get listing
    exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
  }
  else // http
  {
     // Customize http directories as needed

     if ( $dirpath == "$freedesk/proto" )
     {
       if ( count( $proto ) == 0 )
          $proto = http_get_file( $dirpath );

       $lines = $proto;
     }

     else if ( $dirpath == "$freedesk/lib" )
     {
       if ( count( $libs ) == 0 )
          $libs = http_get_file( $dirpath );

       $lines = $libs;
     }

     else if ( $dirpath == "$freedesk/app" )
     {
       if ( count( $apps ) == 0 )
          $apps = http_get_file( $dirpath );

       $lines = $apps;
     }

     else if ( $dirpath == "$freedesk/font" )
     {
       if ( count( $fonts ) == 0 )
          $fonts = http_get_file( $dirpath );

       $lines = $fonts;
     }

     else
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

  if ( $book_index == "xterm" )
    return find_max( $lines, '/^xterm-\d+.tgz.*$/', '/^xterm-(\d+).tgz.*$/' );

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
     array( 'pkg'   => 'xf8', 
            'regex' => "/^xf.*-(\d[\d\.]+).*$/" ),
   );

   foreach( $match as $m )
   {
      $pkg = $m[ 'pkg' ];

      if ( preg_match( "/$pkg/", $line ) ) 
         return $m[ 'regex' ];
   }

   return "/\D*(\d.*\d)\D*$/";
}

function insert_subsection( $header, $subdir, $type )
{
   global $book;
   global $freedesk;

   $xorg_dir  = "/home/bdubbs/BLFS/BOOK/x/installing";

   $book[ $header ] = 
      array( 'basename' => "$header",
          'url'      => "",
          'version'  => "" );

   exec( "grep 'ENTITY.*version' $xorg_dir/$subdir", $lines );

   foreach ( $lines as $line )
   {
      $name    = preg_replace( "/^.*ENTITY (.*)\-version.*$/", "$1", $line );
      $version = preg_replace( '/^.*"(.*)".*$/', "$1", $line );

      $book[ $name ] = array( 
         'basename' => $name,
         'url'      => "$freedesk/$type", 
         'version'  => $version,
         'indent'   => $type );
   }
}
 
function get_current()
{
   global $vers;
   global $book;
   global $freedesk;
   global $start;

   $wget_file = "/home/bdubbs/public_html/blfs-book-xsl/wget-list";
   $xorg_dir  = "/home/bdubbs/BLFS/BOOK/x/installing";

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

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      if ( preg_match( "/^xf/", $file ))
         $basename = preg_replace( "/^(.+\-).*$/", "$1", $file );
      else 
         $basename = strstr( $file, $version, true );
      
      $basename = rtrim( $basename, "-" );

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";

      # Note v1 depends on other chapters -- github issues
      //if ( $index == "v1" ) 
      //{
      //  $basename = "libvdpau-va-gl";
      //  $index = $basename;
      //}
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( $basename == 'glamor-egl' )
      {
         $book[ 'Xorg Drivers' ] = 
           array( 'basename' => "Xorg Drivers",
                  'url'      => "",
                  'version'  => "" );
      }
      
      if ( preg_match( "/^xf86/", $basename ) )
         $book[ $index ][ 'indent' ] = "driver";

      // If $basename is util-macros, add Xorg Protocol Headers 
      if ( $basename == 'util-macros' )
         insert_subsection( "Xorg Protocol Headers", "x7proto.xml", "proto" );

      // If $basename is libxcb, add Xorg libs
      if ( $basename == 'libxcb' )
         insert_subsection( "Xorg Libraries", "x7lib.xml", "lib" );

      // If $basename is xbitmaps, add Xorg Apps
      if ( $basename == 'xbitmaps' )
         insert_subsection( "Xorg Apps", "x7app.xml", "app" );

      // If $basename is xcursor-themes, add Xorg Fonts
      if ( $basename == 'xcursor-themes' )
         insert_subsection( "Xorg Fonts", "x7font.xml", "font" );

      if ( preg_match( "/xinit/", $line ) ) break;
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

      if ( $v == "" ) $vers[ $pkg ] = "";
  
      $name = $pkg;
      //if ( $pkg == "v" ) $name = 'libvdpau-va-gl';

      $classtype = isset( $book[ $pkg ][ 'indent' ] ) ? "indent" : "";

      $f .= "<tr><td class='$classtype'>$name</td>";
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

   if ( $base == 'Xorg Protocol Headers' ) 
      $vers[ $book_index ] = "";
   else
      $vers[ $book_index ] = $v;

   // Stop at the end of the chapter 
   if ( $book_index == $STOP_PACKAGE ) break; 
}

html();  // Write html output
?>
