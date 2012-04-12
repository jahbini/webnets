<?php
/* vi:sts=3:sw=3:ts=3:filetype=php:
 */
//class OrganizerNames extends ModelAdmin {
//   
//  public static $managed_models = array(
//      'Organizer'
//   );
// 
//  static $url_segment = 'Organizers'; // will be linked as /admin/products
//  static $menu_title = 'edit Organizers';
// 
//}

class Organizer extends UsersPenName {
	// a birds of a feather collector
	static $db = array ('GeoLocation'=>'Varchar','Salutation' => 'Varchar','Interaction'=> 'Boolean', 'TwitterName' => 'Varchar' , 'FaceBookName' => 'Varchar');
	static $has_many = array('Contests' => 'Contest');
	static $belongs_to = array('SubDomain'=> 'SubDomain');

	static $indexes = array ('GeoLocation' => true);
	
	protected static $Organizer_location = "";
  function getCMSFields(){              
	 $fields = parent::getCMSFields();
	 return formUtility::removeFields($fields,array('Interaction'));
	}
	static function setOrganizerLocation($location) {
		if(strlen($location) == 2) {
			//$location = 'lang:' . $location;
			// ToDo set the i18n system for an alternate language!!!
		}
		Organizer::$Organizer_location = $location;
	}

   /*
    * Get or Create a Mode by the intended Use: LoggedIn, Attract or
    * whatever.  The mode is used to display the proper set of 
    * panes on the display for the Organizer of the site
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


	protected static $m = false;
	static function getOrganizer(){
		global $OrganizerName;
		if($OrganizerName) {
			Organizer::$m = DataObject::get_one('Organizer', "screen_name='$OrganizerName'" );
		}
		if(!Organizer::$m) {
			$m = DataObject::get_one('Organizer', "`GeoLocation` LIKE'%|" . Organizer::$Organizer_location . "|%'" );
			if (!$m) $m = DataObject::get_one('Organizer', "`GeoLocation` LIKE'%|lang:en|%'" );
			Organizer::$m = $m;
		}
		return Organizer::$m;
	}

	static $required=false;

	function requireDefaultRecords() {
		error_log("Require Organizer Records");
		if(self::$required ) return;
		self::$required=true;	
		/* insure that jahbini has his profile */
		$p = new Profile();
		$p->requireDefaultRecords();

	$OrganizerDef = array (
	'ModernMarianas' => array( 'Salutation' => 'Islander!', 'Location'=>'Saipan,Tinian,Rota','Interaction' => true
	    ,'ProfileName' => 'jahbini'
		,'modes' => <<<JSON
[

{ClassName:"Mode", Use:"Attract",PenNameID: #PID#, Panes:[
	{ClassName: "Pane", userKey: "Places", width: 3, 
		Queries: [
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "CNMI Buzz", keywords: "Saipan,Rota,Tinian,Article12", location:"spn" },
		{ClassName: "FriendsRelayQuery",query:"#Organizer#",authority:"Organizer", Title: "Friends of #Organizer#"},
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "Saipan Places", keywords: "laulau,laulau beach",negativeWords:"BA,london", location:"spn" },
		{ClassName: "SearchRelayQuery",Mobi:true, Title: "OutOfTown", keywords:"San Vicente",negativeWords:"BA,london", location:"spn" }
	]}
	]},

{ClassName:"Mode", Use:"AttendingClub",PenNameID: #PID#, Panes:[
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
		 * ClassName: "FriendsRelayQuery",query:"#Organizer#",authority:"Organizer", Title: "Friends of #Organizer#"
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
		$OrganizerID=array();
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
		foreach ($OrganizerDef as $name => $values) {
			error_log("creating records for $name");
			$b = TweetUser::getTweetUser($name);
			if($b instanceOf Organizer ) continue;  //been here done that
			error_log("Continuing to create records for $name");

			$be= $b->newClassInstance('Organizer');
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
