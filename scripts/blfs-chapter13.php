#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '13';
$CHAPTERS      = 'Chapter 13';
$START_PACKAGE = 'cbindgen';
$STOP_PACKAGE  = 'apache-ant';

$renames = array();
$renames[ 'lua11'      ] = 'lua5.2';
$renames[ 'Python'     ] = 'python2';
$renames[ 'Python1'    ] = 'python3';
$renames[ 'pygobject'  ] = 'pygobject2';
$renames[ 'pygobject1' ] = 'pygobject3';
$renames[ 'junit4-r'   ] = 'junit';

$ignores = array();
$ignores[ 'cfe'          ] = '';
$ignores[ 'clang'        ] = '';
$ignores[ 'compiler-rt'  ] = '';
$ignores[ 'git-htmldocs' ] = '';
$ignores[ 'git-manpages' ] = '';
$ignores[ 'libxml'       ] = '';  // This is for python module; handled in Ch 9
$ignores[ 'lua1'         ] = '';
$ignores[ 'nasm1'        ] = '';
$ignores[ 'tcl1'         ] = '';
$ignores[ 'gcc1'         ] = '';
$ignores[ 'OpenJDK'      ] = '';
$ignores[ 'OpenJDK1'     ] = '';
$ignores[ 'openjdk'      ] = '';
$ignores[ 'hamcrest'     ] = '';
$ignores[ 'apache-ant1'  ] = '';
$ignores[ 'apache-maven1'] = '';
$ignores[ 'icedtea-web'  ] = '';
$ignores[ 'python'       ] = '';
$ignores[ 'python1'      ] = '';
$ignores[ 'NetRexx'      ] = '';

//$current="File-Slurper";  // For debugging

$regex = array();
$regex[ 'check'   ] = "/^.*Check (\d[\d\.]+\d).*$/";
$regex[ 'Python'  ] = "/^.*Latest Python 2.*Python (2[\d\.]+\d).*$/";
$regex[ 'Python1' ] = "/^.*Latest Python 3.*Python (3[\d\.]+\d).*$/";
$regex[ 'Mako'    ] = "/^.*Mako (\d[\d\.]+\d).*$/";
$regex[ 'php'     ] = "/^.*php-(\d[\d\.]+\d).tar.*$/";
$regex[ 'jtreg'   ] = "/^.*jtreg-(\d[b\d\.\-]+\d)\.tar.*$/";
$regex[ 'OpenJDK1'] = "/^.*OpenJDK-(\d[\d\.]+\d)\-.*$/";
$regex[ 'setuptools' ] = "/^.*setuptools-(\d[\d\.]+\d).zip.*$/";
$regex[ 'lxml'       ] = "/^.*lxml-(\d[\d\.]+\d).*$/";
$regex[ 'funcsigs'   ] = "/^.*funcsigs (\d[\d\.]+\d).*$/";
$regex[ 'pycrypto'   ] = "/^.*pycrypto (\d[\d\.]+\d).*$/";
//$regex[ 'ruby'       ] = "/^.*Ruby (\d[\d\.]+\d) Released.*$/";

