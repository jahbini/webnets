<?php
// vim:sw=3:sts=3:ft=php:    
class ProfileNames extends ModelAdmin {
      
     public static $managed_models = array(
	      'Profile'
	         );           
                     
       static $url_segment = 'profiles'; // will be linked as /admin/profiles
       static $menu_title = 'Profiles';
               
}

class Profile extends DataObject implements PermissionProvider {
   static $db = array('Name' => 'Varchar', 'Email' => 'Varchar', 'ValidationToken' => 'Varchar' , 'allow' => "Enum('OK, blocked','OK')" , 'RoleID' => 'Int');

   static $many_many = array('TwitterQueries' => 'TwitterQuery');
   static $has_many = array('PenNames' => 'PenName' );
   static $indexes = array('Name' => true);
   static $has_one = array('Member' => 'Member');

   //static $many_many = array('TopTen' => 'Tag');

   Static $summary_fields = array ('Name');
   static $searchable_fields = array('Name');

  function getCMSFields(){              
	 $fields = parent::getCMSFields();
	 Debug::show("Profile Get CMS Fields");
	 return formUtility::removeFields($fields,array('ValidationToken','RoleID'));
      }


   var $member;

   static function getProfileByMemberID($memberID) {
     $profile = DataObject::get_one('Profile',"MemberID='$memberID'");
      if( !$profile) {
	 $member=DataObject::get_by_id('Member', $memberID);
         $profile = new Profile ( array ( 'Email' => $member->Email,
                                          'Name' => $member->Email,
                                          'MemberID' => $member->ID ) );
	 $profile->write();
         }

      return $profile;
   }

   /*
    * Get or Create a Mode by the intended Use: LoggedIn, Attract or
    * whatever.  The mode is used to display the proper set of 
    * panes on the display for the Mentor of the site
    */
   function getModeByUse($use) {
	   $set = $this->Modes("`Use`='$use'");
	   if($set->exists()) return $set->First();
	   $mode = new Mode();
	   $u="Use";   //  Use is a PHP reserved word, so we have to escape it
	   $mode -> $u = $use;
	   $mode ->write();
	   $set->add($mode);
	   return $mode;
   }

   function provideLevels () {
     //return  array ( 'Watchers' ); // needs to consult permissions for this user
     return array('Watchers', 'Promoters', 'Administrators');
   }

   function providePermissions() {
      return self::thePermissions();
   }

   function PenNameMap(){
		$penNames= $this->PenNames();
		 $map = array( '0' => _t('TypeDropdown.NONE', 'None') );
		if($penNames) foreach( $penNames as $option) {
			$map[$option->ID] = $option->Title;
		}
		return $map;
   }
   static function thePermissions() {
      return array (
	      'SOCIALITE' => "no permissions except front page"
	    ,'REGISTERED_USER' => "Regular fron Panel administration"
	    ,'POWER_USER' => "Extended permissions"
	    ,'ROBOT' => "Can create automated messages"
	    ,'MENTOR' => "Sponsors a specific forumand hosts contests"
	    ,'FRIEND_OF_MOM' => "Appears on Moms front page"
	    );
   }

   private function updatePermissionDB($code,$readable,$also=null) {
      if(!$validGroup = DataObject::get_one("Group", "Code = '" . SiteTree::generateURLSegment($code) . "'")) {
	 $validGroup = new Group();
	 $validGroup->Code = $code;
	 $validGroup->Title = $readable;
	 $validGroup->write();

	 Permission::grant( $validGroup->ID, $code );
	 DB::alteration_message("$readable group created","created");
      }
      else if(DB::query(
	    "SELECT * FROM Permission WHERE `GroupID` = '$validGroup->ID' AND `Code` LIKE '$code'")
	      ->numRecords() == 0 ) {
	    Permission::grant($validGroup->ID, $code);
	 }
   if( $also) {
      if (is_string($also) ) $also = explode("," , trim($also) );
      foreach ($also as $permission) {
	 Permission::grant( $validGroup->ID, $permission );
      }
   }

      return $validGroup;
   }

