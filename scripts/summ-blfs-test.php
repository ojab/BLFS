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
exec( "grep '\*' /home/bdubbs/public_html/chapter*.html", $diff );
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

$msg = "            BLFS Package        BLFS Version             Latest      Ticket\n";

//echo $msg;


foreach ( $a as $l )
{
  $c = preg_split( "/ /", $l );
  if ( $c[0] == "" ) continue;
  $pkg  = $c[1];

  if ( $pkg == "goffice" ) 
  {
    $c[1] .= $c[2];
    $c[2]  = $c[3];
    $c[3]  = $c[4];
  }

  if ( $c[3] == "0" ) $c[3] .= " ";
  $x    = substr("chapter $c[0]: $c[1]                         ", 0, 32);
  $x   .= substr("$c[2]                          ",  0, 25);
  $x   .= substr("$c[3]          ", 0, 12);

  $tick = "";
//print_r($desc);
  for ( $i=0; $i<count($ticket); $i++ )
  {
//echo "pkg=$pkg\n";
     $pkg = preg_replace( "/\+/", ".", $pkg ); 

     if ( preg_match( "/$pkg/i", $desc[$i] ) )
     {
        $tick = $ticket[$i];
        break;
     }
  }

  $x .= "$tick\n";
  //echo $x;
  $msg .= $x;
}

echo $msg;
exit;

$from    = "bdubbs@linuxfromscratch.org";
$subject = "BLFS Package Currency Check - $date GMT";
$to      = "blfs-book@lists.linuxfromscratch.org";

exec ( "echo '$msg' | mailx -r $from -s '$subject' $to" );

?>
