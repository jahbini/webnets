<?php
// vim:ts=3:sw=3:sts=3:ft=php:


class rqNurse {
	var $relayQuery;
	var $scheduler;
	var $penName;
	var $fail = false;
	var $err = '';
	var $channel = false; // the twitter oAuth channel
	var $mentor = false;
	var $mentee = false;
	function __construct($rQ) {
		$this->relayQuery =& $rQ;
		$this ->penName = DataObject::get_by_id('PenName', $rQ->PenNameID);
		   if(! $this->penName instanceOf PenName ) {
				$this->scheduler= IPScheduler::get_IPscheduler();
		   } else {
			   $this-> scheduler = $this->penName -> get_scheduler();
		   }
	}

	function setMentor($m) {
		$this->mentor =& $m;
	}

	function setMentee($m) {
		$this->mentee =& $m;
		$this->penName =& $m;
	}

	function preSchedule(){
	   $s = $this->scheduler->schedule();
		   ERROR_LOGGER("Scheduler says $s");
		   return $s;
	}

	function postSchedule(){
		// get the headers from twitter and use them to re-schedule the next request
		// and update the scheduler on the RelayQuery
	   return $this-> scheduler-> request_complete($this->channel->responseHeaders() ) ; 
	}

	function save_to_cache($cleanContent){
	   $store = serialize($cleanContent);
	   file_put_contents($this->cache_path,$store);
	   return;
	}

	function get_from_cache($age=90){
	   $cache_key = "queryID" . $this->relayQuery->ID;
	   $cachedir = ASSETS_PATH;
	   $this->cache_path = $cachedir."/$cache_key";
	   //return false; // DEBUG JAH   disable cache
	   if (  @file_exists($this->cache_path)  && @filemtime($this->cache_path) + $age > time() ) {
	      $store= file_get_contents($this->cache_path);
	      return  unserialize($store);
	   }
	   return false;
	}
	function go_to_twitter($params , $cacheOK= true){
	   global $consumer_key;
	   global $consumer_secret;
	   $w = $this->relayQuery->requestString();
	   if($this->mentor) $w=str_replace('mentor',$this->mentor->screen_name,$w);
	   if($this->mentee) $w=str_replace('mentee',$this->mentee->screen_name,$w);
	   if ( !$this->relayQuery->Authenticate() ) {
		   $this->channel = new SaneRest($w. '.json' );
		   //error_log(print_r($params,1));
		   $this->channel -> setQueryString($this->relayQuery->requestParams($params) );
		   $jsdata= $this->channel->request();
		   $content = $jsdata->getBody();
	   } else {
		   $this->channel = new TwitterOAuth($consumer_key, $consumer_secret, $this->penName -> request_token , $this->penName -> request_token_secret);
		   $content = $this->channel->OAuthRequest($w . '.json', $this->relayQuery->requestParams($params),'GET');
		//error_log(print_r($this-> channel -> responseHeaders(),1));
	   }

	   if(!$content && $cacheOK ) {
		   $content = $this->get_from_cache(360);   // get anything up to five minutes old
	   }
	   return $content;
	}
}


class TweetRange {
	var $lowestID;
	var $highestID;
	var $stopperID;
	var $stopped;
	var $accepted_tweets;
	var $request_failed;
	var $message;
	var $request;
	var $reschedule;
	function __construct() {
		$this->lowestID=1.0e20;
		$this->highestID=0;
		$this->stopperID=0;
		$this->accepted_tweets =0;
		$this->stopped=false;
		$this->request_failed=true;
		$this->message="";
		$this->request="";
	}

	function setRequest($msg, $params="") {
		$this->request = $msg. " ?" .$params;
	}
	function getRequest() {
		return $this->request;
	}
	function setMessage($msg,$log=false) {
		if($log) error_log($msg);
		if ($this->message !="") $this->message .= "<br>";
		$this->message .= htmlentities($msg);
	}
	function getMessage() {
		return $this->message;
	}

