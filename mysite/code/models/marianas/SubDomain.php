<?php
class SubDomain extends DataObject {

	public static $db = array( 'Title' => 'Varchar'
		, 'HeadLine' => 'Varchar'
		, 'Slogan'=>'Varchar'
		, 'Content' =>'HTMLText'
	);

	public static $has_one = array( 'Organizer' => 'Mentor'
	);

}
