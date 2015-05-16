#! /usr/bin/php
<?php

$specials = array();
$specials[ 'Linux-PAM' ] = "documentation";

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

//$current="haveged";

// Special cases
$exceptions = array();
$exceptions[ 'gnutls' ] = "UPDIR=/.*(v\d[\d\.-]*\d).*$/:DOWNDIR=";

$regex = array();
$regex[ 'krb5'     ] = "/^.*Kerberos V5 Release ([\d\.]+).*$/";
$regex[ 'tripwire' ] = "/^.*Download tripwire-([\d\.]+)-src.*$/";
$regex[ 'haveged'  ] = "/^.*haveged-([\d\.]+)\.tar.*$/";

$url_fix = array(
   array( 'match'   => 'ftp.gnu(pg|tls).org', 
          'replace' => 'ftp.heanet.ie/mirrors/ftp.gnupg.org' ),

   array( 'pkg'     => 'haveged',
          'match'   => '^.*$', 
          'replace' => 'http://sourceforge.net/projects/haveged/files' ),

   array( 'pkg'     => 'openssh',
          'match'   => '^ftp', 
          'replace' => 'http' ),

   array( 'pkg'     => 'tripwire',
          'match'   => '^.*$', 
          'replace' => 'http://sourceforge.net/projects/tripwire/files' ),

   array( 'pkg'     => 'stunnel',
          'match'   => '^.*$', 
          'replace' => 'http://mirrors.zerg.biz/stunnel' ),

   array( 'pkg'     => 'sudo',
          'match'   => '^.*$', 
          'replace' => 'http://www.sudo.ws/sudo/dist' ),

   array( 'pkg'     => 'nss',
          'match'   => '^.*$', 
          'replace' => 'http://ftp.mozilla.org/pub/mozilla.org/security/nss/releases' ),

);

function find_max( $lines, $regex_match, $regex_replace )
{
//echo "regex_match=$regex_match; regex_replace=$regex_replace\n";
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
//echo "regex matches line=$line\n";
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );

//echo "slice=$slice\n";
     if ( $slice == $line ) continue; 

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
//print_r($a);
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
    $dirpath  = substr( $dirpath, 6 );           // Remove ftp://
    $dirpath  = rtrim ( $dirpath, "/" );         // Trim any trailing slash
    $position = strpos( $dirpath, "/" );         // Divide at first slash
    $server   = substr( $dirpath, 0, $position );
    $path     = substr( $dirpath, $position );

    $conn = ftp_connect( $server );
    if ( ! isset( $conn ) )
    {
       //echo "No connection\n";
       return -7;
    }

    if ( ! ftp_login( $conn, "anonymous", "a@b" ) )
    {
        //echo "anonymous ftp login failed\n";
        return -8;
    }

    // See if we need special handling
    if ( isset( $exceptions[ $package ] ) )
    {
       $specials = explode( ":", $exceptions[ $package ] );

       foreach ( $specials as $i )
       {
          list( $op, $regexp ) = explode( "=", $i );

          switch ($op)
          {
            case "UPDIR":
              // Remove last dir from $path
              $position = strrpos( $path, "/" );
              $path = substr( $path, 0, $position );

              // Get dir listing
              $lines = ftp_rawlist ($conn, $path);              
//print_r($lines);
              $max   = find_max( $lines, $regexp, $regexp );
//echo "max=$max\n";
              break;

            case "DOWNDIR":
              // Append found directory
              $path .= "/$max";
              break;

            default:
              echo "Error in specials array for $package\n";
              return 0;
              break;
          }
       }
    }

    $lines = ftp_rawlist ($conn, $path);
