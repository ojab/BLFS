#! /usr/bin/php
<?php

$specials = array();
$specials[ 'Linux-PAM' ] = "documentation";

$book = array();
$book_index = 0;


/*
$dirs = array();
$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d H:i:s" );
*/
// Special cases
$exceptions = array();
$exceptions[ 'gnutls' ] = "UPDIR=/.*(v\d[\d\.-]*\d).*$/:DOWNDIR=";


$regex = array();
$regex[ 'krb'      ] = "/^.*Kerberos V5 Release ([\d\.]+).*$/";
$regex[ 'tripwire' ] = "/^.*Download tripwire-([\d\.]+)-src.*$/";
/*
$regex[ 'expect'   ] = "/^.*Download expect([\d\.]+)\.tar.*$/";
$regex[ 'less'     ] = "/^.*current released version is less-(\d+).*$/";
$regex[ 'mpc'      ] = "/^Version ([\d\.]+).*$/";
$regex[ 'mpfr'     ] = "/^mpfr-([\d\.]+)\.tar.*$/";
$regex[ 'sysvinit' ] = "/^.*sysvinit-([\d\.]+)dsf\.tar.*$/";
$regex[ 'tcl'      ] = "/^.*Download tcl([\d\.]+)-src.*$/";
$regex[ 'tzdata'   ] = "/^.*tzdata([\d]+[a-z]).*$/";
$regex[ 'xz'       ] = "/^.*xz-([\d\.]*\d).*$/";
$regex[ 'zlib'     ] = "/^.*zlib ([\d\.]*\d).*$/";
*/
function find_max( $lines, $regex_match, $regex_replace )
{
  $a = array();
  foreach ( $lines as $line )
  {
     if ( ! preg_match( $regex_match, $line ) ) continue; 
     
     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );
//echo "slice = $slice\n";
     if ( $slice == $line ) continue; 

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
//echo "slice = $slice\n";
     if ( $slice == $line ) continue; 

     // Skip odd numbered minor versions
//echo "slice = $slice\n";
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
   $r = new HttpRequest( $url, HttpRequest::METH_GET );
   $r->setOptions( array('redirect' => 5) );

   try 
   {
     $r->send();
     if ($r->getResponseCode() == 200) 
     {
        $dir = $r->getResponseBody();
     }
     else
     {
        echo "Respose code " . $r->getResponseCode() . "($url)\n";
        echo $r->getResponseBody() . "($url)\n";
        return "-2";
     }
   } 
   catch (HttpException $ex) 
   {
     echo $ex;
     return "-3";
   }

   $dir = strip_tags( $dir );
   return explode( "\n", $dir );  // An array of lines from the url
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

  // Check for ftp
  if ( preg_match( "/^ftp/", $dirpath ) ) 
  { 
    $dirpath  = substr( $dirpath, 6 );           // Remove ftp://
    $dirpath  = rtrim ( $dirpath, "/" );         // Trim any trailing slash
    $position = strpos( $dirpath, "/" );         // Divide at first slash
    $server   = substr( $dirpath, 0, $position );
    $path     = substr( $dirpath, $position );

//echo "server=$server   path=$path\n";

    $conn = ftp_connect( $server );
    if ( ! isset( $conn ) )
    {
       //echo "No connection\n";
       return -7;
    }

    if ( ! ftp_login( $conn, "anonymous", "" ) )
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
              $max   = find_max( $lines, $regexp, $regexp );
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

    //echo "start ftp_rawlist path=$path\n";
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
        //print_r( $lines );
        $max     = find_max( $lines, "/\d{4}-\d{2}-\d{2}/", "/^.*(\d{4}-\d{2}-\d{2}).*$/" );
        if ( $max == 0 ) return -6;
        $dirpath .= "/$max";
        $lines = http_get_file( $dirpath );
        return find_max( $lines, "/$package/", "/^.*$package-([\d\.-]*\d)\.gz.*$/" );
     }

     if ( $book_index == "krb" )
     {
        // Remove last two dirs from $path
        $position = strrpos( $dirpath, "/" );
        $dirpath = substr( $dirpath, 0, $position );
        $position = strrpos( $dirpath, "/" );
        $dirpath = substr( $dirpath, 0, $position );
     }

     $lines = http_get_file( $dirpath );
//print_r( $lines );
     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch


  if ( isset( $regex[ $package ] ) )
  {
     // Custom search for latest package name
//print_r($lines);
     foreach ( $lines as $l )
     {
        $ver = preg_replace( $regex[ $package ], "$1", $l );
        if ( $ver == $l ) continue;
        
        echo "ver=$ver  l=$l\n";

        if ( $package == 'krb' ) $ver = "5-$ver";
        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }
  /*
  if ( $package == "perl" )  // Custom for perl
  {
     $tmp = array();

     foreach ( $lines as $l )
     {
        if ( preg_match( "/sperl/", $l ) ) continue; // Don't want this
        $ver = preg_replace( "/^.*perl-([\d\.]+\d)\.tar.*$/", "$1", $l );
        if ( $ver == $l ) continue;
        list( $s1, $s2, $rest ) = explode( ".", $ver );
        if ( $s2 % 2 == 1 ) continue; // Remove odd minor versions
        array_push( $tmp, $l );
     }

     $lines = $tmp;
  }
  */

  // gnupg-1
  if ( $book_index == "gnupg" )
    return find_max( $lines, "/$package-1/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );

  // Use std for gnupg-2
  if ( $book_index == "gnupg1" )
    $package == "gnupg";

  if ( $package == "acl" || $package == "attr" ) 
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.-]*\d)\.src.tar.*$/" );

  if ( $package == "libcap" )
     return find_max( $lines, "/${package}2_/", "/^.*$package(2_[\d\.-]*\d)\.orig.tar.*$/" );

  if ( $book_index == "Linux-PAM1" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)-docs.tar.*$/" );

  if ( $book_index == "openssh" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.p]*\d).tar.*$/" );

  if ( $book_index == "openssl" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.p]*\d.).tar.*$/" );

  if ( $book_index == "p11-kit" )
     return find_even_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).tar.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}



