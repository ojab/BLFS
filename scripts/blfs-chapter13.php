#! /usr/bin/php
<?php

$CHAPTER=13;
$START_PACKAGE='check';
$STOP_PACKAGE='OpenJDK';

$book = array();
$book_index = 0;
$previous   = 0;

$vers = array();

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d (D) H:i:s" );

// Special cases
$exceptions = array();

$regex = array();
//$regex[ 'bzr'     ] = "/^.*Latest version is (\d[\d\.]+\d).*$/";
$regex[ 'check'   ] = "/^.*Download check-(\d[\d\.]+\d).tar.*$/";
$regex[ 'expect'  ] = "/^.*Download expect(\d[\d\.]+\d).tar.*$/";
$regex[ 'junit4'  ] = "/^\h*(\d[\d\.]+)\h*$/";
$regex[ 'llvm'    ] = "/^.*Download LLVM (\d[\d\.]+\d).*$/";
$regex[ 'scons'   ] = "/^.*Download scons-(\d[\d\.]+\d).*$/";
$regex[ 'tcl'     ] = "/^.*Download tcl(\d[\d\.]+\d).*$/";
$regex[ 'swig'    ] = "/^.*Download swig-(\d[\d\.]+\d).*$/";
$regex[ 'Python1' ] = "/^.*Download Python (3[\d\.]+\d).*$/";
$regex[ 'Mako'    ] = "/^.*version is (\d[\d\.]+\d).*$/";
//$regex[ 'python1' ] = "/^.*Download Python (3[\d\.]+\d).*$/";
$regex[ 'php'     ] = "/^.*php-(\d[\d\.]+\d).tar.*$/";
$regex[ 'ruby'    ] = "/^.*stable version is (\d[\d\.]+\d).*$/";
$regex[ 'valgrind'] = "/^.*valgrind (\d[\d\.]+\d) \(tar.*$/";
$regex[ 'jtreg'   ] = "/^.*jtreg(\d[b\d\.\-]+\d)\.tar.*$/";
$regex[ 'OpenJDK' ] = "/^.*jtreg(\d[b\d\.\-]+\d)\.tar.*$/";

// Perl Modules
$regex[ 'Archive-Zip'       ] = "/^.*Archive-Zip-(\d[\d\.]+\d).*$/";
$regex[ 'autovivification'  ] = "/^.*autovivification-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISBN'     ] = "/^.*Business-ISBN-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISMN'     ] = "/^.*Business-ISMN-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISSN'     ] = "/^.*Business-ISSN-(\d[\d\.]+\d).*$/";
$regex[ 'Data-Compare'      ] = "/^.*Data-Compare-(\d[\d\.]+\d).*$/";
$regex[ 'Date-Simple'       ] = "/^.*Date-Simple-(\d[\d\.]+\d).*$/";
$regex[ 'Encode-EUCJPASCII' ] = "/^.*Encode-EUCJPASCII-(\d[\d\.]+\d).*$/";
$regex[ 'Encode-HanExtra'   ] = "/^.*Encode-HanExtra-(\d[\d\.]+\d).*$/";
$regex[ 'Encode-JIS2K'      ] = "/^.*Encode-JIS2K-(\d[\d\.]+\d).*$/";
$regex[ 'File-Slurp'        ] = "/^.*File-Slurp-(\d[\d\.]+\d).*$/";
$regex[ 'File-Which'        ] = "/^.*File-Which-(\d[\d\.]+\d).*$/";
$regex[ 'HTML-Parser'       ] = "/^.*HTML-Parser-(\d[\d\.]+\d).*$/";
$regex[ 'IPC-Run3'          ] = "/^.*IPC-Run3-(\d[\d\.]+\d).*$/";
$regex[ 'libwww-perl'       ] = "/^.*libwww-perl-(\d[\d\.]+\d).*$/";
$regex[ 'List-AllUtils'     ] = "/^.*List-AllUtils-(\d[\d\.]+\d).*$/";
$regex[ 'Log-Log4perl'      ] = "/^.*Log-Log4perl-(\d[\d\.]+\d).*$/";
$regex[ 'Net-DNS'           ] = "/^.*Net-DNS-(\d[\d\.]+\d).*$/";
$regex[ 'Readonly-XS'       ] = "/^.*Readonly-XS-(\d[\d\.]+\d).*$/";
$regex[ 'Regexp-Common'     ] = "/^.*Regexp-Common-(\d[\d\.]+\d).*$/";
$regex[ 'Text-BibTeX'       ] = "/^.*Text-BibTeX-(\d[\d\.]+\d).*$/";
$regex[ 'Unicode-Collate'   ] = "/^.*Unicode-Collate-(\d[\d\.]+\d).*$/";
$regex[ 'Unicode-LineBreak' ] = "/^.*Unicode-LineBreak-(\d[\d\.]+\d).*$/";
$regex[ 'URI'               ] = "/^.*URI-(\d[\d\.]+\d).*$/";
$regex[ 'XML-LibXML-Simple' ] = "/^.*XML-LibXML-Simple-(\d[\d\.]+\d).*$/";
$regex[ 'XML-LibXSLT'       ] = "/^.*XML-LibXSLT-(\d[\d\.]+\d).*$/";
$regex[ 'XML-Simple'        ] = "/^.*XML-Simple-(\d[\d\.]+\d).*$/";
$regex[ 'XML-Writer'        ] = "/^.*XML-Writer-(\d[\d\.]+\d).*$/";

