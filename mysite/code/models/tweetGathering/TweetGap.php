<?php
class TweetGap extends DataObject {
	static $db=array(
		"gapKind"=>"Enum('low,initial,mid,top','initial')",
		'gapBottom'=>'Varchar(20)',
		'currentTop'=>'Varchar(20)',
		'gapTop'=>'Varchar(20)',
		'failed' => "Enum('no,warn,yes','yes')",
		'TimeOffset' => 'Int'
	);
	static $defaults = array ('TimeOffset' => 3600 );  // one hour
	static $has_one=array('TwitterQuery'=>'TwitterQuery');
	static $has_many=array('StatusMsg' => 'StatusMessage');	

	function sayThis($t){
		StatusMessage::sayThis($this,$t);
	}
	function TimeToSchedule($from) {
		$tq=$this->TwitterQuery();
		if (method_exists($tq,'TimeToSchedule') )
	       		return $tq->TimeToSchedule($from);
		return $this->TimeOffset + $from;
	}

	function onBeforeDelete(){
		$logMessages=$this->StatusMsg();
		foreach($logMessages as $m) { $m->delete(); }
		parent::onBeforeDelete();
	}

	function bottom () {
			if (bccomp($this->gapBottom ,  '1000000000000') >=0 ) return "badly formed";
		return number_format($this->gapBottom);
	}

	function current () {
		if (bccomp($this->currentTop,'1000000000000') >=0 ) return "unlimited";
		return number_format($this->currentTop);
	}

	function topOfGap () {
		if (bccomp($this->gapTop,'1000000000000')>=0 ) return "unlimited";
		return number_format($this->gapTop);
	}
	function sooner ($limit = 300) {
		if ($limit > ($this->TimeOffset *= 0.618) ) 
			$this->TimeOffset = $limit;  //schedule sooner but not too soon
	}
	function later ($limit = false){
		if (!$limit) $limit = 25*60*60; //25 hours
		if ($limit < ($this->TimeOffset *= 1.618) ) 
			$this->TimeOffset = $limit;  //schedule sooner but not too soon
	}
	function getHighTweets() {
		$this ->setStat('getHighTweets',true);
		$parentQuery=$this->TwitterQuery();
		$parentQuery->setLogging($this);
		if (!$parentQuery || $parentQuery->ID ==0) {
			error_log("parent query does not exist or has ID=0");
			$this->delete();
			return false;
		}

		$this->debugMe=false;
		$parentQuery->setDebug(false);
	error_log("needing High tweets ");
	$this->setStat(" needing HIGH Tweets above ". $this->current());
		$range = $parentQuery->grabMoreTweets(array('since_id' => $this->currentTop ));
		$this->reschedule = $range->reschedule;
		if ($range -> request_failed) { // failed, update nothing
			$this->setStat("request failed -- rescheduling",false,'warn');
			$this->TimeOffset = 20*60; // try again in 20 minutes (dont write me out)
			return $this;
		} 
		if($range-> accepted_tweets == 0 ) {
			$maxtime=( $parentQuery->ToDoType() == 'APIToDo' )?30*60:48*60*60;
			$this->later($maxtime) ; //two days max
			$this->setStat("no high Tweets to report",false,'no');
			return $this;
		}
		$newHi = $range->getHighestID();
		$newMid = $range->getLowestID();
		if($range->accepted_tweets>10) {
			$this->sooner();
		} 
		if($range->accepted_tweets>40) {
			$this->sooner();
		} 
		$floor = $this->gapBottom;
		$this->setField('gapBottom',$newHi);
		$this->setField('currentTop',$newHi);
		$this->setField('gapTop','1000000000000');
		//otherwise create a Gap and fill it
		if( ! $range->stopped ) {
			$this->setStat("new high Tweets, and range did not stop --  create mid gap");
			if ($this -> TimeOffset > 3600 ) $this->TimeOffset = 3600;
			$this->sooner();
			$gap = new TweetGap();
			$gap ->setAsMidGap($parentQuery,$floor,$newMid);
			$m = "created new mid gap from ". number_format($floor) ." to ". number_format($newMid) ;
			$gap -> setStat($m,true);
			$this->setStat($m );
		}
		// while we continue up and onward
		$this->setStat( "new high Tweets:returning new bottom " . $this->bottom() . " currentTop=" . $this->current(),false,'no') ;
		return $this;
	}

