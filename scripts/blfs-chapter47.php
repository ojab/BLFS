#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '47';
$CHAPTERS      = 'Chapters 47-49';
$START_PACKAGE = 'mpg123';
$STOP_PACKAGE  = 'simpleburn';

//$current="simpleburn";  // For debugging

$renames = array();

$ignores = array();
$ignores[ 'freetts1' ] = '';
$ignores[ 'Clearlooks' ] = '';

$regex = array();
$regex[ 'mpg123'     ] = "/^.*Download mpg123-(\d[\d\.]+\d).tar.*$/";
$regex[ 'lame'       ] = "/^.*Download (\d[\d\.]+\d) sources.*$/";
$regex[ 'freetts'    ] = "/^.*Download freetts-(\d[\d\.]+\d)-bin.*$/";
$regex[ 'gvolwheel'  ] = "/^.*Download gvolwheel-(\d[\d\.]+\d).tar.*$/";
$regex[ 'cdrdao'     ] = "/^.*Download cdrdao-(\d[\d\.]+\d).tar.*$/";
$regex[ 'pnmixer'    ] = "/^.*pnmixer-(\d[\d\.]+\d).tar.*$/";
$regex[ 'simpleburn' ] = "/^.*SimpleBurn-(\d[\d\.]+\d).*$/";

$url_fix = array (

   array( 'pkg'     => 'mpg123',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/mpg123/files" ),

   array( 'pkg'     => 'lame',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/lame/files" ),

   array( 'pkg'     => 'freetts',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/freetts/files" ),

   array( 'pkg'     => 'amarok',
          'match'   => '^.*$', 
          'replace' => "http://download.kde.org/stable/amarok" ),

   array( 'pkg'     => 'gvolwheel',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/gvolwheel/files" ),

   array( 'pkg'     => 'transcode',
          'match'   => '^.*$', 
          'replace' => "https://bitbucket.org/france/transcode-tcforge/downloads" ),

   array( 'pkg'     => 'vlc',
          'match'   => '^.*$', 
          'replace' => "http://download.videolan.org/vlc/" ),

   array( 'pkg'     => 'xine-ui',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/xine/files/xine-ui" ),

   array( 'pkg'     => 'cdrdao',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/cdrdao/files" ),

   array( 'pkg'     => 'pnmixer',
          'match'   => '^.*$', 
          'replace' => "https://github.com/nicklan/pnmixer/downloads" ),

   array( 'pkg'     => 'k3b',
          'match'   => '^.*$', 
          'replace' => "http://download.kde.org/stable/k3b" ),

   array( 'pkg'     => 'simpleburn',
          'match'   => '^.*$', 
          'replace' => "http://simpleburn.tuxfamily.org/-Download-" ),
);

function get_packages( $package, $dirpath )
{
  global $exceptions;
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;
  if ( $book_index == "mplayer" ) return "daily";

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
     if ( $package == "transcode" )
     {
       # We have to process the stupid javascript to get this to work
       exec( "lynx -dump  $dirpath", $output );
       $max = find_max( $output, "/transcode/", "/^.*transcode-([\d\.]*\d)\.tar.*$/" );
       return $max;
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

  if ( $package == "cdparanoia-III" )
      return find_max( $lines, "/^.*cdparanoia-III-/", 
                               "/^.*cdparanoia-III-([\d\.]+).src.tgz.*$/" );

  if ( $package == "amarok" )
      return find_max( $lines, "/\d\./", "/^.*;([\d\.]+)\/.*$/", TRUE );

  if ( $package == "xine-ui" )
      return find_max( $lines, "/\d\./", "/^\s*([\d\.]+)$/" );

  if ( $package == "dvd+rw-tools" )
      return find_max( $lines, "/dvd\+/", "/^.*dvd\+rw-tools-([\d\.]+).tar.*$/" );

  if ( $package == "vlc" )
      return find_max( $lines, "/\d\.[\d\.]+\//", "/^([\d\.]+)\/.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

function get_pattern( $line )
{
   $match = array(
     array( 'pkg'   => 'mpg123', 
            'regex' => "/^.*mpg123-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'k3b', 
            'regex' => "/^.*k3b-(\d[\d\.]+).*$/" ),

     array( 'pkg'   => 'mplayer', 
            'regex' => "/^.*mplayer-(SVN-r\d+).*$/" ),
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

   echo "book index: $book_index $bver $url \n";

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;
}

html();  // Write html output
?>