$sf = 'sourceforge.net';

//$current="dbus-python";

$url_fix = array (

   array( //'pkg'     => 'gnome',
          'match'   => '^ftp:\/\/ftp.gnome', 
          'replace' => "http://ftp.gnome" ),

   //array( 'pkg'     => 'bzr',
   //       'match'   => '^.*$', 
   //       'replace' => "https://launchpad.net/bzr" ),

   array( 'pkg'     => 'check',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/check/files" ),

   array( 'pkg'     => 'expect',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/expect/files" ),

   array( 'pkg'     => 'icedtea',
          'match'   => '^.*$', 
          'replace' => "http://icedtea.classpath.org/download/source" ),

   array( 'pkg'     => 'junit4',
          'match'   => '^.*$', 
          'replace' => "https://github.com/junit-team/junit/wiki" ),

   array( 'pkg'     => 'llvm',
          'match'   => '^.*$', 
          'replace' => "http://llvm.org/releases/download.html" ),

   array( 'pkg'     => 'nasm',
          'match'   => '^.*$', 
          'replace' => "http://www.nasm.us/pub/nasm/releasebuilds" ),

   array( 'pkg'     => 'python1',
          'match'   => '^.*$', 
          'replace' => "https://docs.python.org/3/archives" ),

   array( 'pkg'     => 'scons',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/scons/files" ),

   array( 'pkg'     => 'tcl',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/tcl/files" ),

   array( 'pkg'     => 'tk',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/tcl/files/Tcl" ),

   array( 'pkg'     => 'swig',
          'match'   => '^.*$', 
          'replace' => "http://$sf/projects/swig/files/swig" ),

   array( 'pkg'     => 'elfutils',
          'match'   => '^.*$', 
          'replace' => "https://fedorahosted.org/releases/e/l/elfutils" ),

   array( 'pkg'     => 'Archive-Zip',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Archive-Zip/" ),

   array( 'pkg'     => 'autovivification',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/autovivification/" ),

   array( 'pkg'     => 'Business-ISBN',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Business-ISBN/" ),

   array( 'pkg'     => 'Business-ISMN',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Business-ISMN/" ),

   array( 'pkg'     => 'Business-ISSN',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Business-ISSN/" ),

   array( 'pkg'     => 'Data-Compare',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Data-Compare/" ),

   array( 'pkg'     => 'Date-Simple',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Date-Simple/" ),

   array( 'pkg'     => 'Encode-EUCJPASCII',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Encode-EUCJPASCII/" ),

   array( 'pkg'     => 'Encode-HanExtra',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Encode-HanExtra/" ),

   array( 'pkg'     => 'Encode-JIS2K',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Encode-JIS2K/" ),

   array( 'pkg'     => 'File-Slurp',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/File-Slurp/" ),

   array( 'pkg'     => 'File-Which',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/File-Which/" ),

   array( 'pkg'     => 'HTML-Parser',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/HTML-Parser/" ),

   array( 'pkg'     => 'IPC-Run3',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/IPC-Run3/" ),

   array( 'pkg'     => 'IPC-Run3',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/IPC-Run3/" ),

   array( 'pkg'     => 'libwww-perl',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/libwww-perl/" ),

   array( 'pkg'     => 'List-AllUtils',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/List-AllUtils/" ),

   array( 'pkg'     => 'Log-Log4perl',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Log-Log4perl/" ),

   array( 'pkg'     => 'Net-DNS',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Net-DNS/" ),

   array( 'pkg'     => 'Readonly-XS',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Readonly-XS/" ),

   array( 'pkg'     => 'Regexp-Common',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Regexp-Common/" ),

   array( 'pkg'     => 'Text-BibTeX',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Text-BibTeX/" ),

   array( 'pkg'     => 'Unicode-Collate',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Unicode-Collate/" ),

   array( 'pkg'     => 'Unicode-LineBreak',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/Unicode-LineBreak/" ),

   array( 'pkg'     => 'URI',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/URI/" ),

   array( 'pkg'     => 'XML-LibXML-Simple',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/XML-LibXML-Simple/" ),

   array( 'pkg'     => 'XML-LibXSLT',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/XML-LibXSLT/" ),

   array( 'pkg'     => 'XML-Simple',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/XML-Simple/" ),

   array( 'pkg'     => 'XML-Writer',
          'match'   => '^.*$', 
          'replace' => "http://search.cpan.org/dist/XML-Writer/" ),

   array( 'pkg'     => 'Python1',
          'match'   => '^.*$', 
          'replace' => "https://www.python.org/downloads" ),

   array( 'pkg'     => 'setuptools',
          'match'   => '^.*$', 
          'replace' => "https://pypi.python.org/pypi/setuptools/" ),

   array( 'pkg'     => 'Beaker',
          'match'   => '^.*$', 
          'replace' => "https://pypi.python.org/pypi/Beaker/" ),

   array( 'pkg'     => 'MarkupSafe',
          'match'   => '^.*$', 
          'replace' => "https://pypi.python.org/pypi/MarkupSafe/" ),

   array( 'pkg'     => 'Jinja2',
          'match'   => '^.*$', 
          'replace' => "https://pypi.python.org/pypi/Jinja2/" ),

   array( 'pkg'     => 'Mako',
          'match'   => '^.*$', 
          'replace' => "http://www.makotemplates.org/download.html" ),

   array( 'pkg'     => 'php',
          'match'   => '^.*$', 
          'replace' => "http://us2.php.net/distributions" ),

   array( 'pkg'     => 'ruby',
          'match'   => '^.*$', 
          'replace' => "https://www.ruby-lang.org/en/downloads" ),

);

function find_max( $lines, $regex_match, $regex_replace )
{
  global $book_index;
  global $previous;
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
     if ( "x$slice" == "x$line" && ! preg_match( "/^\d[\d\.]*$/", $slice ) ) continue; 

     // Skip minor versions in the 90s (most of the time)
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0.0" );
     if ( $minor >= 90                       &&
          $book_index != "librep"            &&
          $book_index != "Glib"              &&
          $book_index != "Business-ISSN"     &&
          $book_index != "XML-LibXML-Simple" &&
          $book_index != "XML-LibXSLT"       &&
          $book_index != "elfutils"          ) continue;

     array_push( $a, $slice );     
  }

  // SORT_NATURAL requires php-5.4.0 or later
  rsort( $a, SORT_NATURAL );  // Max version is at the top
//print_r($a);

  $previous = ( isset( $a[1] ) ) ? $a[1] : 0;;
//echo "previous=$previous\n";
  return ( isset( $a[0] ) ) ? $a[0] : 0;
}

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

function http_get_file( $url, $strip = "yes" )
{
  exec( "curl -L -s -m30 $url", $dir );
//echo "url=$url\n";
//print_r($dir);
  $s   = implode( "\n", $dir );
  if ( "$strip" != "no" )  
      $dir = strip_tags( $s );
  else
      $dir = $s;
  return explode( "\n", $dir );
}

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

function get_packages( $package, $dirpath )
{
  global $exceptions;
  global $regex;
  global $book_index;
  global $url_fix;
  global $current;
  global $previous;

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
    // glib type packages
    if ( $book_index == "pygobject1" ||
         $book_index == "pygobject"  ||
         $book_index == "pygtk"      ||
         $book_index == "pyatspi"     )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      if ( $book_index == "pygobject" )
         $dir      = find_even_max( $lines, '/^2[\d\.]+$/', '/^(2[\d\.]+)$/' );
      else
         $dir      = find_even_max( $lines, '/^[\d\.]+$/', '/^([\d\.]+)$/' );

      $dirpath .= "/$dir/";
    }

    // gcc and similar
    if ( $book_index == "gcc" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $dir = find_max( $lines, "/$book_index-\d[\d\.]+/", "/$book_index-(\d[\d\.]+)/" );
      $dirpath .= "/$book_index-$dir/";
    }

    // slang
    if ( $book_index == "slang" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $dir = find_max( $lines, "/v\d[\d\.]+/", "/^v(\d[\d\.]+).*/" );
      $dirpath .= "/v$dir/";
    }

    if ( $book_index == "vala" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
      $dir = find_even_max( $lines, "/\d[\d\.]+/", "/^(\d[\d\.]+).*/" );
      $dirpath .= "/$dir/";
    }

    if ( $book_index == "cvs" )
    {
       // Get the max directory 
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( "$dirpath/" );
      return find_max( $lines, "/\d\.[\d\.]+/", "/^.* (\d\.[\d\.]+)$/" );
    }

    // Get listing
    exec( "echo 'ls -1;bye' | ncftp $dirpath", $lines );
  }
  else // http
  {
    // glib type packages
    if ( $book_index == "pygobject1" ||
         $book_index == "pygobject"  ||
         $book_index == "pygtk"      ||
         $book_index == "pyatspi"     )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines1 = http_get_file( $dirpath );

      if ( $book_index == "pygobject" )
         $dir      = find_even_max( $lines1, '/^\s+2[\d\.]+.*$/', '/^\s+(2[\d\.]+).*$/' );
      else
         $dir      = find_even_max( $lines1, '/^\s+[\d\.]+.*$/', '/^\s+([\d\.]+).*$/' );

      $dirpath .= "/$dir/";
    }
/*
    if ( $book_index == "ruby" )
    {
      // Parent listing
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
echo "dirpath 1=$dirpath\n";
      $lines1   = http_get_file( "$dirpath/" );
print_r($lines1);
exit;
      $dir      = find_max( $lines1, '/^\d\./', '/^\s*([\d\.]+).*$/' );
echo "dir=$dir\n";
echo "previous=$previous\n";
      $prev     = $previous;
      $save     = $dirpath;
      $dirpath .= "/$dir/";
echo "dirpath 2 =$dirpath\n";
      $lines   = http_get_file( $dirpath );
print_r($lines);
exit;
      $max     =  find_max( $lines, '/ruby-\d/', '/^ruby-(\d[\d\.-]+\d.*).tar.*$/' );
echo "max=$max\n";

      if ( $max != 0 ) return $max;

      $dirpath = "$save/$prev/";
echo "dirpath=$dirpath\n";
exit;
    }
*/
    // Customize http directories as needed
    if ( $book_index == "cmake" )
    {
       $dirpath = max_parent( $dirpath, 'v' );
       $prev    = $previous;

       // Special if there is no current version in $dirpath
       $lines   = http_get_file( $dirpath );
       $max     = find_max( $lines, "/$package/", 
                                    "/^.*$package-([\d\.]*\d)\.tar.*$/" );
       if ( $max != 0 ) return $max;

       $position = strrpos( $dirpath, "/" );
       $dirpath  = substr ( $dirpath, 0, $position ) . "/$prev";
//echo "dirpath=$dirpath\n"; 
    }
     
    $strip = "yes";
    if ( $package == "npapi-sdk" ) $strip = "no";
    $lines = http_get_file( $dirpath, $strip );
    if ( ! is_array( $lines ) ) return $lines;
  } // End fetch

  if ( isset( $regex[ $package ] ) )
  {
     // Custom search for latest package name
     foreach ( $lines as $l )
     {
        if ( preg_match( '/^\h*$/', $l ) ) continue;
        $ver = preg_replace( $regex[ $package ], "$1", $l );
        if ( $ver == $l  &&  ! preg_match( '/^\d\.[\d\.]+$/', $ver ) ) continue;
        if ( $book_index == "exiv" ) $ver = "2-$ver";
        
        return $ver;  // Return first match of regex
     }

     return 0;  // This is an error
  }

  if ( $book_index == "doxygen" )
    return find_max( $lines, '/doxygen/', '/^.*doxygen-([\d\.]+).src.tar.*$/' );

  if ( $book_index == "llvm" )
  {
     //$dir = max_parent( $dirpath, "" );
     //$lines = http_get_file( "$dir" );
     return find_max( $lines, "/^.*$book_index-.*.src.*$/", 
                              "/^.*$book_index-([\d\.]+)\.src.*$/" );
  }

  if ( $book_index == "elfutils" )
  {
     return find_max( $lines, "/^\s*\d[\d\.]+\/.*$/", 
                              "/^\s*(\d[\d\.]+)\/.*$/" );
  }

  if ( $book_index == "nasm" )
    return find_max( $lines, '/\d[\d\.]+\d/', '/^(\d[\d\.]+\d)\/.*$/' );

  if ( $book_index == "Python" )
  {
    $dir = max_parent( $dirpath, "2" );
    $lines = http_get_file( "$dir" );

    return find_max( $lines, "/^Python-\d[\d\.]*\d/", 
                             "/^Python-(\d[\d\.]*\d).tar.*$/" );
  }

  if ( $book_index == "python" )  // python2
  {
    $dir = max_parent( $dirpath, "2" );
    $lines = http_get_file( "$dir" );
    return find_max( $lines, "/^python-\d[\d\.]*\d/", 
                             "/^python-(\d[\d\.]*\d)-docs.*$/" );
  }

  if ( $book_index == "python1" )  // python3 docs
  {
    //$dir = max_parent( $dirpath, "3" );
    //$lines = http_get_file( "$dir" );
    return find_max( $lines, "/python-\d/", 
                             "/^python-(\d[\d\.]*\d)-docs.*$/" );
  }

  if ( $book_index == "cmake" )
  {
    $max =   find_max( $lines, "/$package/", 
                               "/^.*$package-([\d\.]*\d)\.tar.*$/" );
    if ( $max == 0 )
      $max = find_max( $lines, "/$package/", 
                               "/^.*$package-(\d[\d\.]*\d-rc\d).*$/" );
    return $max;
  }

  if ( $book_index == "pygobject1" ||
       $book_index == "pygobject "  )
    $package = "pygobject";

  //if ( $book_index == "ruby" )
  //  return find_max( $lines, '/ruby-\d/', '/^ruby-(\d[\d\.-]+\d.*).tar.*$/' );
     
  if ( $book_index == "librep" )  
    return find_max( $lines, "/librep/", "/^.*[_-](\d[\d\.]*\d)\.tar.*$/" );

  if ( $book_index == "apache-ant" )
    return find_max( $lines, "/$package/", "/^.*$package-(\d[\d\.]+\d)-src.*$/" );

  if ( $book_index == "tk" )
  {
    $dir = find_max( $lines, '/\d\.[\d\.]+\d/', '/^\s+(\d\.[\d\.]+\d).*$/' );
    $lines = http_get_file( "$dirpath/$dir" );
    return find_max( $lines, "/$package/", "/^.*$package([\d\.]*\d)-src.tar.*$/" );
  }

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
//if (  $book_index == "subversion" ) print_r( $lines );
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]+\d).tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     array( 'pkg'   => 'py2cairo', 
            'regex' => "/py2cairo-([\d\.]+)/" ),

     //array( 'pkg'   => 'icedtea', 
     //       'regex' => "/icedtea-([\d\.]+)\-.*$/" ),

     array( 'pkg'   => 'Encode-JIS2K', 
            'regex' => "/\D*Encode-JIS2K-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'IPC-Run3', 
            'regex' => "/\D*IPC-Run3-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'Log-Log4perl', 
            'regex' => "/\D*Log-Log4perl-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'Jinja2', 
            'regex' => "/\D*Jinja2-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'junit4', 
            'regex' => "/junit4_([\d\.]+).*$/" ),
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
   global $STOP_PACKAGE; // not used here

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

      if ( preg_match( "/patch$/"  , $file ) ) continue;     // Skip patches
      if ( preg_match( "/hamcrest/", $file ) ) continue;     // Skip hamcrest
      if ( preg_match( "/OpenJDK/" , $file ) ) continue;     // Skip OpenJDK for now

      $pattern = get_pattern( $line );
      
      $version = preg_replace( $pattern, "$1", $file );   // Isolate version
      $version = preg_replace( "/^-/", "", $version );    // Remove leading #-

      $basename = strstr( $file, $version, true );
      $basename = rtrim( $basename, "-" );
      $basename = rtrim( $basename, "_" );

      $basename = ( $basename == "hd" ) ? "hd2u" : $basename;

      $index = $basename;
      while ( isset( $book[ $index ] ) ) $index .= "1";

      $book[ $index ] = array( 'basename' => $basename,
                               'url'      => $url, 
                               'version'  => $version );

      if ( preg_match( "/apache-ant/", $line ) ) break;
   }

   // Add Java

   $lines   = preg_grep( "/jtreg/" , $wget );
   $line    = array_shift( $lines );
   $url     = dirname ( $line );
   $version = preg_replace( '/^.*jtreg(\d[b\d\.\-]+)\.tar.*$/', "$1", $line );

   $book[ 'OpenJDK' ] = 
     array( 'basename' => 'OpenJDK',
            'url'      => $url, 
            'version'  => $version );
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
<title>BLFS Chapter $CHAPTER Package Currency Check - $date</title>
<link rel='stylesheet' href='currency.css' type='text/css' />
</head>
<body>
$leftnav
<div id='top'>
<h1>BLFS Chapter $CHAPTER Package Currency Check</h1>
<h2>As of $date GMT</h2>
</div>

<table id='table'>
<tr><th>BLFS Package</th> <th>BLFS Version</th> <th>Latest</th> <th>Flag</th></tr>\n";

   // Get the latest version of each package
   foreach ( $vers as $pkg => $v )
   {
      $v    = $book[ $pkg ][ 'version' ];
      $flag = ( $vers[ $pkg ] != $v ) ? "*" : "";
  
      $name = $pkg;
      //if ( $pkg == "OpenJDK"    ) $name = 'icedtea';
      if ( $pkg == "nasm1"      ) $name = 'nasm docs';
      if ( $pkg == "py"         ) $name = 'pycairo';
      if ( $pkg == "Python"     ) $name = 'python2';
      if ( $pkg == "python"     ) $name = 'python2 docs';
      if ( $pkg == "Python1"    ) $name = 'python3';
      if ( $pkg == "python1"    ) $name = 'python3 docs';
      if ( $pkg == "pygobject"  ) $name = 'pygobject2';
      if ( $pkg == "pygobject1" ) $name = 'pygobject3';
      if ( $pkg == "tcl1"       ) $name = 'tcl docs';

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

   // Skip things we don't want
   //if ( preg_match( "/OpenJDK/",    $pkg ) ) continue;
   if ( preg_match( "/cfe/",        $pkg ) ) continue;
   if ( preg_match( "/clang/"      ,$pkg ) ) continue;
   if ( preg_match( "/compiler-rt/",$pkg ) ) continue;
   if ( preg_match( "/nasm1/",      $pkg ) ) continue;
   if ( preg_match( "/tcl1/",       $pkg ) ) continue;
   if ( preg_match( "/gcc1.*/",       $pkg ) ) continue;

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
