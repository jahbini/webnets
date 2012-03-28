<?php
// vim:sw=3:sts=3:ft=php

class TagWatch extends Page_Controller {
   var $Tags;
   var $Watches;
   var $Entry;
   var $scores;
   var $Profile;
   var $canWatch;
   var $Member;


function Profile($usePid=false) {
   if (isset($this->Profile) ) return $this->Profile;

   $loggedInAs= Session::get('loggedInAs');

   if($loggedInAs) { // if we are logged in, then we know who we are
      $this->Profile = Profile::getProfileByMemberID($loggedInAs, $this);
   } elseif ($usePid)  { //if we are not logged in, we may be validating a new user
      $pid = Session::get('ProfileID'); 
      if (! $pid ) {
	 $this->Profile = new Profile(); // a dummy
	 $this->Profile -> write();
	 // set ProfileID in session to signal that we must validate password
	 Session::set('ProfileID' , $this->Profile -> ID);
      } else {
	 $this->Profile=DataObject::get_by_id('Profile', $pid);
      }
   }  else {  // no, we are not validating a new user, so make a pretend one
      $this->Profile = new Profile(); // a dummy
   }

   $this->Member = ($this->Profile->MemberID)
			      ? DataObject::get_by_id('Member', $this->Profile->MemberID )
			      : new Member(); // a dummy
   return $this->Profile;
}

function WatchList () {
      if ( Permission::check('QUERY_WATCHER') ) {
	    $this->canWatch=true;
	    $this->Watches = $this->ProfileData()->TwitterQueries();
      } else {
	 $this -> Watches = false;
      }
   return $this->Watches;
}
} // end class
?>
