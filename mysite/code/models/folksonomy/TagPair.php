<?php
//
// vim:ts=3:sts=3:sw=3:filetype=php:
//
//
class TagPair extends DataObject {
	static $db = array( );
	static $has_one = array('taga'=>'Tag', 'tagb'=>'Tag');
	static $belongs_many_many = array('TagGroup'=>'TagGroup');
	var $identity = false;

	static function getPair($taga, $tagb) {
	$collate = strcmp($taga->ID , $tagb->ID) ;
	if ($collate >= 0) { $temp = $tagb; $tagb = $taga; $taga=$temp; }
	//error_log("in getPair on {$taga->ID}:{$taga->TagText},{$tagb->ID}:{$tagb->TagText}");
	$star = DataObject::get_one('TagPair', "`tagaID` = {$taga->ID} AND `tagbID`= {$tagb->ID} ");
	if (!$star) { 
		$star = new TagPair();
		$star -> tagaID = $taga->ID;
		$star -> tagbID = $tagb->ID;
		$star -> count = 0;
		if ($collate == 0) {
			$star -> identity = true;
			}
		}
	$star->write();
	return $star;
	}
}
?>
