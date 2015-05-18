<?php

/* This include file needs the following to be defined externally:

HTML_DIR       environment variable -- where to put html output
WGET_DIR       environment variable -- where to find wget-list

$renames       array of changes from extracted package name to
               desired display name

$ignores       array of packages to ignore

$START_PACKAGE first package in wget-list to evaluate
$STOP_PACKAGE  last package in wget-list to evaluate

$CHAPTER       string chapter number e.g. "4"

get_pattern()  function to extract package name from wget-list line

*/

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

$d = getenv( 'HTML_DIR' );
$HTML_DIR = ($d) ? $d : '.';

$d = getenv( 'WGET_DIR' );
$WGET_DIR = ($d) ? $d : '.';

$start = false;

function find_max( $lines, $regex_match, $regex_replace, $skip_high = FALSE )
{
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     if ( "x$slice" == "x$line" && 
          ! preg_match( "/^\d[\d\.]*$/", $slice ) ) continue; 

     // Skip minor versions in the 90s if requested
     if ( $skip_high )
     {
       list( $major, $minor, $micro, $rest ) = explode( ".", $slice . ".0.0.0.0" );
       if ( $micro >= 80 ) continue;
       if ( $minor >= 80 ) continue;
     }

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

     if ( "x$slice" == "x$line" ) continue; 

     // Skip odd numbered minor versions and minors > 80
     list( $major, $minor ) = explode( ".", $slice . ".0", 2 );
     if ( $minor % 2 == 1  ) continue;
     if ( $minor     >  80 ) continue;


     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top

  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function http_get_file( $url )
{
  exec( "curl -L -s -m40 -A Firefox/22.0 $url", $dir );
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
  $regex_replace = "#^.*(${prefix}[\d\.]+)/.*$#";
  $max           = find_max( $lines, $regex_match, $regex_replace );

  return "$dirpath/$max"; 
}

function get_current()
{
   global $vers;
   global $book;
   global $START_PACKAGE;
   global $STOP_PACKAGE;
   global $WGET_DIR;
   global $start;

   $wget_file = "$WGET_DIR/wget-list";

   $contents = file_get_contents( $wget_file );
   $wget  = explode( "\n", $contents );

   foreach ( $wget as $line )
   {
      if ( $line == "" ) continue;
      if ( preg_match( "/patch/", $line ) ) continue;     // Skip patches

      $file =  basename( $line );
      $url  =  dirname ( $line );
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

      if ( $basename == $START_PACKAGE ) $start = true;
      if ( ! $start ) continue;

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";

      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      // Custom for chapter 12 -- there is both p7zip, unzip, and zip there
      if ( preg_match( "/p7zip|unzip/", $line ) ) continue;
      
      if ( preg_match( "/$STOP_PACKAGE/", $line ) ) break;
   }
}

function html()
{
   global $book;
   global $date;
   global $vers;
   global $CHAPTER;
   global $HTML_DIR;
   global $renames;
   global $ignores;

   $leftnav = file_get_contents( 'leftnav.html' );

   $f = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
                      'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>BLFS Chapter 4 Package Currency Check - $date</title>
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
  
      $name = $pkg;

      foreach ( $renames as $n => $newname )
      {
        if ( $pkg == $n ) $name = $newname;
      }

      if ( isset( $ignores[ $pkg ] ) ) continue; 

      $f .= "<tr><td>$name</td>";
      $f .= "<td>$v</td>";
      $f .= "<td>${vers[ $pkg ]}</td>";
      $f .= "<td class='center'>$flag</td></tr>\n";
   }

   $f .= "</table>
</body>
</html>\n";

   file_put_contents( "$HTML_DIR/chapter$CHAPTER.html", $f );
}
?>
