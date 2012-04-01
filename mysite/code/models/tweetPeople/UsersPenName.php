<?php
// vim:ts=3:sw=3:sts=3:ft=php:

class UsersPenNameMaintenance {
	function maintain(){
		$p = DataObject::get('UsersPenName');
		foreach ($p as $pn ) {
			ToDo::insureToDoItem('InternalToDo', $pn, 'maintainMe');
		}
		return $this;
	}
}

//class UserPenAdmin extends ModelAdmin {
//   
//  public static $managed_models = array(
//      'UserPenName'
//   );
// 
//  static $url_segment = 'upen'; // will be linked as /admin/products
//  static $menu_title = 'edit UsersPenName';
// 
//}


class UsersPenName extends PenName {
	static $db = array ( 'FriendsAsOf' => 'Date', 'FollowersAsOf' => 'Date' ,'TheCut' => 'Float','AutoBuild' => 'Boolean' );

	static $many_many = array( 'Associates' => 'TweetUser',
		'Orcas' => 'TweetUser');

	static $many_many_extraFields = array('Associates'=> array('FriendshipExtendedOn' => 'Date', 'BFF' => 'Boolean',
	       	'IFollowDate' => 'Date', 'TheyFollowDate' => 'Date')); 

  function getCMSFields(){              
	 $fields = parent::getCMSFields();
	 return formUtility::removeFields($fields,array('FriendsAsOf','FollowersAsOf','TheCut','AutoBuild'));
	}

	//var $Users;
	
	static $required=false;
	function requireDefaultRecords() {
		if(self::$required ) return;
		self::$required=true;	
		parent::requireDefaultRecords();
		ToDo::insureToDoItem('InternalToDo', 'UsersPenName', 'maintain');
	}

	// this is activated automatically by the background "whenever"
	// do any maintenance needed on friends or family
	function maintain($p = false){
		$f = new UsersPenNameMaintenance();
		$f -> ToDoAccess = $this->ToDoAccess;
		return $f->maintain($p);
	}
	function maintainMe($p = false){
		if (!$this->screen_name) {
			$this ->delete();
			return false;
		}
		error_log("User Maintenance for " . $this ->screen_name);
		if ( strtotime($this->FriendsAsOf) + 3600*24 < Time() ) {
			$this -> received = false;
			$this -> fillMe(array('screen_name' => $this->screen_name));
			error_log("need to scan for New Friends");
			$this->fillFriends();
			$this->ToDoAccess->execMethod('waitTillFriendsDone');
		}
		if ( strtotime($this->FollowersAsOf) + 3600*24 < Time() ) {
			error_log("need to scan for New Followers");
			$this->fillFollowers();
		}
		return $this;
	}

	function waitTillFriendsDone(){
		$count = $this->ToDoAccess->exists('fillPenWgatheredFriendsFollowers');
		error_log("waitingTillFriendsDone ($count)  for " . $this ->screen_name);
		if ( 0 == $this->ToDoAccess->exists('fillPenWgatheredFriendsFollowers') ) {
			//$this->ToDoAccess->execMethod('waitTillFriendsDone');
		
			error_log("We can finally reject FRIENDS!!" . $this ->screen_name);
			if(!$this->AutoBuild) {
				$this->ToDoAccess->execMethod('maintainMe');
				return $this;
			}
			if ($this->friendsToDis()) return $this;
				else {
					$this->ToDoAccess->execMethod('AddOrcas');
				}
		}
		return $this;
	}
	function AddOrcas() {
		error_log("O R C A S for " . $this -> screen_name);
		if(!$this->AutoBuild) {
			$this->ToDoAccess->execMethod('maintainMe');
		}
		if($this -> followers_count * 1.5 > $this -> friends_count ) {
			
					$this->ToDoAccess->execMethod('maintainMe');
		}
		 $myID = $this->ID;
		$limit = $this->followers_count / 5;
		$limit = (int)$limit;
		if ($limit > 200) $limit = 200;
		$factor = 1.1;
$sql1 =<<<SQL
DELETE FROM `UsersPenName_Orcas` WHERE UsersPenNameID= $myID 
SQL
;
/* this DB query is rejective, in that it will reject ANY Orca that is already being followed by any UserPenName in the Associates DB */
$sql2 =<<<SQL
 INSERT INTO `UsersPenName_Orcas` 
SELECT NULL,$myID, a.`ID` 
FROM  `TweetUser` AS a 
WHERE  `friends_count` * $factor >  `followers_count` 
AND  `friends_count` < $factor *  `followers_count`
AND NOT EXISTS (SELECT * FROM `UsersPenName_Associates` AS b WHERE b.`IFollowDate` IS NOT NULL AND b.`TweetUserID` = a.`ID` ) LIMIT $limit 
SQL
;
		//error_log("O R C A S for " . $sql2);
		try {
		 $r1=DB::query($sql1);
		 $r2=DB::query($sql2);
		} catch (Exception $e) {
			error_log ("it Blew up" . $e ->getMessage()) ;
		}
		//error_log(print_r($r1,1));
		//error_log(print_r($r2,1));

		error_log("Got some new Orca! " .$this->Orcas()->TotalItems() );
		$this->ToDoAccess->execMethod('CheckMyFriends');
		return $this;
	}

