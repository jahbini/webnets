<?php
// vim:sw=3:sts=3:ft=php:

class OrganizerLayoutPage extends ProfilePage {
//   static $has_one = array("SideBar" => "WidgetArea");

   static $defaults = array(
	   "ShowInMenus" => 0,
	   "ShowInSearch" => 0
   );

   static $required=false;
   function requireDefaultRecords(){
	$myPage = DataObject::get_one("SiteTree", "URLSegment='Organizer'");
	if ($myPage instanceOF OrganizerLayoutPage) return;
	if ($myPage instanceOF Page ) {
	    $myPage = $myPage -> newClassInstance('OrganizerLayoutPage');
	    $myPage -> write();
	}

       if (!$myPage) {
	   $myPage = new OrganizerLayoutPage();
       }
	
       $myPage ->Title = "Organizer Layout";
      $myPage -> CanViewType = 'LoggedInUsers';
      $myPage -> ShowInSearch = false;
      $myPage -> ShowInMenus = false;
				
   $myPage->URLSegment = "Organizer";
   $myPage->Status="Published";
   $myPage->write();
   $myPage->publish("Stage","Live");
   $myPage->flushCache();

  DB::alteration_message("Organizer Layout Page installed");
   }

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

class OrganizerLayoutPage_Controller extends ProfilePage_Controller {
      var $request_token;
      var $EditingPane=false;

      function getProfile(){
	 $key='Profile';
	 if($v=getFromCacheOrURL($key)) return $v;
	 // try to find it in the Organizer
	 if( $v=$this->getOrganizer()) return setLocalItem($key,$v->Profile());
	 return false;
      }

   function getOrganizer(){
	 $key = 'Organizer';
	 if ($v=getFromCacheOrURL($key)) return $v;
	 if ($v=$this->getMode()) {
	    $o=$v->PenName();
	    if (! ($o instanceOf Organizer) ) {
	       error_log("bad access to Organizer in __FILE__");
	       die();
	    }
	    return setLocalItem($key,$o);
	 }
	 return false;
      }

      function getMode(){
	 $key='Mode';
	 if ($v=getFromCacheOrURL($key)) return $v;
	 if ($v=$this->getPane()) return setLocalItem($key,$v->$key());
	 return false;
      }
      function getPane(){
	 $key='Pane';
	 if ($v=getFromCacheOrURL($key)) return $v;
	 if ($v=$this->getQuery()) return setLocalItem($key,$v->$key());
	 return false;
      }
      function getQuery(){
	 $key='Query';
	 if ($v=getFromCacheOrURL($key)) return $v;
	 //Debug::show($_REQUEST);
	 //Debug::show($this->request);
	 //SS_Backtrace::backtrace();
	 //die();
	 return false;
      }

      function init($r="no request"){
	 $this ->request = $r;
	 parent::init($r);
      }

	function editPaneInfo($request){
	   $this->EditingPane=true;
	   return $this->Pane;
	}
	 function deletePaneInfo($request){
	   $this->Pane->delete();
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


	function newPane(){
	   $this->newPane4Mode = $this->Mode->ID;
	   //context is not set to create a new pane in the mode passed in the request
	   return $this;
	}

	function updatePane($data,$form){
	   $PaneID=$data['ID'];
	   if ($PaneID == 0) $pane = new Pane();
	    else $pane = DataObject::get_by_id('Pane',$PaneID);
	   $form ->SaveInto ($pane);
	   $pane -> write();
	   $form->sessionMessage("<h3><em>Pane Information Saved</em></h3>","good");
	   Director::redirectBack();
	   return;
	}

	function newQuery($request) {
	   //Debug::show($this->PenName);
	   $this->Profile = $this->Organizer->Profile();
	   $this->newQuery4Pane=$this->pane->ID;
	   $this->AltPen=$this->PenName;
	   return $this;
	}

	function deleteMode() {
	   if($this->Mode) { $this->Mode->delete(); }
	  Director::redirectBack();
	}

	function forceMode($which){
	   Debug::show($_REQUEST);
	       $p = $this->Organizer;
	   Debug::show($p);
		$modes = $p->Modes('"Use"=\''.$which."'");
		if($modes->count() == 0) {
		   $m = new Mode();
		   $m ->Use = $which;
		   $m -> write();
		   $p->Modes()->add($m);
		   $p->write(); // update the DB
		}
		return '';
	}

	function gimmePenNames(){
	   return array($this->Organizer->screen_name);
	}
	function joinPenNames(){
	   return join(' or ',$this->gimmePenNames() );
	}
	function queryForm($wantedQuery=false)
	   {
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
	     return new Form($this,'queryForm?Pane='. $this->newQuery4Pane,$fs, new FieldList($action));
	   }
	function createQuery($data,$form) {
	   $x= new $data['allQueries']();
	   $form->saveInto($x);
	   $x -> PaneID = $this->Pane->ID;
	   $x -> PenNameID = $this->Organizer->ID;
	   $x->Mobi=true;
	   $x->write();
	   Debug::show($x);
	   Director::redirect($this->Link() . '?Organizer=' . $this->Organizer->ID);
	}
}

