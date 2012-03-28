<?php
class Mode extends DataObject {
	static $db = array ( 'Use' => 'Varchar' );
	static $many_many = array('Panes' => 'Pane');
	static $has_one = array('Profile' => 'Profile');

}
?>