// Perl Modules
$regex[ 'Archive-Zip'             ] = "/^.*Archive-Zip-(\d[\d\.]+\d).*$/";
$regex[ 'autovivification'        ] = "/^.*autovivification-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISBN'           ] = "/^.*Business-ISBN-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISMN'           ] = "/^.*Business-ISMN-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISSN'           ] = "/^.*Business-ISSN-(\d[\d\.]+\d).*$/";
$regex[ 'Data-Compare'            ] = "/^.*Data-Compare-(\d[\d\.]+\d).*$/";
$regex[ 'Data-Dump'               ] = "/^.*Data-Dump-(\d[\d\.]+\d).*$/";
$regex[ 'DateTime-Calendar-Julian'] = "/^.*DateTime-Calendar-Julian-(\d[\d\.]+\d).*$/";
$regex[ 'DateTime-Format-Builder' ] = "/^.*DateTime-Format-Builder-(\d[\d\.]+\d).*$/";
$regex[ 'Encode-EUCJPASCII'       ] = "/^.*Encode-EUCJPASCII-(\d[\d\.]+\d).*$/";
$regex[ 'Encode-HanExtra'         ] = "/^.*Encode-HanExtra-(\d[\d\.]+\d).*$/";
$regex[ 'Encode-JIS2K'            ] = "/^.*Encode-JIS2K-(\d[\d\.]+\d).*$/";
$regex[ 'File-BaseDir'            ] = "/^.*File-BaseDir-(\d[\d\.]+\d).*$/";
$regex[ 'File-Slurper'            ] = "/^.*File-Slurper-(\d[\d\.]+\d).*$/";
$regex[ 'File-Which'              ] = "/^.*File-Which-(\d[\d\.]+\d).*$/";
$regex[ 'HTML-Parser'             ] = "/^.*HTML-Parser-(\d[\d\.]+\d).*$/";
$regex[ 'IPC-Run3'                ] = "/^.*IPC-Run3-(\d[\d\.]+\d).*$/";
$regex[ 'libwww-perl'             ] = "/^.*libwww-perl-(\d[\d\.]+\d).*$/";
$regex[ 'List-AllUtils'           ] = "/^.*List-AllUtils-(\d[\d\.]+\d).*$/";
$regex[ 'Log-Log4perl'            ] = "/^.*Log-Log4perl-(\d[\d\.]+\d).*$/";
$regex[ 'Net-DNS'                 ] = "/^.*Net-DNS-(\d[\d\.]+\d).*$/";
$regex[ 'Parse-Yapp'              ] = "/^.*Parse-Yapp-(\d[\d\.]+\d).*$/";
$regex[ 'PerlIO-utf8_strict'      ] = "/^.*PerlIO-utf8_strict-(\d[\d\.]+\d).*$/";
$regex[ 'Readonly-XS'             ] = "/^.*Readonly-XS-(\d[\d\.]+\d).*$/";
$regex[ 'Regexp-Common'           ] = "/^.*Regexp-Common-(\d[\d\.]+\d).*$/";
$regex[ 'Sort-Key'                ] = "/^.*Sort-Key-(\d[\d\.]+\d).*$/";
$regex[ 'Test-Command'            ] = "/^.*Test-Command-(\d[\d\.]+\d).*$/";
$regex[ 'Test-Differences'        ] = "/^.*Test-Differences-(\d[\d\.]+\d).*$/";
$regex[ 'Text-BibTeX'             ] = "/^.*Text-BibTeX-(\d[\d\.]+\d).*$/";
$regex[ 'Text-Roman'              ] = "/^.*Text-Roman-(\d[\d\.]+\d).*$/";
$regex[ 'Unicode-Collate'         ] = "/^.*Unicode-Collate-(\d[\d\.]+\d).*$/";
$regex[ 'Unicode-LineBreak'       ] = "/^.*Unicode-LineBreak-(\d[\d\.]+\d)$/";
$regex[ 'XML-LibXML-Simple'       ] = "/^.*XML-LibXML-Simple-(\d[\d\.]+\d).*$/";
$regex[ 'XML-LibXSLT'             ] = "/^.*XML-LibXSLT-(\d[\d\.]+\d).*$/";
$regex[ 'XML-Simple'              ] = "/^.*XML-Simple-(\d[\d\.]+\d).*$/";

