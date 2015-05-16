#! /usr/bin/php
<?php

$CHAPTER       = 36;
$START_PACKAGE ='abiword';
$STOP_PACKAGE  ='xscreensaver';
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
$regex[ 'inkscape'     ] = "/^.*Latest.*(\d[\d\.]+\d).*$/";
$regex[ 'gnucash'      ] = "/^.*Download gnucash-(\d[\d\.]+\d).tar.*$/";
$regex[ 'pidgen'       ] = "/^.*Download pidgin-(\d[\d\.]+\d).*$/";
$regex[ 'fontforge'    ] = "/^.*fontforge-(20\d+).tar.*$/";
$regex[ 'asymptote'    ] = "/^.*Download asymptote-(\d[\d\.]+\d).*$/";
$regex[ 'xscreensaver' ] = "/^.*xscreensaver-(\d[\d\.]+\d).tar.*$/";
$regex[ 'tigervnc'     ] = "/^.*TigerVNC (\d[\d\.]+\d).*$/";
$regex[ 'transmission' ] = "/^.*release version.*(\d[\d\.]+\d).*$/";

//$current="fontforge";
$libreoffice = array();

$url_fix = array (

   array( 'pkg'     => 'gnucash',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/gnucash/files" ),

   array( 'pkg'     => 'gnucash-docs',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/gnucash/files/gnucash-docs" ),

   array( 'pkg'     => 'asymptote',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/asymptote/files" ),

   array( 'pkg'     => 'libreoffice',
          'match'   => '^.*$', 
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'libreoffice-help',
          'match'   => '^.*$', 
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'libreoffice-dictionaries',
          'match'   => '^.*$', 
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'libreoffice-translations',
          'match'   => '^.*$', 
          'replace' => "http://download.documentfoundation.org/libreoffice/stable" ),

   array( 'pkg'     => 'gimp',
          'match'   => '^.*$', 
          'replace' => "http://download.gimp.org/pub/gimp" ),
          //'replace' => "ftp://mirrors.xmission.com/gimp/gimp" ),

   array( 'pkg'     => 'gimp-help',
          'match'   => '^.*$', 
          'replace' => "http://download.gimp.org/pub/gimp/help" ),

   array( 'pkg'     => 'balsa',
          'match'   => '^.*$', 
          'replace' => "https://pawsa.fedorapeople.org/balsa/" ),

   array( 'pkg'     => 'gparted',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/gparted/files/gparted" ),

   array( 'pkg'     => 'inkscape',
          'match'   => '^.*$', 
          'replace' => "https://launchpad.net/inkscape" ),

   array( 'pkg'     => 'pidgin',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/pidgin/files" ),

   array( 'pkg'     => 'rox-filer',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/rox/files/rox" ),

   array( 'pkg'     => 'tigervnc',
          'match'   => '^.*$', 
          'replace' => "https://github.com/TigerVNC/tigervnc/releases" ),

   array( 'pkg'     => 'xchat',
          'match'   => '^.*$', 
          'replace' => "http://xchat.org/files/source" ),

   array( 'pkg'     => 'fontforge',
          'match'   => '^.*$', 
          'replace' => "https://github.com/fontforge/fontforge/releases" ),

   array( 'pkg'     => 'xscreensaver',
          'match'   => '^.*$',
          'replace' => "http://www.jwz.org/xscreensaver/download.html" ),

   array( 'pkg'     => 'transmission',
          'match'   => '^.*$',
          'replace' => "https://www.transmissionbt.com/download" ),

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
  //exec( "curl -L -s -m30 -A Firefox $url", $dir );
  if (  ! preg_match( '/hexchat/', $url )  && 
        ! preg_match( '/tigervnc/', $url ) )
    exec( "curl -L -s -tlsv1 -m30 -A Firefox $url", $dir );
  else
    exec( "wget -q --no-check-certificate -O - $url", $dir );

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
  global $libreoffice;

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
     if ( $package == "seamonkey"   ||
          $package == "firefox"     ||
          $package == "thunderbird" )
     {
         $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
         $position = strrpos( $dirpath, "/" );
         $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
         $position = strrpos( $dirpath, "/" );
         $dirpath  = substr ( $dirpath, 0, $position );  // Up 2
         $dirpath .= "/";

         $dirs = http_get_file( $dirpath );

         if ( $package == "seamonkey" )
            return find_max( $dirs, "/\d\./", "/^.* (\d\.[\.\d]+)$/" );
         else 
            return find_max( $dirs, "/\d\./", "/^.*(\d{2}[\.\d]+)$/" );
     }

    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
     if ( preg_match( "/abiword/", $dirpath ) )
     {
        $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
        $position = strrpos( $dirpath, "/" );
        $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
        $position = strrpos( $dirpath, "/" );
        $dirpath  = substr ( $dirpath, 0, $position );  // Up 2
     }

     if ( $book_index == "gnucash-docs" )
     {
        $dirs     = http_get_file( $dirpath );
        $dir      = find_max( $dirs, "/^\s+\d\./", "/^\s+(\d\.[\d\.]+)$/" );
        $dirpath .= "/$dir/";
     }

     if ( $book_index == "xchat" )
     {
        $dirs     = http_get_file( $dirpath );
        $dir      = find_max( $dirs, "/^\s*\d\./", ":^\s*(\d\.[\d\.]+)/.*$:" );
        $dirpath .= "/$dir/";
     }

     if ( preg_match( "/^libre/", "$package" ) )
     {
        if ( count( $libreoffice ) == 0 )
        {
           $dirs     = http_get_file( $dirpath );
           $dir = find_max( $dirs, "/\d\./", "/^.*;([\d\.]+)\/.*$/" );
           $dirpath = "http://download.documentfoundation.org/libreoffice/src/$dir";           
           $libreoffice = http_get_file( $dirpath );
        }
  
        return find_max( $libreoffice, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
     }

     if ( $package == "gimp" )
     {
         $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
         $dirs = http_get_file( "$dirpath/" );
         $dir = find_max( $dirs, "/v\d\./", "/^.*(v\d\.[\d\.]+).*$/" );
         $dirpath .= "/$dir/";
     }
//echo "dirpath=$dirpath\n";
     $lines = http_get_file( $dirpath );
//print_r($lines);
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

  if ( preg_match( "/inkscape/", "$dirpath" ) )
      return find_max( $lines, "/\d\./", "/^.* (\d[\d\.]+\d)$/" );

  if ( preg_match( "/abiword/", "$dirpath" ) )
      return find_max( $lines, "/^\d/", "/^([\d\.]+).*$/" );

  if ( $package == "balsa" )
      return find_max( $lines, "/^.*balsa-/", "/^.*balsa-([\d\.]+).*$/" );

  if ( $package == "gparted" )
      return find_max( $lines, "/$package/", "/^.*$package-([\d\.]+).*$/" );

  if ( $package == "rox-filer" )
      return find_max( $lines, "/^\s*\d/", "/^\s*(\d\.[\d\.]+).*$/" );

  if ( $package == "tigervnc" )
      return find_max( $lines, "/^\s*\d\./", "/^\s*(\d\.[\d\.]+).*$/" );

  if ( $package == "xdg-utils" )
      return find_max( $lines, "/$package/", "/^$package-(\d\.[\d\.rc-]+).tar.*$/" );

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
     //array( 'pkg'   => 'gimp-help', 
     //       'regex' => "/^.*gimp-help-(\d[\d\.]+).*$/" ),
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

      if ( $basename == "v" ) $basename = 'tigervnc';

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
<title>BLFS Chapters $CHAPTER-38 Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<h1>BLFS Chapters $CHAPTER-38 Package Currency Check</h1>
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
      if ( $pkg == "goffice" ) $name = 'goffice (gtk+2)';

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
   if ( preg_match( "/xorg-server/", $pkg ) ) continue;

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
