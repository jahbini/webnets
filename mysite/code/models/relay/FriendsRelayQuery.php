<?php
class FriendsRelayQuery extends TweetRelayQuery {
	static $defaults = array("authority" => "none", "requestKind"=>"browser");

	static function standardQueryDef($params ,$more=array()){
		$n = new FriendsRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function makeForm($who,$headline, $caller){
		return $this->finalMakeForm($who,$headline, new TextField($who. 'PenName','From which Twitter account?',null));
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}

	function ToDoType() {
		return 'SearchToDo';  // scheduled on IP rate limiting by Twitter
	}


	function requestString(){
		// get the screen_name (not as safe as the twitter ID
		return 'http://twitter.com/statuses/friends/'.  $this->forcePenName() ;
	}


	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Friends of ' . $this->forcePenName() );
	}

	function Authenticate() {
		return false;
	}

	function &clean_up($status) {
		$ns =& parent::clean_up($status);
		if ($status->following) $ns ->friend_of = $this->forcePenName();
		$ns ->following = $this->forcePenName();
		return $ns;
	}

	function forTemplate() {
		return "Messages:" . $this->Title();
	}

	function grabMoreTweets($param=array() ) {
		if($this->debug) error_log("grabbing tweets for query " . $this->Title . ' Type= '. $this->ClassName );
		$auth=false;
		$range = $this->fillTweetsFromAPI($param,$this->requestString(),$auth);
		$this -> TotalTweets += $range->accepted_tweets;
		return $range;
	}

}
