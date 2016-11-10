#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '14';
$CHAPTERS      = 'Chapters 14-16';
$START_PACKAGE = 'dhcpcd';
$STOP_PACKAGE  = 'wireshark';

$renames = array();
//$renames[ 'net-tools-CVS_'  ] = 'net-tools';
$renames[ 'wireless_tools.' ] = 'wireless_tools';
$renames[ 'whois_'          ] = 'whois';
$renames[ 'bind'            ] = 'bind9';

$ignores = array();

//$current="bridge-utils";

$regex[ 'nfs-utils'       ] = "/^.*Download nfs-utils-(\d[\d\.]+\d).tar.*$/";
$regex[ 'rpcbind'         ] = "/^.*Download rpcbind-(\d[\d\.]+\d).tar.*$/";
$regex[ 'wireless_tools.' ] = "/^.*latest stable.*version (\d\d).*$/";
$regex[ 'traceroute'      ] = "/^.*Download traceroute-([\d\.]*).tar.*$/";
$regex[ 'wicd'            ] = "/^.*Latest version is ([\d\.]*).*$/";

$url_fix = array (

   array( 'pkg'     => 'rsync',
          'match'   => '^.*$', 
          'replace' => "https://download.samba.org/pub/rsync" ),

   array( 'pkg'     => 'cifs-utils',
          'match'   => '^.*$', 
          'replace' => "https://download.samba.org/pub/linux-cifs/cifs-utils" ),

   array( 'pkg'     => 'samba',
          'match'   => '^.*$', 
          'replace' => "https://download.samba.org/pub/samba/stable" ),

   array( 'pkg'     => 'dhcpcd',
          'match'   => '^.*$', 
          'replace' => "http://roy.marples.name/downloads/dhcpcd" ),

   array( 'pkg'     => 'nfs-utils',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/nfs/files" ),

   array( 'pkg'     => 'NetworkManager',
          'match'   => '^.*$', 
          'replace' => "http://ftp.gnome.org/pub/gnome/sources/NetworkManager" ),

   array( 'pkg'     => 'ntp',
          'match'   => '^.*$', 
          'replace' => "http://www.eecis.udel.edu/~ntp/ntp_spool/ntp4/ntp-4.2" ),

   array( 'pkg'     => 'rpcbind',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/rpcbind/files" ),

   array( 'pkg'     => 'wireless_tools.',
          'match'   => '^.*$', 
          'replace' => "http://www.hpl.hp.com/personal/Jean_Tourrilhes/Linux/Tools.html" ),

   array( 'pkg'     => 'avahi',
          'match'   => '^.*$', 
          'replace' => "https://github.com/lathiat/avahi/releases" ),

   array( 'pkg'     => 'nmap',
          'match'   => '^.*$', 
          'replace' => "http://nmap.org/dist" ),

   array( 'pkg'     => 'traceroute',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/traceroute/files" ),

   array( 'pkg'     => 'wicd',
          'match'   => '^.*$', 
          'replace' => "https://launchpad.net/wicd" ),

   array( 'pkg'     => 'mod_dnssd',
          'match'   => '^.*$', 
          'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/mod_dnssd" ),

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
    if ( $book_index == "bind" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
      $lines1   = http_get_file( "$dirpath/" );
      $dir      = find_max( $lines1, "/\d$/", "/^.* ([\d\.P\-]+)$/" );
      $dirpath .= "/$dir/";
      $lines2   = http_get_file( $dirpath );
      return find_max( $lines2, "/bind-/", "/^.*bind-(\d+[\d\.P\-]+).tar.*$/" );
    }

    if ( $book_index == "dhcp"  )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( "$dirpath/" );
      $dir      = find_max( $lines, "/\d$/", "/^.* (\d\.[\d\.P\-]+)$/" );
      $dirpath .= "/$dir/";
    }

    // Get listing
    $lines    = http_get_file( "$dirpath/" );
  }
  else // http
  {
    if ( $book_index == "NetworkManager" )
    {
      // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $lines1   = http_get_file( $dirpath );
      $dir      = find_even_max( $lines1, "/\d[\d\.]+/", "/^\s*(\d[\d\.]+).*$/" );
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

        if ( $book_index == "exiv" ) $ver = "2-$ver";
        
        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "dhcp" )
    return find_max( $lines, '/dhcp/', '/^.*dhcp-([\d\.P-]+).tar.*$/' );

  if ( $book_index == "ncftp" )
    return find_max( $lines, '/ncftp/', '/^.*ncftp-([\d\.]+)-src.tar.*$/' );

  if ( $book_index == "net-tools-CVS" )
    return find_max( $lines, '/net-tools/', '/^.*_(\d+).tar.*$/' );

  if ( $book_index == "ntp" )
  {
    $dir   = max_parent( $dirpath, "ntp-" );
    $lines = http_get_file( "$dir" );
    return find_max( $lines, '/ntp-.*tar/', '/^ntp-([\d\.p]+).tar.*$/' );
  }

  if ( $book_index == "whois" )
    return find_max( $lines, '/whois_/', '/^.*whois_([\d\.]+).tar.*$/' );

  if ( $book_index == "wireshark" )
    return find_even_max( $lines, '/wireshark/', '/^.*wireshark-([\d\.]+).tar.*$/' );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     //array( 'pkg'   => 'bind', 
     //       'regex' => "/bind(\d+\/[\d\.]+)/" ),
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