	function setStat ( $msg, $clear = false,$final='yes') {
		$this ->failed = $final;
		$v = StatusMessage::sayThis($this,$msg);
		if ($this->debugMe) error_log($msg);
		return $this;
	}
	function status() {
		return $this->StatusMsg();
	}

	function getInitialTweets(){
		$parentQuery=$this->TwitterQuery();
		$parentQuery->setLogging($this);
		// hardly probable, but ifadmin deletes the query before it actually
		// completes through twitter, this is a possibility
		if (!$parentQuery || $parentQuery->ID ==0) {
			$this->delete();
			return false;
		}
		$parentQuery->setDebug(true);
		$this->setStat("Creating initial tweet request for my parent ID = {$parentQuery->ID} ",true);
		$range = $parentQuery->grabMoreTweets();
		$this->reschedule = $range->reschedule;
		if ( $range -> request_failed) { // failed, update nothing
			$this->reschedule = $range->reschedule;
			$this->setStat("request failed - rescheduling",false,'warn');
			return $this;
		} 
		// we can create the upper and lower gaps!
		$this->gapBottom = $range->getLowestID();
		$this->currentTop = $this->gapTop =$range->getHighestID();
		$this->write();
		$parentQuery->normalizeQuery($range);
		return false; // We are successful, so do not reschedule
	}
	function getLowTweets() {
		$parentQuery=$this->TwitterQuery();
		$parentQuery->setLogging($this);
		if (!$parentQuery || $parentQuery->ID ==0) {
			error_log("parent query does not exist or has ID=0");
			$this->delete();
			return false;
		}
		$parentQuery->setDebug(true);

		$this->setStat("Low Gap Tweets below ". $this->current() .", for my parent ID = {$parentQuery->ID} ",true);
		$range = $parentQuery->grabMoreTweets(array('max_id' => $this->currentTop ));
		$this->reschedule = $range->reschedule;
		if ( $range -> request_failed) { // failed, update nothing
			$this->setStat("request failed - rescheduling",false,'warn');
			return $this;
		} 

		if ($range->getLowestID() >= $this->currentTop ) {
			// we have probably run  out of tweets
			$this->later(3600*24*10);
			$this->setStat("We seem to have run out of low tweets for " . $parentQuery->query,false,'warn');
			return $this;
		}
		$this -> currentTop= $range->getLowestID();
		$this -> gapTop= $range->getLowestID();
		$this->setStat("returning, new ceiling is " . $this->current() ,false,'no');
		return $this;
	}

	function getMidTweets() {
		//$this->TimeOffset = 60*60*2; //schedule in two hours
		//return $this;
		$parentQuery=$this->TwitterQuery();
		$parentQuery->setLogging($this);
		$this->debugMe=true;
		if (!$parentQuery || $parentQuery->ID ==0) {
			$this->delete();
			return false;
		}

		$this->TimeOffset = 60*60*2; //schedule in two hours
$this->setStat("Mid Gap Tweets below " . $this->current() .", my parent ID = {$parentQuery->ID} ", true);

		$parentQuery->setDebug(true);
		$range = $parentQuery->grabMoreTweets(array('max_id' => $this->currentTop ,'since_id' => $this->gapBottom));
		$this->reschedule = $range->reschedule;
		if ( $range -> request_failed) { // failed, update nothing
			$this->debugMe=true;
$this->setStat("Mid Gap Tweets below " . $this->current() .", my parent ID = {$parentQuery->ID} ", true);
			$this->setStat("request was " . $range->getRequest() );
			$this->setStat("response was " .$range->getMessage() );
			$this->setStat("request failed - rescheduling",false,'warn');
			return $this;
		} 
		if ($range->stopped) {
			// the range hit the stopper -- we are done
			$this->debugMe=true;
$this->setStat("Mid Gap Tweets below " . $this->current() .", my parent ID = {$parentQuery->ID} ", true);
			$this->setStat("request was " . $range->getRequest() );
			$this->setStat("response was " .$range->getMessage() );
			$this->setStat("Mid Gap M*I*S*S*I*O*N accomplished for " . $parentQuery->query);
			$this->delete();
			return false;
		}
		if ($range->getLowestID() > $this->currentTop ) {
			// we have probably run  out of tweets
			$this->debugMe=true;
$this->setStat("Mid Gap Tweets below " . $this->current() .", my parent ID = {$parentQuery->ID} ", true);
			$this->setStat("request was " . $range->getRequest() );
			$this->setStat("response was " .$range->getMessage() );
			$this->setStat("We seem to have run out of mid tweets for " . $parentQuery->query);
			$this->setStat("We should not do this forever");
			// we should probably not try forever to find tweets
			$this->delete();
			return false;
			return $this;
		}
		$this -> currentTop= $range->getLowestID();
		$this->setStat("request was " . $range->getRequest() );
		$this->setStat("response was " .$range->getMessage() );
		$this->setStat("Finished gathering tweets current ceiling is ". $this->current(),false,'no' );
		$this->write();
		return $this;
	}