	function setParams($inp) {
//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
//error_log(print_r($inp,1));
		// set up params for the twitter request
		//   since_id       max_id
		//      -             -      don't set anything
		//      X             -      set since_id as stopper, and return since_id
		//      -             X      return max_id
		//      X             X      set since_id as stopper and return max_id
		$params=array();
		if(isset($inp['max_id']) && ($inp['max_id']  != 0 || $inp['max_id'] < 1.0e13 ) ) $max_id = $inp['max_id'];
		if(isset($inp['since_id']) && ($inp['since_id']  != 0) ) $since_id = $inp['since_id'];
		if(isset($max_id) && $max_id < 10.0e+12 ) {
			if(isset($since_id) ) { 
				$this->setStopperID($since_id);
				//insure we get at least one tweet
				$params['max_id'] = $max_id + 1;
			} else { 
				//insure we get at least one tweet
				$params['max_id'] = $max_id + 1;
		       	}
		} else {
			if(isset($since_id) ) {
				$params['since_id'] = $since_id - 1;
				$this->setStopperID($since_id);
			} else {
				; // no max_id or since_id -- we take anything
		      	}
		}
//error_log(print_r($params,1));
//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ ."returning");
		return $params;
	}

	function setStopperID($id) {
		$this->stopperID =$id;
	}
	function gotValidLow() {
		return  $this->lowestID != 1.0e20;
	}
	function getLowestID($lowerThan=1.0e20) {
		if (!$lowerThan ) return $this->lowestID;
		return ($this->lowestID < $lowerThan)?$this->lowestID:$lowerThan;
	}
	function getHighestID($higherThan=0) {
		return ($this->highestID > $higherThan)?$this->highestID:$higherThan;
	}
	function requestOK() {
		$this->request_failed=false;
	}
	function containsTweet($tweet) {
		$this->request_failed=false;
		$tID=$tweet->StatusID;
		if (!$tID || $tID===0 ) return;
		if ($this->lowestID > $tID) $this->lowestID = $tID;
		if ($this->highestID < $tID) $this->highestID = $tID;
		//error_log("Range Low=" .$this->lowestID . ", High=" .$this->highestID . ", Tweet=". $tID);
		if($this->stopped = ($this->stopperID >= $tID)) return true;
		$this->accepted_tweets += 1;
		return false;
	}
	function log_me($message) {
		if ($this->request_failed ) {
			$this->setMessage(" Range -- $message Request Failed!",true);
		}
		$this->setMessage( "Range -$message - from ". number_format($this->lowestID). " to " .number_format($this->highestID) .", cataloged ". $this->accepted_tweets ." tweets " . (($this->stopped)?"and stopped":"did not stop"). " at " . number_format($this->stopperID) ,true);
	}
}

/**
 * RestfulService class allows you to consume various RESTful APIs.
 * Through this you could connect and aggregate data of various web services.
 * For more info visit wiki documentation - http://doc.silverstripe.com/doku.php?id=restfulservice  
 * @package sapphire
 * @subpackage integration
 */
class SaneRest extends ViewableData {
	protected $baseURL;
	protected $queryString;
	protected $errorTag;
	protected $checkErrors;
	protected $authUsername, $authPassword;
	protected $customHeaders = array();
	protected $auth_token;
	protected $auth_secret;
	protected $responseHeader;
	protected $responseBody;
	protected $params;
	protected $response;
	
	function getBody() {
		return $this->responseBody;
	}
	/**
 	* Creates a new restful service.
 	* @param string $base Base URL of the web service eg: api.example.com 
 	*/
	function __construct($base,$expiry=1) {
		$this->auth_token=false;
		$this->baseURL = $base;
		$this ->responseHeader = array();
		$this ->params = array();
		$this ->response = false;
	}

	function theResponse() {
		return $this->response;
	}
	function clearResponse(){
		$this->response = false;
	}
	function toString(){
		return "error in saneResponse for ". $this->queryString;
	}

	
	/**
	 * Makes a request to the RESTful server, and return a {@link RestfulService_Response} object for parsing of the result.
	 * @todo Better POST, PUT, DELETE, and HEAD support
	 * @todo Caching of requests - probably only GET and HEAD requestst
	 * @todo JSON support in RestfulService_Response
	 * @todo Pass the response headers to RestfulService_Response
	 *
	 * This is a replacement of {@link connect()}.
	 */
	
