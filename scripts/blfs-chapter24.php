#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '24';
$CHAPTERS      = 'Chapter 24';
$START_PACKAGE = 'util-macros';
$STOP_PACKAGE  = 'xinit';

$renames = array();
$ignores = array();

$d = getenv( 'BLFS_DIR' );
$BLFS_DIR = ($d) ? $d : '.';

$freedesk = "http://xorg.freedesktop.org/releases/individual";
$xorg_drv = "ftp://ftp.x.org/pub/individual/driver";

$proto    = array();
$apps     = array();
$libs     = array();
$fonts    = array();
$drivers  = array();

$regex = array();
$regex[ 'libvdpau-va-gl'  ] = "/^.*version (\d[\d\.]+\d).*$/";

//$current="v";   // For debugging

$url_fix = array (

   array( 'pkg'     => 'xf86-input-wacom',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/linuxwacom/files/xf86-input-wacom" ),

   array( 'pkg'     => 'libva',
          'match'   => '^.*$', 
          'replace' => "https://github.com/01org/libva/releases" ),

   array( 'pkg'     => 'intel-vaapi-driver',
          'match'   => '^.*$', 
          'replace' => "https://github.com/01org/intel-vaapi-driver/releases" ),

   array( 'pkg'     => 'libvdpau-va-gl',
          'match'   => '^.*$', 
          'replace' => "https://github.com/i-rinat/libvdpau-va-gl/releases" ),

   array( 'pkg'     => 'xf86-input-libinput',
          'match'   => '^.*$', 
          'replace' => "$xorg_drv" ),

);
function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;
  global $freedesk;
  global $xorg_drv;
  global $proto;
  global $apps;
  global $libs;
  global $fonts;
  global $drivers;

  if ( isset( $current ) && $book_index != "$current" ) return 0;
  if ( $dirpath == "" ) return "";

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
    // MesaLib
    if ( $book_index == "mesa" )
    {
      $lines = http_get_file( "$dirpath/" );
      return find_max( $lines, "/mesa/", "/^.*mesa-(\d+[\d\.]+).tar.*$/" );
    }

     if ( $dirpath == $xorg_drv )
     {
       if ( count( $drivers ) == 0 )
          $drivers    = http_get_file( "$dirpath/" );

       $lines = $drivers;
     }
     else // Get listing
      $lines    = http_get_file( "$dirpath/" );
  }
  else // http
  {
     // Customize http directories as needed

     if ( $dirpath == "$freedesk/proto" )
     {
       if ( count( $proto ) == 0 )
          $proto = http_get_file( $dirpath );

       $lines = $proto;
     }

     else if ( $dirpath == "$freedesk/lib" )
     {
       if ( count( $libs ) == 0 )
          $libs = http_get_file( $dirpath );

       $lines = $libs;
     }

     else if ( $dirpath == "$freedesk/app" )
     {
       if ( count( $apps ) == 0 )
          $apps = http_get_file( $dirpath );

       $lines = $apps;
     }

     else if ( $dirpath == "$freedesk/font" )
     {
       if ( count( $fonts ) == 0 )
          $fonts = http_get_file( $dirpath );

       $lines = $fonts;
     }

     else
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

        //if ( $book_index == "exiv" ) $ver = "2-$ver";
        
        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "xterm" )
    return find_max( $lines, '/xterm-\d+.tgz/', '/^.*xterm-(\d+).tgz.*$/' );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)

  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/", TRUE );
  return $max;
}

function get_pattern( $line )
{
   // Set up specific pattern matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'xf8', 
            'regex' => "/^xf.*-(\d[\d\.]+).*$/" ),
   );

   foreach( $match as $m )
   {
      $pkg = $m[ 'pkg' ];

      if ( preg_match( "/$pkg/", $line ) ) 
         return $m[ 'regex' ];
   }

   return "/\D*(\d.*\d)\D*$/";
}

