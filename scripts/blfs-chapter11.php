#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '11';
$CHAPTERS      = 'Chapter 11';
$START_PACKAGE = 'asciidoc';
$STOP_PACKAGE  = 'xdg-user-dirs';

$renames = array();
$renames[ 'lsof_'        ] = 'lsof';
$renames[ 'rep-gtk_'     ] = 'rep-gtk';
$renames[ 'ImageMagick'  ] = 'ImageMagick6';
$renames[ 'ImageMagick1' ] = 'ImageMagick7';

$ignores = array();

$regex = array();
$regex[ 'intltool'      ] = "/^.*Latest version is (\d[\d\.]+\d).*$/";
$regex[ 'xscreensaver'  ] = "/^.*xscreensaver-(\d[\d\.]+\d).tar.*$/";

//$current="graphviz";  // For debugging

$url_fix = array (
   array( 'pkg'     => 'bogofilter',
          'match'   => '^.*$',
          'replace' => "https://sourceforge.net/projects/bogofilter/files" ),

   array( 'pkg'     => 'asciidoc',
          'match'   => '^.*$',
          'replace' => "https://sourceforge.net/projects/asciidoc/files" ),

   array( 'pkg'     => 'chrpath',
          'match'   => '^.*$',
          'replace' => "https://alioth.debian.org/projects/chrpath" ),

   array( //'pkg'     => 'gnome',
          'match'   => '^ftp:\/\/ftp.gnome',
          'replace' => "http://ftp.gnome" ),

   array( 'pkg'     => 'iso-codes',
          'match'   => '^.*$', 
          'replace' => "https://salsa.debian.org/iso-codes-team/iso-codes/tags" ),

   array( 'pkg'     => 'intltool',
          'match'   => '^.*$', 
          'replace' => "https://launchpad.net/intltool/trunk" ),

   array( 'pkg'     => 'lsof',
          'match'   => '^.*$', 
          //'replace' => "ftp://lsof.itap.purdue.edu/pub/tools/unix/lsof" ),
          'replace' => "ftp://ftp.fu-berlin.de/pub/unix/tools/lsof" ),

   array( 'pkg'     => 'highlight',
          'match'   => '^.*$', 
          'replace' => "http://www.andre-simon.de/zip/download.php" ),

   array( 'pkg'     => 'ibus',
          'match'   => '^.*$', 
          'replace' => "https://github.com/ibus/ibus/releases" ),

   array( 'pkg'     => 'shared-mime-info',
          'match'   => '^.*$', 
          'replace' => "https://gitlab.freedesktop.org/xdg/shared-mime-info/tags" ),

   array( 'pkg'     => 'tidy-html5',
          'match'   => '^.*$', 
          'replace' => "https://github.com/htacg/tidy-html5/releases" ),

   array( 'pkg'     => 'unixODBC',
          'match'   => '^.*$', 
          'replace' => "ftp://ftp.unixodbc.org/pub/unixODBC" ),

   array( 'pkg'     => 'xscreensaver',
          'match'   => '^.*$', 
          'replace' => "http://www.jwz.org/xscreensaver/download.html" ),

   array( 'pkg'     => 'graphviz',
          'match'   => '^.*$', 
          'replace' => "https://www2.graphviz.org/Packages/stable/portable_source" ),
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

    // Get listing
    $lines = http_get_file( "$dirpath/" );

  }
  else // http
  {
    // glib type packages
    if ( $book_index == "gtk-doc" )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( $dirpath );
      $dir      = find_max( $lines, '/^\s+[\d\.]+\//', '/^\s+([\d\.]+)\/.*$/' );
      $dirpath .= "/$dir/";
    }

    // babl and similar
    if ( $book_index == "rarian" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( $dirpath );
      $dir      = find_max( $lines, '/^\s+[\d\.]+\//', '/^\s+([\d\.]+)\/.*$/' );
      $dirpath .= "/$dir/";
    }
    // Customize http directories as needed
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

  if ( $book_index == "asciidoc" )
    return find_max( $lines, '/Latest/', '/^.*asciidoc-([\d\.]+).tar.*$/' );

  if ( $book_index == "chrpath" )
    return find_max( $lines, '/0\./', '/^\s*([\d\.]+).*$/' );

  if ( $book_index == "hd2u" )
    return find_max( $lines, '/hd2u/', '/^.*hd2u-([\d\.]+).tgz.*$/' );

  if ( $book_index == "ImageMagick" )
    return find_max( $lines, '/Magick-6/', '/^.*ImageMagick-([\d\.-]+).tar.*$/' );

  if ( $book_index == "ImageMagick1" )
    return find_max( $lines, '/Magick/', '/^.*ImageMagick-([\d\.-]+).tar.*$/' );

  if ( $book_index == "js" )
    return find_max( $lines, '/js/', '/^.*js(\d[\d\.-]+\d).tar.*$/' );

  if ( $book_index == "lsof" )
    return find_max( $lines, '/lsof_/', '/^.*lsof_([\d\.]+).tar.*$/' );

  if ( $book_index == "tree" )
    return find_max( $lines, '/tree/', '/^.*tree-([\d\.]+).tgz.*$/' );

  if ( $book_index == "tidy-html5" )
    return find_max( $lines, '/tidy/', '/^.*tidy-([\d\.]+).*$/' );
    //return find_max( $lines, '/tidy/', '/^.*tidy-html5-([\d\.]+).tar.*$/' );

  if ( $book_index == "rep-gtk" )
    return find_max( $lines, '/rep-gtk/', '/^.*rep-gtk[_-]([\d\.]+).tar.*$/' );

  if ( $book_index == "shared-mime-info" )
    return find_max( $lines, '/shared-mime-info/', '/^.*shared-mime-info ([\d\.]+).*$/' );

  if ( $book_index == "telepathy-mission-control" )
    return find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/", TRUE );

  if ( $book_index == "graphviz" )
    return find_max( $lines, "/graphviz-/", "/^.*graphviz-(\d+\.\d+\.\d+)\.tar.*$/" );

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
     array( 'pkg'   => 'hd2u', 
            'regex' => "/hd2u-([\d\.]+)/" ),

     array( 'pkg'   => 'tidy-html', 
            'regex' => "/tidy-html5-([\d\.]+).*/" ),
   );

   foreach( $match as $m )
   {
      $pkg = $m[ 'pkg' ];
      if ( preg_match( "/$pkg/", $line ) ) 
         return $m[ 'regex' ];
   }
/*
   // Workaround because graphviz does not have version in tarball name
   if ( preg_match( "/graphviz/", $line) )
   {
      $url = 'http://www.linuxfromscratch.org/blfs/view/svn/general/genutils.html';
      $f   = http_get_file( $url );
      $p   = find_max( $f, '/Graphviz/', '/^.*Graphviz-([\d\.]+).*$/' );
      return "$p";
   }
*/
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