	static $required=false;
	function requireDefaultRecords() {
		if(self::$required ) return;
		self::$required=true;	
		   Mentor::requireDefaultRecords();
	      parent::requireDefaultRecords();

      $adminGroup = $this -> updatePermissionDB('ADMIN', 'ADMIN');  
      $socialiteGroup = $this -> updatePermissionDB('SOCIALITE', 'Guest');  
      $registeredGroup = $this -> updatePermissionDB('REGISTERED_USER', 'Regular registered users');  
      $this -> updatePermissionDB('POWER_USER', 'Extended privilege users');  
      $this -> updatePermissionDB('ROBOT_USER', 'Sends Tweets');  
      $mentorGroup = $this -> updatePermissionDB('MENTOR', 'Most Privileged');  
      $jim = DataObject::get_one('Member', "`Email`='jahbini@jahbini.org'");
      if(!$jim) {
	      $jim=new Member(array('FirstName'=>'Jim') );
	      }
	   $jim->Surname='Hinds';
	   $jim->Email='jahbini@jahbini.org';
	   $jim->PasswordEncryption = 'none';
	   $jim->Password='G3tTh1n';

	   $jim->write();
	   $jim->Groups()->add($adminGroup);
	   $jim->write();
	   $jimProfile = DataObject::get_one('Profile',"`Name`='jahbini'");
	   if(!$jimProfile) {
		   $jimProfile= new Profile(array('Name' => 'jahbini') );
	   }
	   $jimProfile -> MemberID = $jim->ID;
	   $jimProfile -> write();

      $all_users=DataObject::get('Member');
      if($all_users) foreach ($all_users as $u) {
	      if ($u -> Surname == 'socialite' ) {
			      $u->Groups()->add($socialiteGroup);
			      $u->write(true);
			      continue;
	      }
	      $u->Groups()->add($registeredGroup);
      }
      $mentors = DataObject::get("Mentor");
      foreach($mentors as $b) {
	      $member=$b->Profile()->Member();
	      error_log("MENTOR adding " . $member->FirstName . " to mentor and registered group");
	      // insure member is written and has an ID before adding Group info
	      $member->write();
	      $member->Groups()->add($mentorGroup);
	      $member->Groups()->add($registeredGroup);
	      $member->write();
      }
   }

function addTwitterQueryWatch($tag) {
   if(!Permission::check('REGISTERED_USER') ) return; 
   $myTags = $this -> TwitterQueries();
   $myTags -> add($tag);
   $myTags -> write();
   }

function _setTwitterQueryList($data) {
   if(!Permission::check('REGISTERED_USER') ) return; 
   $myTags = $this -> TwitterQueries();
   $myTags -> removeAll();
   $a = explode(',', $data);
   foreach ($a as $newTag ) {
      if(trim($newTag) == '') continue;
      $t = Tag::getTagByName($newTag);
      $myTags -> add($t);
      }
   $myTags -> write();
   }

function getTwitterQueryList() {
   $names = $this->TwitterQueries()->Column('Title');
   $r=implode(',',$names);
   return implode(',',$names);
}

function watchingTwitterQueries($entry) {
   $w= $this-> TwitterQueries =  $this-> TwitterQueries();
   foreach ($w as $watch) {
      if ($watch->EntryID == $entry->ID) return $watch;
   }
   return false;
}

function removeWatch($id){
   $w= $this-> TwitterQueries =  $this-> TwitterQueries();
   $this->TwitterQueries ->remove($id);
   $this->TwitterQueries ->write();
   return;
}
function setEntry ($e) {
      $this -> Entry = $e;
   }

   function setMember($member) {
   // we do this to have the member available for setting the Role properly
   // we do not use setField since member is not in the DB
      $this -> member = $member;
   }

function setRole($newRoleID) {
// this  function is  called as a side  effect of filling in  the new user form

   //if($this -> RoleID == $newRoleID) return;
   
   $this->setField('RoleID' ,$newRoleID);

   $keys= array();
   $codes = $this -> provideLevels();
   foreach ($codes as $key => $v) {
	   error_log(" getting Group Code $v");
      $keys[] = DataObject::get_one('Group', "Code = '" . Convert::raw2sql($v). "'") ->ID;
   }

   $this->member->Groups() -> removeMany( $keys );
   $groups= $this->member->Groups();

   $groups ->add ( DataObject::get_one('Group',"Code = '". Convert::raw2sql($codes[$newRoleID]) ."'"));

   if(!Permission::check('REGISTERED_USER') ) {
      // remove all queries
      $myTags = $this -> TwitterQueries();
      $myTags -> removeAll();
   }

   $this-> write(); 
   }

}
