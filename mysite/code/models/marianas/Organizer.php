<?php
class Organizer extends Mentor {

	public static $db = array( 'TwitterName' => 'Varchar'
		, 'FaceBookName' => 'Varchar'
	);
	public static $summary_fields=array('TwitterName','Profile.Name');

	public static $has_one = array( 
	);

}
