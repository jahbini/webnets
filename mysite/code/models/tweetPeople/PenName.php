<?php

//Prototype of function &_v($parent,$index,$kind = "stdClass") returns reference to $parent->index if it is of class kind
//Prototype of function _w($text) issues a warning error
//Prototype of function _e($text) throws a big error


class PenName extends TweetUser {
	static $db = array( 'request_token_secret' => 'Varchar', 'request_token' => 'Varchar(100)', 'Password' => 'Varchar' ,  'LastTweetOut' => 'Datetime');
	static $has_many=array('Messages'=>'CannedMessage');
	static $has_one = array('Profile' => 'Profile','Scheduler'=>'Scheduler');

	function getCMSFields(){              
		 $fields = parent::getCMSFields();
	       return formUtility::removeFields($fields,array('SchedulerID','request_token_secret','request_token','LastTweetOut'));
	    }

	function getTitle() {
		return $this->screen_name;
	}
	static $api_access=array('view'=>'LastTweetOut');
	function get_scheduler() {
		$s = Scheduler::get_scheduler($this->SchedulerID);
		if ($this->SchedulerID == 0) {
			$this->SchedulerID = $s ->ID;
			$this->write();
		}
		return $s;
	}
	function autoTweet(){
		$m = $this->Messages("FIND_IN_SET(HOUR(NOW()), `TimeRestriction`)>0","LastSent ASC","","5");
		if ($m && $m->exists()) {
			$pick = rand(0,$m->Count()-1);
			$m = $m -> toArray();
			$m = $m[$pick];
			//error_log("Tweeting - ". $m->Title);
			$m->LastSent = date('Y-m-d H:i:s',time());
			$m->write();
			TwitterIt::TweetOut($m->Title,$this->ID,false,true); // tweet out right now
		}
		return ;
	}

	protected function analyze_users($twitter, $BaseIsLeader = false) {
		//  SOCIALITE users this is a null Operation
		error_log("no upline/downline tracking for socialites");
		return;
	}

	function followEZ($screen_name,$firstMessage="") {
		// 0 every thing may proceed
		//   never return 1 (that is an end of file and is done elsewhere
		// 2 failed, try again later
		// 3 failed, do not attempt later
		// 4 failed authorization
		if(!$screen_name) return 2;
		$firstMessage="";
		$new_friend=& TweetUser::getTweetUser($screen_name);
		if (!$new_friend) return 2;
		$twitter = new SaneRest("http://twitter.com/friendships/create.xml");
		$this -> twitter = $twitter;  // for the UsersPenName if he is the one who called us
		$twitter -> Authenticate($this);
		$params = array('follow' => 'true', 'screen_name' => $screen_name);
		//$twitter->setQueryString($params);
		$conn = SaneResponse::makeSane($x=$twitter->request('','POST', $params) );
		$response = $conn->analyze_code();
		if ($response) {
			$text = $conn->getBody();
			// Twitter sends an error if we are already following, so ignore it
			if (!stristr($text,'Already foll') && !stristr($text, "is already on your") ) return $response;   // did not work
			}
		$default=$conn->setDefaultNamespace();
		$userInfo =  $twitter->getRequestValues($conn->xpath('//'.$default.'user'))->First();
		PleaseMap::object2object($new_friend,  TweetUser::$friendships_create, $userInfo);
		error_log("Greeting Data for " . $new_friend->name  . " ($screen_name)  retrieved");
			
		$new_friend->received = true;
		$new_friend->write();

		$tweetInfo = $twitter->getRequestValues($conn->xpath('//'.$default.'status')); // ArrayData Objects suk
		if ($tweetInfo->exists()) {
			$tweetInfo = $tweetInfo->First();
			$x=array();
			$x['Title'] = $tweetInfo->getField('text');
			$x['author_name'] = $new_friend->screen_name;
			$x['StatusID'] = $tweetInfo->getField('id');
			$new_tweet= Tweet::getasAPITweet($x) ;
		error_log("First tweet " . $x['Title'] . " ($screen_name)  retrieved");
		}

		return 0;
	}



	function setTwitterPassword($value) {
		$this -> Password = $value;
	}

	static function get($id) {
		return self:: getSessionPenName($id,false);
	}

	static function get_by_name($str){
		$self=DataObject::get_one('PenName', "`screen_name`='".$str ."'");
		return $self;
	}

	static function getSessionPenName($id=false,$setSession=false){
		if(!$id) $id = Session::get('penName');
		if(!$id) $id= 1 ; // illegal user
		$self=DataObject::get_by_id('PenName', $id);
		if($setSession) Session::set('penName',$self->ID);
		return $self;
	}
}
