<?php
/* vi:sts=3:sw=3:ts=3:filetype=php:
 */
class MentorNames extends ModelAdmin {
   
  public static $managed_models = array(
      'Mentor'
   );
 
  static $url_segment = 'Mentors'; // will be linked as /admin/products
  static $menu_title = 'edit Mentors';
 
}

class Mentor extends UsersPenName {
	// a birds of a feather collector
	static $db = array ('GeoLocation'=>'Varchar','Salutation' => 'Varchar','Interaction'=> 'Boolean' );
	static $has_many = array('Contests' => 'Contest');

	static $indexes = array ('GeoLocation' => true);
	
	protected static $mentor_location = "";
  function getCMSFields(){              
	 $fields = parent::getCMSFields();
	 return formUtility::removeFields($fields,array('Interaction'));
	}
	static function setMentorLocation($location) {
		if(strlen($location) == 2) {
			//$location = 'lang:' . $location;
			// ToDo set the i18n system for an alternate language!!!
		}
		Mentor::$mentor_location = $location;
	}

	protected static $m = false;
	static function getMentor(){
		global $MentorName;
		if($MentorName) {
			Mentor::$m = DataObject::get_one('Mentor', "screen_name='$MentorName'" );
		}
		if(!Mentor::$m) {
			$m = DataObject::get_one('Mentor', "`GeoLocation` LIKE'%|" . Mentor::$mentor_location . "|%'" );
			if (!$m) $m = DataObject::get_one('Mentor', "`GeoLocation` LIKE'%|lang:en|%'" );
			Mentor::$m = $m;
		}
		return Mentor::$m;
	}

	static $required=false;

	function requireDefaultRecords() {
		if(self::$required ) return;
		self::$required=true;	

	$mentorDef = array (
		'Moms_tweeparty' => array ('Salutation' => 'socialite', 'Location' => 'en','Interaction' => true
			,'modes' => <<<JSON
[
{ClassName:"Mode", Use:"Attract",ProfileID: #PID#, Panes:
 [{RefName: "Notables" ,ClassName: "Pane", userKey: "Notables", width: 3, 
	Queries: {ClassName: "FriendsRelayQuery",query:"Arleen", authority:"query", Title: "Friends of Arleen" }},
    {ClassName: "Pane", userKey: "Quotables", width: 4,
	Queries: {ClassName: "FriendsRelayQuery",query:"#mentor#",authority:"mentor", Title: "Friends of #mentor#" }},
    {RefName: "VIP", ClassName: "Pane", userKey: "VIPs", width: 5,
	Queries: {RefName:"beelzebub_friends",ClassName: "FriendsRelayQuery", query: "be_elzebub", authority:"query",  Title: "Friends of Be_elzebub" }}]
       	},
{ClassName:"Mode", Use:"mentored",ProfileID: #PID#, Panes:
	 [{RefName: "Friends-mentee", ClassName: "Pane", userKey: "Friends of #mentee#", width: 2, 
	Queries: [ {ClassName: "FriendsRelayQuery",query:"#mentee#", authority:"mentee", Title: "Friends of #mentee#" },
		   {ClassName: "FollowersRelayQuery",query:"#mentee#", authority:"mentee", Title: "Followers of #mentee#" }]},
    {ClassName: "Pane", userKey: "Supporters of WeAllTwee", width: 4,
	Queries: [{ClassName: "FriendsRelayQuery",query:"#SN#", Title: "Friends of #SN#" },
		{ClassName: "UserRelayQuery",authority:"mentor", Title: "messages from #SN#" }
]},
    {ClassName: "Pane", userKey: "Your messages", width: 5,
	Queries: [
		   {ClassName: "FollowersRelayQuery",query:"#mentee#", authority:"mentee", Title: "Followers of #mentee#" }]}

]}
 
]
JSON
		) ,

			'governot' => array ('Salutation' => 'anarchist' , 'Location' => 'en', 'Interaction' => true
			,'modes' => <<<JSON
[
{ClassName:"Mode", Use: "Attract", ProfileID: #PID#, Panes:
[
 { ClassName: "Pane", userKey: "Politicos and Pundits", width: 5,
Queries: {ClassName: "FriendsRelayQuery", query:"governot", authority:"query", Title: "Friends of governot" } 
},
 { ClassName: "Pane", userKey: "The word on the stweet", width: 5,
Queries: {ClassName: "SearchRelayQuery",Mobi:false, Title: "economy", keywords: "unemployment,repossess,foreclose,bank failure", location:"" },
	{ClassName: "SearchRelayQuery",Mobi:false, Title: "bogus", keywords: "palin,limbaugh,nazi,olbermann,huffington", location:"" },
}

]
}
]
JSON
	),
	'hotinhnl' => array( 'Salutation' => 'kamaaina', 'Location'=>'hnl,honolulu,oahu','Interaction' => true
		,'modes' => <<<JSON
[

{ClassName:"Mode", Use:"Attract",ProfileID: #PID#, Panes:[
	{ClassName: "Pane", userKey: "Places", width: 4, 
		Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Downtown Honolulu Places", keywords: "aloha tower,ala moana,bishop square,first friday", location:"hnl" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Waikiki Places", keywords: "waikiki",negativeWords:"BA,london", location:"hnl" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "OutOfTown", keywords:"kaneohe,hawaii kai,haleiwa,ewa",negativeWords:"BA,london", location:"hnl" }
	]},
    {ClassName: "Pane", userKey: "Hot Spots", width: 4,
    	Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "watering holes", keywords: "bar35,pipeline cafe,the loft,39 hotel", location:"hnl" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "restaraunts", keywords: "luau,indigos,great food,best food", location:"hnl" }
	]},
    {ClassName: "Pane", userKey: "VIPs", width: 5,
    	Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "parks", keywords: "botanical,kapiolani park,alamoana park,ala moana park", location:"hnl" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "surf", keywords: "sunset beach,waikiki beach,queens beach,waimea falls,turtle bay,makaha beach", location:"hnl" }
	]}
	]},

