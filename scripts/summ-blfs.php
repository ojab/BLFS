#! /usr/bin/php
<?php

date_default_timezone_set( "GMT" );
$date = date( "Y-m-d H:i:s" );


# Get ticket info
$report = "http://wiki.linuxfromscratch.org/blfs/report/1";
$file   = "/tmp/report.html";

system( "wget $report -q -O $file" );

$contents = file_get_contents( $file );
$c = strip_tags( $contents );

$a = explode( "\n", $c );

$l = 0;

$ticket = array();
$desc   = array();

foreach ( $a as $line )
{

   if ( preg_match( "/#/", $line ) )
   {
      $t       = preg_replace( "/^.*(#\d+).*$/",  "$1", $line );
      $summary = $a[$l+3];

      array_push( $ticket, $t );
      array_push( $desc, trim($summary) );
   }

   $l++;
}

# Get package info

// HTML_DIR       environment variable -- where to put html output
$d = getenv( 'HTML_DIR' );
$HTML_DIR = ($d) ? $d : '.';

exec( "grep '\*' $HTML_DIR/chapter*.html", $diff );
$d = implode( "\n", $diff );

$file = preg_replace( "/<\/td><td>/"   , " "        ,   $d    );
$file = preg_replace( "/chapter(\d)\./", "chapter0$1.", $file );
$file = preg_replace( "/\*/"           , " *"       ,   $file );
$file = preg_replace( "/\.html/"       , ""         ,   $file );
$file = preg_replace( "/:/"            , " "        ,   $file );
$file = preg_replace( "/\/home.*chapter/"      , "" ,   $file );

$f = strip_tags( $file );
$a = explode( "\n", $f );
sort($a);

$msg = "            BLFS Package        BLFS Version              Latest      Ticket\n";

foreach ( $a as $l )
{
  $c = preg_split( "/ /", $l );
  if ( $c[0] == "" ) continue;
  $pkg  = $c[1];

  if ( preg_match( "/libreoffice-/", $pkg ) ) continue;

  if ( $pkg == "baloo"            ||
       $pkg == "baloo-widgets"    ||
       $pkg == "kactivities"      ||
       $pkg == "kfilemetadata"    ||
       $pkg == "kdeplasma-addons" ||
       $pkg == "konsole"          ||
       $pkg == "kate"             ||
       $pkg == "gwenview"         ||
       $pkg == "oxygen-icons"     ||
       $pkg == "kde-base-artwork" ||
       $pkg == "kde-baseapps"     ||
       $pkg == "kdepimlibs"       ||
       $pkg == "obconf-qt"        ||
       $pkg == "libdbusmenu-qt"   ||
       $pkg == "juffed"     
     ) continue;

  if ( $c[3] == "0" ) $c[3] .= " ";
  $x    = substr("chapter $c[0]: $c[1]                         ", 0, 32);
  $x   .= substr("$c[2]                          ",  0, 25);
  $x   .= substr("$c[3]           ", 0, 13);

  $tick = "";

  for ( $i=0; $i<count($ticket); $i++ )
  {
     $pkg = preg_replace( "/\+/", ".", $pkg );
     if ( preg_match( "/$pkg/i", $desc[$i] ) )
     {
        // Make sure cmake != cmake-extra-modules
        if ( $pkg == "cmake" &&
             preg_match( "/extra/", $desc[$i] ) 
           ) continue;

        $tick = $ticket[$i];
        break;
     }
  }

  $x .= "$tick\n";
  //echo $x;
  $msg .= $x;
}

//echo $msg;  // For debugging
//exit;

$from    = "bdubbs@linuxfromscratch.org";
$subject = "BLFS Package Currency Check - $date GMT";
$to      = "blfs-book@lists.linuxfromscratch.org";

exec ( "echo '$msg' | mailx -r $from -s '$subject' $to" );

?>
