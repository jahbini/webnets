<?php
// vim:sts=3:sw=3:ft=php:
/**
 * @package sapphire
 * @subpackage security
 */

/**
 * Log-in form for the "member" authentication method
 * @package sapphire
 * @subpackage security
 */
class ProfileLoginForm extends LoginForm {

    /**
    * Constructor
    *
    * @param Controller $controller The parent controller, necessary to
    *                create the appropriate form action tag.
    * @param string $name The method on the controller that will return this
    *           form object.
    * @param FieldSet|FormField $fields All of the fields in the form - a
    *               {@link FieldSet} of {@link FormField}
    *               objects.
    * @param FieldSet|FormAction $actions All of the action buttons in the
    *                 form - a {@link FieldSet} of
    *                 {@link FormAction} objects
    * @param bool $checkCurrentUser If set to TRUE, it will be checked if a
    *                the user is currently logged in, and if
    *                so, only a logout button will be rendered
    */
function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true) {

   $this->authenticator_class = 'ProfileAuthenticator';
   Session::set('SessionForms.LoginForm', 'ProfileLoginForm');

   $customCSS = project() . '/css/member_login.css';
   if(Director::fileExists($customCSS)) {
      Requirements::css($customCSS);
   }

   if(isset($_REQUEST['BackURL'])) {
      $backURL = $_REQUEST['BackURL'];
   } else {
      $backURL = Session::get('BackURL');
   }

   if($checkCurrentUser && Member::currentUserID()) {
      $fields = new FieldSet();
                                                 
      $actions = new FieldSet(new FormAction("profile", _t('Profile.BUTTONPROFILE', "Profile")),
	    new FormAction("logout", _t('Member.BUTTONLOGINOTHER', "Log Out")));
   } else { if(!$fields) {
	 $fields = new FieldSet(
	    new HiddenField("AuthenticationMethod", null, $this->authenticator_class, $this),
	    //new TextField("Name", _t('Profile.Name'),
	    new TextField("Name", "User Name",
	       Session::get('SessionForms.ProfileLoginForm.Name'), null, $this),
	    new PasswordField("Password", _t('Member.PASSWORD'), null, $this),
	    new CheckboxField("Remember", _t('Member.REMEMBERME', "Remember me next time?"),
	       Session::get('SessionForms.ProfileLoginForm.Remember'), $this)
	 );
      }
      if(!$actions) {
	 $actions = new FieldSet(
	    new FormAction("dologin", _t('Member.BUTTONLOGIN', "Log in")),
	    new FormAction("forgotPassword", _t('Member.BUTTONLOSTPASSWORD', "Lost password?"))
	 );
      }
   }

   if(isset($backURL)) {
      $fields->push(new HiddenField('BackURL', 'BackURL', $backURL));
   }
   return parent::__construct($controller, $name, $fields, $actions);
}


/**
 * Get message from session
 */
protected function getMessageFromSession() {
   parent::getMessageFromSession();
   if(($member = Member::currentUser()) &&
	 !Session::get('ProfileLoginForm.force_message')) {
         $this->message = sprintf(_t('Member.LOGGEDINAS', "You're logged in as %s."), $member->FirstName);
   }
   Session::set('ProfileLoginForm.force_message', false);
}


/**
* Login form handler method
*
* This method is called when the user clicks on "Log in"
*
* @param array $data Submitted data
*/
public function dologin($data) {
   if($this->performLogin($data)) {
      Session::clear('SessionForms.ProfileLoginForm.Name');
      Session::clear('SessionForms.ProfileLoginForm.Remember');

      if(isset($_REQUEST['BackURL']) && $backURL = $_REQUEST['BackURL']) {
	 Session::clear("BackURL");
	 Director::redirect($backURL);
      } else {
	 Director::redirectBack();
      }
   } else {
      Session::set('SessionForms.ProfileLoginForm.Name', $data['Name']);
      Session::set('SessionForms.ProfileLoginForm.Remember', isset($data['Remember']));
         
      if(isset($_REQUEST['BackURL']) && $backURL = $_REQUEST['BackURL']) {
	 Session::set('BackURL', $backURL);
      }
         
      if($badLoginURL = Session::get("BadLoginURL")) {
	 Director::redirect($badLoginURL);
      } else {
	 // Show the right tab on failed login
	 Director::redirect(Director::absoluteURL(Security::Link("login")) .  '#' . $this->FormName() .'_tab');
      }
   }
}


/**
* Log out form handler method
*
* This method is called when the user clicks on "logout" on the form
* created when the parameter <i>$checkCurrentUser</i> of the
* {@link __construct constructor} was set to TRUE and the user was
* currently logged in.
*/
static public function logout($redirectback=false) {
   Session::clear('SessionForms.ProfileLoginForm.Name');
   Session::clear('SessionForms.ProfileLoginForm.Remember');
   Session::clear('ProfileID');
   $s = new Security();
   $s->logout($redirectback);
   Session::clear("Security.Message.message" );
   Session::clear("Security.Message.type");
   // Initialize the session.
   // If you are using session_name("something"), don't forget it now!
   //session_start();

   // Unset all of the session variables.
   $_SESSION = array();

   // If it's desired to kill the session, also delete the session cookie.
   // Note: This will destroy the session, and not just the session data!
   if (isset($_COOKIE[session_name()])) {
       setcookie(session_name(), '', time()-42000, '/');
       }

       // Finally, destroy the session.
       session_destroy();
}

public function profile() {
   Director::redirect('/user-profile');
}

/**
* Try to authenticate the user
*
* @param array Submitted data
* @return Member Returns the member object on successful authentication
*      or NULL on failure.
* this is for our 'username' (name) log-in panel
* on succesful login, it calls the Login method of the Member DO
*/
public function performLogin($data) {
   if($member = ProfileAuthenticator::authenticate($data, $this)) {
      $firstname = Convert::raw2xml($member->FirstName);
      Session::set("Security.Message.message", 
	 sprintf(_t('Member.WELCOMEBACKxx', "Welcome Back xx, %s"), $firstname)
      );
      Session::set("Security.Message.type", "good");

      $member->LogIn(isset($data['Remember']));
      return $member;

   } else {
      return null;
   }
}


/**
 * Forgot password form handler method
 *
 * This method is called when the user clicks on "I've lost my password"
 *
 * @param array $data Submitted data
 */
function forgotPassword($data) {
	error_log("forgot password a");
   $SQL_data = Convert::raw2sql($data);
   if(($data['Name']) && ($profile = DataObject::get_one("Profile",
	 "Profile.Name = '$SQL_data[Name]'"))) {

	$MemID=$profile->MemberID;
	error_log("forgot password b MemID = $MemID");
	$member = DataObject::get_by_id('Member', $MemID );
	error_log("forgot password c");
	if ($member) {
	error_log("forgot password d");
	      $member->generateAutologinHash();
	      $member->sendInfo('forgotPassword', array('PasswordResetLink' =>
		 Security::getPasswordResetLink($member->AutoLoginHash)));
	      Director::redirect('Security/passwordsent/' . urlencode($profile->Email));
	} else {
	error_log("forgot password e");
      $this->sessionMessage(
	 "Sorry, but we are waiting for that user to validate the account.",
	 "bad");
      Director::redirectBack();

	}

   } else if($data['Name']) {
      $this->sessionMessage(
	 "Sorry, but I don't recognise the username. Maybe you would like to sign up?",
	 "bad");
      Director::redirectBack();

   } else {
      Director::redirect("Security/lostpassword");
   }
}

}


?>