	function OKFriends($start=0,$limit=25,$sorting = false){
		if ( !$sorting) $sorting = "followers_count";
		$n = $this->Associates("`IFollowDate` >= '".$this->FriendsAsOf .
			"' AND `TheyFollowDate` IS NOT NULL AND `TheyFollowDate` >= '" .$this->FollowersAsOf ."'"
		       	,"$sorting","","$start,$limit");
		return $n;
	}
	function allAssociates($start=0,$limit=25,$sorting = false){
		if ( !$sorting) $sorting = "followers_count";
		$n = $this->Associates("" ,"$sorting","","$start,$limit");
		return $n;
	}

	function Friends($start=0,$limit=25,$sorting = false){
		if ( !$sorting) $sorting = "followers_count";
		$n = $this->Associates("`IFollowDate` >= '".$this->FriendsAsOf ."'" ,"$sorting","","$start,$limit");
		return $n;
	}


	function Followers($start=0,$limit=25,$sorting = false){
		if ( !$sorting) $sorting = "followers_count";
		$n = $this->Associates("`TheyFollowDate` >= '".$this->FollowersAsOf ."'" ,"$sorting","","$start,$limit");
		return $n;
	}

	function nonFollowers($start=0,$limit=25,$sorting = false){
		if ( !$sorting) $sorting = "followers_count";
		$n = $this->Associates("`IFollowDate`>= '". $this->FriendsAsOf . 
			"' AND (`TheyFollowDate` IS NULL OR `TheyFollowDate` < '" .$this->FollowersAsOf .
			"') AND `FriendshipExtendedOn` < DATE_SUB(NOW(), INTERVAL 1 WEEK)" ,"$sorting, FriendshipExtendedOn ASC","","$start,$limit");
		return $n;
	}

