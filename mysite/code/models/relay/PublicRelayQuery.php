<?php
// for the drag of the public stream
class PublicRelayQuery extends RelayQuery {

	static function standardQueryDef($params ,$more=array()){
		$n = new PublicRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
		//error_log("Twitter Query I N I T I A L I Z E");
		$this->requestKind= 'browser';
		$this->authority='none';
	}

	function makeForm($who,$headline, $penNames=null){
		if (!$penNames ) $penNames=array($this->forcePenName());
		$map=array();
		foreach($penNames as $p){
			if ($p == "#mentee") $map['Logged In User']='#mentee#';
			else $map[$p] = $p;
		}
		if (count($map) ==1 ) return $this->makeShortForm(new Hiddenfield($who.".auth",'Selected', $p));
		                else return $this->finalMakeForm($who,$headline, new DropdownField($who. '_penNames','user',$map));
	}

	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Public Messages');
	}

	function forTemplate() {
		return "Public Messages" . $this->forcePenName() ;
	}

	function requestString() {
		return 'http://twitter.com/statuses/public_timeline';
	}

	function grabMoreTweets($param=array() ) {
		if($this->debug) error_log("grabbing tweets for query " . $this->Title . ' Type= '. $this->ClassName );
// to Do use standard Query Def (I'd do it now, but where is the correct PenName stored??)

		$auth=false;
		$range = $this->fillTweetsFromAPI($param,$this->requestString(),$auth);
		$this -> TotalTweets += $range->accepted_tweets;
		return $range;
	}
}
