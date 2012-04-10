<?php
class DirectRelayQuery extends RelayQuery {
	// there are two direct query API methods
	//   sent and received messages
	//
	function requestString() {
		return "http://twitter.com/direct_messages";
	}
	function forTemplate() {
		return "Direct Messages to " . $this->forcePenName();
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}

	function makeForm($who,$headline, $caller){
		$penNames= $caller->gimmePenNames(); //as an array of simple strings
		$map=array();
		foreach($penNames as $p){
			if($p != '#mentee') $map[$p] = $p;
			else $map['Logged In User']='#mentee#';
		}
		if (count($map) ==1 ) return $this->makeShortForm(new Hiddenfield($who.".auth",'Selected', $p));
		else return $this->finalMakeForm($who,$headline, new DropdownField($who. '_penNames','user',$map));
	}

	function &clean_up($status,$enter_user=true,$enter_tweet=true) {
		try {
		$new_sender = new stdClass;
		$new_recipient = new stdClass;
		$new_status = new stdClass;
		//error_log(print_r($status,1));
		if ($this->highStatus < (float)$status->id) $this->highStatus = (float)$status->id;

		$user = $status->sender;
		if($enter_user) { 
			$new_sender = DataObject::get_one('TweetUser','"twitter_id"=\'' . Convert::raw2sql($user->id) ."'");
			if (!$new_sender) $new_sender = DataObject::get_one('TweetUser','"screen_name"=\'' . Convert::raw2sql($user->screen_name) ."'");
			if (!$new_sender) {
				$new_sender = new TweetUser();
				$new_sender ->write();
			}
		}
		$new_sender -> twitter_id = $user -> id;
		$new_sender -> screen_name = $user ->screen_name;

		$new_sender -> description = $user ->description;
		$new_sender -> friends_count = $user->friends_count;
		$new_sender -> statuses_count = $user->statuses_count;
		$new_sender -> created_at = $user->created_at; // a text date/time stamp
		$new_sender -> url = $user ->url;
		$new_sender -> name = $user->name;
		$new_sender -> verified = $user -> verified;
		$new_sender -> profile_image_url = $user -> profile_image_url;
		$new_sender -> location = $user ->location;
		$new_sender -> followers_count = $user -> followers_count;
		$new_sender ->friend_of = ($status->recipient->screen_name);
		if($enter_user) { 
			$new_sender->forceChange();
			$new_sender->write();
		}

		if($user -> following) $new_sender -> follows = ($status->recipient->screen_name);


		$user = $status->recipient;
		if($enter_user) { 
			$new_recipient = DataObject::get_one('TweetUser','"twitter_id"=\'' . Convert::raw2sql($user->id) ."'");
			if (!$new_recipient) $new_recipient = DataObject::get_one('TweetUser','"screen_name"=\'' . Convert::raw2sql($user->screen_name) ."'");
			if (!$new_recipient) {
				$new_recipient = new TweetUser();
				$new_recipient ->write();
			}
		}
		$new_recipient -> description = $user ->description;
		$new_recipient -> screen_name = $user ->screen_name;
		$new_recipient -> friends_count = $user->friends_count;
		$new_recipient -> statuses_count = $user->statuses_count;
		$new_recipient -> created_at = $user->created_at; // a text date/time stamp
		$new_recipient -> url = $user ->url;
		$new_recipient -> name = $user->name;
		$new_recipient -> verified = $user -> verified;
		$new_recipient -> profile_image_url = $user -> profile_image_url;
		$new_recipient -> location = $user ->location;
		$new_recipient -> twitter_id = $user -> id;
		$new_recipient -> followers_count = $user -> followers_count;
		if($enter_user) { 
			$new_sender->forceChange();
			$new_sender->write();
		}
//
		if ($enter_tweet) {
			$new_status = Tweet::getTweet($status->id);
		}
		$new_status -> text = $status -> text;
		$new_status -> created_at = $status -> created_at; // a text date/time stamp
		if(isset($status->source)) $new_status -> source = $status -> source;
		$new_status -> recipient = $new_recipient;
		$new_status -> sender = $new_sender;
		if ($enter_tweet) {
			$new_status->forceChange();
			$new_status->write();
		}
		} catch (Exception $e) {
			$new_status=false;
		}
		return $new_status;
	}

}
