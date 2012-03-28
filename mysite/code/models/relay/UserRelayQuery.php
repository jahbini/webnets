<?php
class UserRelayQuery extends RelayQuery {

	static function standardQueryDef($params ,$more=array()){
		$n = new UserRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}


	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Messages from' . $this->forcePenName() );
	}

	function forTemplate(){
		return "Messages sent by " . $this->forcePenName();
	}

	function requestString() {
		return "http://twitter.com/statuses/user_timeline/". $this->forcePenName();
	}

	function &clean_up($status) {
		$screen_name = DataObject::get_by_id('PenName', $this->PenNameID)->screen_name;
		try {
		$new_user = new stdClass;
		$new_status = new stdClass;
		//error_log(print_r($status,1));
		if(isset($status->user) ) $user = $status->user;
		 else $user = $status;
		if(!isset($status->text)) if ( isset($user->status)) $status = $user->status;

		if (! isset($status->text) ) {
			$st = new stdClass;
			$st -> text = "no text!, user has a statuses_count of " . @$user->statuses_count ;
			$st -> created_at = $user->created_at;
			$st -> source = "No message, no source";
			$st -> id = 0.0;
			$status->status = $st;
		}
		if ($this->highStatus < (float)$status->id) $this->highStatus = (float)$status->id;
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

		$new_status -> text = $status -> text;
		$new_status -> created_at = $status -> created_at; // a text date/time stamp
		$new_status -> source = $status -> source;
		$new_user -> status =& $new_status;
		} catch (Exception $e) {
			$new_user=false;
		}
		return $new_user;
	}
}
?>
