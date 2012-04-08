<?php
class Mode extends DataObject {
	static $db = array ( 'Use' => "Enum('attract,loggedin','attract')");
	static $has_many = array('Panes' => 'Pane');
	static $has_one = array('PenName' => 'PenName');

	function onDelete(){
		$myPanes = $this->Panes();
		foreach($myPanes as $pane) {
			$pane->deletePane();
		}
		$this->delete();
		return;
	}
	   function forcePane($modeID){
	    $panes = $this->Panes();
	    if ($panes -> count() == 0) {
		$pd = new Pane();
		$pd->userKey = 'Selected Tweets';
		$pd->width = 3;
		$pd->ModeID=$this->ID;
		$pd->Write();
		$panes->add($pd);
		$this->write();  //update the DB
	    }
	    return '';
	   }
}
