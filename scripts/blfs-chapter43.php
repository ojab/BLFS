#! /usr/bin/php
<?php

$CHAPTER       = 43;
$START_PACKAGE ='cups';
$STOP_PACKAGE  ='xindy';
$start         = false;
$sf            = 'sourceforge.net';

$vers = array();
$book = array();
$book_index = 0;

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

$regex = array();
$regex[ 'ghostscript-fonts-std' ] = 
    "/^.*Download ghostscript-fonts-std-(\d[\d\.]+\d).tar.*$/";
//$regex[ 'gnu-gs-fonts-other' ] = 
//    "/^.*(\d[\d\.]+\d).*misc.*GPL.*$/";
$regex[ 'gutenprint'      ] = "/^.*Download gutenprint-(\d[\d\.]+\d).*$/";
$regex[ 'OpenSP'          ] = "/^.*Download OpenSP-(\d[\d\.]+\d).*$/";
$regex[ 'docbook-xsl-doc' ] = "/^.*Download docbook-xsl-(\d[\d\.]+\d).*$/";
$regex[ 'paps'            ] = "/^.*Download paps-(\d[\d\.]+\d).tar.*$/";
$regex[ 'biblatex'        ] = "/^.*Download biblatex-(\d[\d\.]+\d).*$/";

//$current="gnu-gs-fonts-other";

