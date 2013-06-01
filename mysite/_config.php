<?php

global $project;
$project = 'mysite';

global $databaseConfig;
$databaseConfig = array(
	"type" => 'MySQLDatabase',
	"server" => '127.0.0.1:3306',
	"username" => 'jahbini',
	"password" => 'G3tTh1n',
	"database" => 'webnets',
	"path" => '',
);
/*
$databaseConfig = array(
	"type" => 'MySQLDatabase',
	"server" => 'mysql.modernmarianas.com',
	"username" => 'modern_marianas',
	"password" => 'mM2f4qMD',
	"database" => 'modern_marianas',
	"path" => '',
);
 */

global $thisDomain;
$thisDomain = 'webnets';   // will be ModernMarianas.com on Dreamhost


MySQLDatabase::set_connection_charset('utf8');

// Set the current theme. More themes can be downloaded from
// http://www.silverstripe.org/themes/
SSViewer::set_theme('Simple');

// Set the site locale
i18n::set_locale('en_US');

// Enable nested URLs for this site (e.g. page/sub-page/)
if (class_exists('SiteTree')) SiteTree::enable_nested_urls();

global $Geo; $Geo = 'North America';
global $GeoShort; $GeoShort = 'en';
global $databaseConfig;
	
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

  global $OrganizerLocation;
  $OrganizerLocation = 'en';

global $WantedSubDomain;
  $WantedSubDomain = array_shift($names);
  if (strcasecmp($WantedSubDomain,"www") == 0) $WantedSubDomain = array_shift($names);
if (!$WantedSubDomain) $WantedSubDomain= 'all';
switch ($WantedSubDomain) {
default: $WantedSubDomain = 'all';
}

error_log("-----------------------------------------------------------------");
error_log("config says WantedSubDomain= $WantedSubDomain");

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
//global $lucky;
//$lucky="error_log('hello');";
//eval($lucky);
//$_GET['isDev']=1;
function ERROR_LOGGER($f) {error_log($f); }

Security::setDefaultAdmin('jahbini','password');
function _e($msg) {user_error($msg,E_USER_ERROR);}
function _w($msg) {user_error($msg,E_USER_WARNING);}
/*
define('RELAY_HOST', 'webnets');
 */
define('RELAY_HOST', 'modernmarianas.com');
define('ANALYTICS', false);  //only enable google analytics when site is live
Director::set_environment_type("dev");

function setLocalItem($key,$item){
      global $locals;
	 $locals[$key] = $item;
	 return $item;
}

function getFromCacheOrURL($what,$fetch=true){
      global $locals;
 if(isset($locals[$what]) ) return $locals[$what];
 if(isset($_REQUEST[$what] )) {
    $value = $_REQUEST[$what];
    if ($fetch) $value = DataObject::get_by_id($what,$value);
    $locals[$what]=$value;
    return $value;
 }
 return false;
}


function getFromCacheOrSession($what,$fetch=true,$sessionKey='loggedInAs'){
      global $locals;
 if(isset($locals[$what]) ) return $locals[$what];
 $value = Session::get($sessionKey);
 if($value>0 ) {
    if ($fetch) $value = DataObject::get_by_id($what,$value);
    $locals[$what]=$value;
    return $value;
 }
 return false;
}

