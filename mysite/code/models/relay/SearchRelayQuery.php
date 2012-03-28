<?php

class SearchRelayQuery extends RelayQuery {
	static $db= array (
		'keywords'=>'Varchar(100)'
		,'negativeWords'=>'Varchar(100)'
		,'location'=>'Varchar(100)'
		);
	static $defaults = array("requestKind" => "browser", "authority" => "none") ;

	static function standardQueryDef($params ,$more=array()){
		$n = new SearchRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}

	function ToDoType() {
		return 'SearchToDo';  // scheduled on IP rate limiting by Twitter
	}

	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag($this->ClassName . ' ' . $this->ID);
	}

	function Authenticate() {
		return false;
	}

	function forTemplate() {
		return $this->Title ;
	}

	public function getKeywordQueries(){
		return  new FieldSet(
			new DropdownField("TwitterQueries", "Existing Queries", DataObject::get('TwitterQuery')->column('Title')  )
			);
	}

	function TimeToSchedule($from) {
		return $from + mt_rand(70,200);
	}
	/* general clean up function to take raw tweets from API and make then
	 * suitable for the DB or the web client. overridden here to acess the results
	 * @param the (json_decoded) result from a twitter API request
	 */
	public function &clean_all($content) {
		$nw = array();
		if(isset($content->results) ) {
			$r = $content->results;
			$nw = array();
			$this->range->requestOK();
			$tweetsProcessed = 0;
			$elapsedTime = time();

			if (isset($r) && is_array($r) ) foreach($r as $d) {
				$t =&  $this->clean_up($d);
				if (!$t) continue;
				$nw[] =& $t;
				$tweetsProcessed += 1;
			}
		}

		$content->results =& $nw;

		$value = json_encode($content);
		return $value;
	}

	/* convert keywords, negative words, etc to legal Twitter Search query
	 * as an array of params to be stuffed into the GET or POST curl request
	 */
	function requestParams($param=false) {
		if (!$param) $param=array();
		$nots = explode(',',$this->negativeWords);
		$n=array();
		foreach($nots as $p) {
			$n[] = trim($p);
		}
		$nots = implode(' -',$n);

		$phrases = explode(',', $this->keywords);
		$q= array();
		foreach($phrases as $p) {
			$p = trim($p);
			if(str_word_count($p) == 0)  continue;
			$q[] = (str_word_count($p) == 1)? $p : '"' . $p . '"';
		}
		// default to english, Todo: expand airport DB to have language codes
		$param['lang'] = 'en';
		if ($this->location) {
			$loc=false;
			$airport = DataObject::get_one('Location', '`code3`="' . $this->location . '"' ) ;
			if ($airport ) $loc = $airport->forSearch();
			if ($loc) $param['geocode']=$loc;
		} 
		$param ['q'] = implode(' OR ', $q) . (($nots=="")?'':' -' . $nots) ;
		$param ['rpp'] = 100;
		//Debug::show($param);
		$this->params = $param;
		return $param;
	}

	/*
	 * make the URL for a search request
	 */
	function requestString() {
		$rv = 'http://search.twitter.com/search';
		 $rv= 'http://128.121.146.235/search';
		return $rv;
	}

	function grabMoreTweets($param=array() ) {
		$this -> nurse = new rqNurse($this);
		$range = $this->range;
		$range->reschedule = $this->nurse->preSchedule();

		if($range->reschedule > 30 ) return $range;

		$q= $this->requestString();
		if($this->debug) error_log("grabbing tweets for query " . $this->Title . ' Type= '. $this->ClassName );
		//no rate limiting needed
		//
		$range = $this->fillTweets($param);
		$range->reschedule = $this->nurse-> postSchedule();
		$this -> TotalTweets += $range->accepted_tweets;
		$this->write();
		return $range;
	}

	function &clean_up($status,$enter_user=true,$enter_tweet=true) {
		if($enter_tweet) $tag = $this->mysetQueryTag();
		//print_r($status);
		try {
		$new_user = new stdClass;
		$new_status = new stdClass;
		if($enter_user) { 
			$t = DataObject::get_one('TweetUser',"`screen_name`='" . Convert::raw2sql($status->from_user) ."'");
		}
		$new_user -> profile_image_url = $status -> profile_image_url;
		$new_user -> screen_name = $status ->from_user;
		$new_user -> name = "known only as" ;

		if ($enter_user && $t && $t->friends_count & $t->followers_count) {
			$new_user ->screen_name = $t->screen_name;
			$new_user ->name = $t->name;
			$new_user ->description = $t->description;
			$new_user ->url = $t->url;
			$new_user -> friends_count = $t->friends_count;
			$new_user -> followers_count = $t -> followers_count;
			$new_user ->tweet_quality = $t->tweet_quality;
			$new_user ->follow_worthy = $t->follow_worthy;
			$new_user -> statuses_count = $t->statuses_count;
		}

		$new_status -> text = $status -> text;
		$new_status -> created_at = gmdate("d d M H:i:s Y Y", strtotime( $status -> created_at)) ; // a text date/time stamp
		$new_status -> source = $status -> source;
		$new_user -> status =& $new_status;

if($enter_tweet) {
			$entry = new stdClass;
			$entry->Title      = $status->text;
			$entry->author_name= $status->from_user;
			$entry->published  = $status->created_at;
			$entry->StatusID = $status->id;
			$entry->apiTweet=false; // full author information not at hand

			$t=Tweet::getTweet($entry,$tag);  // JAH MAY HAVE TO FIND OUT HOW TO UPDATE THE TWEET TIME
			$t->tagMe(Tag::getTagByName($this->ClassName . " " .$this->ID));
			$t->write();
			$this->processNewTweet($t);
}

				
		} catch (Exception $e) {
			$new_user=false;
		}
	
		//print_r($new_user);
		return $new_user;
	}

}
?>
