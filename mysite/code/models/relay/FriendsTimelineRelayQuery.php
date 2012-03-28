<?php
class FriendsTimelineRelayQuery extends TweetRelayQuery {
	static function standardQueryDef($params ,$more=array()){
		$n = new FriendsTimelineRelayQuery();
		return $n ->fillQuery($params,$more);
	}


	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}

	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Messages for ' . $this->forcePenName() );
	}

	function &clean_up($status) {
		$ns =& parent::clean_up($status);
		if ($status->following) $ns ->friend_of = $this->forcePenName();
		$ns ->following = $this->forcePenName();
		return $ns;
	}

	function forTemplate() {
		return "The messages " . $this->forcePenName() ." follows" ;
	}

	function requestString() {
		return 'http://twitter.com/statuses/friends_timeline.json';
	}

	function grabMoreTweets($param=array() ) {
		if($this->debug) error_log("grabbing tweets for query " . $this->Title . ' Type= '. $this->ClassName );
		$auth=true;
		$this->forcePenName();
		$range = $this->fillTweetsFromAPI($param,$this->requestString(),$auth);
		$this -> TotalTweets += $range->accepted_tweets;
		return $range;
	}

}
?>