	public function request($subURL = '', $method = "GET", $data = null, $headers = null) {
		global $consumer_key;
		global $consumer_secret;
		if($this->response) return $this->response;
		$url = $this->baseURL . $subURL; // Url for the request
		if($this->queryString) {
			if(strpos($url, '?') !== false) {
				$url .= '&' . $this->queryString;
			} else {
				$url .= '?' . $this->queryString;
			}
		}

		$url = str_replace(' ', '%20', $url); // Encode spaces
		$method = strtoupper($method);

		if ($this->auth_token) {
			$r = new TwitterOAuth($consumer_key,$consumer_secret, $this->auth_token, $this->auth_secret);
			if (!$data) $data=array();
			$data = array_merge($data,$this->params);
			$this->responseBody = $r -> oAuthRequest($url,$data,$method);
			$this->responseHeader = $r -> responseHeaders();
			//error_log(print_r($this->responseHeader,1));

			$this->response = new RestfulService_Response($this->responseBody, $r->lastStatusCode());
			return $this->response;
		}
		
		assert(in_array($method, array('GET','POST','PUT','DELETE','HEAD','OPTIONS')));
		
			
		$ch = curl_init();
		$timeout = 5;
		$useragent = "SilverStripe/2.2";
		$useragent = "curl/7.16.3 (powerpc-apple-darwin9.0) libcurl/7.16.3 OpenSSL/0.9.7l zlib/1.2.3";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		
		error_log("G E T T I N G   F R O M    unauthorized curl - $url, method = $method, agent=$useragent"); 
	// Add headers
		if($this->customHeaders) {
			$headers = array_merge((array)$this->customHeaders, (array)$headers);
		}
	
		if($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($headers) {
		//	error_log( " special headers ");
			//error_log(print_r($headers,1));
		}
		
		// Add basic authentication -- deprecated by twitter
		if($this->authUsername) curl_setopt($ch, CURLOPT_USERPWD, "$this->authUsername:$this->authPassword");
		
		// Add fields to POST requests
		if($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array ($this, 'readHeader') );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$this->responseBody = curl_exec($ch);
		$curlError = curl_error($ch);
		//error_log("Returned headers");
		//error_log(print_r($this->responseHeader,1));	
		if($curlError) {
			if (!stristr($curlError, 'timed out') && !stristr($curlError,"couldn't connect")
				&& ! stristr($curlError, 'Empty reply from server')
				){
				error_log("Curl Error:" . $curlError, E_USER_WARNING);
				$statusCode = 408 ; // HTTP code request time out
				$this->responseBody = "";
			}
			$statusCode = 408 ; // HTTP code request time out
		} else {
			$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}
		$this->response = new RestfulService_Response($this->responseBody, curl_getinfo($ch, CURLINFO_HTTP_CODE));
		curl_close($ch);
		return $this->response;
	}

	private function readHeader($ch, $header) {
		// process header - signal go ahead to CURL by returning the length of the header	
			$len = strlen($header);
			$pair = explode(':',trim($header),2);
			if ( count($pair) !=2) return $len;
			$this ->responseHeader[$pair[0]] = $pair[1];
			return $len;
		}

	function responseHeaders(){
		return $this->responseHeader;
	}

	function returnHeaders ($which="") {
		//error_log( print_r($this->responseHeader,1) );
		if($which) return $this->responseHeader[$which];
		return $this->responseHeader;
	}
	

	function Authenticate ( $penName = false ) {
		if (!$penName) {
			// get it from session
			$penName= PenName::getSessionPenName();
		}
		$this -> auth_token = $penName -> request_token;
		$this -> auth_secret = $penName -> request_token_secret;
		return;
	}
	
	/**
 	* Sets the Query string parameters to send a request.
 	* @param array $params An array passed with necessary parameters. 
 	*/
	function setQueryString($params=NULL){
		if(!$params) $params = array();
		$this->params = $params;
		$this->queryString = http_build_query($params,'','&');
	}

	function getQueryString ( ) {
		return $this->queryString;
	}
	
	/**
	 * Set basic authentication
	 */
	function basicAuth($username, $password) {
		$this->authUsername = $username;
		$this->authPassword = $password;
	}
	
	/**
	 * Set a custom HTTP header
	 */
	function httpHeader($header) {
		$this->customHeaders[] = $header;
	}

}

class RestfulService_Response extends SS_HTTPResponse {
	protected $simpleXML;

