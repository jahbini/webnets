<?php
//  Class fir Direct messages "to" a user
class ToDirectRelayQuery extends DirectRelayQuery {

	static function standardQueryDef($params ,$more=array()){
		$n = new ToDirectRelayQuery();
		return $n ->fillQuery($params,$more);
	}

	function mySetQueryTag(){
		if ($t = parent::QueryTagOK()) return  $t;
		return parent::mySetQueryTag('Direct To ' . $this->forcePenName() );
	}

	function requestString() {
		return 'http://twitter.com/direct_messages';
	}
}
