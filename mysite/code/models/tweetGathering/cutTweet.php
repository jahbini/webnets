<?php
$lt1 = "thisisaverylongwtwwtwithtweetliciosnothingness,verymuchlikeanyotheretweet,exceptthisismor elikenonsensethanmost,but since it actually attempts to explain itself, it is less like the nonsense of nearly everyother tweet, nes-pas?";

$myid = (int)987654321;

// Decimal > Custom
function dec2any( $num, $base=62, $index=false ) {
    if (! $base ) {
        $base = strlen( $index );
    } else if (! $index ) {
        $index = substr( "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" ,0 ,$base );
    }
    $out = "";
    for ( $t = floor( log10( $num ) / log10( $base ) ); $t >= 0; $t-- ) {
        $a = floor( $num / pow( $base, $t ) );
        $out = $out . substr( $index, $a, 1 );
        $num = $num - ( $a * pow( $base, $t ) );
    }
    return $out;
}
//Parameters:
//$num - your decimal integer
//$base - base to which you wish to convert $num (leave it 0 if you are providing $index or omit if you're using default (62))
//$index - if you wish to use the default list of digits (0-1a-zA-Z), omit this option, otherwise provide a string (ex.: "zyxwvu")

// Custom > Decimal
function any2dec( $num, $base=62, $index=false ) {
    if (! $base ) {
        $base = strlen( $index );
    } else if (! $index ) {
        $index = substr( "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", 0, $base );
    }
    $out = 0;
    $len = strlen( $num ) - 1;
    for ( $t = 0; $t <= $len; $t++ ) {
        $out = $out + (int)strpos( $index, substr( $num, $t, 1 ) ) * (int)pow( $base, $len - $t );
    }
    return $out;
}

function chopIt($text,$at) {
	global $z;
	$chop = strrpos(substr($text,0,$at),' ' );
	$link = "http://wealltwee.com?s=".$z;
	$full = substr($text,0,$chop) . "... -> " . $link;
	return $full;
}

function chopper($text,$min,$max) {
	echo("Min = $min, Max = $max\n");
	if($min+1 >= $max ) return chopIt( $text,$min);
	$cut = ($min+$max)/2 ;
	$full = chopIt($text,(int)$cut);
	echo("strlen=" . strlen($full) . " text = $full \n");
	if(strlen($full) > 140 ) {
		return chopper($text,$min,(int)$cut);
	} else return chopper($text,(int)$cut,$max);
}

echo (PHP_INT_MAX. "\n");
echo (($z = dec2any( PHP_INT_MAX) ). "\n");
echo (any2dec($z). "\n");

echo ( $myid . "\n");
echo (($z= dec2any( $myid) ). "\n");
echo (any2dec( $z). "\n");

echo (strlen($lt1) . "\n");
echo (strrpos(substr($lt1,0,105),' ' ). "\n");
$chop = strrpos(substr($lt1,0,105),' ' );
$start = substr($lt1,0,$chop)  ;
$link = "http://wealltwee.com?s=".$z;
$full = $start . "... -> " . $link;
echo (substr($lt1,0,$chop) . "|\n") ;
echo $full . "\n";
echo (strlen($full) . "\n");
echo (strlen($link) . "\n");

$realCut = chopper($lt1,1,140);
echo($realCut . "\n");
echo(strlen($realCut) . "\n" );

?>
