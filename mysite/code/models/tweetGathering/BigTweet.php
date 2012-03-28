<?php
class BigTweet extends Tweet {
	static $db = array( "All" => "Text" );
	var $chopped;
	var $link;

	function forTemplate() {
		return $this -> All;
	}
	
// Decimal > Custom
static function dec2any( $num, $base=62, $index=false ) {
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
static function any2dec( $num, $base=62, $index=false ) {
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

function chopIt($at) {
	$chop = strrpos(substr($this->All,0,$at),' ' );
	$link = "http://wealltwee.com?s=". self::dec2any($this->ID);
	$full = substr($this->All,0,$chop) . "... -> " . $link;
	return $full;
}

function chopper($min,$max) {
	//error_log("Min = $min, Max = $max\n");
	if($min+1 >= $max ) return $this->chopIt( $min);
	$cut = ($min+$max)/2 ;
	$full = $this->chopIt((int)$cut);
	//error_log("strlen=" . strlen($full) . " text = $full \n");
	if(strlen($full) > 140 ) {
		return $this->chopper($min,(int)$cut);
	} else return $this->chopper((int)$cut,$max);
}

static function newTweet($text, TweetUser $user, $extraTag=false) {
	$newTweet = Tweet::newTweet("", $user, $extraTag)->newClassInstance('BigTweet');
	$newTweet -> All = $text;
	$newTweet ->Title =  $newTweet->chopper(1,140);
	$newTweet->write();
	return $newTweet;
}

}

?>
