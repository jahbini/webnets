<?php
//
//vim:ts=2:sts=3:sw=3:filetype=php:
//

class TweetToDo extends ToDo {

	function firstSuspend() {
		error_log("not too easy first suspension");
		$lastSuspend = DataObject::get_one('TweetToDo', "",true ,'`Suspend` DESC' );
		if (! $lastSuspend ) return 3*60;
		$s=$lastSuspend -> Suspend;
		if (! $s ) return 3*60;
		$scheduledTime = strtotime($s) + rand(2,6)*60; // schedule two to 6 minutes after last tweet
		$future = $scheduledTime - time() /* now */;
		$real_time = date("Y-m-d H:i:s", $scheduledTime) ;
		error_log("suspend new tweet until after $future -- $real_time" );
		return $future;
	}
}
?>
