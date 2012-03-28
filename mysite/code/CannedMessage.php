<?php
class CannedMessage extends DataObject {
	static $db = array('Title' => 'VarChar(140)','LastSent'=>'Datetime','TimeRestriction'=>'VarChar');
	// TimeRestriction must be entered like ('1','2','3','10','12')  and indicates the OK hours to send this out
	static $has_one = array('PenName' => 'PenName');

	function onWrite() {
		$this->LastSent = date('Y-m-d H:i:s',time());
		parent::onWrite();
	}
}
?>
