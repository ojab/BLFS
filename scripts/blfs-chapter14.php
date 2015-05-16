#! /usr/bin/php
<?php

$CHAPTER=14;
$START_PACKAGE='dhcpcd';
$STOP_PACKAGE='wireshark';

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

//$regex = array();
$regex[ 'bridge-utils'    ] = "/^.*Download bridge-utils-(\d[\d\.]+\d).tar.*$/";
$regex[ 'nfs-utils'       ] = "/^.*Download nfs-utils-(\d[\d\.]+\d).tar.*$/";
$regex[ 'rpcbind'         ] = "/^.*Download rpcbind-(\d[\d\.]+\d).tar.*$/";
$regex[ 'wireless_tools.' ] = "/^.*latest stable.*version (\d\d).*$/";
//$regex[ 'mod_dnssd'       ] = "/^.*Version ([\d\.]*) is more.*$/";
$regex[ 'traceroute'      ] = "/^.*Download traceroute-([\d\.]*).tar.*$/";
$regex[ 'wicd'            ] = "/^.*Latest version is ([\d\.]*).*$/";

$sf = 'sourceforge.net';

//$current="dhcp";

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
          'replace' => "http://$sf/projects/nfs/files" ),

   array( 'pkg'     => 'NetworkManager',
          'match'   => '^.*$', 
          'replace' => "http://ftp.gnome.org/pub/gnome/sources/NetworkManager" ),

   array( 'pkg'     => 'ntp',
          'match'   => '^.*$', 
          'replace' => "http://www.eecis.udel.edu/~ntp/ntp_spool/ntp4/ntp-4.2" ),

   array( 'pkg'     => 'rpcbind',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/rpcbind/files" ),

   array( 'pkg'     => 'wireless_tools.',
          'match'   => '^.*$', 
          'replace' => "http://www.hpl.hp.com/personal/Jean_Tourrilhes/Linux/Tools.html" ),

   array( 'pkg'     => 'nmap',
          'match'   => '^.*$', 
          'replace' => "http://nmap.org/dist" ),

   array( 'pkg'     => 'traceroute',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/traceroute/files" ),

   array( 'pkg'     => 'wicd',
          'match'   => '^.*$', 
          'replace' => "https://launchpad.net/wicd" ),

   array( 'pkg'     => 'mod_dnssd',
          'match'   => '^.*$', 
          'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/mod_dnssd" ),

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
     list( $major, $minor, $point, $rest ) = explode( ".", $slice . ".0.0" );
     if ( $minor      >= 90  &&  
          $book_index != "dhcpcd" && 
          $book_index != "NetworkManager" ) continue;

     if ( $point >= 90 ) continue;

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
//echo "start curl...$url...";
  exec( "curl -L -s -m30 -A Firefox/22.0 $url", $dir );
//echo "end\n";
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
    if ( $book_index == "bind" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines1 );
      $dir = find_max( $lines1, "/^[\d\.P-]+$/", "/^([\d\.P-]+)$/" );
      $dirpath .= "/$dir/";
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines2 );
      return find_max( $lines2, "/^bind-9/", "/^bind-(\d+[\d\.P-]+).tar.*$/" );
    }

    if ( $book_index == "NetworkManager" )
    {
echo "Still at ftp\n";
      // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $dir = find_max( $lines, "/\d[\d\.]+/", "/^(\d[\d\.]+).*$/" );
      $dirpath .= "/$dir/";
    }

    if ( $book_index == "dhcp"  )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $dir = find_max( $lines, "/\d\.[\d\.P-]+/", "/^(\d\.[\d\.P-]+)$/" );
      $dirpath .= "/$dir/";
    }

    // Get listing
    exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
  }
  else // http
  {
    if ( $book_index == "NetworkManager" )
    {
      // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
//echo "dirpath=$dirpath\n";
      $lines1 = http_get_file( $dirpath );
//print_r($lines1);
      $dir = find_max( $lines1, "/\d[\d\.]+/", "/^\s*(\d[\d\.]+).*$/" );
      $dirpath .= "/$dir/";
//echo "dirpath=$dirpath\n";
    }

    // Customize http directories as needed
    if ( $book_index == "cmake" )
      $dirpath = max_parent( $dirpath, 'v' );
     
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

  if ( $book_index == "net-tools-CVS_" )
    return find_max( $lines, '/net-tools/', '/^.*_(\d+).tar.*$/' );

  if ( $book_index == "ntp" )
  {
    $dir = max_parent( $dirpath, "ntp-" );
    $lines = http_get_file( "$dir" );
    return find_max( $lines, '/ntp-.*tar/', '/^ntp-([\d\.p]+).tar.*$/' );
  }

  if ( $book_index == "whois_" )
    return find_max( $lines, '/whois_/', '/^.*whois_([\d\.]+).tar.*$/' );

  if ( $book_index == "wireshark" )
    return find_even_max( $lines, '/wireshark/', '/^.*wireshark-([\d\.]+).tar.*$/' );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}
//********************************************************
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

      // Use iced tea from patch instead of OpenJDK
      //if ( preg_match( "/icedtea.*add_cacerts/", $file ) )
      //  $file = preg_replace( "/\.patch$/", "", $file ); 

      if ( preg_match( "/patch$/", $file ) ) continue;     // Skip patches

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-
      
      //if ( preg_match( "/^bind/", $file ))
      //{
      //   $v = preg_replace( "/^bind-(\d+)\..*$/", "$1", $file );
      //   $version = "$v-$version";
      //   $basename = "bind";
      //}
      //else 
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
<title>BLFS Chapters $CHAPTER-16 Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<h1>BLFS Chapters $CHAPTER-16 Package Currency Check</h1>
<h2>As of $date GMT</h2>

<table>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
  
      $name = $pkg;
      if ( $pkg == "net-tools-CVS_"  ) $name = 'net-tools';
      if ( $pkg == "wireless_tools." ) $name = 'wireless_tools';
      if ( $pkg == "whois_"          ) $name = 'whois';
      if ( $pkg == "bind"            ) $name = 'bind9';

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

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;

   // Stop at the end of the chapter 
   if ( $book_index == $STOP_PACKAGE ) break; 
}

html();  // Write html output
?>