	function setDefaultNamespace($as='default') {
		$ns=$this->simpleXML()->getNamespaces(true);
		$this -> simpleXML->registerXPathnamespace($as,$ns['']);
		return $as;
	}
	
	function __construct($body, $statusCode = 200, $headers = null) {
		$this->setbody($body);
		$this->setStatusCode($statusCode);
		$this->headers = $headers;
	}
	
	function simpleXML() {
		if(!$this->simpleXML) $this->simpleXML = new SimpleXMLElement($this->body);
		return $this->simpleXML;
	}
	
	/**
	 * Return an array of xpath matches
	 */
	function xpath($xpath) {
		return $this->simpleXML()->xpath($xpath);
	}
	
	/**
	 * Return the first xpath match
	 */
	function xpath_one($xpath) {
		$items = $this->xpath($xpath);
		return $items[0];
	}
}


class SaneResponse extends RestfulService_Response {
	function toString(){
		return "error in saneResponse";
	}
	static function makeSane ($insaneResponse) {
		if (!$insaneResponse) {
			$body="";
			$status = "empty";
			$headers = "";
		} else {
			$body = $insaneResponse->getBody();
			$status = $insaneResponse->getStatusCode();
			$headers = $insaneResponse->getHeaders();
		}
		$new = new SaneResponse($body, $status, $headers);
		return $new;
	}

	 function analyze_code($debug=false){
		// look at the return status code from twitter
		// and return three results
		// 0 every thing may proceed
		//   never return 1 (that is an end of file and is done elsewhere
		// 2 failed, try again later
		// 3 failed, do not attempt later
		// 4 failed authorization
		
		$code=$this->getStatusCode() ;
		if($code  == "empty") {
			error_log("Twitter Connect returned empty!");
			return 2;
		}

		if($code == 200) return 0;
		if($code==502) {
			//twitter over capacity, return bad try again
			if($debug) error_log ("twitter over capacity");
			return 2;
		}
		$contents = $this->getBody();
		if (  stristr($contents, "Rate limit exceeded") ) {
			error_log("Twitter says: Rate limit exceeded");
			DoToDoItem::MustDie("Rate limit exceeded");
			return 2;
		}
		if ( stristr($contents, "Not authorized") ) {
			return 4;
		}
		// some other error?
		error_log("Twitter Error for API " . $contents );
		return 3 ;
	}

	
	function setDefaultNamespace($as='default') {
		$ns=$this->simpleXML()->getNamespaces(true);
		if ($ns) {
		$this -> simpleXML->registerXPathnamespace($as,$ns['']);
		$as .= ":";
		} else {
			$as="";
		}
		return $as;
	}
}
class PleaseMap {
	static function map2(&$destination_array,$mapper,$source_array='_POST') {
		if(is_class($destination) ) {
			if (is_class($source)) { PleaseMap::Class2Class($destination,$mapper,$source_array); }
			else {PleaseMap::Array2Class($destination,$mapper,$source_array);}
		} else {
			if (is_class($source)) { PleaseMap::Class2Array($destination,$mapper,$source_array); }
			else {PleaseMap::Array2Array($destination,$mapper,$source_array);}
		}
	}
	static function Array2Array (&$destination_array,$mapper,$source_array='_POST') {
		if (is_string($source_array) ) {
			// if the source_array is unspecified or a string, we assume it is
			// a global array,like _POST or _GET or such, otherwise it is an array that
			// the caller has passed to us explicitly
			global ${$source_array};
			$source_array = &${$source_array};
		}
		//  loop through specification array grabbing the proper source array variables
		//  placing them in the destination
		foreach ($mapper as $dest=>$src ) {
			//echo("Mapping $dest and $src\n");
			@$destination_array[$dest] = $source_array[$src];
		}
		return @$destination_array;
	}

