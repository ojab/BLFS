#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '46';
$CHAPTERS      = 'Chapter 46';
$START_PACKAGE = 'alsa-lib';
$STOP_PACKAGE  = 'xvidcore';

$renames = array();
$renames[ 'libmusicbrainz'  ] = 'libmusicbrainz2';
$renames[ 'libmusicbrainz1' ] = 'libmusicbrainz5';
$renames[ 'libvpx-v'        ] = 'libvpx';
$renames[ 'v'               ] = 'fdk-aac';
$renames[ 'x264-snapshot'   ] = 'x264';
$renames[ 'x1'              ] = 'x265';

$ignores = array();

//$current="frei0r-plugins";

$regex = array();
$regex[ 'faac'             ] = "/^.*Download faac-(\d[\d\.]+\d).tar.*$/";
$regex[ 'fdk-aac'          ] = "/^.*Download fdk-aac-(\d[\d\.]+\d).tar.*$/";
$regex[ 'a52dec'           ] = "/^.*a52dec-(\d[\d\.]+\d) is.*$/";
$regex[ 'libass'           ] = "/^.*Release (\d[\d\.]+\d).*$/";
$regex[ 'libdv'            ] = "/^.*Download libdv-(\d[\d\.]+\d).*$/";
$regex[ 'libmpeg2'         ] = "/^.*libmpeg2-(\d[\d\.]+\d).*$/";
$regex[ 'libmusicbrainz1'  ] = "/^.*libmusicbrainz-(5[\d\.]+\d).*$/";
$regex[ 'libquicktime'     ] = "/^.*Download libquicktime-([\d\.]+\d).tar.*$/";
$regex[ 'libsamplerate'    ] = "/^.*libsamplerate-([\d\.]+\d).tar.*$/";
$regex[ 'soundtouch'       ] = "/^.*Download Source Codes release ([\d\.]+\d).*$/";
$regex[ 'xine-lib'         ] = "/^.*Download xine-lib-([\d\.]+\d).tar.*$/";
$regex[ 'v'                ] = "/^.*fdk-aac ([\d\.]+) *$/";


