<?php
class MentionsRelayQuery extends TweetRelayQuery {
	
	static function standardQueryDef($params ,$more=array()){
		$n = new MentionsRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}

	function &clean_up($status) {
		$ns =& parent::clean_up($status);
		if ($status->following) $ns ->following = $this->forcePenName();
		return $ns;
	}

	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Mentions of ' . $this->forcePenName() );
	}

	function forTemplate() {
		return "Messages about or to " . $this->forcePenName() ;
	}

	function requestString() {
		return 'http://twitter.com/statuses/mentions';
	}

	function grabMoreTweets($param=array() ) {
		if($this->debug) error_log("grabbing tweets for query " . $this->Title . ' Type= '. $this->ClassName );
// to Do use standard Query Def (I'd do it now, but where is the correct PenName stored??)
		$auth=true;
		$this->forcePenName();
		error_log("MENTIONS of ". $this->requestString() );
		$range = $this->fillTweetsFromAPI($param,$this->requestString(),$auth);
		$this -> TotalTweets += $range->accepted_tweets;
		return $range;
	}
}
