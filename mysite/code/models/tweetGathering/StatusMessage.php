<?php
/* class status message
 * a simple textline for time stamped logging purposes
 */
class StatusMessage extends DataObject {
	static $db = array('Title' => "HTMLText");
	static $has_one = array("TweetGap" => "TweetGap");

	static function &sayThis($caller,$t) {
		$s = new StatusMessage();
		$s->Title = $t;
		$s ->TweetGapID=$caller->ID;
		$s -> write();
		return $s;
	}

}