//print_r($lines);
    ftp_close( $conn );
  }
  else // http
  {
     // Customize http directories as needed
     if ( $package == 'cracklib' )
        $dirpath = "http://sourceforge.net/projects/cracklib/files/$package";

     // Need to get max directory from here
     if ( $package == 'cracklib-words' )
     {
        $dirpath = "http://sourceforge.net/projects/cracklib/files/$package";
        $lines   = http_get_file( $dirpath );
        $max     = find_max( $lines, "/\d{4}-\d{2}-\d{2}/", "/^.*(\d{4}-\d{2}-\d{2}).*$/" );

        if ( $max == 0 ) return -6;
        
        $dirpath .= "/$max";
        $lines = http_get_file( $dirpath );
        return find_max( $lines, "/$package/", "/^.*$package-([\d\.-]*\d)\.gz.*$/" );
     }

     if ( $book_index == "krb5" )
     {
        // Remove last two dirs from $path
        $position = strrpos( $dirpath, "/" );
        $dirpath = substr( $dirpath, 0, $position );
        $position = strrpos( $dirpath, "/" );
        $dirpath = substr( $dirpath, 0, $position );
     }

     $lines = http_get_file( $dirpath );
//print_r( $lines);
//echo "book_index=$book_index; package=$package\n";
     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  if ( isset( $regex[ $package ] ) )
  {
     // Custom search for latest package name
     foreach ( $lines as $l )
     {
        $ver = preg_replace( $regex[ $package ], "$1", $l );
        if ( $ver == $l ) continue;
        
        //if ( $package == 'krb' ) $ver = "5-$ver";
        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $package == "acl" || $package == "attr" ) 
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.-]*\d)\.src.tar.*$/" );

  if ( $package == "libcap2_" )
     return find_max( $lines, "/${package}/", "/^.*${package}([\d\.-]*\d)\.orig.tar.*$/" );

  if ( $book_index == "Linux-PAM1" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)-docs.tar.*$/" );

  if ( $book_index == "openssh" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.p]*\d).tar.*$/" );

  if ( $book_index == "openssl" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.p]*\d.?).tar.*$/" );

  if ( $book_index == "p11-kit" )
     return find_even_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).tar.*$/" );

  if ( $book_index == "shadow_" )
     return find_max( $lines, "/$package/", "/^.*$package([\d\.]*\d).orig.tar.*$/" );

  if ( $book_index == "cracklib" )
     return find_max( $lines, "/\d\.\d+\.\d+/", "/^.*(\d\.\d+\.\d+).*$/" );

  if ( $book_index == "nettle" )
     return find_max( $lines, "/nettle-2/", "/^.*nettle-(2\.\d+\.\d+).tar.*$/" );

  if ( $book_index == "gnupg" )
     return find_max( $lines, "/gnupg-2.0/", "/^.*gnupg-(2\.0\.\d+).tar.*$/" );

  if ( $book_index == "nss" )
  {
     $ver = find_max( $lines, "/NSS_/", "/^.*NSS_(\d[_\d]+)_RTM.*$/" );
     return preg_replace( "/_/", ".", $ver ); // Change underscors to dots
  }

  if ( $book_index == "sudo" )
     return find_max( $lines, "/sudo-\d[\.\d]+/", "/^.*sudo-(\d\.[\d\.]+p?\d?).tar.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array (
      array( 'pkg' => 'p11-kit', 
             'regex' => "/p11-kit.(\d.*\d)\D*$/" ),

      array( 'pkg' => 'openssl', 
             'regex' => "/\D*(\d.*\d.*)$/" ),

      array( 'pkg' => 'krb',     
             'regex' => "/krb5-([\d.]+)-signed$/" ),

       array( 'pkg' => 'krb',     
              'regex' => "/krb5-([\d.]+)-signed$/" ),
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

   $i = 0;

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

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";

      if ( $index == 'openssh1' ) continue;

      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/tripwire/", $line ) ) break;
   }
}

function html()
{
   global $book;
   global $date;
   global $vers;

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
<h1>BLFS Chapter 4 Package Currency Check</h1>
<h2>As of $date GMT</h2>

<table>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
  
      $name = $pkg;
      //if ( $pkg == "gnupg1"     ) $name = 'gnupg2';
      if ( $pkg == "Linux-PAM1" ) $name = 'Linux-PAM-docs';
      //if ( $pkg == "libcap2_"   ) $name = 'libcap2';
      if ( $pkg == "shadow_"   ) $name = 'shadow';

      $f .= "<tr><td>$name</td>";
      $f .= "<td>$v</td>";
      $f .= "<td>${vers[ $pkg ]}</td>";
      $f .= "<td class='center'>$flag</td></tr>\n";
   }

   $f .= "</table>
</body>
</html>\n";

file_put_contents( "/home/bdubbs/public_html/chapter4.html", $f );
}

get_current();  // Get what is in the book

// Get latest version for each package 
foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   echo "book index: $book_index $url\n";
   $v = get_packages( $base, $url );
   $vers[ $book_index ] = $v;

   // Stop at the end of Chapter 4
   if ( $book_index == 'tripwire' ) break; 
}

html();  // Write html output
?>
