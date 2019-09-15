#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '25';
$CHAPTERS      = 'Chapter 25';
$START_PACKAGE = 'atk';
$STOP_PACKAGE  = 'webkitgtk';

$renames = array();
$renames[ 'gtk+'                           ] = 'gtk+2';
$renames[ 'gtk+1'                          ] = 'gtk+3';
$renames[ 'gtkmm'                          ] = 'gtkmm2';
$renames[ 'gtkmm1'                         ] = 'gtkmm3';
$renames[ 'gtksourceview'                  ] = 'gtksourceview3';
$renames[ 'gtksourceview1'                 ] = 'gtksourceview4';
$renames[ 'qt-everywhere-opensource-src'   ] = 'qt5';
$renames[ 'qt-everywhere-opensource'       ] = 'qt5';
$renames[ 'qtwebengine-everywhere-src'     ] = 'qtwebengine';
$renames[ 'qtwebkit-opensource-src'        ] = 'qtwebkit';

$ignores = array();

//$current="libepoxy"; // For debugging

$regex = array();

$url_fix = array (
   array( 'pkg'     => 'fltk',
          'match'   => '^.*$', 
          'replace' => "https://www.fltk.org/software.php" ),

   array( 'pkg'     => 'freeglut',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/freeglut/files" ),

   array( 'pkg'     => 'imlib2',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/enlightenment/files/imlib2-src" ),

   array( 'pkg'     => 'glew',
          'match'   => '^.*$', 
          'replace' => "https://github.com/nigels-com/glew/releases" ),

   array( 'pkg'     => 'keybinder',
          'match'   => '^.*$', 
          'replace' => "https://github.com/kupferlauncher/keybinder/releases" ),

   array( 'pkg'     => 'libepoxy',
          'match'   => '^.*$', 
          'replace' => "https://github.com/anholt/libepoxy/releases" ),

   array( 'pkg'     => 'QScintilla_gpl',
          'match'   => '^.*$', 
          'replace' => "https://sourceforge.net/projects/pyqt/files/QScintilla2" ),
);

function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;

  if ( $book_index == "qtwebengine-everywhere-src" ) return "manual";

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
    // glib type packages
    if ( $book_index == "atk"            ||
         $book_index == "atkmm"          ||
         $book_index == "at-spi2-core"   ||
         $book_index == "at-spi2-atk"    ||
         $book_index == "cogl"           ||
         $book_index == "clutter"        ||
         $book_index == "clutter-gtk"    ||
         $book_index == "gdk-pixbuf"     ||
         $book_index == "gtksourceview"  ||
         $book_index == "gtksourceview1" ||
         $book_index == "gtk+1"          ||
         $book_index == "gtk-engines"    ||
         $book_index == "gtk-vnc"        ||
         $book_index == "libglade"       ||
         $book_index == "pango"          ||
         $book_index == "pangomm"        ||
         $book_index == "gtkmm1"         ||
         $book_index == "goffice"         )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $dirlines = http_get_file( "$dirpath/" );

      if ( $book_index == "gtk-vnc" ) 
        $dir   = find_max( $dirlines, '/\d$/', '/^.* ([\d\.]+)$/' );
      elseif ( $book_index == "gtksourceview" )
        $dir   = find_even_max( $dirlines, '/3\.\d/', '/^.*(3[\d\.]+)$/', TRUE );
      else
        $dir   = find_even_max( $dirlines, '/\d$/', '/^.* ([\d\.]+)$/', TRUE );

      $dirpath .= "/$dir/";
    }
 
    if ( $book_index == "gtk+"    || 
         $book_index == "gtkmm"    )
      $dirpath .= "/";

    if ( $book_index == "libnotify"     ||
         $book_index == "libxklavier"   )
    {
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $dirlines = http_get_file( "$dirpath/" );

      $dir      = find_max( $dirlines, '/\d$/', '/^.* ([\d\.]+)$/' );
      $dirpath .= "/$dir/";
    }

    if ( $book_index == "firefox" )
    {
      // Customize http directories as needed
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up 1
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up 2
    }

    // Get listing
    $lines = http_get_file( "$dirpath/" );
  }
  else // http
  {
    /*
    if ( $book_index == "qt-everywhere-opensource-src" )
    {
      // Customize http directories as needed
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up 1
      $position = strrpos( $dirpath, "/" );
      $dirlines = http_get_file( $dirpath );
      $dir      = find_max( $dirlines, '/^[\d\.]+.*$/', '/^([\d\.]+).*$/' );
      $dirpath .= "/$dir";
    }
    */
    $qt_len = strlen( "qt-everywhere" );
    if ( substr($book_index, 0, $qt_len)  == "qt-everywhere" )
    {
      // Customize http directories as needed
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up 1
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up 2
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ); // Up 3
      $dirlines = http_get_file( $dirpath );
      $dir      = find_max( $dirlines, '/^[\d\.]+.*$/', '/^([\d\.]+).*$/' );
      $dirpath .= "/$dir";
      $dirlines = http_get_file( "$dirpath" );
      $dir      = find_max( $dirlines, '/^[\d\.]+.*$/', '/^([\d\.]+).*$/' );
      $dirpath .= "/$dir/single";
    }

    if ( $book_index == "QScintilla_gpl" )
    {
      $dirs = http_get_file( "$dirpath/" );    
      $dir = find_max ( $dirs, "/QScintilla/", "/^.*(QScintilla-[\d\.]+).*$/" );
      $dirpath .= "/$dir";
      $lines    = http_get_file( "$dirpath/" );
      $ver = find_max( $lines, "/QScintilla_gpl/", "/^.*QScintilla_gpl-([\d\.]+).tar.*$/" );
      return $ver;
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

        //if ( $book_index == "libepoxy" )
        //{
        //  for ( $i = 0; $i < strlen($ver); $i++ ) if ( $max[$i] == '.' ) $dots++;
        //  if ( $dots < 2 ) $ver .= '.0';
        // }

        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

    if ( $book_index == "gtksourceview" )
    {
      $lines = http_get_file( "$dirpath" );
      return find_max( $lines, '/gtksourceview-/', 
          '/^.*gtksourceview-(\d\.[\d\.]+).tar.*$/' );
    }

    if ( $book_index == "gtksourceview1" )
    {
      $lines = http_get_file( "$dirpath" );
      return find_max( $lines, '/gtksourceview-/', 
          '/^.*gtksourceview-(\d\.[\d\.]+).tar.*$/' ); 
    }

  if ( $book_index == "glew" )
    return find_max( $lines, '/GLEW/', '/^.*GLEW (\d\.[\d\.]+\d).*$/' );

  if ( $book_index == "gtk+"  ||
       $book_index == "gtk+1" )
    return find_max( $lines, '/gtk/', '/^.*gtk.-([\d\.]*\d)\.tar.*$/' );

  if ( $book_index == "gtkmm1" )
    $package = "gtkmm";

  if ( $book_index == "imlib2" )
    return find_max( $lines, '/\d\.[\d\.]+\d/', '/^.* (\d\.[\d\.]+\d).*$/' );

  if ( $book_index == "keybinder" )
    return find_max( $lines, '/keybinder-/', '/^.*keybinder-(\d\.[\d\.]+).tar.*$/' );

  if ( $book_index == "webkitgtk" )
    return find_even_max( $lines, '//', '/^.*webkitgtk-(\d[\d\.]+\d).tar.*$/' );

  if ( $book_index == "cairo"    || 
       $book_index == "cairomm"  )
    return find_even_max( $lines, "/$package/", "/^$package-(\d[\d\.]+\d).tar.*$/" );

  if ( $book_index == "firefox" )
    return find_max( $lines, "/^\d/", "/^(\d{2}.\d+)$/" );

  if ( substr($book_index, 0, strlen( "qt-everywhere" ) )  == "qt-everywhere" )
    return find_max( $lines, "/src.*tar.xz/", "/^.*src-([\d\.]+).tar.*$/" );

  if ( $book_index == "fltk" )
    return find_max( $lines, "/fltk-/", "/.*fltk-(\d[\d\.-]+)-source.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array(
     array( 'pkg'   => 'at-spi', 
            'regex' => "/^.*at-spi2-.{3,4}-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'imlib2', 
            'regex' => "/^.*imlib2-(\d[\d\.]+).*$/" ),
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
