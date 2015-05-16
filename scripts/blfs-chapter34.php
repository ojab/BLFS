#! /usr/bin/php
<?php

$CHAPTER       = 34;
$START_PACKAGE ='lxmenu-data';
$STOP_PACKAGE  ='lxterminal';
$start         = false;

$freedesk = "http://xorg.freedesktop.org/releases/individual";
$sf       = 'sourceforge.net';
$kde_ver  = "";

$book = array();
$book_index = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
//$regex[ 'midori_' ] = "/^.*Source of Midori.* (\d[\d\.]+\d).*\d\.\d MB.*$/";
//$regex[ 'midori_' ] = "/^.*Source of Midori.* (\d[\d\.]+\d) .*$/";
$regex[ 'libfm'   ] = "/^.*Download libfm-(\d[\d\.]+\d).tar.*$/";
$regex[ 'libfm1'  ] = "/^.*Download libfm-(\d[\d\.]+\d).tar.*$/";

//$current="lxpanel";

$url_fix = array (

   array( 'pkg'     => 'midori_',
          'match'   => '^.*$', 
          'replace' => "http://www.midori-browser.org/download/source" ),

   array( 'pkg'     => 'lxmenu-data',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/lxmenu-data%20%28desktop%20menu%29" ),

   array( 'pkg'     => 'lxde-icon-theme',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXDE%20Icon%20Theme" ),

   array( 'pkg'     => 'menu-cache',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/menu-cache" ),
//http://sourceforge.net/projects/lxde/files/menu-cache

   array( 'pkg'     => 'libfm',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/pcmanfm/files" ),

   array( 'pkg'     => 'libfm1',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/pcmanfm/files" ),
# http://sourceforge.net/projects/pcmanfm/files
   array( 'pkg'     => 'lxpanel',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXPanel%20%28desktop%20panel%29" ),
# http://sourceforge.net/projects/lxde/files/LXPanel%20%28desktop%20panel%29

   array( 'pkg'     => 'lxappearance',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXAppearance" ),

   array( 'pkg'     => 'lxpolkit',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXPolkit" ),

   array( 'pkg'     => 'lxsession',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXSession%20%28session%20manager%29" ),
#http://sourceforge.net/projects/lxde/files/LXSession%20%28session%20manager%29
   array( 'pkg'     => 'lxde-common',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/lxde-common%20%28default%20config%29" ),

   array( 'pkg'     => 'lxappearance-obconf',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXAppearance%20Obconf" ),

   array( 'pkg'     => 'lxinput',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXInput%20%28Kbd%20and%20amp_%20mouse%20config%29/" ),
#http://sourceforge.net/projects/lxde/files
   array( 'pkg'     => 'gpicview',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/GPicView%20%28image%20Viewer%29" ),

   array( 'pkg'     => 'lxrandr',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXRandR%20%28monitor%20config%20tool%29" ),
#http://sourceforge.net/projects/lxde/files/LXRandR%20%28monitor%20config%20tool%29
   array( 'pkg'     => 'lxshortcut',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXShortcut%20%28edit%20app%20shortcut%29" ),

   array( 'pkg'     => 'lxtask',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXTask%20%28task%20manager%29" ),
#http://sourceforge.net/projects/lxde/files/LXTask%20%28task%20manager%29

   array( 'pkg'     => 'lxterminal',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/lxde/files/LXTerminal%20%28terminal%20emulator%29" ),

   array( 'pkg'     => 'pcmanfm',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/pcmanfm/files/PCManFM%20%2B%20Libfm%20%28tarball%20release%29/PCManFM" ),

);
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
function find_max( $lines, $regex_match, $regex_replace )
{
  global $book_index;
  $a = array();
//echo "regex_match=$regex_match; regex_replace=$regex_replace\n";

  foreach ( $lines as $line )
  {
     // Ensure we skip verbosity of NcFTP
     if ( ! preg_match( $regex_match,   $line ) ) continue; 
//echo "line = $line\n";
     if ( preg_match( "/^\s*$/", $line ) ) continue; //else echo "not blank\n";
     if ( preg_match( "/KB/",    $line ) ) continue; //else echo "not KB\n"; 
     if ( preg_match( "/beta/",  $line ) ) continue; //else echo "not beta\n"; 

     // Isolate the version and put in an array
     $slice = preg_replace( $regex_replace, "$1", $line );
//echo "slice= $slice\n";

     // Numbers and whitespace
     if ( "x$slice" == "x$line" && ! preg_match( "/^\d[\d\.]+$/", $slice ) ) continue; 

     // Skip minor versions in the 90s (most of the time)
     list( $major, $minor, $micro, $rest ) = explode( ".", $slice . ".0.0.0.0" );
     if ( $micro >= 80  &&  $book_index != "automoc4" ) continue;
     array_push( $a, $slice );     
  }
//print_r($a);
  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
//print_r($a);
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

     //if ( "x$slice" == "x$line" && ! preg_match( "/^[\d\.]+$/", $slice ) ) continue; 
     if ( "x$slice" == "x$line" ) continue; 
     
     // Skip odd numbered minor versions
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0.0.0" );
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
  exec( "curl -L -s -m30 $url", $dir );
//echo "url=$url\n";
//print_r($dir);
  $s   = implode( "\n", $dir );
  $dir = strip_tags( $s );
//print_r(explode( "\n", $dir ));
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
  global $kde_ver;

  //if ( $book_index == "lxpanel" ||
  //     $book_index == "lxde-common" ) return "manual";

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
//echo "dirpath=$dirpath\n";
  // Check for ftp
  if ( preg_match( "/^ftp/", $dirpath ) ) 
  { 
     // All ftp enties for this chapter
     /* N/A for this chapter
     $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
     $position = strrpos( $dirpath, "/" );
     $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
     $dirs     = http_get_file( "$dirpath/" );
  
     if ( $book_index == "libwnck" ||
          $book_index == "gtksourceview" )
        $dir = find_even_max( $dirs, "/ 2\./", "/^.* (2[\d\.]+)$/" );
     
     else if ( $book_index == "vte" )
        $dir = "0.28";
     
     else if ( $book_index == "libunique" )
        $dir = "1.1";
     
     else
        $dir = find_even_max( $dirs, "/^\d/", "/^([\d\.]+).*$/" );
     
     $dirpath .= "/$dir/";

    // Get listing
    $lines = http_get_file( "$dirpath/" );
    */
  }
  else if ( $book_index != "midori_"      &&
            $book_index != "lxmenu-data"  &&
            $book_index != "menu-cache"   &&
            $book_index != "pcmanfm"      &&
            $book_index != "libfm"        &&
            $book_index != "libfm1"       &&
            $book_index != "lxpanel"      &&
            $book_index != "lxappearance" &&
            $book_index != "lxpolkit"     &&
            $book_index != "lxsession"    &&
            $book_index != "lxde-common"  &&
            $book_index != "gpicview"     &&
            $book_index != "lxinput"      &&
            $book_index != "lxrandr"      &&
            $book_index != "lxshortcut"   &&
            $book_index != "lxtask"       &&
            $book_index != "lxterminal"   &&
            $book_index != "lxappearance-obconf"  &&
            $book_index != "lxde-icon-theme" ) // http
  {
//echo "else1\n";
     // Most http enties for this chapter
     $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
     $position = strrpos( $dirpath, "/" );
     $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
     $dirs     = http_get_file( "$dirpath/" );

     if ( preg_match( "/xf/", $package ) )
       $dir = find_even_max( $dirs, "/^\d/", "/^([\d\.]+)\/.*$/" );
     else
       $dir = find_max     ( $dirs, "/^\d/", "/^([\d\.]+)\/.*$/" );

     $dirpath .= "/$dir";
     $lines    = http_get_file( "$dirpath/" );

     if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  // Others
  else 
  {
//echo "else others\n";
     if ( $book_index == "menu-cache" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/ 1\./", "/^.* (1[\d\.]+).*$/" );
       $dirpath .= "/$dir";
       
       //$d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       //$dirpath .= "/$d";
     }

     if ( $book_index == "lxsession" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXSession \d/", "/^.*(LXSession [\d\.x]+).*$/" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
//echo "lxsession dirpath=$dirpath\n";
     }

     if ( $book_index == "lxde-common" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXDE[ -]Common/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxinput" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXInput/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "gpicview" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/GpicView \d/i", "/^.*(Gpic.* [\d\.]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxrandr" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXRandR \d/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxshortcut" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXShortcut \d/i", "/^.*(LX.* [\d\.]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxtask" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXTask \d/i", "/^.*(LX.* [\d\.x]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxterminal" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXTerminal \d/i", "/^.*(LX.* [\d\.]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "lxpanel" )
     {
       $lines1 = http_get_file( "$dirpath" );
       $dir    = find_max( $lines1, "/LXPanel/i", "/^.*(LXPanel [\d\.x]+).*$/i" );
       $d      = preg_replace( "/ /", "%20", $dir ); // Fix embedded blank
       $dirpath .= "/$d";
     }

     if ( $book_index == "midori_" )
     {
       exec( "curl -L -s -m30 $dirpath", $lines );
     }

//echo "end of else others\n";
     //else
     //{
       $lines = http_get_file( "$dirpath" );
     //}
  }
//print_r($lines);
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

  if ( $book_index == "lxmenu-data" )
  {
    $ver = find_max( $lines, "/$package/", "/^.*$package ([\d\.]*\d).*$/" );
    return $ver;
  }

  if ( $book_index == "lxde-icon-theme" )
    return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d).*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
}
//********************************************************
Function get_pattern( $line )
{
   global $start;

   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
   
     array( 'pkg'   => '.*xfce', 
            'regex' => "/^.*xfce.*-(\d[\d\.]+).*$/" ),

     array( 'pkg'   => 'xfwm', 
            'regex' => "/^.*xfwm4-(\d[\d\.]+).*$/" ),

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
      $file = preg_replace( "/\.gz$/",        "", $file ); // Remove .gz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      if ( preg_match( "/patch$/", $file         ) ) continue; // Skip patches

      //$pattern = get_pattern( $line );
      $pattern = get_pattern( $file );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      if ( $basename == $START_PACKAGE ) $start = true;
      if ( ! $start ) continue;


      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
      
      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/$STOP_PACKAGE/", $line ) ) break;
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
<title>BLFS Chapters $CHAPTER-35 Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<h1>BLFS Chapters $CHAPTER-35 Package Currency Check</h1>
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
      if ( $pkg == "midori_" ) $name = 'midori';
      //if ( $pkg == "midori_" ) $vers[ $pkg ] = 'hidden';

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
