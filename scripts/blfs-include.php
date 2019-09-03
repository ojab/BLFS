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
//echo "line=$line\n";
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );
//echo "slice=$slice\n";
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

     // Remove trailing things like -P2
     $slice1 = preg_replace( "/^(.*)-.*$/", "$1", $slice );

     // Skip odd numbered minor versions and minors > 80
     list( $major, $minor, $other ) = explode( ".", $slice1 . ".0", 3 );
     if ( $minor % 2 == 1  ) continue;
     if ( $minor     >  80 ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top

  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function find_odd_max( $lines, $regex_match, $regex_replace )
{
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

     if ( "x$slice" == "x$line" ) continue; 

     // Skip even numbered minor versions and minors > 80
     list( $major, $minor ) = explode( ".", $slice . ".0", 2 );
     if ( $minor % 2 == 0  ) continue;
     if ( $minor     >  80 ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top

  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function http_get_file( $url )
{
  if ( preg_match( "/graphviz/", $url ) ||
       preg_match( "/ntfs-3g/",  $url ) ||
       preg_match( "/libgcrypt/",$url ) ||
       preg_match( "/libksba/",  $url ) ||
       preg_match( "/swig/",     $url ) ||
       preg_match( "/llvm/",     $url ) )
  {
     exec( "links -dump $url", $lines );
     return $lines;
  }

  // Do not strip tags
  if ( preg_match( "/gpm/",      $url ) ||
       preg_match( "/libvdpau/", $url ) ||
       preg_match( "/alsa/",     $url ) )
  {
     exec( "wget -q --no-check-certificate -O- $url", $dir );
     return $dir;
  }

  if ( ! preg_match( "/sourceforge/", $url ) ||
         preg_match( "/jfs/", $url         ) ||
         preg_match( "/liba52/", $url      ) ||
         preg_match( "/cracklib/", $url    ) ||
         preg_match( "/libmpeg2/", $url    ) ||
         preg_match( "/tcl/", $url         ) ||
         preg_match( "/tk/", $url          ) ||
         preg_match( "/docutils/", $url    ) ||
         preg_match( "/expect/", $url      ) ) 
  {
     exec( "curl -L -s -m40 -A Firefox/41.0 $url", $dir );
     $s   = implode( "\n", $dir );
     $dir = strip_tags( $s );
     $strip =  explode( "\n", $dir );
//print_r($strip);
     return $strip;
  }
  else if ( preg_match( "/scons/", $url ) )
  {
     exec( "wget -q -O- $url", $dir );
     $s   = implode( "\n", $dir );
     $dir = strip_tags( $s );
     $strip =  explode( "\n", $dir );
//print_r($strip);
     return $strip;
  }
  else
  {
//echo "url=$url\n";
     exec( "links -dump $url", $lines );
     return $lines;
  }
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
      if ( preg_match( "/metacpan/", $line ) ) continue;     // Skip perl depeperl dependenciess

      $file =  basename( $line );
      $url  =  dirname ( $line );

      $file = preg_replace( "/\.tar\..z.*$/", "", $file ); // Remove .tar.?z*$
      $file = preg_replace( "/\.tar$/",       "", $file ); // Remove .tar$
      $file = preg_replace( "/\.gz$/",        "", $file ); // Remove .gz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      if ( preg_match( "/php_manual/", $file ) ) continue;

      $pattern = get_pattern( $file );
/*
      // Workaround because graphviz does not have version in filename
      if ( preg_match( "/graphviz/", $file ) )
      {
         $file = preg_replace( '/graphviz/', "graphviz-$pattern", $file );
         $pattern = '/\D*(\d.*\d)\D*$/'; 
      }
*/
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );
      $basename = rtrim( $basename, "_" );

      if ( $basename == $START_PACKAGE ) $start = true;
      if ( ! $start ) continue;

      // Custom for tidy in Chapter 11 (github is not friendly)
      //if ( preg_match( "/tidy-html/", $line ) )
      //{
      //   $version  = $file;
      //   $basename = 'tidy';
      //}

      // Custom for tripwire/github
      if ( preg_match( "/tripwire/", $line ) )
         $basename = 'tripwire';

      // Custom for Chapter 10
      if ( preg_match( "/opencv_contrib/", $line ) )
        $basename = 'opencv_contrib';

      // Custom for Chapter 43
      if ( preg_match( "/chromium-launcher/", $line ) )
        $basename = 'chromium-launcher';

      // Custom for Chapter 55
      if ( $version == "install-tl-unx" ) 
      {
        $version = "Unversioned";
        $basename = "install-tl-unx";
      }

      if ( preg_match( "/biber/", $line ) )
        $basename = "biber";

      if ( preg_match( "/hg\.mozilla\.org/", $line ) )
        $basename = "firefox";

      // Skip chromium-freetype
      if ( preg_match( "/chromium-freetype/", $line ) ) continue;

      // Skip UCD.zip in ibus package
      if ( preg_match( "/UCD/", $line ) ) continue;

      if ( $version == "biblatex-biber" ) 
      {
        $version = basename( $url ); 
        $basename = "biblatex-biber";
      }

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";

      if ( $index == "fuse1" ) $basename = "fuse1";

      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      // Custom for chapter 12 -- there is both p7zip, unzip, and zip there
      if ( preg_match( "/p7zip|unzip/", $line ) ) continue;

      // Custom for chapter 30 -- there is both yelp and yelp-sxl
      if ( preg_match( "/yelp-xsl/", $line ) ) continue;
      
      if ( preg_match( "/$STOP_PACKAGE/", $line ) ) break;
   }
}

function html()
{
   global $book;
   global $date;
   global $vers;
   global $CHAPTER;
   global $CHAPTERS;
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
<h1>BLFS $CHAPTERS Package Currency Check</h1>
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