Function get_pattern( $line )
{
   global $specials;


   $pattern     = "/\D*(\d.*\d)\D*$/";

   if ( preg_match( "/p11-kit/", $line )  ) 
   {
      $pattern = "/p11-kit.(\d.*\d)\D*$/";
   }

   if ( preg_match( "/openssl/", $line )  ) 
   {
      $pattern = "/\D*(\d.*\d.*)$/";
   }

   

   return $pattern;
}

function get_current()
{
   global $dirs;
   global $vers;

   global $book;

   /*
   // Fetech from svn and get wget-list
   $current = array();
   $lfssvn = "svn://svn.linuxfromscratch.org/LFS/trunk";

   $tmpdir = exec( "mktemp -d /tmp/lfscheck.XXXXXX" );
   $cdir   = getcwd();
   chdir( $tmpdir );
   exec ( "svn --quiet export $lfssvn LFS" );
   chdir( $cdir );

   $PAGE       = "$tmpdir/LFS/BOOK/chapter03/chapter03.xml";
   $STYLESHEET = "$tmpdir/LFS/BOOK/stylesheets/wget-list.xsl";

   exec( "xsltproc --xinclude --nonet $STYLESHEET $PAGE", $current );
   exec( "rm -rf $tmpdir" );
   */

   $wget_file = "/home/bdubbs/public_html/blfs-book-xsl/wget-list";

   $contents = file_get_contents( $wget_file );
   $wget  = explode( "\n", $contents );

   //print_r( $current );
   $i = 0;

   foreach ( $wget as $line )
   {
      if ( $i++ > 40 ) break;
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
      //echo "$pattern\n";
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      //echo "$version\t$basename\t$url\n";

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      continue;

      if ( preg_match( "/patch$/", $file ) ) { continue; } // Skip patches

      $file = preg_replace( "/bz2/", '', $file ); // The 2 confusses the regex

      $file        = rtrim( $file );
      $pkg_pattern = "/(\D*).*$/";
      //$pattern     = "/\D*(\d.*\d)\D*$/";
      $pattern     = "/\D*(\d.*\d)\D*$/";
    
      if ( preg_match( "/e2fsprogs/", $file ) )
      {
        $pattern = "/e2\D*(\d.*\d)\D*$/";
        $pkg_pattern = "/(e2\D*).*$/";
      }

      else if ( preg_match( "/tzdata/", $file ) )
      {
        $pattern = "/\D*(\d.*[a-z])\.tar\D*$/";
      }

      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^\d-/", "", $version );  // Remove leading #-

      // Touch up package names
      $pkg_name = preg_replace( $pkg_pattern, "$1", $file );
      $pkg_name = trim( $pkg_name, "-" );

      if ( preg_match( "/bzip|iproute/", $pkg_name ) ) { $pkg_name .= "2"; }
      if ( preg_match( "/^m$/"         , $pkg_name ) ) { $pkg_name .= "4"; }

      $dirs[ $pkg_name ] = dirname( $line );
      $vers[ $pkg_name ] = $version;
   }
   //echo "===============\n";
   //print_r( $book );
}
/*
function mail_to_lfs()
{
   global $date;
   global $vers;
   global $dirs;

   //$to      = "bruce.dubbs@gmail.com";
   $to      = "lfs-book@linuxfromscratch.org";
   $from    = "bdubbs@linuxfromscratch.org";
   $subject = "LFS Package Currency Check - $date GMT";
   $headers = "From: bdubbs@anduin.linuxfromscratch.org";

   $message = "Package         LFS      Upstream  Flag\n\n";

   foreach ( $dirs as $pkg => $dir )
   {
      //if ( $pkg != "tzdata" ) continue;  //debug
      $v = get_packages( $pkg, $dir );

      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";

      // Pad for output
      $pad = "                ";
      $p   = substr( $pkg          . $pad, 0, 15 );
      $l   = substr( $vers[ $pkg ] . $pad, 0,  8 );
      $c   = substr( $v            . $pad, 0, 10 );

      $message .= "$p $l $c $flag\n";
   }

   //echo $message;
   exec ( "echo '$message' | mailx -r $from -s '$subject' $to" );
   //mail( $to, $subject, $message );
}
*/
function html()
{

   global $date;
   global $vers;
   global $dirs;

   echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
                      'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>LFS Package Currency Check - $date</title>
<style type='text/css'>
h1, h2 {
   text-align      : center;
}

table {
   border-width    : 1px;
   border-spacing  : 0px;
   border-style    : outset;
   border-color    : gray;
   border-collapse : separate;
   background-color: white;
   margin          : 0px auto;
}

table th {
   border-width    : 1px;
   padding         : 2px;
   border-style    : inset;
   border-color    : gray;
   background-color: white;
}

table td {
   border-width    : 1px;
   padding         : 2px;
   border-style    : inset;
   border-color    : gray;
   background-color: white;
}
</style>

</head>
<body>
<h1>BLFS Package Currency Check</h1>
<h2>As of $date GMT</h1>

<table>
<tr><th>LFS Package</th> <th>LFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $dirs as $pkg => $dir )
   {
      $v    = get_packages( $pkg, $dir );
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
      echo "<tr><td>$pkg</td> <td>${vers[ $pkg ]}</td> <td>$v</td> <td>$flag</td></tr>\n";
   }

   echo "</table>
</body>
</html>\n";

}

get_current();  // Get what is in the book

$i = 0;

foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 
   $i++;
   if ( $i < 28 ) continue;
echo "book index=$book_index\n";
   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   if ( preg_match( "/ftp.gnu(pg|tls).org/", $url ) )
     $url = preg_replace( "/ftp.gnu(pg|tls).org/", "ftp.heanet.ie/mirrors/ftp.gnupg.org", $url );
   
   if ( $book_index == 'openssh' ) 
     $url = preg_replace( "/^ftp/", "http", $url );

   if ( $book_index == 'tripwire' ) 
     $url = "http://sourceforge.net/projects/tripwire/files/";

   $v = get_packages( $base, $url );

   echo "$base  book: $bver   remote: $v  $url\n";

   if ( $i == 30 ) break; 
}

//mail_to_lfs();
//html();  // Write html output
?>
