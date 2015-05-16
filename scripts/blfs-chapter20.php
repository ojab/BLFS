#! /usr/bin/php
<?php

$CHAPTER=20;
$START_PACKAGE='httpd';
$STOP_PACKAGE='xinetd';

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 'vsftpd'          ] = "/^.*vsftpd-(\d[\d\.]+\d) released.*$/";
$regex[ 'db'              ] = "/^.*Berkeley DB (\d[\d\.]+\d).tar.*$/";
$regex[ 'mysql'           ] = "/^.*Current Generally Available Release: (\d[\d\.]+\d).*$/";
$regex[ 'sqlite-doc'      ] = "/^.*sqlite-doc-(\d+).zip.*$/";
$regex[ 'soprano'         ] = "/^.*Download soprano-(\d[\d\.]*).tar.*$/";
$regex[ 'xinetd'          ] = "/^.*xinetd_(\d[\d\.]*).orig.tar.*$/";
$regex[ 'mariadb'         ] = "/^.*Download (\d[\d\.]*\d) Stable.*$/";
$regex[ 'sqlite-autoconf'     ] = "/^.*sqlite-autoconf-([\d]+).tar.*$/";
$regex[ 'virtuoso-opensource' ] = "/^.*Download virtuoso-opensource-(\d[\d\.]*).tar.*$/";

$sf = 'sourceforge.net';

//$current="postfix";

$url_fix = array (

   array( 'pkg'     => 'xinetd',
          'match'   => '^.*$', 
          'replace' => "http://packages.debian.org/sid/xinetd" ),

   array( 'pkg'     => 'soprano',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/soprano/files" ),

   array( 'pkg'     => 'virtuoso-opensource',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/virtuoso/files" ),

   array( 'pkg'     => 'sqlite-doc',
          'match'   => '^.*$', 
          'replace' => "http://sqlite.org/download.html" ),

   array( 'pkg'     => 'sqlite-autoconf',
          'match'   => '^.*$', 
          'replace' => "http://sqlite.org/download.html" ),

   array( 'pkg'     => 'mysql',
          'match'   => '^.*$', 
          'replace' => "http://dev.mysql.com/downloads" ),

   array( 'pkg'     => 'mariadb',
          'match'   => '^.*$', 
          'replace' => "https://downloads.mariadb.org" ),

   array( 'pkg'     => 'db',
          'match'   => '^.*$', 
          'replace' => "http://www.oracle.com/technetwork/products/berkeleydb/downloads/index.html" ),

   array( 'pkg'     => 'vsftpd',
          'match'   => '^.*$', 
          'replace' => "https://security.appspot.com/vsftpd.html#download" ),

   array( 'pkg'     => 'postgresql',
          'match'   => '^.*$', 
          'replace' => "http://ftp.postgresql.org/pub/source" ),

   array( 'pkg'     => 'postfix',
          'match'   => '^.*$', 
          'replace' => "ftp://ftp.reverse.net/pub/postfix/official" ),

);
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
function find_max( $lines, $regex_match, $regex_replace )
{
  global $book_index;
  $a = array();

  foreach ( $lines as $line )
  {
     // Skip lines that don't match
     if ( ! preg_match( $regex_match, $line ) ) continue; 

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
//echo "url=$url\n";
  exec( "curl -L -s -m30 $url", $dir );
//print_r($dir);
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
    // bind
    if ( $book_index == "bind1" )
    {
      // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
      $lines1   = http_get_file( "$dirpath/" );

      $dir = find_max( $lines1, "/\.[\d\.P-]+\s*$/", "/^.* (\d\.[\d\.P-]+)$/" );
      $dirpath .= "/$dir";
      $lines2   = http_get_file( "$dirpath/" );

      return find_max( $lines2, "/bind-9/", "/^.*bind-(\d+[\d\.P-]+).tar.*$/" );
    }

    // postgresql
/*
    if ( $book_index == "postgresql" )
    {
      // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
      $lines = http_get_file( "$dirpath/" );
      return find_max( $lines, "/v\d+/", "/^.*v(\d+[\d\.]+)$/" );
    }
*/
    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
     // Customize http directories as needed

     if ( $book_index == "dovecot" )
     {
        // Get the max directory and adjust the directory path
        $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
        $position = strrpos( $dirpath, "/" );
        $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
        $lines1   = http_get_file( "$dirpath/" );
        $dir      = find_max( $lines1, "/^\s*\d+/", "/^\s*(\d+[\d\.]+)\/.*$/" );
        $dirpath .= "/$dir/";
     }

     $lines = http_get_file( $dirpath );
//print_r($lines);
     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  if ( isset( $regex[ $package ] ) )
  {
//print_r($lines);
     // Custom search for latest package name
     foreach ( $lines as $l )
     {
        if ( preg_match( '/^\h*$/', $l ) ) continue;
        $ver = preg_replace( $regex[ $package ], "$1", $l );

        if ( $ver == $l ) continue;

        if ( $package == "sqlite-autoconf" ) 
           $ver = preg_replace( "/\./", "0", $ver );

        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "qpopper" )
    return find_max( $lines, '/qpopper[\d\.]+.tar.*$/', '/^.* qpopper([\d\.]+).tar.*$/' );

  if ( $book_index == "sendmail." )
    return find_max( $lines, '/sendmail\.[\d\.]+.tar.*$/', '/^.* sendmail\.([\d\.]+).tar.*$/' );

  if ( $book_index == "openldap" )
    return find_max( $lines, '/openldap-[\d\.]+.tgz.*$/', '/^.* openldap-([\d\.]+).tgz.*$/' );

  if ( $book_index == "proftpd" )
    return find_max( $lines, '/proftpd-[a-m\d\.]+.tar.*$/', '/^.* proftpd-([a-m\d\.]+).tar.*$/' );

  if ( $book_index == "dovecot" )
    return find_max( $lines, '/dovecot-/', '/^.*dovecot-([\d\.]+).tar.*$/' );

  if ( $book_index == "postgresql" )
    return find_max( $lines, '/v\d/', '/^.*v([\d\.]+)\/.*$/' );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.* $package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}
//********************************************************
Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'bind9', 
            'regex' => "/bind-(\d[\d\.P-]+)/" ),

     array( 'pkg'   => 'proftpd', 
            'regex' => "/proftpd-(\d[a-z\d\.]+)/" ),
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

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading dash
      
      $basename = strstr( $file, $version, true );
      
      $basename = rtrim( $basename, "-" );

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );
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
<title>BLFS Chapters $CHAPTER-23 Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<h1>BLFS Chapters $CHAPTER-23 Package Currency Check</h1>
<h2>As of $date GMT</h2>

<table>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
  
      $name = $pkg;
      if ( $pkg == "bind1"               ) $name = 'bind9';
      if ( $pkg == "sendmail."           ) $name = 'sendmail';
      if ( $pkg == "virtuoso-opensource" ) $name = 'virtuoso';

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