function insert_subsection( $header, $subdir, $type )
{
   global $book;
   global $freedesk;
   global $BLFS_DIR;

   $xorg_dir = "$BLFS_DIR/x/installing";

   $book[ $header ] = 
      array( 'basename' => "$header",
          'url'      => "",
          'version'  => "" );

   exec( "grep 'ENTITY.*version' $xorg_dir/$subdir", $lines );

   foreach ( $lines as $line )
   {
      $name    = preg_replace( "/^.*ENTITY (.*)\-version.*$/", "$1", $line );
      $version = preg_replace( '/^.*"(.*)".*$/', "$1", $line );

      $book[ $name ] = array( 
         'basename' => $name,
         'url'      => "$freedesk/$type", 
         'version'  => $version,
         'indent'   => $type );
   }
}
 
function get_current_xorg()
{
   global $vers;
   global $book;
   global $freedesk;
   global $start;
   global $WGET_DIR;
   global $START_PACKAGE;
   global $STOP_PACKAGE;

   $wget_file = "$WGET_DIR/wget-list";

   $contents = file_get_contents( $wget_file );
   $wget  = explode( "\n", $contents );

   foreach ( $wget as $line )
   {
      if ( $line == "" ) continue;
      if ( preg_match( "/patch/", $line ) ) continue;     // Skip patches

      $file = basename( $line );
      $url  = dirname ( $line );
      $file = preg_replace( "/\.tar\..z.*$/", "", $file ); // Remove .tar.?z*$
      $file = preg_replace( "/\.tar$/",       "", $file ); // Remove .tar$
      $file = preg_replace( "/\.gz$/",        "", $file ); // Remove .gz$
      $file = preg_replace( "/\.orig$/",      "", $file ); // Remove .orig$
      $file = preg_replace( "/\.src$/",       "", $file ); // Remove .src$
      $file = preg_replace( "/\.tgz$/",       "", $file ); // Remove .tgz$

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      if ( preg_match( "/^xf/", $file ))
         $basename = preg_replace( "/^(.+\-).*$/", "$1", $file );
      else 
         $basename = strstr( $file, $version, true );
      
      $basename = rtrim( $basename, "-" );

      if ( $basename == $START_PACKAGE ) $start = true;
      if ( ! $start ) continue;

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";

      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( $basename == 'glamor-egl' )
      {
         $book[ 'Xorg Drivers' ] = 
           array( 'basename' => "Xorg Drivers",
                  'url'      => "",
                  'version'  => "" );
      }
      
      if ( preg_match( "/^xf86/", $basename ) )
         $book[ $index ][ 'indent' ] = "driver";

      #// If $basename is util-macros, add Xorg Protocol Headers 
      #if ( $basename == 'util-macros' )
      #   insert_subsection( "Xorg Protocol Headers", "x7proto.xml", "proto" );

      // If $basename is libxcb, add Xorg libs
      if ( $basename == 'libxcb' )
         insert_subsection( "Xorg Libraries", "x7lib.xml", "lib" );

      // If $basename is xbitmaps, add Xorg Apps
      if ( $basename == 'xbitmaps' )
         insert_subsection( "Xorg Apps", "x7app.xml", "app" );

      // If $basename is xcursor-themes, add Xorg Fonts
      if ( $basename == 'xcursor-themes' )
         insert_subsection( "Xorg Fonts", "x7font.xml", "font" );

      if ( preg_match( "/$STOP_PACKAGE/", $line ) ) break;
   }
}

get_current_xorg();  // Get what is in the book

// Get latest version for each package 
foreach ( $book as $pkg => $data )
{
   $book_index = $pkg; 

   $base = $data[ 'basename' ];
   $url  = $data[ 'url' ];
   $bver = $data[ 'version' ];

   echo "book index: $book_index $bver $url \n";

   $v = get_packages( $book_index, $url );

   if ( $base == 'Xorg Protocol Headers' ) 
      $vers[ $book_index ] = "";
   else
      $vers[ $book_index ] = $v;
}

html();  // Write html output
?>
