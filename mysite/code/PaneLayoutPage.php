<?php
// vim:sw=3:sts=3:ft=php:    

class PaneLayoutPage extends ProfilePage {
//   static $has_one = array("SideBar" => "WidgetArea");

   static $defaults = array(
	   "ShowInMenus" => 0,
	   "ShowInSearch" => 0
   );

   static $required=false;

/*
   function getCMSFields() {
      $fields = parent::getCMSFields();
      $fields->removeFieldFromTab("Root.Content.Main","Content");
      $fields->addFieldToTab("Root.Content.Widgets",
	        new WidgetAreaEditor("SideBar"));
      return $fields;
   }
 */
}

class PaneLayoutPage_Controller extends ProfilePage_Controller {
      var $request_token;
      var $wantedPane=null;
      var $EditingPane=false;
      var $mode=null;

      function instantiateFromPane($paneID){
	   $this->wantedPane=DataObject::get_by_id('Pane',$paneID);
	   $this->mode= $this->wantedPane->Mode();
	   $this->PenName = $this->mode->PenName();
	   $this->Profile = $this->PenName->Profile();
      }
	function editPaneInfo($request){
	   $this->EditingPane=true;
	   // All we have is the pane.  It belongs to a Mode, so get it
	   // and chase back all the way to the Profile it belongs to
	   // That way we recover all the context belonging to this page
	   $paneID= $request->param('ID');
	   $this -> instantiateFromPane($paneID);
	   return $this;
	}
	 function deletePaneInfo($request){
	   $paneID= $request->param('ID');
	   $p=DataObject::get_by_id('Pane',$paneID);
	   $p->delete();
	  Director::redirectBack();
	 }

        
	function paneForm ($modeID=0) {
	   $f=new FieldSet(
	      new HeaderField("enter a name you wish to call this pane and the width in icons"),
	      new TextField("userKey","Identifier <em>*<em>"),
	      new TextField("width","Width in Icons <em>*</em>",3),
	      new HiddenField("ModeID",null,$modeID),
	      new HiddenField("ID",null,0)
	   );
	   $validator = new RequiredFields("userKey","width");
	   $action = new FieldSet(new FormAction("updatePane","update"));

	   return new Form($this,"paneForm",$f,$action,$validator);
	}

	function displayForm() {
	   if ($this->wantedPane) {
	      //Debug::show("We are at the edit pane #".$this->wantedPane->ID);
	      $f= $this->paneForm($this->mode->ID);
	      $f ->LoadDataFrom($this->wantedPane); //existing pane
	      return $f;
	   }
	   if ($this->wantedQuery) {
	      //Debug::show("We are at the edit query #".$this->wantedQuery->ID);
	      $f = $this->queryForm ($wantedQuery);
	      $f -> LoadDataFrom($wantedQuery);
	      return $f;
	   }
	   if ($this->newPane4Mode) { //newPane4Mode is the ModeID
	      //Debug::show("We are at the new Pane 4 Mode #".$this->newPane4Mode);
	      $f=$this->paneForm($this->newPane4Mode);
	      return $f;
	   }
	   if ($this->newQuery4Pane) {
	      //Debug::show("We are at the new query 4 pane #".$this->newQuery4Pane);
	      $f = $this->queryForm ();
	      return $f;
	   }
	   return false;
	}
	function PenName(){
	   if(!$this->PenName) $this->PenName=$this->AltPen;
	   return $this->PenName;
	}


	function newPane($request){
	   $ModeID=$request->param('ID');
	   $this->mode = DataObject::get_by_id('Mode',$ModeID);
	   $this->newPane4Mode = $this->mode->ID;
	   $this->PenName = $this->mode->PenName();
	   $this->Profile = $this->PenName->Profile();
	   $this->AltPen=$this->PenName;
	   //context is not set to create a new pane in the mode passed in the request
	   return $this;
	}

	function updatePane($data,$form){
	   $PaneID=$data['ID'];
	   if ($PaneID == 0) $pane = new Pane();
	    else $pane = DataObject::get_by_id('Pane',$PaneID);
	   $form ->SaveInto ($pane);
	   $pane -> write();
	   $form->sessionMessage("Pane Information Saved","good");
	   Director::redirectBack();
	   return;
	}