	function friendsToDis() {
			error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		$n = $this->Associates("`IFollowDate` >= '".$this->FriendsAsOf . "' AND `quality_time` < DATE_SUB(NOW(), INTERVAL 2 WEEK)", "","",100);
		if ( $n ) foreach ($n as $user) {
			error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
			ToDo::addToDoItem('InternalToDo', $this, 'checkAndCutOldFriend', array('screen_name' => $user->screen_name ));
		}
		if($this->TheCut < 00.5) $this->TheCut = 5.5;
		$n = $this->Associates("`IFollowDate` >= '" . $this->FriendsAsOf . "' AND `quality_time` BETWEEN DATE_SUB( NOW(), INTERVAL 2 WEEK)  AND DATE_SUB( NOW(), INTERVAL 1  MINUTE) AND `follow_worthy` < " . $this -> TheCut ,"","",5);
			error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		if ($n -> exists() ) foreach( $n as $user) {
			error_log("UNFOLLOWING in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
			$this->unfollow ($user -> screen_name);
		}
		return $n->exists();
	}
//  We override the standard unfollow because we need to use the proper pen-name as authorization
	function unfollow($screen_name) {
		error_log("PenName ". $this->screen_name . " is attempting to unfollow $screen_name");
		$old_friend=& TweetUser::getTweetUser($screen_name);

		$twitter = new SaneRest("http://twitter.com/friendships/destroy.xml");
		$twitter -> Authenticate($this);
		$params = array('screen_name' => $screen_name);
		//$twitter->setQueryString($params);
		$conn = SaneResponse::makeSane($twitter->request('','POST', $params) );
		$response = $conn->analyze_code();
		if ($response && !stristr($conn->getBody(), 'not friends') ) return false;
		error_log(" removing association with " . $screen_name );
			//
		$associateInfo = $this->Associates('`TweetUserID`=' .$old_friend->ID);
		//static $many_many_extraFields = array('Associates'=> array('FriendshipExtendedOn' => 'Date',
				 //'IFollowDate' => 'Date', 'TheyFollowDate' => 'Date'));
		$extraFields=array('FriendshipExtendedOn'=>'FriendshipExtendedOn'
				, 'IFollowDate'=>'IFollowDate', 'TheyFollowDate'=>'TheyFollowDate');
		$extras=array();
		$info= $associateInfo->first();
		PleaseMap::Object2Array($extras,$extraFields,$info);

		$extras['IFollowDate']="" ; // no date, we are not following

		//error_log(print_r($extras,1));
			// we will need to verify that this actually gets to
			// the database (will we need to explicitly write it?)
		$associateInfo->add($old_friend,$extras);

		return true;
	}


	function checkAndCutOldFriend($p) {
		$screen_name = $p['oldFriend'];
		$r = new rateUser();
		$return = $r -> rate ($screen_name);
		if ($return ->error) {
			$this->unfollow ($user -> screen_name);
		}
		if ($p -> quality_factor < $this-> TheCut) {
			$this->unfollow ($user -> screen_name);
		}
		return false; // remove from scheduler
	}

	function addToDesiredFriends($screen_name) {
		//  We add friends in bulk here and actually send the twitter request 
		//  in bursts of 10 with 'CheckMyFriends'
		//error_log("Attempting to add $screen_name to down line ");
		$myWantedFriends=$this->Orcas("`screen_name`='".$screen_name."'");
		if(!$myWantedFriends->exists() ) $myWantedFriends->push(TweetUser::getTweetUser($screen_name,false) ); // get by name and do not fill in
		$myWantedFriends->write(true);
		$this->write();
	}

	function CheckMyFriends($param){
		//  We add friends in groups of 10 here and actually send the twitter request 
		//  the friends are taken from the list of 'Orcas'
		//  and  only put out the request if we are  not following this
		//  tweeter already
		$newFriends = $this->Orcas("","","",10);
		if (! $newFriends->exists() ) return false; // unplug from  the scheduler
		foreach($newFriends as $friend) {
			$sn= $friend->screen_name;
			//error_log("will follow $sn");
			//$friend -> follow ($friend);
			switch (  $this->followEZ($sn) )  {
		// 0 every thing may proceed
				case 0: 
					$newFriends->remove($friend);
					break; // if unsuccessful then we break and try again with this name
				case 1:
		//   never return 1 (that is an end of file and is done elsewhere
				case 2:
		// 2 failed, try again later
					break;
				case 3:
		// 3 failed, do not attempt later
					$newFriends->remove($friend);
					break; // if unsuccessful then we break and try again with this name
				case 4:
		// 4 failed authorization
					$newFriends->remove($friend);
					break; // if unsuccessful then we break and try again with this name
				default:
					break;

			}
		}
               return $this;
	}

	function fillFriends($params = false) {
		// we erase all the old friends and  ask twitter for a new list of friends
		// we want to incorporate the date of friendship with this twitteraccess (penname)
		// That information is currently kept in the user's upline/downline
		// information, but should be moved here
		error_log('fillFriends for '. $this->screen_name);
		
		$service = "http://twitter.com/statuses/friends.json";
		$twitter = new SaneRest($service );
		$twitter -> Authenticate( $this);

		//$this->Users=new DataObjectSet();
		// set the twitter drag date as of today.
		$page = 1;
		if($params) $page = $params['page'];
		while ($page*100 < 100 + $this -> friends_count) {
			$params = array('page'=>$page,'screen_name' => $this->screen_name, 'count'=>100);
			$twitter->setQueryString($params);
			error_log("Page $page of friends of ". $this->screen_name);
			$code = $this -> analyze_users($twitter);
		//	error_log("upline code = $code");
			if ($code == 3) return false;  // Twitter  is not cooperating with us
			if ($code == 2) {
				ToDo::addToDoItem('InternalToDo',$this
					,'fillFriends'
					,array( 'page' => $page));
				return false;
			}
			if ($code ==1) break;  // things are OK
			$page += 1;
		}
		$this->FriendsAsOf = date('Y-m-d');
		$this->write();
		// we also add a request to scan the 'Orcas' (bulk add list)
		// to send out mandatory (must do) friend requests
		ToDo::addToDoItem('InternalToDo', $this, 'CheckMyFriends', array( ));
		return false;
	}

	function followEZ($screen_name,$firstMessage="") {
		error_log("calling  Parent followEZ on $screen_name");
		$response = parent::followEZ($screen_name,$firstMessage);
		if(Permission::check('SOCIALITE') ) return $response ;  // we really should NOT be a socialite from this class, though
		if($response) return $response;  //only continue on response 0 (all OK)
		error_log("returned from Parent followEZ");

		return $this->analyze_one_user($this->twitter);  //and put into the Associates DB
	}


	function fillFollowers ($params=false) {
		error_log('fillFollowers of ' . $this->screen_name);

		//find all followers of this screen name
		$service = "http://twitter.com/statuses/followers.json";
		$twitter = new SaneRest($service );
		$twitter -> Authenticate( $this);
		//$this->Users=new DataObjectSet();
		if($params) $page = $params['page'];
		$page = 1;
		while ($page*100 < 100 + $this -> followers_count) {
			$params = array('page' => $page, 'screen_name' => $this->screen_name, 'count'=>100);
			$twitter->setQueryString($params);
			$code = $this -> analyze_users($twitter, true);
		//	error_log("downline code = $code");
			if ($code ==3 ) return false;  // Twitter  is not cooperating with us
			if ($code == 2) {
				ToDo::addToDoItem('InternalToDo',$this
					,'grabDownLine'
					,array( 'page' => $page));
				return false;
			}
			if ($code ==1) break;  // things are OK
			// code is zero and we can continue with the next page	
			$page += 1;
		}
		$this->FollowersAsOf = date('Y-m-d');
		$this->write();
		return false;
	}

	protected function analyze_one_user($twitter) {
		// return 0,1,2
		// 0 if request is OK,  and we can proceed
		// 1 if we have hit the NORMAL end and things are good
		// 2 failed, try again later
		// we quickly look at the response of our friends or followers
		// request to insure that twitter is still alive and talking 
		// nice to us today
		//
		// We then schedule a scan of  the data at short intervals
		// to put the data into our tables
		$conn = SaneResponse::makeSane($twitter->theResponse());
//error_log('analyze_users headers = ');		
//$headers = $twitter -> returnHeaders();
//error_log(print_r($headers,1));

		$msgs = $twitter->getBody();
		//error_log($jsdata);
		ToDo::addToDoItem('InternalToDo', $this, 'fillPenWgatheredFriendsFollowers', array( 'base' => false,'msgs' => $msgs));
		return 0;
	}


	protected function analyze_users($twitter, $BaseIsLeader = false) {
		// return 0,1,2
		// 0 if request is OK,  and we can proceed
		// 1 if we have hit the NORMAL end and things are good
		// 2 if twitter had a problem
		// we quickly look at the response of our friends or followers
		// request to insure that twitter is still alive and talking 
		// nice to us today
		//
		// We then schedule a scan of  the data at short intervals
		// to put the data into our tables
		$twitter->clearResponse();
		$conn = SaneResponse::makeSane($twitter->request());
//error_log('analyze_users headers = ');		
//$headers = $twitter -> returnHeaders();
//error_log(print_r($headers,1));
		$response =$conn->analyze_code();
		$msgs =$conn->getBody();
		if (stristr($text,'Already foll') || stristr($text, "is already on your") ) $response=0;   // did work
		if ($response) return $response;   // 0 means OK

		//error_log($jsdata);
		ToDo::addToDoItem('InternalToDo', $this, 'fillPenWgatheredFriendsFollowers', array( 'base' => $BaseIsLeader,'msgs' => $msgs));
		return 0;
	}

	function fillIt($data) { return $this->fillPenWgatheredFriendsFollowers($data) ; }
	function fillPenWgatheredFriendsFollowers($data) {
		// data has be retrieved by some prior request and we are just stuffing
		// them into the database.  the data represents the friends (or followers)
		$msgs = $data['msgs'];
		$msgs = json_decode($jsdata);
		print_r($msgs);
		if(! $msgs instanceOf stdClass || !isset($msgs->results) ) return 0;
		$msgs = $msgs->results;
		if(!$msgs instanceOf stdClass || !is_array ($msgs) ) return 0;
		$BaseIsLeader = $data['base'];
		//$theData=print_r($msgs,true);
		//file_put_contents("/tmp/jahdata", $theData);
		//if(!is_array($msgs)) { $msgs = array( $msgs); }
		$count=0;
		$sn=array();
		foreach  ($msgs as $user) {
			print_r($user);
			$u = TweetUser::GetTweetUser($user->screen_name,false); // do not attempt to fill this user, we already got the data
			$utag =& $u->getUserTag();
				// fill in user info
			PleaseMap::Object2Object($u, TweetUser::$show, $user);
			$u->received=true;
			// twitter updates the user with the latest tweet record that so we can sort on it later
			$u->last_tweet = $user->status_created_at;
			$u->write();
			$count += 1;
			//
			// baseisleader tells us if we are populating as friend  or follower
			//
			$associateInfo = $this->Associates('`TweetUserID`=' .$u->ID);
			//static $many_many_extraFields = array('Associates'=> array('FriendshipExtendedOn' => 'Date',
				 //'IFollowDate' => 'Date', 'TheyFollowDate' => 'Date'));
			$extraFields=array('FriendshipExtendedOn'=>'FriendshipExtendedOn'
				, 'IFollowDate'=>'IFollowDate', 'TheyFollowDate'=>'TheyFollowDate');
			$extras=array();
			if ($associateInfo -> exists() ) {
				$info= $associateInfo->first();
				PleaseMap::Object2Array($extras,$extraFields,$info);
			}
			$sn[]=$user->screen_name;
			if ( count($sn) == 10 ) {
				error_log(" names " . join(",",$sn) );
				$sn=array();
			}
			if ($BaseIsLeader ) {
				$extras['TheyFollowDate']=$this->FollowersAsOf;
		//error_log($u -> screen_name . " follows " . $this->screen_name);
			} else {
				if( !isset( $extras['FriendshipExtendedOn']) ||  $extras['FriendshipExtendedOn'] == "" ) 
					$extras['FriendshipExtendedOn']=$this->FriendsAsOf;
				$extras['IFollowDate']=$this->FriendsAsOf;
		//error_log("Ifollow " .$u -> screen_name);
			}
		//error_log(print_r($extras,1));
			// we will need to verify that this actually gets to
			// the database (will we need to explicitly write it?)
			$associateInfo->add($u,$extras);

			if ($user->status_id) {
				$tdata = array("published" => $user->status_created_at,
						"Title" => $user->status_text,
						"StatusID"=> $user->status_id,
						"author_name" => $u->screen_name);
				// we have a tweet from this user!
				// tag the tweet with our new user's tag
				$t=Tweet::getTweet($tdata,$utag);
			
			}
		}
			if ( count($sn) != 0 ) {
				error_log(" names " . join(",",$sn) );
			}
		error_log(" fill Pen with $count");
		//$this->write();
		// signal that we are done to delete this object from the ToDo list
		return false;
	}
}
?>
