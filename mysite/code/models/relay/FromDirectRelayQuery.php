<?php
//  Class for Direct messages "From" a user
class FromDirectRelayQuery extends DirectRelayQuery {

	static function standardQueryDef($params ,$more=array()){
		$n = new FromDirectTimelineRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct', $args );
	}


	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Direct From ' . $this->forcePenName() );
	}

	function forTemplate() {
		return "The direct messages " . $this->forcePenName() ." has sent" ;
	}

	function requestString() {
		return 'http://twitter.com/direct_messages/sent';
	}

}
?>
