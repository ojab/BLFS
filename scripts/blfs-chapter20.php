#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '20';
$START_PACKAGE = 'httpd';
$STOP_PACKAGE  = 'xinetd';

$renames = array();
$renames[ 'sendmail.'           ] = 'sendmail';
$renames[ 'virtuoso-opensource' ] = 'virtuoso';

$ignores = array();

//$current="postfix";

$regex = array();
$regex[ 'vsftpd'          ] = "/^.*vsftpd-(\d[\d\.]+\d) released.*$/";
$regex[ 'db'              ] = "/^.*Berkeley DB (\d[\d\.]+\d).tar.*$/";
$regex[ 'mysql'           ] = "/^.*Current Generally Available Release: (\d[\d\.]+\d).*$/";
$regex[ 'sqlite-doc'      ] = "/^.*sqlite-doc-(\d+).zip.*$/";
$regex[ 'soprano'         ] = "/^.*Download soprano-(\d[\d\.]*).tar.*$/";
$regex[ 'xinetd'          ] = "/^.*xinetd_(\d[\d\.]*).orig.tar.*$/";
$regex[ 'mariadb'         ] = "/^.*Download (\d[\d\.]*\d) Stable.*$/";
$regex[ 'sqlite-autoconf' ] = "/^.*sqlite-autoconf-([\d]+).tar.*$/";
$regex[ 'virtuoso-opensource' ] = "/^.*Download virtuoso-opensource-(\d[\d\.]*).tar.*$/";

$url_fix = array (

   array( 'pkg'     => 'xinetd',
          'match'   => '^.*$', 
          'replace' => "http://packages.debian.org/sid/xinetd" ),

   array( 'pkg'     => 'soprano',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/soprano/files" ),

   array( 'pkg'     => 'virtuoso-opensource',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/virtuoso/files" ),

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
    // bind
    if ( $book_index == "bind1" )
    {
      // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
      $lines1   = http_get_file( "$dirpath/" );
      $dir      = find_max( $lines1, "/\.[\d\.P-]+\s*$/", "/^.* (\d\.[\d\.P-]+)$/" );
      $dirpath .= "/$dir";
      $lines2   = http_get_file( "$dirpath/" );

      return find_max( $lines2, "/bind-9/", "/^.*bind-(\d+[\d\.P-]+).tar.*$/" );
    }

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
    return find_max( $lines, '/proftpd-[a-m\d\.]+.tar.*$/', 
                             '/^.* proftpd-([a-m\d\.]+).tar.*$/' );

  if ( $book_index == "dovecot" )
    return find_max( $lines, '/dovecot-/', '/^.*dovecot-([\d\.]+).tar.*$/' );

  if ( $book_index == "postgresql" )
    return find_max( $lines, '/v\d/', '/^.*v([\d\.]+)\/.*$/' );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.* $package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'bind', 
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

get_current();  // Get what is in the book

// Get latest version for each package 
foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   echo "book index: $book_index $bver $url\n";

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;
}

html();  // Write html output
?>
