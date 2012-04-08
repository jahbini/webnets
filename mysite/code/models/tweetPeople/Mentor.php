<?php
/* vi:sts=3:sw=3:ts=3:filetype=php:
 */
//class MentorNames extends ModelAdmin {
//   
//  public static $managed_models = array(
//      'Mentor'
//   );
// 
//  static $url_segment = 'Mentors'; // will be linked as /admin/products
//  static $menu_title = 'edit Mentors';
// 
//}

class Mentor extends UsersPenName {
	// a birds of a feather collector
	static $db = array ('GeoLocation'=>'Varchar','Salutation' => 'Varchar','Interaction'=> 'Boolean', 'TwitterName' => 'Varchar' , 'FaceBookName' => 'Varchar');
	static $has_many = array('Contests' => 'Contest');
	static $belongs_to = array('SubDomain'=> 'SubDomain');

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
		error_log("Require Mentor Records");
		if(self::$required ) return;
		self::$required=true;	
		/* insure that jahbini has his profile */
		$p = new Profile();
		$p->requireDefaultRecords();

	$mentorDef = array (
	'ModernMarianas' => array( 'Salutation' => 'Islander!', 'Location'=>'Saipan,Tinian,Rota','Interaction' => true
	    ,'ProfileName' => 'jahbini'
		,'modes' => <<<JSON
[

{ClassName:"Mode", Use:"Attract",PenNameID: #PID#, Panes:[
	{ClassName: "Pane", userKey: "Places", width: 3, 
		Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "CNMI Buzz", keywords: "Saipan,Rota,Tinian,Article12", location:"spn" },
		{ClassName: "FriendsRelayQuery",query:"#mentor#",authority:"mentor", Title: "Friends of #mentor#"},
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Saipan Places", keywords: "laulau,laulau beach",negativeWords:"BA,london", location:"spn" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "OutOfTown", keywords:"San Vicente",negativeWords:"BA,london", location:"spn" }
	]}
	]},

{ClassName:"Mode", Use:"mentored",PenNameID: #PID#, Panes:[
	{ClassName: "Pane", userKey: "Places", width: 3, 
		Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "CNMI Buzz", keywords: "Saipan,Rota,Tinian,Article12", location:"spn" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Saipan Places", keywords: "laulau,laulau beach",negativeWords:"BA,london", location:"spn" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "OutOfTown", keywords:"San Vicente",negativeWords:"BA,london", location:"spn" }
	]}
] }


]
JSON
	) 
		/* example 
		 * {RefName: "Notables" ,ClassName: "Pane", userKey: "Notables", width: 3, 
		 * -       Queries: {ClassName: "FriendsRelayQuery",query:"Arleen", authority:"query", Title: "Friends of Arleen" }},
		 * -    {ClassName: "Pane", userKey: "Quotables", width: 4,
		 * -       Queries: {
		 * ClassName: "FriendsRelayQuery",query:"#mentor#",authority:"mentor", Title: "Friends of #mentor#"
		 * }},
		 * -    {RefName: "VIP", ClassName: "Pane", userKey: "VIPs", width: 5,
		 * -       Queries: {RefName:"beelzebub_friends",ClassName: "FriendsRelayQuery", query: "be_elzebub", authority:"query",  Title: "Friends of Be_elzebub" }}]
		 * -               },
		,

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
		 */
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
			error_log("creating records for $name");
			$b = TweetUser::getTweetUser($name);
			if($b instanceOf Mentor ) continue;  //been here done that
			error_log("Continuing to create records for $name");

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
			if (!isset($values['ProfileName'])) $values['ProfileName'] = $name;
			$profile = niceData::getOne('Profile','name', $values['ProfileName'] );

			$values['GeoLocation'] = '|' . join('|', explode(',',$values['Location']) ).'|' ;

			// put the values into the DB too.
			$b->castedUpdate($values);

			$b->ProfileID = $profile->ID;
			$b->write();
			PaneDef::JsonToObject(str_replace(array('#PID#','#SN#','#MTRID#'),array($this->ID, $name,$this->ID), $values['modes']),$b->Modes());
		}
	}
}