$url_fix = array (

   array( 'pkg'     => 'cups',
          'match'   => '^.*$', 
          'replace' => "http://www.cups.org/software.php" ),

   array( 'pkg'     => 'cups-filters',
          'match'   => '^.*$', 
          'replace' => "https://www.openprinting.org/download/cups-filters/" ),

   array( 'pkg'     => 'ghostscript-fonts-std',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/gs-fonts/files/gs-fonts" ),

   array( 'pkg'     => 'gnu-gs-fonts-other',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/gs-fonts/files/gs-fonts" ),

   array( 'pkg'     => 'gutenprint',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/gimp-print/files" ),

   array( 'pkg'     => 'OpenSP',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/openjade/files" ),

   array( 'pkg'     => 'openjade',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/openjade/files/openjade" ),

   array( 'pkg'     => 'docbook-xml',
          'match'   => '^.*$', 
          'replace' => "http://www.docbook.org/schemas/4x.html" ),

   array( 'pkg'     => 'docbook-xsl',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/docbook/files/docbook-xsl" ),

   array( 'pkg'     => 'docbook-xsl-doc',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/docbook/files/docbook-xsl-doc" ),

   array( 'pkg'     => 'enscript',
          'match'   => '^.*$', 
          'replace' => "http://ftp.gnu.org/gnu/enscript" ),

   array( 'pkg'     => 'epdfview',
          'match'   => '^.*$', 
          'replace' => "http://anduin.linuxfromscratch.org/sources/BLFS/conglomeration/epdfview" ),

   array( 'pkg'     => 'paps',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/paps/files" ),

#   array( 'pkg'     => 'texlive',
#          'match'   => '^.*$', 
#          'replace' => "http://www.ctan.org/tex-archive/systems/texlive/Source" ),

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
     list( $major, $minor, $micro, $rest ) = explode( ".", $slice . ".0.0.0.0" );
     if ( $micro >= 80 ) continue;
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
  if (  ! preg_match( '/cups/', $url ) )
    exec( "curl -L -s -tlsv1 -m30 -A Firefox $url", $dir );
  else
    exec( "wget -q --no-check-certificate -O - $url", $dir );

  //exec( "curl -L -s -m30 -1 '$url'", $dir );
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
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;
  global $libreoffice;

  //if ( $book_index == "psutils" ) return 0;
  if ( isset( $current ) && $book_index != "$current" ) return 0;

  // These are constant - have not changed in 10 years
  if ( $package == "docbk"             ) return "31";
  if ( $package == "docbook"           ) return "4.5";
  if ( $package == "openjade"          ) return "1.3.2";
  if ( $package == "docbook-dsssl"     ) return "1.79";
  if ( $package == "docbook-dsssl-doc" ) return "1.79";
  
  if ( $package == "install-tl-unx"    ) return "Unversioned";

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
     if ( $package == "texlive" )
     {
         $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
         $position = strrpos( $dirpath, "/" );
         $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
         $dirs     = http_get_file( "$dirpath/" );
//echo "dirpath=$dirpath\n";
//print_r($dirs);
         $dir = find_max( $dirs, "/20\d\d/", "/^.*(20\d\d).*$/" );
         $lines    = http_get_file( "$dirpath/$dir/" );
         return find_max( $lines, "/texlive-/", "/^.*texlive-(\d+).*$/" );
     }

    // Get listing
    #exec( "echo 'ls -1;bye' | ncftp $dirpath/", $lines );
    $lines = http_get_file( "$dirpath/" );
//print_r($lines);
  }
  else // http
  {
     if ( $book_index == "gnu-gs-fonts-other" )
     {
        $dirs = http_get_file( $dirpath );
        $dir  = find_max( $dirs, "/misc.*GPL/", "/^\s*([\d\.]+.*)$/" );
        $dir  = preg_replace( "/ /",  '%20', $dir );
        $dir  = preg_replace( "/\(/", '%28', $dir );
        $dir  = preg_replace( "/\)/", '%29', $dir );
        $dirpath .= "/$dir";
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

        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $package == "cups" )
      return find_max( $lines, "/^.*cups-/", "/^.*cups-([\d\.]+)-source.*$/" );

  if ( $package == "sgml-common" )
      return find_max( $lines, "/sgml-common/", "/^.*sgml-common-([\d\.]+).tgz.*$/" );

  if ( $package == "docbook-xml" )
      return find_max( $lines, "/4\.\d/", "/^.*(4\.\d),.*$/" );

  if ( $package == "docbook-xsl" )
      return find_max( $lines, "/\.\d+/", "/^\s*([\d\.]+)$/" );

  if ( $package == "psutils" )
      return find_max( $lines, "/$package/", "/^.*$package-(p[\d\.]+).tar.*$/" );

  if ( $package == "fop" )
      return find_max( $lines, "/$package/", "/^.*$package-([\d\.]+)-src.tar.*$/" );

  if ( $package == "texlive" )
      return find_max( $lines, "/$package/", "/^.*$package-([\d\.]+)-source.tar.*$/" );

  if ( $package == "mupdf" )
      return find_max( $lines, "/mupdf/", "/^.*$package-([\d\.]+)-source.tar.*$/" );

  if ( $package == "biblatex-biber" )
      return find_max( $lines, "/\.\d+/", "/^\s*([\d\.]+)$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}
//********************************************************
Function get_pattern( $line )
{
   global $start;

   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'a2ps', 
            'regex' => "/^.*a2ps-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'i18n-fonts', 
            'regex' => "/^.*i18n-fonts-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'psutils', 
            'regex' => "/^.*psutils-(p\d[\d\.]+).*$/" ),

     array( 'pkg'   => 'install-tl-unx', 
            'regex' => "/(unversioned)/" ),
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
      $file = preg_replace( "/\..z$/",        "", $file ); // Remove .gz$/xz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      if ( preg_match( "/patch$/", $file         ) ) continue; // Skip patches

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      if ( $version == "install-tl-unx" ) $version = "Unversioned";
      if ( $version == "biblatex-biber" ) 
      {
         $position = strrpos( $url, "/" );
         $version  = substr ( $url, $position + 1 ); 
         $url      = substr ( $url, 0, $position );
         $file    .= $version;
      }

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );

      if ( $version == "Unversioned" ) $basename = "install-tl-unx";

      if ( $basename == $START_PACKAGE ) $start = true;
      if ( ! $start ) continue;

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";
     
      if ( $index == "texlive1" ) continue;

      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/$STOP_PACKAGE$/", $line ) ) break;
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
<title>BLFS Chapters $CHAPTER-48 Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<h1>BLFS Chapters $CHAPTER-48 Package Currency Check</h1>
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
