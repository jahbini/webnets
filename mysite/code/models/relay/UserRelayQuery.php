<?php
class UserRelayQuery extends RelayQuery {
	static $has_one=array('UserOfInterest'=>"TweetUser");

	static function standardQueryDef($params ,$more=array()){
		$n = new UserRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function setUserOfInterest($p){
		$this->UserOfInterestID= is_int($p)? $p:$p->ID;
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}

	function makeForm($who,$headline, $caller){
		return $this->finalMakeForm($who,$headline, new TextField($who. 'PenName','From which Twitter account?',null));
	}
	function setUserRelayQueryPenName($text){
		$n= TweetUser::getTweetUser( $text); // resolve the name with twitter and get all twitter knows.
		$n->fillMe( array('screen_name' => $text) );
		if ( stristr('error',$n->description) ) {
			$n->delete();
		       	throw new exception ("$n->description for user name $text");
		}
		$this->UserOfInterest = $n;
		return;
	}

	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Messages from' . $this->forcePenName() );
	}

	function forTemplate(){
		return "Messages sent by " . $this->UserOfInterest()->screen_name;
	}

	function requestString() {
		$s="http://twitter.com/statuses/user_timeline/". $this->UserOfInterest()->screen_name;
		return $s;
	}
	static $firstCleaning=true;

	function &clean_up($status,$enterUser=true,$enterStatus=true) {
		$enterUser &= self::$firstCleaning;
		self::$firstCleaning=false;  // since all users will be identical, only record the first

		$screen_name = $this->UserOfInterest()->screen_name;
		try {
			if(isset($status->user) ) $user = $status->user;
			 else $user = $status;
		
			// clean up any malformed stuff from twitter (yes, this really does happen)
			if (! isset($status->text) ) {
				$st = new stdClass;
				$st -> text = "no text!, user has a statuses_count of " . @$user->statuses_count ;
				$st -> created_at = $user->created_at;
				$st -> source = "No message, no source";
				$st -> id = 0.0;
				$status->status = $st;
			}

			if(!isset($status->text)) if ( isset($user->status)) $status = $user->status;

			if($enterUser) {
				$new_user = DataObject::get_one('TweetUser','"twitter_id"=\'' . Convert::raw2sql($user->id) ."'");
				if (!$new_user) $new_sender = DataObject::get_one('TweetUser','"screen_name"=\'' . Convert::raw2sql($user->screen_name) ."'");
				if (!$new_user) {
					$new_user = new TweetUser();
					$new_user ->write();
				}
			}
			else $new_user = new stdClass;
		if (bccomp($this->highStatus , $status->id) < 0) $this->highStatus = $status->id;

			// since we are creating javascript JSON to send out to a relayed client, we do this for each status

			$new_user -> description = $user ->description;
			$new_user -> screen_name = $user ->screen_name;
			$new_user -> friends_count = $user->friends_count;
			$new_user -> statuses_count = $user->statuses_count;
			$new_user -> created_at = $user->created_at; // a text date/time stamp
			$new_user -> url = $user ->url;
			$new_user -> name = $user->name;
			$new_user -> verified = $user -> verified;
			$new_user -> profile_image_url = $user -> profile_image_url;
			$new_user -> location = $user ->location;
			$new_user -> twitter_id = $user -> id;
			$new_user -> followers_count = $user -> followers_count;

			if ($enterStatus) {
				$entry = new stdClass;
				$entry->Title      = $status->text;
				$entry->author_name= $user->screen_name;
				$entry->published  = $status->created_at;
				$entry->StatusID = $status->id;
				$entry->apiTweet=false; // full author information not at hand
				
				$t=Tweet::getTweet($entry,$this -> mySetQueryTag());  // JAH MAY HAVE TO FIND OUT HOW TO UPDATE THE TWEET TIME
				$t->tagMe(Tag::getTagByName($this->ClassName . " " .$this->ID));
				$t->write();
				$this->processNewTweet($t);
		       	}
			$new_status = new stdClass;
			$new_status -> text = $status -> text;
			$new_status -> created_at = $status -> created_at; // a text date/time stamp
			$new_status -> source = $status -> source;

			if($enterUser) {
				$new_user->forceChange();
				$new_user->write();
			}
			$new_user -> status = $new_status;
		//$this->sayThis(print_r($new_user,1));
		} catch (Exception $e) {
			$this->sayThis("Exception! - " . $e->getMessage() );
			$new_user=false;
		}
		return $new_user;
	}
}
