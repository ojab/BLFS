#! /usr/bin/php
<?php

$CHAPTER       = 39;
$START_PACKAGE ='alsa-lib';
$STOP_PACKAGE  ='xvidcore';
$start         = false;

$sf            = 'sourceforge.net';

$vers = array();
$book = array();
$book_index = 0;

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

$regex = array();
$regex[ 'faac'             ] = "/^.*Download faac-(\d[\d\.]+\d).tar.*$/";
$regex[ 'fdk-aac'          ] = "/^.*Download fdk-aac-(\d[\d\.]+\d).tar.*$/";
$regex[ 'a52dec'           ] = "/^.*a52dec-(\d[\d\.]+\d) is.*$/";
$regex[ 'libass'           ] = "/^.*Release (\d[\d\.]+\d).*$/";
//$regex[ 'libcdio-paranoia' ] = "/^.*The current release.*(\d[\d\.]+\d).*$/";
$regex[ 'libdv'            ] = "/^.*Download libdv-(\d[\d\.]+\d).*$/";
$regex[ 'libmpeg2'         ] = "/^.*libmpeg2-(\d[\d\.]+\d).*$/";
$regex[ 'libmusicbrainz1'  ] = "/^.*libmusicbrainz-(5[\d\.]+\d).*$/";
$regex[ 'libquicktime'     ] = "/^.*Download libquicktime-([\d\.]+\d).tar.*$/";
$regex[ 'libsamplerate'    ] = "/^.*libsamplerate-([\d\.]+\d).tar.*$/";
$regex[ 'soundtouch'       ] = "/^.*Download Source Codes release ([\d\.]+\d).*$/";
$regex[ 'xine-lib'         ] = "/^.*Download xine-lib-([\d\.]+\d).tar.*$/";
$regex[ 'v'                ] = "/^.*fdk-aac ([\d\.]+) *$/";

//$current="libvpx";

