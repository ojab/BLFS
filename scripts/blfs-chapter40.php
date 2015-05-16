#! /usr/bin/php
<?php

$CHAPTER       = 40;
$START_PACKAGE ='mpg123';
$STOP_PACKAGE  ='libisofs';
$start         = false;

$freedesk = "http://xorg.freedesktop.org/releases/individual";
$sf       = 'sourceforge.net';

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 'mpg123'    ] = "/^.*Download mpg123-(\d[\d\.]+\d).tar.*$/";
$regex[ 'lame'      ] = "/^.*Download (\d[\d\.]+\d) sources.*$/";
$regex[ 'freetts'   ] = "/^.*Download freetts-(\d[\d\.]+\d)-bin.*$/";
$regex[ 'gvolwheel' ] = "/^.*Download gvolwheel-(\d[\d\.]+\d).tar.*$/";
$regex[ 'cdrdao'    ] = "/^.*Download cdrdao-(\d[\d\.]+\d).tar.*$/";
//$regex[ 'k3b'       ] = "/^.*Download k3b-(\d[\d\.]+\d).tar.*$/";
$regex[ 'pnmixer'   ] = "/^.*pnmixer-(\d[\d\.]+\d).tar.*$/";
//$regex[ 'MPlayer'   ] = "/^.*MPlayer (\d[\d\.]+\d) release.*$/";

//$current="vlc";
$libreoffice = array();

$url_fix = array (

   array( 'pkg'     => 'mpg123',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/mpg123/files" ),

   array( 'pkg'     => 'lame',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lame/files" ),

   array( 'pkg'     => 'freetts',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/freetts/files" ),

   array( 'pkg'     => 'amarok',
          'match'   => '^.*$', 
          'replace' => "http://download.kde.org/stable/amarok" ),

   array( 'pkg'     => 'gvolwheel',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/gvolwheel/files" ),

   array( 'pkg'     => 'transcode',
          'match'   => '^.*$', 
          'replace' => "https://bitbucket.org/france/transcode-tcforge/downloads" ),

   array( 'pkg'     => 'vlc',
          'match'   => '^.*$', 
          'replace' => "http://download.videolan.org/vlc/" ),

   array( 'pkg'     => 'xine-ui',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/xine/files/xine-ui" ),

   array( 'pkg'     => 'cdrdao',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/cdrdao/files" ),

   array( 'pkg'     => 'pnmixer',
          'match'   => '^.*$', 
          'replace' => "https://github.com/nicklan/pnmixer/downloads" ),

   array( 'pkg'     => 'k3b',
          'match'   => '^.*$', 
          'replace' => "http://download.kde.org/stable/k3b" ),

);
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
function find_max( $lines, $regex_match, $regex_replace )
{
  global $book_index;
  $a = array();

//echo "regex_match=$regex_match\n";
//echo "regex_replace=$regex_replace\n";

  foreach ( $lines as $line )
  {
//echo "line=$line\n";
     // Ensure we skip verbosity of NcFTP
     if ( ! preg_match( $regex_match,   $line ) ) continue; 

     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     // Numbers and whitespace
     //if ( "x$slice" == "x$line" && ! preg_match( "/^\d[\d\.]*$/", $slice ) ) continue; 
     if ( "x$slice" == "x$line" ) continue; 
//echo "slice=$slice\n";
     // Skip minor versions in the 90s (most of the time)
     list( $major, $minor, $micro, $rest ) = explode( ".", $slice . ".0.0.0.0" );
     if ( $micro >= 80  &&  $book_index != "automoc4" ) continue;

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
  global $book_index;

  exec( "curl -L -s -m30 -A 'Mozilla/5.0 (X11; Linux i686; rv:13.0)' $url", $dir );
  $s   = implode( "\n", $dir );

  if ( $book_index != "transcode" )
    $dir = strip_tags( $s );
  else 
    $dir = $s;

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
  global $libreoffice;

  if ( isset( $current ) && $book_index != "$current" ) return 0;
  if ( $book_index == "mplayer" ) return "daily";

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
    // Get listing
    $lines = http_get_file( "$dirpath/" );
//echo "dirpath=$dirpath\n";
//print_r($lines);
  }
  else // http
  {
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

  if ( $package == "cdparanoia-III" )
      return find_max( $lines, "/^.*cdparanoia-III-/", 
                               "/^.*cdparanoia-III-([\d\.]+).src.tgz.*$/" );

  if ( $package == "amarok" )
      return find_max( $lines, "/\d\./", "/^.*;([\d\.]+)\/.*$/" );

  if ( $package == "xine-ui" )
      return find_max( $lines, "/\d\./", "/^\s*([\d\.]+)$/" );

  if ( $package == "dvd+rw-tools" )
      return find_max( $lines, "/dvd\+/", "/^.*dvd\+rw-tools-([\d\.]+).tar.*$/" );

  if ( $package == "vlc" )
      return find_max( $lines, "/\d\.[\d\.]+\//", "/^([\d\.]+)\/.*$/" );

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
     array( 'pkg'   => 'mpg123', 
            'regex' => "/^.*mpg123-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'k3b', 
            'regex' => "/^.*k3b-(\d[\d\.]+).*$/" ),
     
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
   global $freedesk;
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
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

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
<title>BLFS Chapters $CHAPTER-42 Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<h1>BLFS Chapters $CHAPTER-42 Package Currency Check</h1>
<h2>As of $date GMT</h2>

<table>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];  // book version
      $cv   = $vers[ $pkg ];               // web version
      $flag = ( "x$cv" != "x$v" ) ? "*" : "";

      if ( $v == "" ) $vers[ $pkg ] = "";
  
      $name = $pkg;
      //if ( $pkg == "libmusicbrainz"    ) $name = 'libmusicbrainz2';

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
   if ( $pkg == 'freetts1'   ) continue;
   if ( $pkg == "Clearlooks" ) continue;

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
