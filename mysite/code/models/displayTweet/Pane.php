<?php
class Pane extends DataObject {
	static $db = array( 'userKey' =>'Varchar', // the user's name for this column
			'width' => 'Int'  // number of  waterfalls
			);
	static $belongs_many_many = array('Mode' => 'Mode');
	static $many_many = array ('Queries' =>'RelayQuery');


}

?>