$url_fix = array (

   array( 'pkg'     => 'faac',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/faac/files" ),

   array( 'pkg'     => 'faad2',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/faac/files/faad2-src" ),

   array( 'pkg'     => 'fdk-aac',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/opencore-amr/files" ),

   array( 'pkg'     => 'frei0r-plugins',
          'match'   => '^.*$', 
          'replace' => "https://files.dyne.org/frei0r/releases" ),

   array( 'pkg'     => 'a52dec',
          'match'   => '^.*$', 
          'replace' => "http://liba52.sourceforge.net/" ),

   array( 'pkg'     => 'libao',
          'match'   => '^.*$', 
          'replace' => "http://downloads.xiph.org/releases/ao" ),

   array( 'pkg'     => 'libass',
          'match'   => '^.*$', 
          'replace' => "https://github.com/libass/libass/releases" ),

   array( 'pkg'     => 'libdv',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/libdv/files" ),

   array( 'pkg'     => 'libmpeg2',
          'match'   => '^.*$', 
          'replace' => "http://libmpeg2.sourceforge.net/downloads.html" ),

   array( 'pkg'     => 'libmpeg3',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/heroines/files/releases" ),

   array( 'pkg'     => 'libmusicbrainz',
          'match'   => '^.*$', 
          'replace' => "http://ftp.musicbrainz.org/pub/musicbrainz/historical" ),

   array( 'pkg'     => 'libmusicbrainz1',
          'match'   => '^.*$', 
          'replace' => "http://musicbrainz.org/doc/libmusicbrainz" ),

   array( 'pkg'     => 'libquicktime',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/libquicktime/files" ),

   array( 'pkg'     => 'libsamplerate',
          'match'   => '^.*$', 
          'replace' => "http://www.mega-nerd.com/SRC/download.html" ),

   array( 'pkg'     => 'libvpx',
          'match'   => '^.*$', 
          'replace' => "https://chromium.googlesource.com/webm/libvpx/" ),

   array( 'pkg'     => 'soundtouch',
          'match'   => '^.*$', 
          'replace' => "http://www.surina.net/soundtouch/sourcecode.html" ),

   array( 'pkg'     => 'taglib',
          'match'   => '^.*$', 
          'replace' => "http://taglib.github.io" ),

   array( 'pkg'     => 'xine-lib',
          'match'   => '^.*$', 
          'replace' => "http://sourceforge.net/projects/xine/files" ),

   array( 'pkg'     => 'xvidcore',
          'match'   => '^.*$', 
          'replace' => "http://ftp.br.debian.org/debian-multimedia/pool/main/x/xvidcore" ),

   array( 'pkg'     => 'v',
          'match'   => '^.*$', 
          'replace' => "https://github.com/mstorsjo/fdk-aac/releases" ),

   array( 'pkg'     => 'libcanberra',
          'match'   => '^.*$', 
          'replace' => "http://pkgs.fedoraproject.org/repo/pkgs/libcanberra" ),

);

function get_packages( $package, $dirpath )
{
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;

  if ( isset( $current ) && $book_index != "$current" ) return 0;
  if ( $package == "x264-snapshot" ) return 'daily'; // Daily snapshot for x264

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
     if ( $package == "audiofile" ||
          $package == "esound"    ||
          $package == "opal"      )
     {
         // Default ftp enties for this chapter
         $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
         $position = strrpos( $dirpath, "/" );
         $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
         exec( "echo 'ls -1;bye' | ncftp $dirpath/", $dirs );
         $dir = find_max( $dirs, "/^\d/", "/^([\d\.]+).*$/" );
         $dirpath .= "/$dir/";
     }

    // Get listing
    exec( "echo 'ls -1;bye' | ncftp $dirpath/", $lines );
  }
  else // http
  {
     if ( $package == "libdvdcss" ||
          $package == "libdvdnav" ||
          $package == "libdvdread" )
     {
        $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
        $position = strrpos( $dirpath, "/" );
        $dirpath  = substr ( $dirpath, 0, $position );  // Up 1
        $lines    = http_get_file( $dirpath . "/" );
        return find_max( $lines, "/^\d\./", ":^(\d[\d\.]+)/.*$:", TRUE );
     }

     // Directories are in the form of mmddyy :(
     if ( $package == "libmpeg3" )
     {
        $a     = array();
        $dirs  = http_get_file( $dirpath );
        foreach ( $dirs as $d )
        {
           // Isolate the version and put in an array
           $slice = preg_replace( "/^\s*(\d{2})(\d{2})(\d{2})\s*$/", "$3$2$1", $d );
           if ( "x$slice" == "x$d" ) continue; 
           array_push( $a, $slice );     
        }
        
        rsort( $a, SORT_NATURAL );  // Max version is at the top
        $dir   = $a[ 0 ];
        $lines = http_get_file( "$dirpath/$dir" );
        return find_max( $lines, "/libmpeg3/", "/^.*libmpeg3-(\d[\d\.]+)-src.*$/" );
     }

     if ( $package == "x265" )
     {
        # We have to process the stupid javascript to get this to work
        exec( "lynx -dump  $dirpath", $output );
        $max = find_max( $output, "/x265_/", "/^.*x265_([\d\.]*\d)\.tar.*$/" );
        return $max;
     }

     if ( $package == "frei0r-plugins" )
     {
        exec( "wget  -q --no-check-certificate -O- $dirpath", $output );
        $max = find_max( $output, "/frei0r/", "/^.*plugins-([\d\.]*\d)\.tar.*$/" );
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

  if ( $package == "faad2" )
      return find_max( $lines, "/faad2-\d/", "/^.*faad2-([\d\.]+).*$/" );

  if ( $package == "gstreamer" )
      return find_even_max( $lines, "/gstreamer/", "/^.*gstreamer-(1\.[\d\.]+).tar.*$/" );

  if ( $package == "gst-plugins-base" )
      return find_even_max( $lines, "/base-/", "/^.*base-(1\.[\d\.]+).tar.*$/" );

  if ( $package == "gst-plugins-good" )
      return find_even_max( $lines, "/good-/", "/^.*good-(1\.[\d\.]+).tar.*$/" );

  if ( $package == "gst-plugins-bad" )
      return find_even_max( $lines, "/bad-/", "/^.*bad-(1\.[\d\.]+).tar.*$/" );

  if ( $package == "gst-plugins-ugly" )
      return find_even_max( $lines, "/ugly-/", "/^.*ugly-(1\.[\d\.]+).tar.*$/" );

  if ( $package == "gst-libav" )
      return find_even_max( $lines, "/$package/", "/^.*$package-([\d\.]+).tar.*$/" );

  if ( $package == "libmad" )
      return find_max( $lines, "/^.*libmad-/", "/^.*libmad-([\d\.]+[a-m]{0,1}).tar.*$/" );

  if ( $package == "libmusicbrainz" )
      return find_max( $lines, "/^.*libmusicbrainz-/", "/^.*libmusicbrainz-(2[\d\.]+).tar.*$/" );

  if ( $package == "libmusicbrainz1" )
      return find_max( $lines, "/^.*libmusicbrainz-/", "/^.*libmusicbrainz-(3[\d\.]+).tar.*$/" );

  // Very sensitive to upstream format that appears to be script based
  if ( $package == "libvpx" )
      return find_max( $lines, "/v\d/", "/^.*sv([\d\.]+)v.*$/" );

  if ( $package == "soundtouch" )
      return find_max( $lines, "/soundtouch/", "/^.*soundtouch-([\d\.]+).*$/" );

  if ( $package == "speex" || 
       $package == "speexdsp" )
      return find_max( $lines, "/$package/", "/^.*$package-([\d\.rc]+).tar.*$/" );

  if ( $package == "taglib" )
      return find_max( $lines, "/TagLib/", "/^.*TagLib ([\d\.]+).*$/" );

  if ( $package == "xvidcore" )
      return find_max( $lines, "/xvidcore_/", "/^.*xvidcore_([\d\.]+).orig.*$/" );

  if ( $package == "libcdio-paranoia" )
      return find_max( $lines, "/paranoia/", "/^.*paranoia-([\d\.\+]+).tar.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]*\d)\.tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   $match = array(
     array( 'pkg'   => 'a52dec', 
            'regex' => "/^.*a52dec-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'faad2', 
            'regex' => "/^.*faad2-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'frei', 
            'regex' => "/^.*frei0r-plugins-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'libmpeg2', 
            'regex' => "/^.*libmpeg2-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'libmpeg3', 
            'regex' => "/^.*libmpeg3-(\d[\d\.]+).*$/" ),
     
     array( 'pkg'   => 'libmad', 
            'regex' => "/^.*libmad-(\d[\d\.]+[a-m]{0,1}).*$/" ),

     array( 'pkg'   => 'v4l-utils', 
            'regex' => "/^.*v4l-utils-(\d[\d\.]+\d).*$/" ),

     array( 'pkg'   => 'x264', 
            'regex' => "/^.*x264-snapshot-(\d+)-.*$/" ),

     array( 'pkg'   => 'x265', 
            'regex' => "/^.*x265_(\d[\d\.]+\d).*$/" ),
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

   echo "book index: $book_index $url $bver\n";

   $v = get_packages( $book_index, $url );
   $vers[ $book_index ] = $v;
}

html();  // Write html output
?>
