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

	function editPaneInfo($request){
	   $this->EditingPane=true;
	   // All we have is the pane.  It belongs to a Mode, so get it
	   // and chase back all the way to the Profile it belongs to
	   // That way we recover all the context belonging to this page
	   $paneID= $request->param('ID');
	   $this->wantedPane=DataObject::get_by_id('Pane',$paneID);
	   $this->mode= $this->wantedPane->Mode();
	   $this->PenName = $this->mode->PenName();
	   $this->Profile = $this->PenName->Profile();
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
	      Debug::show("We are at the edit pane #".$this->wantedPane->ID);
	      $f= $this->paneForm($this->mode->ID);
	      $f ->LoadDataFrom($this->wantedPane); //existing pane
	      return $f;
	   }
	   if ($this->wantedQuery) {
	      Debug::show("We are at the edit query #".$this->wantedQuery->ID);
	      $f = $this->queryForm ();
	      $f -> LoadDataFrom($wantedQuery);
	      return $f;
	   }
	   if ($this->newPane4Mode) { //newPane4Mode is the ModeID
	      Debug::show("We are at the new Pane 4 Mode #".$this->newPane4Mode);
	      $f=$this->paneForm($this->newPane4Mode);
	      return $f;
	   }
	   if ($this->newQuery4Pane) {
	      Debug::show("We are at the new query 4 pane #".$this->newQuery4Pane);
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

	function queryForm(){
	   $m=$this->formJSONMenu();

	   $fields= new FieldSet();
	   if (! $this->wantedQuery ) {
	   $p = DataObject::get_by_id('Pane', $this->newQuery4Pane);
	   $v=new RelayQuery();
	   } else {
	      $v=$this->wantedQuery();
	      $p = DataObject::get_by_id('Pane', $v->PaneID);
	   }
	    $count=1; // only one pane
	    $paneGroup = new FieldGroup();
	    $paneName = "Pane_" . $p->ID;
	    $fields -> push(HiddenField::create($paneName.'ID')->setValue($p->ID) );
				$vcount=1;
				$visName = "Vis_".$vcount;

	      $fields -> push(new HeaderField("What shall we get from Twitter?",'headQuery'));
				$vd = $v->ID;
				$fields -> push(HiddenField::create($visName.'ID')->setValue($vd) );
		 $menuMap = array('first' => 1);
				$menuCurrently = 2;
				$main = $v->ClassName;
				$authority = $v->ID;
				if($main != "SearchRelayQuery") {
					$main = $v->PenName() -> screen_name;
					$authority = $v->ClassName;
				}
				$fields -> push( $d1 =new DropdownField($visName . "qMain", "request", $menuMap, $main));
				$fields -> push( $d2 =new DropdownField($visName . "qAttr", "specifically?", $menuMap, $authority));
				//$fields -> push(new TextField($visName . "request", "Request", $v->requestString));
				//$fields -> push(HiddenField::create($visName."request")->setValue($v->requestString) );
				$javaScripting ='$("#Form_visualFormEditor_'. $d1->id(). '").doubleSelect("Form_visualFormEditor_' . $d2->id() .'",dropSpec,'.
					'{ preselectFirst: "' . $main . '" , preselectSecond: "' . $authority . '"}' 
				       	.');';
				$fields -> push(HiddenField::create($visName. 'Pane')->setValue($p->ID) );
				

		$fields->push(HiddenField::create('totalPanes')->setValue($count) );
		$fields->push(HiddenField::create('totalQuery')->setValue($vcount) );
				View::wrapJava( 'dropSpec = '.  $this-> formJSONMenu () .';' . $javaScripting);
		return new Form($this,'visualFormEditor',$fields, new FieldList( new FormAction("editQuery","Edit Query")) );

	}
	function formJSONMenu () {
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		$menu=array();
		foreach( $this->menuJson as $key => $specification) {
			switch ($key) {
			case "PenName" : {
				$penNames = $this->profile->PenNames();
				if (!$penNames->exists() ) break;
				$screenNames = $penNames->column('screen_name');
				foreach ($penNames as $name) {
					if($name instanceOf Mentor) {
						$screenNames[] = '#mentee#';
					}
				}	
				foreach ($screenNames as $p_name) {
					$values = array();
					foreach ($specification as $k =>$s) {
						$values[ str_replace('#P#',$p_name , $s['Title'])]=$k;
					}
					$menu[$p_name] = array(
						'key' =>  $p_name,
						'values' => $values,
						'defaultvalue' => 'UserRelayQuery'
						);
				}
				break;
			}
			case "Query" : {
				$queries = $this -> profile-> TwitterQueries();
				$values = array();
				error_log(print_r($specification,1));
				foreach ( $queries as $k => $q ) {
					foreach ($specification as $key =>$s) {
						$s['queryID'] =$q->ID;
						$t = str_replace('#Q#', $q->Title , $s['Title']);
						unset($s['Title']);
						$values[$t]=$q->ID;
					}
				}
				$menu['query'] = array(
						'key' => 'SearchRelayQuery',
						'values' => $values,
					);
				break;
			}
			case "Mentor" : {

				break;
			}
			}
		}
		error_log( print_r($menu,1));
		//error_log( json_encode($menu));
		return json_encode($menu);

	}

	var $menuJson = array ( "PenName" => array(
		'FriendsRelayQuery' => array('Title' => "The folks #P# follows",
		      "requestString" => "statuses/friends/#P#",
		      "usermark" => "friend of #P#",
		      "auth" => "none"),
		'FollowersRelayQuery' => array('Title' => "followers of #P#",
		      "requestString" => "statuses/followers/#P#",
		      "usermark" => "follower of #P#",
		      "TweetClass" => "UserRelayQuery",
		      "auth" => 'PenName'),
		'MentionsRelayQuery' => array("Title" => 'Mentions of #P#',
		      "requestString" => "statuses/mentions",
		      "filter" => "#P#",
		      "TweetClass" => "TweetRelayQuery",
		      "auth" => "PenName"),
		'ToDirectRelayQuery' => array("Title" => 'Direct messages to #P#',
		      "requestString" => "direct_messages",
		      "tweetmark" => "for the eyes of #P#",
		      "TweetClass" => "ToDirectRelayQuery",
		      "auth" => "PenName"),
		'FromDirectRelayQuery' =>array("Title" => 'Direct messages from #P#',
		      "requestString" => "direct_messages/sent",
		      "tweetmark" => "Direct",
		      "TweetClass" => "FromDirectRelayQuery",
		      "auth" => "PenName"),
		'FriendsTimelineRelayQuery' => array("Title" => 'Public messages received by #P#',
		      "requestString" => "statuses/friends_timeline",
		      "auth" => "PenName"),
		'UserRelayQuery' => array("Title" => 'Public messages sent by #P#',
		      "requestString" => "statuses/user_timeline",
		      "auth" => "PenName")
	      ),
	      "Query" => array( 'SearchRelayQuery' =>	array(
		        "Title" => 'Query for #Q#',
			"queryID" => 0,
			"auth" => "none")
		)
	);

}

