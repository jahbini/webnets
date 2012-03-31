<?php


global $project;
$project = 'tell140';
global $Geo; $Geo = 'North America';
global $GeoShort; $GeoShort = 'en';
global $databaseConfig;
global $consumer_key;
global $consumer_secret;

// Sites running on the following servers will be
// run in development mode. See
// http://doc.silverstripe.com/doku.php?id=devmode
// for a description of what dev mode does.
Director::set_dev_servers(array(
	'webnets',
	'127.0.0.1',
));

	
  if ( array_key_exists('HTTP_HOST', $_SERVER)) {
	  $names = explode ('.', strtolower($_SERVER['HTTP_HOST'] ));
  } else {
	  if (!isset($_SERVER['SCRIPT_URI']) ) $_SERVER['SCRIPT_URI'] = 'http://localhost/';
	  preg_match('@^(?:http://)?([^/]+)@i', strtolower($_SERVER['SCRIPT_URI']), $matches);
	  $host = $matches[1];
	  $names = explode ('.', $host );
  }

// strip off the trailing .com or .mobi -- 
  $comMobi= array_pop($names);

  global $MentorLocation;
  $MentorLocation = 'en';
  global $MentorName;
  $MentorName = '';

global $wantedSubDomain;
  $wantedSubDomain = array_shift($names);
  if (strcasecmp($wantedSubDomain,"www") == 0) $wantedSubDomain = array_shift($names);
if (!$wantedSubDomain) $wantedSubDomain= 'generic';
switch ($wantedSubDomain) {
case 'webnets': break;
default: $wantedSubDomain = 'generic';
}

//echo("local group is $localGroup");
//die();
/*
  $prefix = array_shift($names);
  if (strcasecmp($prefix,"www") == 0) $prefix = array_shift($names);
  if($prefix == "powerstation") $prefix = '';
  if ($prefix){
	  switch($prefix) {
	  case "sfo":
	  	$tweeparty_profile = 'hotinsfo';
		$sponsor = 'hotinsfo';
		$GeoShort = "SFO";
		$Geo="San Francisco";
		  $MentorLocation = 'sfo';
		break;
	  case "hnl":
	  case "honolulu":
		$tweeparty_profile = 'hotinhnl';
		$sponsor = 'hotinhnl';
		$GeoShort = "HNL";
		$Geo='Honolulu';
		  $MentorLocation = 'hnl';
		break;
	case "governot":
		$tweeparty_profile = 'governot';
		$sponsor = 'governot';
		$MentorName = 'governot';
		break;
	  default: 
	  if ($comMobi == "mobi") {
		$tweeparty_profile = 'hotinhnl';
		  $MentorLocation = 'hnl';
		$sponsor = 'hotinhnl';
		$Geo='Honolulu';
	  }
	  else {
		  $tweeparty_profile = 'socialite';
	  }
	  }
  }
 */
if (getenv('DREAMHOST') ) {
if(Director::isLive()) {
	Debug::send_errors_to("error@kegare.org",true);
	$consumer_key = "TqRy6dnFvGJaPa4OeBljQ"; // tweeparty
	$consumer_secret = 'bsSXEmLox4woNCGwT3uPQixyidh5NC0rSU0joZb62E';

	$consumer_key = "bfOttpET0m7Zf3Oxeg4bQ"; // we all twee
	$consumer_secret = 'eJYR3VZbRWb1kTrfFgMSGsg1L3rJ9n0CI3DRrSTAE';
	$databaseConfig = array(
		"type" => "MySQLDatabase",
		"server" => "mysql.411-source.com", 
		"username" => "jahbini", 
		"password" => "1amwhat1am", 
		"database" => "wealltwee"
	);
	} else {
	$consumer_key = 	"HEYLLGiET8IrJZli7hoFwA";
	$consumer_secret = "kKbd2OZjdbD4A3Q5f2FBELxPSOWD5X4EKic9mRQfEk";
	// really do not want two databases, but oAuth application keys cause user tokens to fail
	$databaseConfig = array(
		"type" => "MySQLDatabase",
		"server" => "mysql.411-source.com", 
		"username" => "jahbini", 
		"password" => "1amwhat1am", 
		"database" => "tweeparty_test"
	);
       	}


} else {
Security::setDefaultAdmin('jahbini','password');
/* use powerstation credentials */
	$consumer_key = 	"HEYLLGiET8IrJZli7hoFwA";
	$consumer_secret = "kKbd2OZjdbD4A3Q5f2FBELxPSOWD5X4EKic9mRQfEk";

$databaseConfig = array(
	"type" => "MySQLDatabase",
	"server" => "p:127.0.0.1:3306", 
	"username" => "jahbini", 
	"password" => "G3tTh1n", 
	"database" => "webnets"
);
}
/* use tell140 credentials */
	$consumer_key = 	"uCTUCMz2s5K4fxqzTThiQ";
	$consumer_secret = "nvrbum4ruYGX3irYq42TGy4rbY2vN0uetaaOwykF0Ls";

//Security::setDefaultAdmin('jahbini','password');
Authenticator::register_authenticator('ProfileAuthenticator');
Authenticator::set_default_authenticator('ProfileAuthenticator');
//Cookie::set_report_errors(false);
//$_GET['debug']=1;
//$_REQUEST['debug']=1;
include_once("code/utility/PleaseMap.php");
//include_once("code/utility/PaneDef.php");
include_once("code/models/tweetGathering/TwitterQuery.php");
//include_once("code/background/DoToDoItem.php");
include_once("code/models/tweetGathering/Tweet.php");
//include_once("code/auth/twitterOAuth.php");

//Director::addRules(60,array( 'tag//$ID' => 'TagPage' ) );
Director::addRules(60,array( 'Test//$action/$ID/$who' => 'Test' ) );
Director::addRules(60,array( 'DoToDoItem' => 'DoToDoItem' ) );
Director::addRules(60,array( 'DoToDoItem//$action' => 'DoToDoItem' ) );
//Director::addRules(90,array( '$ID' => 'ShowContest') );

//require_once("../../sapphire/core/model/DB.php");


function &_v($parent,$index,$kind = "stdClass",$fail=true){
	if ( isset($parent->{$index} ) && is_object($t =& $parent->{$index} ) && is_a($t,$kind) ) return $t;
	if($fail) { 
		$pid = isset($parent->ID)?$parent->ID:"no ID";
		$className = get_class($parent);
		_e ("The $index element of $className ($pid) is NOT a $kind thing");
	}
	// we return false if the caller wants to suppress the error
	return $fail;
}
$_GET['isDev']=1;

Security::setDefaultAdmin('jahbini','password');
function _e($msg) {user_error($msg,E_USER_ERROR);}
function _w($msg) {user_error($msg,E_USER_WARNING);}
?>