$url_fix = array (

   array( 'pkg'     => 'faac',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/faac/files" ),

   array( 'pkg'     => 'faad2',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/faac/files/faad2-src" ),

   array( 'pkg'     => 'fdk-aac',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/opencore-amr/files" ),

   array( 'pkg'     => 'a52dec',
          'match'   => '^.*$', 
          'replace' => "http://liba52.$sf/" ),

   array( 'pkg'     => 'libao',
          'match'   => '^.*$', 
          'replace' => "http://downloads.xiph.org/releases/ao" ),

   array( 'pkg'     => 'libass',
          'match'   => '^.*$', 
          'replace' => "https://github.com/libass/libass/releases" ),

   array( 'pkg'     => 'libdv',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/libdv/files" ),

   array( 'pkg'     => 'libmpeg2',
          'match'   => '^.*$', 
          'replace' => "http://libmpeg2.$sf/downloads.html" ),

   array( 'pkg'     => 'libmpeg3',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/heroines/files/releases" ),

   array( 'pkg'     => 'libmusicbrainz',
          'match'   => '^.*$', 
          'replace' => "http://ftp.musicbrainz.org/pub/musicbrainz/historical" ),

   array( 'pkg'     => 'libmusicbrainz1',
          'match'   => '^.*$', 
          'replace' => "http://musicbrainz.org/doc/libmusicbrainz" ),

   array( 'pkg'     => 'libquicktime',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/libquicktime/files" ),

   array( 'pkg'     => 'libsamplerate',
          'match'   => '^.*$', 
          'replace' => "http://www.mega-nerd.com/SRC/download.html" ),

   array( 'pkg'     => 'libvpx',
          'match'   => '^.*$', 
          'replace' => "https://chromium.googlesource.com/webm/libvpx/" ),

   array( 'pkg'     => 'soundtouch',
          'match'   => '^.*$', 
          'replace' => "http://www.surina.net/soundtouch/sourcecode.html" ),

   array( 'pkg'     => 'taglib',
          'match'   => '^.*$', 
          'replace' => "http://taglib.github.io" ),

   array( 'pkg'     => 'xine-lib',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/xine/files" ),

   array( 'pkg'     => 'xvidcore',
          'match'   => '^.*$', 
          'replace' => "http://ftp.br.debian.org/debian-multimedia/pool/main/x/xvidcore" ),

   array( 'pkg'     => 'v',
          'match'   => '^.*$', 
          'replace' => "https://github.com/mstorsjo/fdk-aac/releases" ),

   array( 'pkg'     => 'libcanberra',
          'match'   => '^.*$', 
          'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/libcanberra" ),

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
//echo "=====line=$line\n";
//echo "=====slice=$slice\n";
     // Numbers and whitespace
     if ( "x$slice" == "x$line" && ! preg_match( "/^\d[\d\.P-]*$/", $slice ) ) continue; 

     // Skip minor versions in the 90s (most of the time)
     list( $major, $minor, $micro, $rest ) = explode( ".", $slice . ".0.0.0.0" );
     if ( $micro >= 80  &&  
          $book_index != "automoc4" && 
          $book_index != "libcdio-paranoia" ) continue;

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
  exec( "curl -L -s -m30 $url", $dir );
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
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;
  if ( $package == "x" ) return 0; // Daily snapshot for x264

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
     if ( $package == "audiofile" ||
          $package == "esound"    ||
          $package == "opal"      )
     {
         // Default ftp enties for this chapter
         $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
         $position = strrpos( $dirpath, "/" );
         $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
         exec( "echo 'ls -1;bye' | ncftp $dirpath/", $dirs );
         $dir = find_max( $dirs, "/^\d/", "/^([\d\.]+).*$/" );
         $dirpath .= "/$dir/";
     }

    // Get listing
    exec( "echo 'ls -1;bye' | ncftp $dirpath/", $lines );
  }
  else // http
  {
     if ( $package == "libdvdcss" ||
          $package == "libdvdnav" ||
          $package == "libdvdread" )
     {
        $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
        $position = strrpos( $dirpath, "/" );
        $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
        $lines    = http_get_file( $dirpath . "/" );
        return find_max( $lines, "/^\d\./", ":^(\d[\d\.]+)/.*$:" );
     }

     // Directories are in the form of mmddyy :(
     if ( $package == "libmpeg3" )
     {
        $a = array();
        $dirs  = http_get_file( $dirpath );
        foreach ( $dirs as $d )
        {
           // Isolate the version and put in an array
           $slice = preg_replace( "/^\s*(\d{2})(\d{2})(\d{2})\s*$/", "$3$2$1", $d );
           if ( "x$slice" == "x$d" ) continue; 
           array_push( $a, $slice );     
        }
        
        rsort( $a, SORT_NATURAL );  // Max version is at the top
        $dir   = $a[ 0 ];
        $lines = http_get_file( "$dirpath/$dir" );
        return find_max( $lines, "/libmpeg3/", "/^.*libmpeg3-(\d[\d\.]+)-src.*$/" );
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

        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $package == "faad2" )
      return find_max( $lines, "/faad2-\d/", "/^.*faad2-([\d\.]+).*$/" );

  if ( $package == "gst-plugins-bad" )
      return find_max( $lines, "/^.*bad-/", "/^.*bad-(0.10[\d\.]+).tar.*$/" );

  if ( $package == "gst-plugins-ugly" )
      return find_max( $lines, "/^.*ugly-/", "/^.*ugly-(0.10[\d\.]+).tar.*$/" );

  if ( $package == "gst-ffmpeg" )
      return find_even_max( $lines, "/^.*ffmpeg/", "/^.*ffmpeg-([\d\.]+).tar.*$/" );

  if ( preg_match(  "/gst.*1/", $package ) )
  {
      $package = preg_replace( "/(.*)1/", "$1", $package );
      return find_even_max( $lines, "/$package/", "/^.*$package-([\d\.]+).tar.*$/" );
  }

  if ( $package == "gst-libav" )
      return find_even_max( $lines, "/$package/", "/^.*$package-([\d\.]+).tar.*$/" );

  if ( $package == "libmad" )
      return find_max( $lines, "/^.*libmad-/", "/^.*libmad-([\d\.]+[a-m]{0,1}).tar.*$/" );

  if ( $package == "libmusicbrainz" )
      return find_max( $lines, "/^.*libmusicbrainz-/", "/^.*libmusicbrainz-(2[\d\.]+).tar.*$/" );

  if ( $package == "libmusicbrainz1" )
      return find_max( $lines, "/^.*libmusicbrainz-/", "/^.*libmusicbrainz-(3[\d\.]+).tar.*$/" );

  // Very sensitive to upstream format that appears to be script based
  if ( $package == "libvpx" )
      return find_max( $lines, "/v\d/", "/^.*sv([\d\.]+)v.*$/" );

  if ( $package == "soundtouch" )
      return find_max( $lines, "/soundtouch/", "/^.*soundtouch-([\d\.]+).*$/" );

  if ( $package == "speex" || 
       $package == "speexdsp" )
      return find_max( $lines, "/$package/", "/^.*$package-([\d\.rc]+).tar.*$/" );

  if ( $package == "taglib" )
      return find_max( $lines, "/TagLib/", "/^.*TagLib ([\d\.]+).*$/" );

  if ( $package == "xvidcore" )
      return find_max( $lines, "/xvidcore_/", "/^.*xvidcore_([\d\.]+).orig.*$/" );

  if ( $package == "libcdio-paranoia" )
      return find_max( $lines, "/paranoia/", "/^.*paranoia-([\d\.\+]+).tar.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}
//********************************************************
Function get_pattern( $line )
{
   global $start;

   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'a52dec', 
            'regex' => "/^.*a52dec-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'faad2', 
            'regex' => "/^.*faad2-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'libmpeg2', 
            'regex' => "/^.*libmpeg2-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'libmpeg3', 
            'regex' => "/^.*libmpeg3-(\d[\d\.]+).*$/" ),
     
     //array( 'pkg'   => 'libvpx', 
     //       'regex' => "/^.*libvpx-v(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'libmad', 
            'regex' => "/^.*libmad-(\d[\d\.]+[a-m]{0,1}).*$/" ),

     array( 'pkg'   => 'v4l-utils', 
            'regex' => "/^.*v4l-utils-(\d[\d\.]+\d).*$/" ),
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
   global $START_PACKAGE;
   global $STOP_PACKAGE;
   global $start;

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
      $file = preg_replace( "/\..z$/",        "", $file ); // Remove .gz$/xz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      if ( preg_match( "/patch$/", $file         ) ) continue; // Skip patches

      $pattern = get_pattern( $line );
//echo "line=$line; pattern=$pattern\n";      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-
//echo "version=$version\n";
      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      if ( $basename == $START_PACKAGE ) $start = true;
      if ( ! $start ) continue;

      $index = $basename;

      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/$STOP_PACKAGE$/", $line ) ) break;
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

<table id='table'>>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];  // book version
      $cv   = $vers[ $pkg ];               // web version
      $flag = ( "x$cv" != "x$v" ) ? "*" : "";

      if ( $v == "" ) $vers[ $pkg ] = "";
  
      $name = $pkg;
      if ( $pkg == "libmusicbrainz"    ) $name = 'libmusicbrainz2';
      if ( $pkg == "libmusicbrainz1"   ) $name = 'libmusicbrainz5';
      if ( $pkg == "libvpx-v"          ) $name = 'libvpx';
      if ( $pkg == "v"                 ) $name = 'fdk-aac';
      if ( $pkg == "x"                 ) $name = 'x264';
      if ( $pkg == "x"                 ) $vers[ $pkg ] = 'daily';
      if ( $pkg == "x"                 ) $v = substr( $v, 4);

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

   echo "book index: $book_index url=$url bver=$bver latest=";

   $v = get_packages( $book_index, $url );
   echo "$v\n";
   $vers[ $book_index ] = $v;

   // Stop at the end of the chapter 
   if ( $book_index == $STOP_PACKAGE ) break; 
}

html();  // Write html output
?>