{ClassName:"Mode", Use:"mentored",ProfileID: #PID#, Panes:[
	{ClassName: "Pane", userKey: "Places", width: 3, 
		Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Downtown Honolulu Places", keywords: "aloha tower,ala moana,bishop square,first friday", location:"hnl" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Waikiki Places", keywords: "waikiki",negativeWords:"BA,london", location:"hnl" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "OutOfTown", keywords:"kaneohe,hawaii kai,haleiwa,ewa",negativeWords:"BA,london", location:"hnl" }
	]},
    {ClassName: "Pane", userKey: "Hot Spots", width: 4,
    	Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "watering holes", keywords: "bar35,pipeline cafe,the loft,39 hotel", location:"hnl" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "restaraunts", keywords: "luau,indigos,great food,best food", location:"hnl" }
	]},
    {RefName: "Your-mentee", ClassName: "Pane", userKey: "messages of #mentee#", width: 3,
	Queries: [{RefName:"beelzebub_friends",Mobi:true,ClassName: "FriendsRelayQuery", query: "be_elzebub",  Title: "Friends of Be_elzebub" },
		   {ClassName: "FollowersRelayQuery",query:"#mentee#", authority:"mentee", Title: "Followers of #mentee#" }]},
    {RefName: "Parks", ClassName: "Pane", userKey: "Parks & Surf", width: 3,
    	Queries: [
		{ClassName: "SearchRelayQuery", Title: "parks", keywords: "botanical,kapiolani park,alamoana park,ala moana park", location:"hnl" },
		{ClassName: "SearchRelayQuery", Title: "surf", keywords: "sunset beach,waikiki beach,queens beach,waimea falls,turtle bay,makaha beach", location:"hnl" }
	]}
]
	}


]
JSON
	) ,
	'hotinsfo' => array( 'Salutation' => 'friend', 'Location'=>'sfo,sanfrancisco,frisco','Interaction' => false
		,'modes' => <<<JSON
{ClassName:"Mode", Use:"Attract",ProfileID: #PID#, Panes:
	[{ClassName: "Pane", userKey: "Night Spots", width: 3, 
	Queries:
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Night Spots", keywords: "111 minna,the knockout,vesuvios", location:"sfo" }
},
    {ClassName: "Pane", userKey: "Places!", width: 3,
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "places", keywords: "delores park,embarcadero,mission district,haight", location:"sfo" }
},
    {ClassName: "Pane", userKey: "Grab-Bag", width: 3,
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "more places", keywords: "deyoung museum,moma,metrion", location:"sfo" }
},
    { ClassName: "Pane", userKey: "Restaraunts", width: 3,
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "restaraunts", keywords: "dosa,tartine bakery,bi-rite creamery,french laundry", location:"sfo" }
}
]
       	}
JSON
       	) ,
	'hotindfw' => array( 'Salutation' => 'partner', 'Location'=>'dfw,dallas,fortworth','Interaction' => true
		,'modes' => <<<JSON
{ClassName:"Mode", Use:"Attract",ProfileID: #PID#, Panes:
   [{RefName: "Notables" ,ClassName: "Pane", userKey: "Notables", width: 3, 
	Queries: {ClassName: "FriendsRelayQuery",query:"Arleen", Title: "Friends of Arleen" }},
    {ClassName: "Pane", userKey: "Quotables", width: 4,
    	Queries: {ClassName: "FriendsRelayQuery",query:"#SN#", Title: "Friends of #SN#" }},
    {RefName: "VIP", ClassName: "Pane", userKey: "VIPs", width: 5,
	Queries: {RefName:"beelzebub_friends",ClassName: "FriendsRelayQuery", query: "be_elzebub",  Title: "Friends of Be_elzebub" }}]
       	}
JSON
       	) ,
	);


		$memberID=array();
		$profileID=array();
		$mentorID=array();
		//include_once("utility/PaneDef.php");
		/*
		 * Mentee is no longer needed as a real tweetuser
		 *
		$mentee = DataObject::get_one( 'PenName', "`screen_name`= '#mentee#'");
		if (!$mentee) {
			$mentee = new PenName(array('screen_name' => '#mentee#') );
			$mentee -> write();
		}
		 */
		foreach ($mentorDef as $name => $values) {
			$b = TweetUser::getTweetUser($name);
			if($b instanceOf Mentor) continue;  //been here done that

			$be= $b->newClassInstance('Mentor');
			$b ->destroy();
			$b = $be;

			$member = DataObject::get_one('Member', "`FirstName`='" . $name . "'");
			if (!$member instanceOF Member){
				$member = Object::create('Member');
				$member -> email = $member -> FirstName = $member->Surname = $name;
				$member -> Password = 'N0t'.$name;
				$member -> write();
			}

			$profile = DataObject::get_one('Profile', "`Name`='" . $name . "'");
			if(!$profile) {
				$profile = new Profile();
				$profile -> Name = $name;
				$profile -> MemberID = $member -> ID;
				$profile -> allow = 'OK';
				$profile ->write();
			}
			$values['GeoLocation'] = '|' . join('|', explode(',',$values['Location']) ).'|' ;

			// put the values into the DB too.
			$b->castedUpdate($values);

			$b->ProfileID = $profile->ID;
			$b->write();
			PaneDef::JsonToObject(str_replace(array('#PID#','#SN#','#MTRID#'),array($b->ProfileID, $name,$this->ID), $values['modes']),$profile->Modes());
		}
	}
}
?>
