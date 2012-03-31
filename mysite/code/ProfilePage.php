<?php
// vim:sw=3:sts=3:ft=php:    

class ProfilePage extends Page {
//   static $has_one = array("SideBar" => "WidgetArea");

   static $defaults = array(
	   "ShowInMenus" => 0,
	   "ShowInSearch" => 0
   );

   static $required=false;
   function requireDefaultRecords() {
	   if(self::$required) return;
	   self::$required=true;
	   parent::requireDefaultRecords();
	   $pPage = DataObject::get_one("SiteTree", "URLSegment='user-profile'");
	   if ($pPage instanceOF ProfilePage) return;
	   if ($pPage instanceOF Page ) {
		   $pPage = $pPage -> newClassInstance('ProfilePage');
		   $pPage -> write();
	   }
	   if (!$pPage) {
		   $pPage = new ProfilePage();
	   }
	   
	   //$pPage -> CanViewType = 'LoggedInUsers';
	   $pPage -> ShowInSearch = false;
	   $pPage ->Title = "Your Profile";
	   $pPage ->Content = "";
	   $pPage->URLSegment = "user-profile";
	   $pPage->Status="Published";
	   $pPage->write();
	   $pPage->publish("Stage","Live");
	   $pPage->flushCache();
	   DB::alteration_message("Profile Page installed");
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

class ProfilePage_Controller extends TagWatch {
      var $request_token;
      var $query_edit_id =0;// if non-zero is the query we wish to edit

	function init( $params = false){

		//$_REQUEST['previewwrite']=1;
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		parent::init();
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		$this->PenNames = $this->profile->PenNames();
		$this->request_token= Session::get('request_token');
		error_log("Retrieved request token as " .$this->request_token);
		Session::set('request_token', '' );
		if( !$this->request_token && $this->Profile()->ValidationToken == 0 && !Permission::check('REGISTERED_USER')) {
		  if (!$this->request_token) error_log("User has no request token");
		  if ($this->Profile()->ValidationToken ) error_log("User validation token is non-zero");
		  if (Permission::check('REGISTERED_USER') ) error_log("User is REGISTERED USER");
		  error_log("User is not registered properly");
		  die();
		   Director::Redirect('/');  
		}
		$modeUse = 'loggedIn';
		if(isset($_REQUEST['mode'] ) ) $modeUse = $_REQUEST['mode'];
		$m = $this->profile->getModeByUse($modeUse);
		if (!$m || !$m->exists() ) $m= $this->profile->getModeByUse('loggedIn');
		$this->currentMode =$m;
		Requirements::javascript('tell140/javascript/jquery.doubleSelect.js');
	}
   //var $WatchTags;
   //var $WatchList;

      /* function newPenName is in Page.php */
// if this user has not activated the validation link, we need to get a password and that's all
	function completePenName () {
     error_log("completePenName");
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		global $consumer_key;
		global $consumer_secret;
		include_once('auth/twitterOAuth.php');
		$request_token = $this->request_token;
		$request_token_secret = Session::get('request_token_secret');
		$askForAuth = new TwitterOAuth($consumer_key, $consumer_secret,$request_token,$request_token_secret);
		$accessToken = $askForAuth->getAccessToken();
		$gotAuth = new TwitterOAuth($consumer_key, $consumer_secret,$accessToken['oauth_token'],$accessToken['oauth_token_secret']);
		$validator = $gotAuth->OAuthRequest('https://twitter.com/account/verify_credentials.json', array(),'GET');
		if (!$validator) {
			echo("Validator problems");
			die();
		}
		$new_data = json_decode($validator);
		//print_r($new_data);
		$new_pen =& TweetUser::getTweetUser( $new_data -> screen_name );
		if($new_pen -> ClassName == 'TweetUser'){ // Twitter Usernames need to be promoted
		   $new_pen = $new_pen -> newClassInstance("PenName");
		   $new_pen->write();
		}
		$new_pen->request_token = $accessToken['oauth_token'];
		$new_pen->request_token_secret = $accessToken['oauth_token_secret'];
		$new_data -> twitter_id = $new_data -> id;
		unset($new_data -> id);
		PleaseMap::object2object($new_pen,TweetUser::$friendships_create,$new_data);
		$new_pen->write();

		$wayBack = Session::get('pre_auth');
		error_log("Way Back = $wayBack");
		global $userState;
		switch ($userState) {
		   case 'anonymous':
		      Page_Controller::sessionInfo('UserState',true,'mentored');
		     // create a socialite reference a dummy profile, that has links to the Standard Queries
		     $pro = DataObject::get_one('Profile','`Name`="'. $new_pen->screen_name  .'"' );
		     if (!$pro) {
			$pro = new Profile();
			$pro -> Name = $new_pen->screen_name;
			$pro -> write();
		     }
		     $this->profile = $this->mentee = $pro;
		      //  $this->standardPanes($new_pen); 
		      // This socialite has NO panes, panes will be generated from 
		      // mentor's panes on the fly
		      //
		     $new_pen -> ProfileID = $this->profile->ID;
		     $new_pen -> write();
		     Session::set('socialiteProfileID', $this->profile->ID );
		     PenName::getSessionPenName($new_pen->ID,true);
		     break;
		  case 'loggedIn':
		     // we just need to attach this new pen name to the existing logged in
		     // user's profile
		      if($new_pen -> ClassName == 'PenName'){ // Twitter Usernames need to be promoted
			 $new_pen = $new_pen -> newClassInstance("UsersPenName");
			 $new_pen->write();
			ToDo::insureToDoItem('InternalToDo', $new_pen, 'maintainMe');
		      }
		      $new_pen -> ProfileID = $this->profile->ID;
		      $new_pen -> write();

		     if ($this->PenNames->count() == 0) {
			// that is, this is the very, very first pen name for this account
			     // generate the standard Panes for this user from the mentor
			  $pd = new PaneDef();
			  $pd -> setMentor($this->mentor);
			  $pd -> setMentee($new_pen);
			  $pd -> deepCopy();
		     }

		      $wayBack = $this->Link();	
		     break;
	       default: 
		  error_log("Bad state when validating from Twitter authorization: $userState");
		  die();
		}

		error_log("Way Back = $wayBack");
		Director::Redirect($wayBack );	
	}

	function visualFormEditor() {
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		$names =$this->profile->PenNames(); 
		if (!$names ->exists() )
		{
			$modes = $this->profile->Modes();
			$modes -> removeAll();
			return "You now want to link your Twitter User name with us to create a Pen Name.";
		}
		$this-> formJSONMenu () ;
		// todo, put in the Query style of waterfall display
		//
		$panes = $this->currentMode->Panes( "" , "`userKey` ASC");
		error_log("Pane count == " .$panes->count() );
		if($panes->count() == 0) {
			  $pd = new PaneDef();
			  $pd -> setMentor($this->mentor);
			  $pd -> setMentee($names->First());
			  $pd -> shallowCopy();
			  $pd -> deepCopy();
		}

	$javaScripting = "";
		$fields=new FieldSet();
		$modeUse = 'loggedIn';
		if(isset($_REQUEST['mode'] ) ) $modeUse = $_REQUEST['mode'];
		$fields -> push(HiddenField::create('mode')->setValue($modeUse) );
		//$fields->setID("visuals_Holder");
		if($panes->count() <5 ) $panes -> push(new Pane() );
		$vcount=0;
		$count=0;
		foreach($panes as $p) {
			$count++;
			$paneGroup = new FieldGroup();
			$paneName = "Pane_" . $count;
			$paneGroup ->setID($paneName . "_Holder");
			if ($p->ID) {
				$check = new CheckboxField($paneName . "delete", "Delete this Pane?", false);
				$check -> addExtraClass('pdeletor');
			} else {
				$check = new CheckboxField($paneName . "create", "Create new Pane?", false);
				$check -> addExtraClass('pcreator');
			}
			$paneGroup -> push($check);
			$paneGroup -> push(new TextField($paneName . "userKey", "Title", $p->userKey));
			$paneGroup -> push(new TextField($paneName . "width", "Number of columns", $p->width));
			$fields -> push(HiddenField::create($paneName.'ID')->setValue($p->ID) );
			$queries = $p->Queries();
			if($p->ID && $queries->count() == 0) {
				// here again, we should never have to do this,
				// but just in  case the db gets wacko, we need to do this
				$queries -> push(new RelayQuery() );
				$queries -> write(true);
			}
			if($queries->count() <5 ) $queries -> push(new RelayQuery() );
			$creatVcheck = false;
			foreach ($queries as $v) {
				$vcount++;
				$visGroup = new FieldGroup();
				$visName = "Vis_".$vcount;

				$visGroup -> setRightTitle("What shall we get from Twitter?");
				$vd = $v->ID;
				$fields -> push(HiddenField::create($visName.'ID')->setValue($vd) );
				if ($vd != 0) {
					if ( $creatVcheck) {
						$vcheck = new CheckboxField($visName . "delete", "Delete this Query?", false);
						$vcheck -> addExtraClass('vdeletor');
					} else 
						$vcheck = HiddenField::create($visName."delete") -> setValue(false);
				} else {
					if ( $creatVcheck) {
						$vcheck = new CheckboxField($visName . "create", "Create new Query?", false);
						$vcheck -> addExtraClass('vcreator');
					} else $vcheck = HiddenField::create($visName."create") -> setValue(true);
				}
				$creatVcheck= true; // after the first one
				$visGroup -> push($vcheck);
				$menuMap = array('first' => 1);
				$menuCurrently = 2;
				$main = $v->ClassName;
				$authority = $v->ID;
				if($main != "SearchRelayQuery") {
					$main = $v->PenName() -> screen_name;
					$authority = $v->ClassName;
				}
				$visGroup -> push( $d1 =new DropdownField($visName . "qMain", "request", $menuMap, $main));
				$visGroup -> push( $d2 =new DropdownField($visName . "qAttr", "specifically?", $menuMap, $authority));
				//$visGroup -> push(new TextField($visName . "request", "Request", $v->requestString));
				//$fields -> push(HiddenField::create($visName."request")->setValue($v->requestString) );
				$javaScripting .='$("#Form_visualFormEditor_'. $d1->id(). '").doubleSelect("Form_visualFormEditor_' . $d2->id() .'",dropSpec,'.
					'{ preselectFirst: "' . $main . '" , preselectSecond: "' . $authority . '"}' 
				       	.');';
				$fields -> push(HiddenField::create($visName. 'Pane')->setValue($p->ID) );
				$paneGroup -> push($visGroup);
				}
			$fields->push($paneGroup);
			}
		$fields->push(HiddenField::create('totalPanes')->setValue($count) );
		$fields->push(HiddenField::create('totalQuery')->setValue($vcount) );
				View::wrapJava( 'dropSpec = '.  $this-> formJSONMenu () .';' . $javaScripting);
		return new Form($this,'visualFormEditor',$fields, new FormAction("editQuery","Edit Query"));
	}

   function WatchList(){
   error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

	return $this -> profile-> TwitterQueries();
   }
	function editQuery($data,$form) {
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		$data=$form->getData();
	Debug::show($data);
		$panes = $this->currentMode->Panes();
			//error_log(print_r($data,1) );
		$panes_to_be_deleted=array();
		$the_panes = array();
		$the_new_pane=0;
		for ($i=1; $i <= $data['totalPanes'] ; $i++ ) {
			$prefix='Pane_'.$i;
			if(! isset($data[$prefix."ID"] ) ) continue;
			$pid= $data[$prefix. "ID"];

			if ($pid >0) {
				$p=DataObject::get_by_id('Pane',$pid);
				if (!$p) continue;
				if($data[$prefix.'delete'] == 1 ) {
					error_log("Deletion selected on " . $p->ID);
					$panes_to_be_deleted[]=$p;
				}
			} else { 
				if ($data[$prefix.'create'] == 0  ) continue;
				$p= new Pane;
				$p->write();
				$the_new_pane=$p->ID;
				error_log("Creation selected on " . $p->ID);
				$panes->add($p);
				}
			$the_panes[$p->ID] = $p;
			$p->userKey = $data[$prefix . 'userKey'];
			$p->width = $data[$prefix . 'width'];
			$p->write();
		}

		for ($i=1; $i <= $data['totalQuery'] ; $i++ ) {
			$prefix='Vis_'.$i;
			if(! isset($data[$prefix."ID"] ) ) continue;
			$vid= $data[$prefix. "ID"];
			$pID = $data[$prefix."Pane"];  // a hidden field that supposedly points to the correct pane
			if($pID == 0 ) {
			       	$pID = $the_new_pane;  // the newly created pane
				error_log("The new pane is=" . $the_new_pane . " the prefix is ". $prefix);
				$data[$prefix.'create']=1;   // force creation of first Query in new pane
			}
			if($pID == 0 ) continue;  // an empty, uncreated pane
			$Queries = $the_panes[$pID] ->Queries();
			if ($vid >0) {
				$v=DataObject::get_by_id('RelayQuery',$vid);
				// we remove it now, but in all likely hood will add it back in the next phase
				if ($v)	$Queries->remove($v);
				if( $data[$prefix.'delete'] == 1 ) {
					error_log("Deletion selected on Query " . $v->ID);
						//		$v->delete();   Don't remove the query altogether
					continue;
				}
			} else {
				if(@$data[$prefix.'create'] != 1) continue;
			}



			$className = $data[$prefix.'qMain'];
			$authentication = $data[$prefix.'qAttr'];

			if ($className != 'SearchRelayQuery') {
				$t = $className;
				$className = $authentication;
				$authentication = $t;
				$penName = TweetUser::getTweetUser( $authentication);
				$v = DataObject::get_one($className, '`Title`="'. $authentication . '"' );
				if (!$v) {
					$v = new $className ( array('Title' => $authentication , 'query' => $authentication) );
					$v -> write();
				}
				if (! $v->PenNameID) {
					$v->PenNameID = $penName->ID;
				}
			} else { 
				$v = DataObject::get_by_id($className,$authentication);
			}
			$Queries->add($v);

			/* JAH here is V */
			$v->forceChange();
			$v->write();
			$Queries ->write();
		}			
		foreach($panes_to_be_deleted as $p ) {
			$vis = $p->Queries();
			foreach ($vis as $v) {
				$vis->remove($v);
				$v->delete();
			}
			$panes->remove($p);
			$p->delete();
		}
		return $this;
		Director::redirectBack();
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
	//	error_log( print_r($menu,1));
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

// go through the standard 'socialite' Query sub-array and create the Query on the fly for this person
	function standardQueries($pane,$specs,$penName) {
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		$relays = $pane->Queries();
		$relays -> removeAll();
		foreach ($specs as $vData ) {
				// retrieve the basic info to populate a RelayQuery for the socialite user
			$relays->add(RelayQuery::setRelayFromVData($vData, $penName,$pane) );
		}
		$relays ->write(true);
		return ;
	}

	function editWatch( $data ) {
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		//$this->profile->removeWatch( $data->param('ID') );
		$this->query_edit_id = $data->param('ID');
		$this->misc="wow";
		// format page exactly as if we had been called at the 'index' entryPoint
		return  $this;
	}

/*
	function index() {
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		error_log("index of whazzit");
		return $this;
	}
	*/
	function newQueryForm () {
		$existing = $this->query_edit_id;
		$searchKey = "";
		$searchNot = "";
		$searchLoc = "";
		$searchText = "";
		$deleteOnly=false;
		$e=false;
		if ($existing) {
			$e = DataObject::get_by_id('TwitterQuery',$existing);
			if ($e instanceOf RelayQuery && ! $e instanceOf SearchRelayQuery) {
				$deleteOnly = true;
			} else {
				if(! $e instanceOf SearchRelayQuery ) {
					$e = $e->newClassInstance('SearchRelayQuery');
					$e->keywords = $e->query;
				}
			$e = DataObject::get_by_id('SearchRelayQuery',$existing);
			$searchKey = $e -> keywords;
			if ($searchKey == "") $searchKey = $e->query;
			$searchNot = $e -> negativeWords;
			$searchLoc = $e -> location;
			}
			$searchText= $e->forTemplate();
		}
		$titleField =  new TextField("Title", "Display Name", $searchText);
		if(Permission::check('MENTOR') ) {
		   $ContestBox = new CheckboxField("contest", "Contest?");
		   $ContestBox -> setValue( $e instanceOf Contest);

		   $dateStart = new PopupDateTimeField("StartTime", "Contest start date");
		   $dateCutoff = new PopupDateTimeField("CutOff", "Contest end time");
		   $dateCutoff -> futureDateOnly();
		} else {
		   $ContestBox = HiddenField::create('contest')->setValue(0);
		   $dateStart = HiddenField::create('StartTime')->setValue(0);
		   $dateCutoff = HiddenField::create('CutOff')->setValue(0);

		}

		if (! $deleteOnly ) {
		      $fields =  new FieldSet( $titleField, $ContestBox,$dateStart, $dateCutoff);
			$fields -> push( new TextField("keywords", "words or phrases to find - separate with commas", $searchKey) );
			$fields -> push( new TextField("negativeWords", "words to NOT find", $searchNot) );
			$fields -> push( new TextField("location", "Geographic location", $searchLoc) );
		} else {
			$fields =  new FieldSet( $titleField ->performDisabledTransformation() );
			 $fields =  new FieldSet( $titleField->performDisabledTransformation() , $ContestBox->performDisabledTransformation() ,$dateStart->performDisabledTransformation(),  $dateCutoff->performDisabledTransformation() );
		}
		if($existing || $deleteOnly) {
			$fields->push( new checkboxField("deletequery", "Delete Query"));
		} else $fields -> push(HiddenField::create('deletequery')->setValue(0));
		$fields -> push(HiddenField::create('existingID')->setValue($existing));

		$actions = new FieldSet( new FormAction('createNewQuery', $existing?'Update Query':'New Query'));
	  	return new Form($this, "newQueryForm", $fields, $actions);
	}

	function createNewQuery($dataArray,$form) {
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		$dataFields = $form->getData();
		$existing= $this->xfer($dataArray,'existingID',$dataArray,'existingID');
			
		if($existing) {
			$e = DataObject::get_by_id('TwitterQuery',$existing);
			if(isset ($dataFields['deletequery'] ))  {
				$this->profile->TwitterQueries()->remove($e);
				$e->delete();
				return  Director::redirect(Director::baseURL() . $this->URLSegment);
			}
		} else {
			$e = ($dataArray['contest'] && Permission::check("MENTOR") ?new Contest():
					     new SearchRelayQuery() ) ;
		}
		$form->saveInto($e);
		$e->MentorID = $this->Profile->ID;
		$e->write();
		$this->profile->addTwitterQueryWatch($e);
		// fill my local database from twitter
		$tag=$e->mysetQueryTag(); 
		return  Director::redirect(Director::baseURL() . $this->URLSegment);
	}



function notValid ( ) {
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

   return ($this->Profile(true)->ValidationToken != 0 ) ;
}

function profileFormFields ($f) {
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

      // List your fields here
// the next line is a hack to fix a broken admin group: WHAT A HASSLE!!!
//Security::findAnAdministrator('jahbini','password');
if (Permission::check("MENTOR") ) $userClass = "Mentor";
   $name = new TextField( $name = "Name" , $title= "Your user name as a ". $userClass,$this->Profile()->Name);
   $f->push( $name -> performReadOnlyTransformation());
   $f->push(new TextField( $name = "FirstName" , $title= "First name"));
   $f->push(new TextField("Surname", "Last Name"));
   $f->push(new PointyEmailField("Email", "Email address", $this->Profile(true)->Email));
   /*
   $f->push(new DropdownField ( $name = "Role","Membership Level",
		  $source = FullMember::provideLevels(),
		  $source = $this->profile-> RoleID) 
);
   */

// member set by call to 'Profile()' above
//   $f->push(new DropdownField ( $name = "Country","Country (if outside US)",
//		  $source = Geoip::getCountryDropDown()
//		  , $this->Member->Country
//		 // , $geo = Geoip::visitor_country()
//		  ) );
//   $f->push( new TextField("TopTenList","Tags you follow", $this->profile()->getTopTenList()));
   //$f->push(new RequiredFields ( "FirstName"));
   return $f;
}

function form() {
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

   $fields = new FieldSet ();
   $data = Session::get("Profile.Form_RegistrationForm.data");
   $use_openid=false;
   $profile=$this->Profile(true);
   $collapsePasswordField=false;
      $mustFillIn = "You Must select a password to complete this registration";
   if ($profile->ValidationToken == 0 )  {
      // this user is already valid so show all the fields in a regular profile
      $fields = $this->profileFormFields($fields);
	 //with only a javascript 'link' to change the password field
      $collapsePasswordField=true;
   $mustFillIn = _t('Member.PASSWORD',"Password");
   }
   $pass = new ConfirmedPasswordField("Password", $mustFillIn
	 ,null,null,$collapsePasswordField);
   if($this -> Member -> Password != '') {
      $pass -> setCanBeEmpty(true);
   }
   $fields-> push($pass);
   if ($profile->ValidationToken != 0 )  {
	 //$validator = new RequiredFields($this, "TopTenList", "Password");
	 $validator = new RequiredFields($this, "Password");
	 $action = (new FieldSet(new FormAction("process", "Register")));
   } else {
	 $validator = new myProfileValidator($this, "FirstName");
	 $action = (new FieldSet(new FormAction("process", "Update Profile")));
   }
   $form = new Form($this, 'form', $fields, $action, $validator);
   $form->loadDataFrom($this);      // If OpenID is used, we should also load the data stored in the session
   $form->loadDataFrom($this->Member);      // If OpenID is used, we should also load the data stored in the session
   return $form;
}

function process ($data , $form) {
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

     // obtain this member
      $profile = $this->Profile(true);
      $form->saveInto($this->Member);
      $this->Member ->write();
      //$this->Member ->login();
      // make the new user a member of the REGISTERED_USER group
      $g = $this->Member -> Groups();
      $group = DataObject::get_one("Group","Code = '" . SiteTree::generateURLSegment("REGISTERED_USER") . "'");
      $g -> add($group);
      $g -> write(true);

      $profile->ValidationToken=0;
      //user now has a password and we can remove the validation token
      // we also have a new element named 'ProfileData'
      $profile->setMember($this->Member);

      $form -> saveInto($profile);
      $profile -> write();
      Session::set('ProfileID' , $profile-> ID);

      Director::redirectBack();
      return $this;
   }
}

class myProfileValidator extends Validator {

   //var $TopTenName;
   var $entryFieldName;
   var $thisPage;
   var $Firstname;  // the entry that is is being entered on the form
   var $data;

   function __construct($thisPage,$entryFieldName) {
   parent::__construct($entryFieldName);
   $this->thisPage = $thisPage;
   $this->entryFieldName = $entryFieldName;
   //$this->TopTenName = $TopTenName;
   }


function javascript () { return "";}

function canWatch ($tags) {

if (trim($tags)=="") return true;
   if(Permission::check('ADMIN')) return true;  // ALWAYS OK for admin to watch

   $tagsArray = explode(',',$tags);
   // it is not legal if we have no permission to do so
   //if(!Permission::check('TAG_WATCHER') )
    //  {
//	 $this->validationError($this->TopTenName, "only Registered Voters and above may get top ten tag ranking", "permission" );
//	  return false;
 //     }
   if(sizeof($tagsArray) <= $this->thisPage->profile->TagWatchLimit) return true;
   // if we are already watching this site, then it is OK.
   
   //$this->validationError($this->TopTenName, "You have reached the maximum number of tags you may watch", "permission" );
   return false;
}

function debug() {
Debug::show($this->data);
}

function php ($data) {
	// method php records the data array into the data field
	// and  validates that "entryFieldName" is filled in
	// this routine is a form helper for field 'entryFieldName'
   $this -> data = $data;
   $success = $this -> canEntry($data[$this->entryFieldName]);
   if ($success) return true;
   $_REQUEST['_REDIRECT_BACK_URL'] = Director::link($this->thisPage->URLSegment);
   return false;
   } 

function canEntry($field) {
	// helper for method 'php'
   if($field=="") {
      $this->validationError( $this->entryFieldName,
				 sprintf( _t('Form.FIELDISREQUIRED'),
					       strip_tags($this->entryFieldName)),
					  "required");
       return false;
      }
   return true;
   }
}
?>
