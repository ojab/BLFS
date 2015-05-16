#! /usr/bin/php
<?php

$CHAPTER=5;
$START_PACKAGE='fuse';
$STOP_PACKAGE='xfsprogs';

$specials = array();
$specials[ 'Linux-PAM' ] = "documentation";

$book = array();
$book_index = 0;

$vers = array();
//$current="sshfs-fuse";

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
$regex[ 'fuse'     ] = "/^.*Download fuse-([\d\.]+).tar.*$/";
$regex[ 'jfsutils' ] = "/^.*jfsutils release ([\d\.]+) is available.*$/";
$regex[ 'ntfs-3g_ntfsprogs' ] = "/^.*Stable Source Release ([\d\.]+).*$/";

$sf = 'sourceforge.net';

$url_fix[ 0 ] = array( 'pkg'     => 'fuse',
                       'match'   => '^.*$', 
                       'replace' => "http://$sf/projects/fuse/files" );

$url_fix[ 1 ] = array( 'pkg'     => 'jfsutils',
                       'match'   => '^.*$', 
                       'replace' => "http://jfs.$sf/jfs_lr.html" );

$url_fix[ 2 ] = array( 'pkg'     => 'ntfs-3g_ntfsprogs',
                       'match'   => '^.*$', 
                       'replace' => 'http://www.tuxera.com/community/ntfs-3g-download' );

$url_fix[ 3 ] = array( 'pkg'     => 'gptfdisk',
                       'match'   => '^.*$', 
                       'replace' => "http://$sf/projects/gptfdisk/files/gptfdisk/" );

$url_fix[ 4 ] = array( 'pkg'     => 'sshfs-fuse',
                       'match'   => '^.*$', 
                       'replace' => "http://$sf/projects/fuse/files/sshfs-fuse/" );

$url_fix[ 5 ] = array( 'pkg'     => 'reiserfsprogs',
                       'match'   => '^.*$', 
                       'replace' => "https://www.kernel.org/pub/linux/kernel/people/jeffm/reiserfsprogs/" );

function find_max( $lines, $regex_match, $regex_replace )
{
  $a = array();
//echo "regex_match=$regex_match; regex_replace=$regex_replace\n";     
  foreach ( $lines as $line )
  {
//echo "line=$line\n";     
     if ( ! preg_match( $regex_match, $line ) ) continue; 
//echo "match\n";     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );
//echo "slice=$slice\n";     

     if ( "x$slice" == "x$line" ) continue;  // Numbers and whitespace 

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

     if ( $slice == $line ) continue; 

     // Skip odd numbered minor versions
     list( $major, $minor ) = explode( ".", $slice . ".0", 2 );
     if ( $minor % 2 == 1 ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

function http_get_file( $url )
{
  exec( "curl -L -s -m30 $url", $dir );
//echo "printing url results";
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
  $regex_replace = "#^.*(${prefix}[\d\.]+)/.*$#";
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
    exec( "echo 'ls -1' | ncftp $dirpath", $lines );
  }
  else // http
  {
     // Customize http directories as needed
     $lines = http_get_file( $dirpath );

     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  if ( isset( $regex[ $package ] ) )
  {
     // Custom search for latest package name
     foreach ( $lines as $l )
     {
        $ver = preg_replace( $regex[ $package ], "$1", $l );
        if ( $ver == $l ) continue;
        
        if ( $package == 'krb' ) $ver = "5-$ver";
        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "LVM2." )
    return find_max( $lines, "/$package/", "/^.*$package([\d\.]*\d).tgz.*$/" );

  if ( $book_index == "gptfdisk" )
  {
    $dir = find_max( $lines, '/\d\./', '/^\s*([\d\.]+)\s*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
  }

  if ( $book_index == "mdadm" )
  {
    $max = find_max( $lines, '/mdadm-[\d\.]+/', '/^.*mdadm-([\d\.]+).tar.*$/' );
    return $max;
  }

  if ( $book_index == "reiserfsprogs" )
  {
    $max = find_max( $lines, '/^v[\d\.]+.*$/', '/^v([\d\.]+).*$/' );
    return $max;
  }

  if ( $book_index == "sshfs-fuse" )
  {
    $dir = find_max( $lines, '/\d\./', '/^\s*([\d\.]+)\s*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
  }

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array( 
      array( 'pkg'   => 'ntfs', 
             'regex' => "/ntfs-3g_ntfsprogs-(\d.*\d)\D*$/" ),

      array( 'pkg'   => 'LVM2', 
             'regex' => "/LVM2.(\d.*\d)\D*$/" ),
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
      if ( preg_match( "/patch$/", $line ) ) continue;     // Skip patches

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

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/xfsprogs/", $line ) ) break;
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
  
      $name = $pkg;
      if ( $pkg == "gnupg1"     ) $name = 'gnupg2';
      if ( $pkg == "Linux-PAM1" ) $name = 'Linux-PAM-docs';
      if ( $pkg == "LVM2."      ) $name = 'LVM2';

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

   $v = get_packages( $base, $url );

   $vers[ $book_index ] = $v;

   // Stop at the end of the chapter 
   if ( $book_index == $STOP_PACKAGE ) break; 
}

html();  // Write html output
?>