	function setAsMidGap($parent,$low,$hi){
		$this-> setField('gapKind', 'mid');
		$this-> setField('gapTop',$hi);
		$this-> setField('currentTop',$hi);
		$this-> setField('gapBottom',$low);
		$this-> write();
		$parent->addAGap($this);
		$todoType=$parent->ToDoType();
		$toDoItem = ToDo::addToDoItem($todoType,$this , "getMidTweets",false);
		return $this;
	}

	function setAsTopGap($parent,$top){
		$this-> setField('gapKind', 'top');
		$this-> setField('gapTop','1000000000000');
		$this-> setField('currentTop',$top);
		$this-> setField('gapBottom',$top);
		$this-> write();
		$parent->addAGap($this);
		$todoType=$parent->ToDoType();
		$toDoItem = ToDo::addToDoItem($todoType,$this , "getHighTweets",false);
		return $this;
	}

	function reschedule(){
		$parent=$this->TwitterQuery();
		$todoType=$parent->ToDoType();
		$t = DataObject::get_one('ToDo', NiceData::Query('TheObject', "TweetGap") 
				. " AND " . NiceData::Query( 'ObjectID' , $this->ID) );
		if ($t) { $t->delete();}
		switch ( $this->gapKind)  {
			case "initial": $operation = 'getInitialTweets';
				break;
			case "top": $operation = 'getHighTweets';
				break;
			case "mid": $operation = 'getMidTweets';
				break;
			case "low": $operation = 'getLowTweets';
				break;
		}
		$this->setStat("Rescheduled",true,true);
		$this->write();
		$toDoItem = ToDo::addToDoItem($todoType,$this , $operation,false);
		return $this;

	}

	function setAsInitialGap($parent){
		$this-> setField('gapKind', 'initial');
		$this-> write();
		$this->TwitterQueryID=$parent->ID;  // cause silverstripe can't figure this out!!
		$parent->addAGap($this);
		// we really want to execute this now, but if there is a problem, we can 
		// schedule it for 'soon'  after it completes correctly, we can add high and low gaps
		if($this->getInitialTweets()) { // if it returns an object ($this) it needs rescheduling
			$todoType=$parent->ToDoType();
			$toDoItem = ToDo::addToDoItem($todoType,$this , "getInitialTweets",false);
		}
		return $this;
	}


	function setAsLowerGap($parent,$top,$range=false){
		if($range) {
			$this->setStat("request was " . $range->getRequest() );
			$this->setStat("response was " .$range->getMessage(),false,'no' );
		}
		$this-> setField('gapKind', 'low');
		$this-> setField('gapTop',$top);
		$this-> setField('currentTop',$top);
		$this-> setField('gapBottom',1);
		$this-> write();
		$parent->addAGap($this);
		$todoType=$parent->ToDoType();
		$toDoItem = ToDo::addToDoItem($todoType,$this , "getLowTweets",false);
		return $this;
	}
	
}
