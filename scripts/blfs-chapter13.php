#! /usr/bin/php
<?php

include 'blfs-include.php';

$CHAPTER       = '13';
$CHAPTERS      = 'Chapter 13';
$START_PACKAGE = 'check';
$STOP_PACKAGE  = 'junit';

$renames = array();
//$renames[ 'librep_'    ] = 'librep';
$renames[ 'py'         ] = 'pycairo';
$renames[ 'Python'     ] = 'python2';
#$renames[ 'python'     ] = 'python2 docs';
$renames[ 'Python1'    ] = 'python3';
#$renames[ 'python1'    ] = 'python3 docs';
$renames[ 'pygobject'  ] = 'pygobject2';
$renames[ 'pygobject1' ] = 'pygobject3';
//$renames[ 'junit4_'    ] = 'junit';

$ignores = array();
$ignores[ 'cfe'          ] = '';
$ignores[ 'clang'        ] = '';
$ignores[ 'compiler-rt'  ] = '';
$ignores[ 'git-htmldocs' ] = '';
$ignores[ 'git-manpages' ] = '';
$ignores[ 'lua1'         ] = '';
$ignores[ 'nasm1'        ] = '';
$ignores[ 'tcl1'         ] = '';
$ignores[ 'gcc1'         ] = '';
$ignores[ 'gcc11'        ] = '';
$ignores[ 'OpenJDK1'     ] = '';
$ignores[ 'hamcrest'     ] = '';
$ignores[ 'apache-ant1'  ] = '';
$ignores[ 'icedtea-web'  ] = '';
$ignores[ 'python'       ] = '';
$ignores[ 'python1'      ] = '';

//$current="XML-Writer";  // For debugging

$regex = array();
$regex[ 'check'   ] = "/^.*Download check-(\d[\d\.]+\d).tar.*$/";
$regex[ 'expect'  ] = "/^.*Download expect(\d[\d\.]+\d).tar.*$/";
$regex[ 'junit4'  ] = "/^\h*(\d[\d\.]+)\h*$/";
$regex[ 'llvm'    ] = "/^.*Download LLVM (\d[\d\.]+\d).*$/";
$regex[ 'scons'   ] = "/^.*Download scons-(\d[\d\.]+\d).*$/";
$regex[ 'tcl'     ] = "/^.*Download tcl(\d[\d\.]+\d).*$/";
$regex[ 'swig'    ] = "/^.*Download swig-(\d[\d\.]+\d).*$/";
$regex[ 'Python'  ] = "/^.*Latest Python 2.*Python (2[\d\.]+\d).*$/";
$regex[ 'Python1' ] = "/^.*Latest Python 3.*Python (3[\d\.]+\d).*$/";
$regex[ 'Mako'    ] = "/^.*version is (\d[\d\.]+\d).*$/";
$regex[ 'php'     ] = "/^.*php-(\d[\d\.]+\d).tar.*$/";
$regex[ 'valgrind'] = "/^.*valgrind (\d[\d\.]+\d) \(tar.*$/";
$regex[ 'jtreg'   ] = "/^.*jtreg-(\d[b\d\.\-]+\d)\.tar.*$/";
$regex[ 'OpenJDK' ] = "/^.*OpenJDK-(\d[\d\.]+\d)\-.*$/";

// Perl Modules
$regex[ 'Archive-Zip'       ] = "/^.*Archive-Zip-(\d[\d\.]+\d).*$/";
$regex[ 'autovivification'  ] = "/^.*autovivification-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISBN'     ] = "/^.*Business-ISBN-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISMN'     ] = "/^.*Business-ISMN-(\d[\d\.]+\d).*$/";
$regex[ 'Business-ISSN'     ] = "/^.*Business-ISSN-(\d[\d\.]+\d).*$/";
$regex[ 'Data-Compare'      ] = "/^.*Data-Compare-(\d[\d\.]+\d).*$/";
$regex[ 'Data-Dump'         ] = "/^.*Data-Dump-(\d[\d\.]+\d).*$/";
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
$regex[ 'Test-Command'      ] = "/^.*Test-Command-(\d[\d\.]+\d).*$/";
$regex[ 'Test-Differences'  ] = "/^.*Test-Differences-(\d[\d\.]+\d).*$/";
$regex[ 'Test-Pod'          ] = "/^.*Test-Pod-(\d[\d\.]+\d).*$/";
$regex[ 'Test-Pod-Coverage' ] = "/^.*Test-Pod-Coverage-(\d[\d\.]+\d).*$/";
$regex[ 'Text-BibTeX'       ] = "/^.*Text-BibTeX-(\d[\d\.]+\d).*$/";
$regex[ 'Text-Roman'        ] = "/^.*Text-Roman-(\d[\d\.]+\d).*$/";
$regex[ 'Unicode-Collate'   ] = "/^.*Unicode-Collate-(\d[\d\.]+\d).*$/";
$regex[ 'Unicode-LineBreak' ] = "/^.*Unicode-LineBreak-(\d[\d\.]+\d)$/";
$regex[ 'XML-LibXML-Simple' ] = "/^.*XML-LibXML-Simple-(\d[\d\.]+\d).*$/";
$regex[ 'XML-LibXSLT'       ] = "/^.*XML-LibXSLT-(\d[\d\.]+\d).*$/";
$regex[ 'XML-Simple'        ] = "/^.*XML-Simple-(\d[\d\.]+\d).*$/";

