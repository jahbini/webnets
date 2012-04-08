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

}

class ProfilePage_Controller extends TagWatch {
      var $request_token;
      var $query_edit_id =0;// if non-zero is the query we wish to edit

	function init( $params = false){
		parent::init();
		$this->PenNames = $this->profile->PenNames();
		$this->request_token= Session::get('request_token');
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
		Requirements::javascript('tell140/javascript/jquery.doubleSelect.js');
	}
   //var $WatchTags;
   //var $WatchList;

      /* function newPenName is Page_Controller in Page.php */

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

function notYetValid ( ) {
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
$v=$this->Profile(true) -> ValidationToken;
error_log("ValidationToken = $v");
   return ($this->Profile(true)->ValidationToken != 0 ) ;
}

function &profileFormFields (&$f) {
   $profile = $this->Profile();  // Also sets Member
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
//Security::findAnAdministrator('jahbini','password');
   $f->push(new TextField( $name = "FirstName" , $title= "First name",$this->Member->FirstName));
   $f->push(new TextField("Surname", "Last Name",$this->Member->Surname));
   $f->push(new PointyEmailField("Email", "Email address", $profile->Email));
   /*
   $f->push(new DropdownField ( $name = "Country","Country (if outside US)",
		  $source = Geoip::getCountryDropDown()
		  , $this->Member->Country
		  , $geo = Geoip::visitor_country()
		  ) );
    */
//   $f->push( new TextField("TopTenList","Tags you follow", $this->profile()->getTopTenList()));
   //$f->push(new RequiredFields ( "FirstName"));
   return $f;
}

function form() {
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

   $fields = new FieldSet ();
   $fields = $this->profileFormFields($fields);
   // append the password field(s)
   $data = Session::get("Profile.Form_RegistrationForm.data");
   $use_openid=false;
   $profile=$this->Profile(true);
   $collapsePasswordField=false;
      $mustFillIn = "You Must select a password to complete this registration";
   if ($profile->ValidationToken == 0 )  {
      // this user is already valid so show all the fields in a regular profile
      $fields = $this->profileFormFields($fields);
	 //with only a javascript 'link' to change the password field
      $collapsePasswordField='change your password';;
   $mustFillIn = _t('Member.PASSWORD',"Password");
   }

   $pass = new ConfirmedPasswordField("Password", $mustFillIn,null,null,$collapsePasswordField);
   $pass -> setCanBeEmpty( $this -> Member -> Password != '' );

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
   //$form->loadDataFrom($this->Member);      // If OpenID is used, we should also load the data stored in the session
   $form->loadDataFrom($this->Profile);      // If OpenID is used, we should also load the data stored in the session
   return $form;
}

function process ($data , $form) {
error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

     // obtain this Member
      $profile = $this->Profile(true);
      $form->saveInto($this->Member);
      $this->Member ->write();
      //$this->Member ->login();
      // make the new user a Member of the REGISTERED_USER group
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