	static function Object2Array (&$destination_array,$mapper=null, $source_class) {
		if (!$mapper) $mapper = 'pleaseMap';
		if (is_string($mapper) ) {
			// if the source specification map is not explicitly passed
			// to us, look for a 'pleaseMap' array in the source class
			//
			// if the source specification map IS explicitly passed
			// as a string, we take it as an alternat map name in the source class
			//
			// The source class will usually be the caller's class
			$mapper = &$source_class->$mapper;
		} // otherwise mapper is an array map passed to us 

		//  loop through the mapper array grabbing the proper source class variables
		//  placing them in the destination
		//  either by setting the value directly or calling the correct 'set' method
		foreach ($mapper as $dest=>$srcloc ) {
			// is there a getvalue method in the source?
			if (method_exists($source_class,$srcloc)) {
				$val = $source_class->$srcloc();
			} else @$val = $source_class->$srcloc;

			$destination_array[$dest]= $val;
		}
		return @$destination_array;
	}


	static function Object2Object (&$destination_class,$mapper=null, $source_class) {
		if (!$mapper) $mapper = 'pleaseMap';
		if (is_string($mapper) ) {
			// if the source specification map is not explicitly passed
			// to us, look for a 'pleaseMap' array in the destination class
			//
			// if the source specification map IS explicitly passed
			// as a string, we take it as an alternat map name in the destination class
			//
			// The destination class will usually be the caller's class
			$mapper = &$destination_class->$mapper;
		} // otherwise mapper is an array map passed to us 

		//  loop through the mapper array grabbing the proper source class variables
		//  placing them in the destination
		//  either by setting the value directly or calling the correct 'set' method
		foreach ($mapper as $destloc=>$srcvar ) {
			// is there a getvalue method in the source to get the srcvar?
			if (method_exists($source_class,$srcvar)) {
				$val = $source_class->$srcvar( $destloc);
			} else @$val = $source_class->$srcvar;

			if (method_exists($destination_class,$destloc)) {
				$destination_class->$destloc( $val,$key);
			} else $destination_class->$destloc = $val;
		}
		return @$destination_class;
	}