$url_fix = array (

   array( //'pkg'     => 'gnome',
          'match'   => '^ftp:\/\/ftp.gnome',
          'replace' => "http://ftp.gnome" ),

   array( 'pkg'     => 'check',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/check/files" ),

   array( 'pkg'     => 'expect',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/expect/files" ),

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

#   array( 'pkg'     => 'python',
#          'match'   => '^.*$',
#          'replace' => "https://docs.python.org/2/archives" ),

#   #array( 'pkg'     => 'python1',
#          'match'   => '^.*$',
#          'replace' => "https://docs.python.org/3/archives" ),

   array( 'pkg'     => 'scons',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/scons/files" ),

   array( 'pkg'     => 'tcl',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/tcl/files" ),

   array( 'pkg'     => 'tk',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/tcl/files/Tcl" ),

   array( 'pkg'     => 'swig',
          'match'   => '^.*$',
          'replace' => "http://sourceforge.net/projects/swig/files/swig" ),

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

   array( 'pkg'     => 'Data-Dump',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Data-Dump/" ),

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

   array( 'pkg'     => 'Test-Command',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Test-Command/" ),

   array( 'pkg'     => 'Test-Differences',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Test-Differences/" ),

   array( 'pkg'     => 'Test-Pod',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Test-Pod/" ),

   array( 'pkg'     => 'Test-Pod-Coverage',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Test-Pod-Coverage/" ),

   array( 'pkg'     => 'Text-BibTeX',
          'match'   => '^.*$',
          'replace' => "http://search.cpan.org/dist/Text-BibTeX/" ),

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
          'replace' => "http://www.makotemplates.org/download.html" ),

   array( 'pkg'     => 'php',
          'match'   => '^.*$',
          'replace' => "http://us2.php.net/distributions" ),

);

/*
     // Skip minor versions in the 90s (most of the time)
     list( $major, $minor, $rest ) = explode( ".", $slice . ".0.0" );
     if ( $minor >= 90                       &&
          $book_index != "librep"            &&
          $book_index != "Glib"              &&
          $book_index != "Business-ISSN"     &&
          $book_index != "XML-LibXML-Simple" &&
          $book_index != "XML-LibXSLT"       &&
          $book_index != "elfutils"          ) continue;
*/

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

    if ( $book_index == "vala" )
    {
       // Get the max directory and adjust the directory path
      $dirpath  = rtrim  ( $dirpath, "/" );    // Trim any trailing slash
      $position = strrpos( $dirpath, "/" );
      $dirpath  = substr ( $dirpath, 0, $position );
      $lines    = http_get_file( "$dirpath/" );
      $dir      = find_even_max( $lines, "/\d[\d\.]+/", "/^(\d[\d\.]+).*/" );
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
         $dir      = find_even_max( $lines1, '/^\s+2[\d\.]+.*$/', '/^\s+(2[\d\.]+).*$/' );
      else
         $dir      = find_even_max( $lines1, '/^\s+[\d\.]+.*$/', '/^\s+([\d\.]+).*$/' );

      $dirpath .= "/$dir/";
    }

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
    }

    if ( $package == "npapi-sdk" )
    {
      # We have to process the stupid javascript to get this to work
      exec( "lynx -dump  $dirpath", $output );
      $max = find_max( $output, "/npapi-sdk/", "/^.*npapi-sdk-([\d\.]*\d)\.tar.*$/" );
      return $max;
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

  if ( $book_index == "doxygen" )
    return find_max( $lines, '/doxygen/', '/^.*doxygen-([\d\.]+).src.tar.*$/' );

  if ( $book_index == "llvm" )
  {
     return find_max( $lines, "/^.*$book_index-.*.src.*$/",
                              "/^.*$book_index-([\d\.]+)\.src.*$/" );
  }

  if ( $book_index == "elfutils" )
  {
     return find_max( $lines, "/^\s*\d[\d\.]+\/.*$/",
                              "/^\s*(\d[\d\.]+)\/.*$/" );
  }

  if ( $book_index == "nasm" )
    return find_max( $lines, '/^\d/', '/^(\d[\d\.]+\d)\/.*$/' );

#  if ( $book_index == "python" || $book_index == "python1" )  // python docs
#  {
#    return find_max( $lines, "/python-\d/",
#                             "/^python-(\d[\d\.]*\d)-docs.*$/" );
#  }

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

     array( 'pkg'   => 'Encode-JIS2K',
            'regex' => "/\D*Encode-JIS2K-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'IPC-Run3',
            'regex' => "/\D*IPC-Run3-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'Log-Log4perl',
            'regex' => "/\D*Log-Log4perl-([\d\.]+)\D*$/" ),

     array( 'pkg'   => 'Jinja2',
            'regex' => "/\D*Jinja2-([\d\.]+)\D*$/" ),

     // Order matters here.  jtreg must be before OpenJDK
     array( 'pkg'   => 'jtreg',
            'regex' => "/jtreg-(\d[\d\.b-]+)$/" ),

     array( 'pkg'   => 'OpenJDK',
            'regex' => "/OpenJDK-([\d\.]+)-.*$/" ),

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
