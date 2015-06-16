#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = "4";
$CHAPTERS      = "Chapters 2-4";
$START_PACKAGE = "lsb-release";
$STOP_PACKAGE  = "tripwire";

$renames = array();
$renames[ 'Linux-PAM1' ] = 'Linux-PAM-docs';
$renames[ 'shadow_'    ] = 'shadow';

$ignores = array();
$ignores[ 'openssh1' ] = "";

//$current="p11-kit";   // For debugging

$regex = array();
$regex[ 'krb5'     ] = "/^.*Kerberos V5 Release ([\d\.]+).*$/";
$regex[ 'tripwire' ] = "/^.*Download tripwire-([\d\.]+)-src.*$/";
$regex[ 'haveged'  ] = "/^.*haveged-([\d\.]+)\.tar.*$/";

$url_fix = array(

   array( 'pkg'     => 'gnupg',
          'match'   => '^.*$', 
          'replace' => 'ftp://ftp.gnupg.org/gcrypt/gnupg/' ),

   array( 'pkg'     => 'gnutls',
          'match'   => '^.*$', 
          'replace' => 'ftp://ftp.gnupg.org/gcrypt/gnutls/' ),

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

function get_packages( $package, $dirpath )
{
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
     if ( $package == 'gnutls' )
     {
       $lines1 = http_get_file( $dirpath );
       $dir = find_max( $lines1, "/v\d\.\d/", "/.*(v\d[\d\.-]*\d).*$/" );
       $dirpath .= "$dir/";
     }

     $lines = http_get_file( "$dirpath/" );
     if ( ! is_array( $lines ) ) return $lines;
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
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).tar.*$/" );

  if ( $book_index == "shadow_" )
     return find_max( $lines, "/$package/", "/^.*$package([\d\.]*\d).orig.tar.*$/" );

  if ( $book_index == "cracklib" )
     return find_max( $lines, "/\d\.\d+\.\d+/", "/^.*(\d\.\d+\.\d+).*$/" );

  if ( $book_index == "nettle" )
     return find_max( $lines, "/nettle/", "/^.*nettle-([\.\d]+\d).tar.*$/" );

  if ( $book_index == "gnupg" )
     return find_max( $lines, "/gnupg-2/", "/^.*gnupg-([\.\d]+).tar.*$/" );

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
   // Set up specific pattern matches for extracting book versions
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

get_current();  // Get what is in the book (in include file)

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
}

html();  // Write html output
?>
