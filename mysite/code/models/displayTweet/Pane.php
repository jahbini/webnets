<?php
class Pane extends DataObject {
	static $db = array( 'userKey' =>'Varchar', // the user's name for this column
			'width' => 'Int'  // number of  waterfalls
			);
	static $has_one = array('Mode' => 'Mode');
	static $has_many = array ('Queries' =>'RelayQuery');

	function onDelete(){
		$queries=$this->Queries();
		foreach($queries as $query) {
			$query->delete();
		}
		return;
	}
}

