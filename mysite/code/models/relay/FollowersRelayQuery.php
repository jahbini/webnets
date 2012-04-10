<?php
class FollowersRelayQuery extends TweetRelayQuery {

	static function standardQueryDef($params ,$more=array()){
		$n = new FollowersRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}


	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Followers of ' . $this->forcePenName() );
	}

	function &clean_up($status) {
		$ns =& parent::clean_up($status);
		if ($status->following) $ns->following = $this->forcePenName();
		$ns ->friend_of = $this->forcePenName();
		return $ns;
	}

	function forTemplate() {
		return "Messages of followers of " . $this->forcePenName();
	}

	function requestString(){
		// get the screen_name (not as safe as the twitter ID
		return 'http://twitter.com/statuses/followers/'.  $this->forcePenName();
	}

	function grabMoreTweets($param=array() ) {
		if($this->debug) error_log("grabbing tweets for query " . $this->Title . ' Type= '. $this->ClassName );
		$auth=true;
		$range = $this->fillTweetsFromAPI($param,$this->requestString(),$auth);
		$this -> TotalTweets += $range->accepted_tweets;
		$this->write();
		return $range;
	}
}
