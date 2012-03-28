<?php
// vim:sts=3:ts=3:ft=php:

/**
 * @package sapphire
 * @subpackage security
 * @author Markus Lanthaler <markus@silverstripe.com>
 */

/**
 * Authenticator for the default "ProfileLoginForm" method
 *
 * @author Markus Lanthaler <markus@silverstripe.com>
 * @author Jim Hinds <jahbini@jahbini.org>
 * @package sapphire
 * @subpackage security
 */
class ProfileAuthenticator extends Authenticator {

  /**
   * Method to authenticate an user
   *
   * @param array $RAW_data Raw data to authenticate the user
   * @param Form $form Optional: If passed, better error messages can be
   *                             produced by using
   *                             {@link Form::sessionMessage()}
   * @return bool|Profile Returns FALSE if authentication fails, otherwise
   *                     the member object
   * @see Security::setDefaultAdmin()
   */
  public static function authenticate($RAW_data, Form $form = null) {
		$nick = Convert::raw2sql($RAW_data['Nickname']);
		$Profile = DataObject::get_one("Profile", "Profile.Nickname = '$nick'");
		if (!$Profile) {
			$form->sessionMessage(
			_t('Profile.ERRORWRONGCRED',
			 "That doesn't seem to be the right username or password. Please try again."),
			"bad"
			);
			return "";
		}
		$Email = $Profile->Email;
		$MemberID = $Profile->MemberID;
		// Default login (see Security::setDefaultAdmin())
		if(Security::check_default_admin($nick, $RAW_data['Password'])) {
			$member = Security::findAnAdministrator($nick, $RAW_data['Password']);
		} else {
			$member = DataObject::get_one("Member", "ID='$MemberID' AND Password IS NOT NULL");
			if($member && ($member->checkPassword($RAW_data['Password']) == false)) {
					$member = null;
			}
		}

		if($member) {
			Session::clear("BackURL");
		} elseif(!is_null($form)) {
			$form->sessionMessage(
			_t('Profile.ERRORWRONGCRED',
			 "That doesn't seem to be the right username or password. Please try again."),
			"bad"
			);
		}
		return $member;
		}


		/**
		* Method that creates the login form for this authentication method
		*
		* @param Controller The parent controller, necessary to create the
		*                   appropriate form action tag
		* @return Form Returns the login form to use with this authentication
   *              method
   */
  public static function get_login_form(Controller $controller) {
    return Object::create("ProfileLoginForm", $controller, "LoginForm");
  }


  /**
   * Get the name of the authentication method
   *
   * @return string Returns the name of the authentication method.
   */
  public static function get_name() {
		return "Username &amp; Password";
	}
}


?>
