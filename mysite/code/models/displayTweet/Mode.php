<?php
class Mode extends DataObject {
	static $has_many = array('Panes' => 'Pane');
	static $has_one = array('LoggedIn' => 'SubDomain.LoggedIn','Attract'=>'SubDomain.Attract');

	function onDelete(){
		$myPanes = $this->LoggedIn();
		$myPanes -> merge($this->Attract());
		foreach($myPanes as $pane) {
			$pane->deletePane();
		}
		$this->delete();
		return;
	}
	   function forcePane($modeID,$Use){
	    $panes = $this->$Use();
	    if ($panes -> count() == 0) {
		$pd = new Pane();
		$pd->userKey = 'Selected Tweets';
		$pd->width = 3;
		$pd->ModeID=$this->ID;
		$pd->Write();
		$pointer = $Use . 'ID';
		$this->$pointer = $pd->ID;
		$this->write();  //update the DB
	    }
	    return '';
	   }
}
