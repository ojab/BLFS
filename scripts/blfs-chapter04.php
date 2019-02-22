#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = "4";
$CHAPTERS      = "Chapters 2-4";
$START_PACKAGE = "lsb-release";
$STOP_PACKAGE  = "volume_key";

$renames = array();
$renames[ 'Linux-PAM1' ] = 'Linux-PAM-docs';
$renames[ 'openssl'    ] = 'openssl-1.0';
//$renames[ 'openssl1'   ] = 'openssl-1.0';

$ignores = array();
$ignores[ 'openssh1'       ] = "";
$ignores[ 'cracklib-words' ] = "";
$ignores[ 'lsb-release'    ] = ""; // Has not changed in 12 years

//$current="gnutls";   // For debugging

$regex = array();
$regex[ 'lsb-release'    ] = "/^.*lsb-release_([\d\.]+).*_all\.deb*$/";
$regex[ 'krb5'           ] = "/^.*Kerberos V5 Release ([\d\.]+).*$/";
$regex[ 'haveged'        ] = "/^.*haveged-([\d\.]+)\.tar.*$/";
//$regex[ 'cracklib'       ] = "/^.*cracklib-([\d\.]+)\.tar.*$/";
//$regex[ 'cracklib-words' ] = "/^.*cracklib-words-([\d\.]+)\.bz2.*$/";
$regex[ 'ConsoleKit2'    ] = "/^.*ConsoleKit2-([\d\.]+)\.tar.*$/";
$regex[ 'make-ca'        ] = "/^.*make-ca-(\d[\d\.]+\d).*$/";

$url_fix = array(

   array( 'pkg'     => 'lsb-release',
          'match'   => '^.*$',
          'replace' => 'https://sourceforge.net/projects/lsb/files/' ),

   array( 'pkg'     => 'cyrus-sasl',
          'match'   => '^.*$',
          'replace' => 'https://github.com/cyrusimap/cyrus-sasl/releases/' ),

   array( 'pkg'     => 'liboauth',
          'match'   => '^.*$',
          'replace' => 'https://sourceforge.net/projects/liboauth/files/' ),

   array( 'pkg'     => 'ConsoleKit2',
          'match'   => '^.*$',
          'replace' => 'https://github.com/ConsoleKit2/ConsoleKit2/releases' ),

   array( 'pkg'     => 'cracklib',
          'match'   => '^.*$',
          'replace' => 'https://github.com/cracklib/cracklib/releases' ),

   array( 'pkg'     => 'cracklib-words',
          'match'   => '^.*$',
          'replace' => 'https://github.com/cracklib/cracklib/releases' ),

   array( 'pkg'     => 'gnupg',
          'match'   => '^.*$',
          'replace' => 'ftp://ftp.gnupg.org/gcrypt/gnupg/' ),

   array( 'pkg'     => 'gnutls',
          'match'   => '^.*$',
          'replace' => 'http://www.gnupg.org/ftp/gcrypt/gnutls/' ),

   array( 'pkg'     => 'haveged',
          'match'   => '^.*$',
          'replace' => 'http://sourceforge.net/projects/haveged/files' ),

   array( 'pkg'     => 'krb5',
          'match'   => '^.*$',
          'replace' => 'http://web.mit.edu/kerberos/www/dist/' ),

   array( 'pkg'     => 'openssh',
          'match'   => '^ftp',
          'replace' => 'http' ),

   array( 'pkg'     => 'p11-kit',
          'match'   => '^.*$',
          'replace' => 'https://github.com/p11-glue/p11-kit/releases' ),

   array( 'pkg'     => 'libpwquality',
          'match'   => '^.*$',
          'replace' => 'https://github.com/libpwquality/libpwquality/releases' ),

   array( 'pkg'     => 'tripwire',
          'match'   => '^.*$',
          'replace' => 'https://github.com/Tripwire/tripwire-open-source/releases' ),

   array( 'pkg'     => 'shadow',
          'match'   => '^.*$',
          'replace' => 'https://github.com/shadow-maint/shadow/releases' ),

   array( 'pkg'     => 'sudo',
          'match'   => '^.*$',
          'replace' => 'http://www.sudo.ws/sudo/dist' ),

   array( 'pkg'     => 'nss',
          'match'   => '^.*$',
          'replace' => 'https://ftp.mozilla.org/pub/security/nss/releases/' ),

   array( 'pkg'     => 'make-ca',
          'match'   => '^.*$',
          'replace' => 'https://github.com/djlucas/make-ca/releases' ),

   array( 'pkg'     => 'volume_key',
          'match'   => '^.*$',
          'replace' => 'https://github.com/felixonmars/volume_key/releases' ),
);

function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

  if ( $package == "polkit" ) return "manual";

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
     $lines = http_get_file( "$dirpath/" );
     if ( ! is_array( $lines ) ) return $lines;
  }
  else // http
  {
     if ( $package == 'gnutls' )
     {
       $lines1 = http_get_file( $dirpath );
       $dir = find_max( $lines1, "/v\d\.\d/", "/.*v(\d[\d\.-]*\d).*$/" );
       $dirpath .= "v$dir/";
     }

     // Customize http directories as needed
     //if ( $book_index == "krb5" )
     //{
     //   // Remove last two dirs from $path
     //   $position = strrpos( $dirpath, "/" );
     //   $dirpath = substr( $dirpath, 0, $position );
     //   $position = strrpos( $dirpath, "/" );
     //   $dirpath = substr( $dirpath, 0, $position );
     //}

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

  if ( $package == "cyrus-sasl" )
     return find_max( $lines, "/${package}/", "/^.*${package}-([\d\.]+\d).tar.*$/" );

  if ( $package == "libcap2_" )
     return find_max( $lines, "/${package}/", "/^.*${package}([\d\.-]*\d)\.orig.tar.*$/" );

  if ( $book_index == "Linux-PAM1" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)-docs.tar.*$/" );

  if ( $book_index == "openssh" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.p]*\d).tar.*$/" );

//  if ( $book_index == "openssl" )
//     return find_max( $lines, "/$package/", "/^.*$package-([\d\.p]*\d.?).tar.*$/" );

  if ( $book_index == "openssl" )
     return find_max( $lines, "/$package/", "/^.*$package-(1.0[\d\.p]*\d.?).tar.*$/" );

  if ( $book_index == "p11-kit" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).tar.*$/" );

  if ( $book_index == "shadow" )
     return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).tar.*$/" );

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

  if ( $book_index == "tripwire" )
     return find_max( $lines, "/Open Source/", "/^.*Tripwire ([\.\d]+).*$/" );

  if ( $book_index == "sudo" )
     return find_max( $lines, "/sudo-\d[\.\d]+/", "/^.*sudo-(\d\.[\d\.]+p?\d?).tar.*$/" );

  if ( $book_index == "volume_key" )
     return find_max( $lines, "/volume_key/", "/^.*volume_key-(\d\.[\d\.]+\d).*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific pattern matches for extracting book versions
   $match = array (
      array( 'pkg' => 'ConsoleKit',
             'regex' => "/ConsoleKit2-([\d.]+)$/"),

      array( 'pkg' => 'p11-kit',
             'regex' => "/p11-kit.(\d.*\d)\D*$/" ),

      array( 'pkg' => 'openssl',
             'regex' => "/\D*(\d.*\d.*)$/" ),

//      array( 'pkg' => 'openssl1',
//             'regex' => "/\D*(\d.*\d.*)$/" ),

      array( 'pkg' => 'krb',
             'regex' => "/krb5-([\d.]+)$/" ),
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
