<?php
// vim:sw=3:sts=3:ft=php:set syn=on:    

class SignupPage extends Page {
	static $defaults = array(
		"ShowInMenus" => 0,
		"ShowInSearch" => 0
	);

	   static $required = false;
   function requireDefaultRecords() {
	   if (self::$required) return;
	   parent::requireDefaultRecords();
	   self::$required = true;
	   $signUp = DataObject::get_one("SiteTree", "URLSegment='sign-up'");
	   if($signUp instanceOf SignupPage) return;
	   if($signUp instanceOf Page) {
		   $signUp = $signUp -> newClassInstance('SignupPage');
		   $signUp -> write();
	   }
	   if (!$signUp) {
		   $signUp = new SignupPage();
	   }
	   $signUp -> Title = "Create an account";
	   $signUp -> Content = "<h1>Enter your name and e-mail address</h1>";
	   $signUp -> URLSegment = "sign-up";
	   $signUp -> Status = "Published";
	   $signUp -> ShowInMenus=false;
	   $signUp -> ShowInSearch=false;
	   $signUp -> write();
	   $signUp -> publish("Stage","Live");
	   $signUp -> flushCache();
   }

}

class SignupPage_Controller extends Page_Controller {
  public $Taglist;

function init() {
    $this->Content='';
   parent::init();
    global $userState;
    if ($userState != 'mentored') {
	    Director::Redirect(BASE_URL);
    }
}

//function index () {
//if ($this-> nocan != '') { $this ->Content = $this-> nocan;}
//return $this;
//}

function Form() {
  //create fields 
  $fields = new FieldSet ( 
	     $name= new TextField("Name",'Your user name',$this->mentee->screen_name),
	     new PointyEmailField('Email','Enter a valid e-mail address'));
  $actions = new FieldSet(
	     new FormAction('createUser', 'Submit')
	     );
  $name->setDisabled(true);
  return new Form ($this, 'Form', $fields, $actions);
  }

function validate () {
   $content = "Invalid Key for account validation";
   do {
	   if (!isset($_REQUEST['Validation'])) break;
	   $ValidationCode = $_REQUEST['Validation'];
	   if (!isset($_REQUEST['ID'])) break;
	   $ID= $_REQUEST['ID'];
	   $Profile = DataObject::get_by_id('Profile' , $ID);
	   if (!$Profile) break;
      //clear any current login as we have a new validated user
	   if (($id=Session::get('loggedInAs')) >0 ) {
	      Debug::show("FORCING LOGOUT2");
	      $Member=DataObject::get_by_id('Member',$id);
	      $Member->logOut();
	   }

	   if ($Profile ->ValidationToken == 0) {
	      // password has already been filled in, so do not re-activate
	      $content = "Account is active: Login";
	      break;
	   }
	   if ($ValidationCode != $Profile ->ValidationToken) {
	      $content = "Invalid Key for account validation";
	      break;
	   } 
	   $content="Success";
	   if ($Profile -> memberID == 0) {
		 $Member = new Member();
		 $Member -> Email = $Profile -> Email;
		 $Member -> FirstName =$Profile->Name;
		 $Member -> Surname = "";
		 $Member -> NumVisit = 0;
		 $Member -> write();
		 $Profile->MemberID = $Member->ID;
	      } else {
		 $Member = DataObject::get_by_id('Member', $Profile-> memberID);
		 if(!$Member) {
			 $content = "Internal DB error on this Member ID, call support";
			 break;
		 }
	      }
	   // this will change the userState to 'loggedIn next time through the Page Init routine
	      $Member -> logIn();
	      $Member -> write();

	      // We do NOT reset the Validation Token here, we wait until the user has entered a good password
	      // on the profile page, which we are sending the guy to
	      //       NOT HERE --  $Profile->ValidationToken=0;
	      $Profile->write();

	      Session::set("Security.Message.message", 
		    sprintf(_t('Member.WELCOMEBACK', "Welcome Back, %s"), $Profile->Name)
			  );
	      Session::set("Security.Message.type", "good");
	      Session::set('ProfileID', $Profile->ID);
	      Director::redirect("user-profile/");
	      return ;
   }
   while (false);
  return $this ->customise(array( 'Form' => '', 'Content' => $content )) ->renderWith("Page");
}

private function send_email ( $Profile ) {
   $link= Director::absoluteURL($this->Link('validate'));
   $htmlcontent=$this->customise(array ( 'Profile'=> $Profile, 'Link'=> $link ) ) -> renderWith('Profile_suspense_email');

   $plainContent = "Use this link in your browser to activate your account:\n";
   $plainContent .= Director::absoluteURL($this->Link('validate/?ID='.$Profile->ID.'&Validation='.$Profile->ValidationToken)."\n");
   $x=new Mailer();
   $result=htmlEmail(
	  $Profile->Email
          , 'no-reply@' . $this->myHostName() ,
	  'activate your ' . $this->myHostName() . ' account', $htmlcontent, false, false, $plainContent);
    if (!$result) {
       error_log("Email returns false");
    } else {
       error_log("Email returns true");
    }
   return;
}

function Resend ( ) {
   if(! isset($_REQUEST['Name']) ) {
      Director::redirectBack();
   }

   $Profile = DataObject::get_one('Profile' , "`Name`='".$_REQUEST['Name']."'");
   $this->send_email ($Profile);
   $this-> Content = 'E-mail has been sent';
   return $this ->customise(array( 'Form' => '')) ->renderWith('Page');
}

function createUser($data, $form) {
   $Profile = $this->profile;
   $form->saveInto($Profile);

   $Profile->ValidationToken= rand();
   $Profile->write();

   $this->send_email ($Profile);

   $this->Content =  "<h1>You will be recieving a new e-mail with directions on how to validate your account</h1>";
   return $this ->customise(array( 'Form' => '')) ->renderWith("Page");
   }

}