$url_fix = array (

   array( //'pkg'     => 'gnome',
          'match'   => '^ftp:\/\/ftp.gnome',
          'replace' => "http://ftp.gnome" ),

   array( 'pkg'     => 'cbindgen',
          'match'   => '^.*$',
          'replace' => "https://github.com/eqrion/cbindgen/releases" ),

   array( 'pkg'     => 'check',
          'match'   => '^.*$',
          'replace' => "http://github.com/libcheck/check/releases" ),

   array( 'pkg'     => 'cmake',
          'match'   => '^.*$',
          'replace' => "https://cmake.org/download/" ),

   array( 'pkg'     => 'docutils',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/docutils/files" ),

   array( 'pkg'     => 'doxygen',
          'match'   => '^.*$',
          'replace' => "http://www.doxygen.nl/download.html" ),

   array( 'pkg'     => 'expect',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/expect/files" ),

   array( 'pkg'     => 'icedtea',
          'match'   => '^.*$',
          'replace' => "http://icedtea.classpath.org/download/source" ),

   array( 'pkg'     => 'junit4-r',
          'match'   => '^.*$',
          'replace' => "https://github.com/junit-team/junit4/releases" ),

   array( 'pkg'     => 'jdk',
          'match'   => '^.*$',
          'replace' => "https://www.oracle.com/technetwork/java/javase/downloads" ),
          //'replace' => "http://hg.openjdk.java.net/jdk-updates" ),

   array( 'pkg'     => 'ninja',
          'match'   => '^.*$',
          'replace' => "https://github.com/ninja-build/ninja/releases" ),
          //'replace' => "https://ninja-build.org/" ),

   array( 'pkg'     => 'scour',
          'match'   => '^.*$',
          'replace' => "https://github.com/scour-project/scour/releases" ),

   array( 'pkg'     => 'rustc',
          'match'   => '^.*$',
          'replace' => "https://github.com/rust-lang/rust/releases" ),

   array( 'pkg'     => 'llvm',
          'match'   => '^.*$',
          'replace' => "http://llvm.org/releases/download.html" ),

   array( 'pkg'     => 'nasm',
          'match'   => '^.*$',
          'replace' => "http://www.nasm.us/pub/nasm/releasebuilds" ),

   array( 'pkg'     => 'scons',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/scons/files/scons" ),

   array( 'pkg'     => 'tcl',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/tcl/files" ),

   // Same as tcl
   array( 'pkg'     => 'tk',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/tcl/files" ),

   array( 'pkg'     => 'swig',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/swig/files/swig" ),

   array( 'pkg'     => 'elfutils',
          'match'   => '^.*$',
          'replace' => "ftp://sourceware.org/pub/elfutils" ),

   array( 'pkg'     => 'Archive-Zip',
          'match'   => '^.*$',
          #'replace' => "http://search.cpan.org/dist/Archive-Zip/" ),
          'replace' => "http://search.cpan.org/dist/Archive-Zip/" ),
# Mirror problem was search.cpan.org
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

   array( 'pkg'     => 'Data-Dump',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Data-Dump/" ),

   array( 'pkg'     => 'DateTime-Calendar-Julian',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/DateTime-Calendar-Julian/" ),

   array( 'pkg'     => 'DateTime-Format-Builder',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/DateTime-Format-Builder/" ),

   array( 'pkg'     => 'Encode-EUCJPASCII',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Encode-EUCJPASCII/" ),

   array( 'pkg'     => 'Encode-HanExtra',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Encode-HanExtra/" ),

   array( 'pkg'     => 'Encode-JIS2K',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Encode-JIS2K/" ),

   array( 'pkg'     => 'File-BaseDir',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/File-BaseDir/" ),

   array( 'pkg'     => 'File-Slurper',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/File-Slurper/" ),

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

   array( 'pkg'     => 'Net-SSLeay',
          'match'   => '^.*$',
          'replace' => " https://github.com/rurban/Net-SSLeay/releases" ),

   array( 'pkg'     => 'Parse-Yapp',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Parse-Yapp/" ),

   array( 'pkg'     => 'PerlIO-utf8_strict',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/PerlIO-utf8_strict/" ),

   array( 'pkg'     => 'Readonly-XS',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Readonly-XS/" ),

   array( 'pkg'     => 'Regexp-Common',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Regexp-Common/" ),

   array( 'pkg'     => 'Sort-Key',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Sort-Key/" ),

   array( 'pkg'     => 'Test-Command',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Test-Command/" ),

   array( 'pkg'     => 'Test-Differences',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Test-Differences/" ),

   array( 'pkg'     => 'Text-BibTeX',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Text-BibTeX/" ),

   array( 'pkg'     => 'Text-CSV',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Text-CSV/" ),

   array( 'pkg'     => 'Text-Roman',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Text-Roman/" ),

   array( 'pkg'     => 'Unicode-Collate',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Unicode-Collate/" ),

   array( 'pkg'     => 'Unicode-LineBreak',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Unicode-LineBreak/" ),

   array( 'pkg'     => 'XML-LibXML-Simple',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/XML-LibXML-Simple/" ),

   array( 'pkg'     => 'XML-LibXSLT',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/XML-LibXSLT/" ),

   array( 'pkg'     => 'XML-Simple',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/XML-Simple/" ),

   array( 'pkg'     => 'Python',
          'match'   => '^.*$',
          'replace' => "https://www.python.org/downloads/source/" ),

   array( 'pkg'     => 'Python1',
          'match'   => '^.*$',
          'replace' => "https://www.python.org/downloads/source/" ),

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
          'replace' => "https://pypi.org/project/Mako" ),

   array( 'pkg'     => 'php',
          'match'   => '^.*$',
          'replace' => "http://us2.php.net/distributions" ),

   array( 'pkg'     => 'ruby',
          'match'   => '^.*$',
          'replace' => "https://www.ruby-lang.org/en" ),
          //'replace' => "http://cache.ruby-lang.org/pub/ruby/" ),

   array( 'pkg'     => 'setuptools',
          'match'   => '^.*$',
          'replace' => "https://pypi.python.org/pypi/setuptools" ),

   array( 'pkg'     => 'lxml',
          'match'   => '^.*$',
          'replace' => "https://github.com/lxml/lxml/releases" ),

   array( 'pkg'     => 'funcsigs',
          'match'   => '^.*$',
          'replace' => "https://pypi.python.org/pypi/funcsigs" ),

   array( 'pkg'     => 'pycrypto',
          'match'   => '^.*$',
          'replace' => "https://pypi.python.org/pypi/pycrypto" ),

   array( 'pkg'     => 'pycairo',
          'match'   => '^.*$',
          'replace' => "https://github.com/pygobject/pycairo/releases" ),

   array( 'pkg'     => 'scour',
          'match'   => '^.*$',
          'replace' => "https://github.com/scour-project/scour/releases" ),

   array( 'pkg'     => 'six',
          'match'   => '^.*$',
          'replace' => "https://pypi.python.org/pypi/six" ),
);

function get_packages( $package, $dirpath )
{
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
      $lines = http_get_file( "$dirpath/" );

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
      $lines    = http_get_file( "$dirpath/" );
      return find_max( $lines, "/gcc-\d/", "/^.*gcc-(\d[\d\.]+).*$/" );
    }

    // slang
    if ( $book_index == "slang" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position ) . "/latest";
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
    $lines = http_get_file( "$dirpath/" );
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
         $dir   = find_even_max( $lines1, '/^\s+2[\d\.]+.*$/', '/^\s+(2[\d\.]+).*$/' );
      else
         $dir   = find_even_max( $lines1, '/^\s+[\d\.]+.*$/', '/^\s+([\d\.]+).*$/' );

      $dirpath .= "/$dir/";
    }

    if ( $package == "npapi-sdk" )
    {
      # We have to process the stupid javascript to get this to work
      exec( "lynx -dump  $dirpath", $output );
      $max = find_max( $output, "/npapi-sdk/", "/^.*npapi-sdk-([\d\.]*\d)\.tar.*$/" );
      return $max;
    }

    if ( $package == "jdk" )
    {
      exec( "lynx -dump  $dirpath", $lines );
      $version = find_max( $lines, "/Java SE/", "/^.*Java SE (\d[\d\.]+).LTS.*$/" );
      return $version;
/*
      exec( "lynx -dump  $dirpath", $lines1 );
      $dir = find_max( $lines1, "/jdk\d/", "/^.*(jdk\d+u).*$/" );
      $dirpath .= "/$dir/tags";
      exec( "lynx -dump  $dirpath", $lines );
//print_r($lines);
      $version = find_max( $lines, "/jdk-/", "/^.*jdk-([\d\.\+]+) .*$/" );
//echo "version=$version\n";
      return $version;
*/
    }

    if ( $book_index == "vala" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( "$dirpath/" );
      $dir      = find_even_max( $lines, "/\d[\d\.]+/", "/^\s*(\d[\d\.]+).*/" );
      $dirpath .= "/$dir/";
    }

    $strip = "yes";
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

  if ( $book_index == "ruby" )
    return find_max( $lines, '/Released/', '/^.*Ruby (\d\.[\d\.]+\d) R.*$/' );

  if ( $book_index == "Net-SSLeay" )
    return find_max( $lines, '/\d\./', '/^.*(\d\.[\d\._]+\d)$/' );

  if ( $book_index == "lua11" )
    return find_max( $lines, '/lua-5.2/', '/^.*lua-(5.2.[\d\.]+).tar.*$/' );

  if ( $book_index == "doxygen" )
    return find_max( $lines, '/doxygen/', '/^.*doxygen-([\d\.]+).src.tar.*$/' );

  if ( $book_index == "llvm" )
     return find_max( $lines, "/Download LLVM/",  "/^.*LLVM ([\d\.]+).*$/" );

  if ( $book_index == "elfutils" )
     return find_max( $lines, "/\d[\d\.]+/", "/^.* (\d[\d\.]+)$/" );

  if ( $book_index == "scour" )
     return find_max( $lines, "/v\d/", "/^.*v(\d[\d\.]+).*$/" );

  if ( $package == "ninja" )  // ninja
  {
    $max = find_max( $lines, "/v\d/", "/^.*v(\d[\d\.]*\d).*$/" );
    return $max;
  }

  if ( $package == "scour" )
    return find_max( $lines, "/v/", "/^.*v(\d[\d\.]*\d).*$/" );

  if ( $package == "cbindgen" )
    return find_max( $lines, "/v\d/", "/^.*v(\d[\d\.]*\d).*$/" );

  if ( $package == "expect" )
    return find_max( $lines, "/expect/", "/^.*expect(\d[\d\.]*\d).tar.*$/" );

  if ( $package == "tcl" )
    return find_max( $lines, "/tcl/", "/^.*tcl(\d[\d\.]*\d)-src.*$/" );

  // Same as tcl
  if ( $book_index == "tk" )
  {
    return find_max( $lines, "/tcl/", "/^.*tcl(\d[\d\.]*\d)-src.*$/" );
    //$dir = find_max( $lines, '/8\./', '/^.*(8\.[\d\.]+\d).*$/' );
    //$lines = http_get_file( "$dirpath/$dir" );
    //return find_max( $lines, "/$package/", "/^.*$package([\d\.]*\d)-src.tar.*$/" );
  }

  if ( $package == "rustc" )
  {
    $max = find_max( $lines, "/release/", "/^.* (\d[\d\.]+\d) release.*$/" );
    return $max;
  }

  if ( $book_index == "swig" )
     return find_max( $lines, "/swig-/", "/.*swig-(\d[\d\.]+\d).*/" );

  if ( $book_index == "nasm" )
    return find_max( $lines, '/^\d/', '/^(\d[\d\.]+\d)\/.*$/' );

  if ( $book_index == "cmake" )
  {
    $max =   find_max( $lines, "/$package/",
                               "/^.*$package-([\d\.]*\d)\.tar.*$/" );
    if ( $max == 0 )
      $max = find_max( $lines, "/$package/",
                               "/^.*$package-(\d[\d\.]*\d-rc\d).*$/" );
    return ( $max != 0 ) ? $max : "pending";
  }

  if ( $book_index == "pygobject1" ||
       $book_index == "pygobject "  )
    $package = "pygobject";

  if ( $book_index == "librep" )
    return find_max( $lines, "/librep/", "/^.*[_-](\d[\d\.]*\d)\.tar.*$/" );

  if ( $book_index == "pycairo" )
    return find_max( $lines, "/pycairo/", "/^.*pycairo-(\d[\d\.]*\d)\.tar.*$/" );

  if ( $book_index == "apache-ant" )
    return find_max( $lines, "/$package/", "/^.*$package-(\d[\d\.]+\d)-src.*$/" );

  if ( $book_index == "Text-CSV" )
    return find_max( $lines, "/$package/", "/^.*$package-(\d[\d\.]+\d).*$/" );

  if ( $book_index == "scons" )
    return find_max( $lines, "/\d\.\d\.\d/", "/^.*(\d\.\d\.\d).*$/" );

  if ( $book_index == "apache-maven" )
    return find_max( $lines, "/$package/", "/^.*$package-(\d[\d\.]+\d)-src.*$/" );

  if ( $book_index == "junit4-r" )
    return find_max( $lines, "/junit/", "/^.*junit-(\d[\d\.]+\d).jar.*$/" );

  // Most packages are in the form $package-n.n.n
  // Occasionally there are dashes (e.g. 201-1)
  $max = find_max( $lines, "/$package/", "/^.*$package-([\d\.]+\d).tar.*$/" );
  return $max;
}

Function get_pattern( $line )
{
   // Set up specific patter matches for extracting book versions
   $match = array();

   $match = array(
     //array( 'pkg'   => 'py2cairo',
     //       'regex' => "/py2cairo-([\d\.]+)/" ),

     array( 'pkg'   => 'Encode-JIS2K',
            'regex' => "/\D*Encode-JIS2K-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'IPC-Run3',
            'regex' => "/\D*IPC-Run3-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'Log-Log4perl',
            'regex' => "/\D*Log-Log4perl-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'PerlIO-utf8_strict',
            'regex' => "/\D*PerlIO-utf8_strict-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'Jinja2',
            'regex' => "/\D*Jinja2-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'junit',
            'regex' => "/\D*junit4-r([\d\.]+)\D*$/" ),

     // Order matters here.  jtreg must be before OpenJDK
     array( 'pkg'   => 'jtreg',
            'regex' => "/jtreg-(\d[\d\.b-]+)$/" ),

     array( 'pkg'   => 'OpenJDK',
            'regex' => "/OpenJDK-([\d\.]+)\+?.*$/" ),

     array( 'pkg'   => 'openjdk',
            'regex' => "/openjdk-([\d\.]+)\+?.*$/" ),
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
