<?php
class Page extends SiteTree {

	public static $db = array(
		'DisableSubDomain' => 'Boolean'
	);

	public static $has_one = array(
	);
   
    function getCMSFields() {
        $fields = parent::getCMSFields();
         
        $fields->addFieldToTab('Root.Main', new CheckboxField('DisableSubDomain',$this->fieldLabel('Disable eGroup Text')),"Content");
         
        return $fields;
    }

}
class Page_Controller extends ContentController {

	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	public static $allowed_actions = array (
	);

	public function init() {
		parent::init();

	}

	public function subDomain () {
		if ($this -> DisableSubDomain) return false;
	global $wantedSubDomain;
		$the_sub = DataObject::get_one('SubDomain','"Title"=' ."'$wantedSubDomain'");
		return $the_sub;
	
	}
}