	function allModes($request) {
	   $this->wantedPane=false;  //these should go in the constructor.
	   $this->wantedQuery=false;
	   $this->newPane4Mode=false;
	   $this->newQuery4Pane=false;
	   $Action=$request->param('OtherID');
	   // if this is for a dispatched function, it is up to that function to decode the ID
	   if (isset($Action) && method_exists($this,$Action) ) $this->$Action($request);  // go to sub action
	   $PenNameID=$request->param('ID');
	   $this->PenName = DataObject::get_by_id('PenName',$PenNameID);
	   return $this;
	}

	function newQuery($request) {
	   // All we have is the pane.  It belongs to a Mode, so get it
	   // and chase back all the way to the Profile it belongs to
	   // That way we recover all the context belonging to this page
	   $paneID= $request->param('ID');
	   $pane=DataObject::get_by_id('Pane',$paneID);
	   $this->mode= $pane->Mode();
	   $this->PenName = $this->mode->PenName();
	   //Debug::show($this->PenName);
	   $this->Profile = $this->PenName->Profile();
	   $this->newQuery4Pane=$paneID;
	   $this->AltPen=$this->PenName;
	   return $this;
	}

	function deleteMode($id) {
	   $modeID=$id->param('ID');
	   $modes=$this->Profile()->Modes();
	   $mode=$modes->byID($modeID);
	   $modes->remove($mode);
	   $mode->delete();
	  Director::redirectBack();
	}

	function forceMode($which){
	       $p = $this->PenName();
		$modes = $p->Modes('"Use"=\''.$which."'");
		if($modes->count() == 0) {
		   $m = new Mode();
		   $m ->Use = $which;
		   $m -> write();
		   $p->modes()->add($m);
		   $p->write(); // update the DB
		}
		return '';
	}

	function gimmePenNames(){
	   return array($this->PenName()->screen_name);
	}
	function joinPenNames(){
	   return join(' or ',$this->gimmePenNames() );
	}
	function queryForm($wantedQuery=false)
	   {
	      if (isset($_REQUEST['paneID'])){
		 $this->instantiateFromPane($_REQUEST['paneID']);
	      }
	      $RelayClasses = array ( 
		 'UserRelayQuery' => 'public messages sent from twitter account',
		 'FriendsTimelineRelayQuery' => "messages from friend of ".$this->joinPenNames()." in Timeline",
		 'FromDirectRelayQuery' => "Direct messages sent by ".$this->joinPenNames(),
		 'ToDirectRelayQuery' => "messages directly to ".$this->joinPenNames(),
		 'FollowersRelayQuery' => "Status from followers",
		 'FriendsRelayQuery' =>"Status from friends of any twitter account",
		 'MentionsRelayQuery' => "tweets containing @".$this->joinPenNames(),
		 'SearchRelayQuery' => "messages with keywords"
	      );
	      if(!$wantedQuery || !($wantedQuery instanceof RelayQuery)) {
		 $action = new FormAction("createQuery", "Create Query");
		 $map = array();
		 $f = new Fieldset();
		 foreach($RelayClasses as $k=>$v) {
		      $obj=singleton($k);
		      $field= $obj->makeForm($k,$v,$this);
		    $map[ $k .'//'. $v] = $field;
		   }

		 $fs=new FieldList(new HeaderField("Select One"),  new SelectionGroup('allQueries',$map));
	      } else {
		 $action = new FormAction("editQuery", "Edit Query");
		 $fs=new FieldList();
		 $c= $wantedQuery->ClassName;
		 $fs->push($c::makeForm($c,$RelayClasses[$c], $this));
	      }
	     return new Form($this,'queryForm?paneID='. $this->newQuery4Pane,$fs, new FieldList($action));
	   }
	function createQuery($data,$form) {
	   $x= new $data['allQueries']();
	   $form ->saveInto($x);
	   $x->write();
	   Director::redirect($this->Link() . 'allModes/' . $this->PenName()->ID);
	}
}