	static function Array2Object (&$destination_class,$mapper=null, $source_array='_POST') {
		if (!$mapper) $mapper = 'pleaseMap';
		if (!$source_array) $source_array = '_POST';
		if (is_string($source_array) ) {
			// if the source_array is unspecified or a string, we assume it is
			// a global array,like _POST or _GET or such, otherwise it is an array that
			// the caller has passed to us explicitly
			global ${$source_array};
			$source_array = &${$source_array};
		}
		if (is_string($mapper) ) {
			// if the source specification map is not explicitly passed
			// to us, look for a 'pleaseMap' array in the destination class
			//
			// if the source specification map IS explicitly passed
			// as a string, we take it as an alternat map name in the destination class
			//
			// The destination class will usually be the caller's class
			$mapper = &$destination_class->$mapper;
			//  and place them in the destination
		} 	//otherwise the mapper is an array passed by the caller

			//  if the source specification map is passed explicitly as an array, we
			//  loop through the map grabbing the proper source array variables
			//  placing them in the destination
			//  either by setting the value directly or calling the correct 'set' method
		foreach ($mapper as $dest=>$var ) {
			if (method_exists($destination_class,$dest)) {
				$destination_class->$dest( $source_array[$var],$var);
			} else @$destination_class->$dest = $source_array[$var];
		}
		return @$destination_class;
	}

}
if (false) { //set true for test
class TestMap {
	var $myName;
	// always remember array keys are destination variable names, values are the source field names
	var $pleaseMap = array ('var1' => 'var4', 'var2' => 'nonAvalue' , 'var3'=>'var5' );
	var $otherMap = array ('setmyvalue' => 'grabValue', 'var4' => 'grabValue' , 'var5'=>'var1' );

	var $var1="bob";
	var $var2="sam";
	var $var3="hal";
	var $var4="ellen";
	var $var5="candice";
	var $myvalue = 5;
	function setmyvalue ($v) {
		$this->myvalue=$v;
	}
	function grabvalue() {
		return "value from method";
	}
	function show($name) {
		$v = $this -> $name;
		echo (" class " . $this->myName . " variable $name is $v\n" );
		return $v;
	}
	function debug () {
		$debugMap = array ('var1' => 'show', 'var2' => 'show', 'var3' => 'show', 'var4' => 'show', 'var5' =>'show', 'myvalue'=> 'show');
		//  this prints all my variables
		pleaseMap::Object2Object($this,$debugMap,$this);
	}
	function clearAll() {
		$this->var1 = "------";
		$this->var2 = "------";
		$this->var3 = "------";
		$this->var4 = "------";
		$this->var5 = "------";
		$this->myvalue = "------";
	}

}

$c1= new TestMap;
$c2= new TestMap;
$c1 -> myName = "destination";
$c2 -> myName = "source";
$c2->debug();
$c1->clearAll();
echo("Object to Object test:");
echo("\nNow mapping source using default 'pleaseMap' specification arrray\n");
echo("the map of destination class elements from source values for array ('var1' => 'var4', 'var2' => 'nonAvalue' , 'var3'=>'var5' )");
echo("\n expected results ellen in var1,  empty  in  var2, and candice in var3\n");
pleaseMap::Object2Object($c1,null,$c2);
$c1->debug();
$c1->clearAll();
echo("\nNow mapping source using 'otherMap' specification arrray\n");
echo ("the map of destination class elements from source values for array ('setmyvalue' => 'grabValue', 'var4' => 'grabValue' , 'var5'=>'var1' )");
echo("\n expected results 'value from method' in myvalue,  'value from method in  var4, and bob in var5\n");
pleaseMap::Object2Object($c1,'otherMap',$c2);
$c1->debug();
$c1->clearAll();
echo("\n\nArray to Object test:");
echo("\nNow mapping source using 'otherMap' specification arrray from an array rather than a class\n");
echo("\n expected results 'just a constant' in myvalue,  'just a constant'  in  var4, and 1000 in var5\n");
pleaseMap::Array2Object($c1,'otherMap',array('grabValue'=> 'just a constant', 'var1' =>1000));
$c1->debug();

echo("\n\nObject to Array test:");
$destArray=array('key1'=>'some value', 'var4' => 'previous value');
$c2->debug();
print_r($destArray);
echo("\nNow mapping source using 'otherMap' specification arrray from an array rather than a class\n");
echo ("the map of destination array elements from source values for array ('setmyvalue' => 'grabValue', 'var4' => 'grabValue' , 'var5'=>'var1' )");
echo("\n expected results 'value from method' in setmyvalue,  'value from method'  in  var4, and bob in var5, while preserving 'key1' as 'some value'\n");
$destArray=pleaseMap::Object2Array($destArray,$c2->otherMap, $c2);
print_r($destArray);
echo("\n\nArray to Array test:");
$destArray=array('key1'=>'some value', 'var4' => 'previous value');
echo("\nNow mapping source using 'otherMap' specification arrray from an array rather than a class\n");
echo ("the map of destination class elements from source values for array ('myvalue'=>'thiskey', 'var4' => 'var4' )");
echo("\n expected results NULL in myvalue,  'source value'  in  var4,  while preserving 'key1' as 'some value'\n");
$destArray=array('key1'=>'some value', 'var4' => 'previous value');
$srcArray=array('key1'=>'some value', 'var4' => 'source value','thiskey'=>'will be ignored');
$destArray=pleaseMap::Array2Array($destArray,array('myvalue'=>'nonexisting', 'var4'=>'var4'), $srcArray);
print_r($destArray);

}

?>
