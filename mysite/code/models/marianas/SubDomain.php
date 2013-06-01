<?php
class SubDomain extends DataObject {

	public static $db = array( 'Title' => 'Varchar'
		, 'HeadLine' => 'Varchar'
		, 'Slogan'=>'Varchar'
		, 'Content' =>'HTMLText'
		, 'Salutation' => 'Varchar'
		, 'AllowLogin' => 'Boolean'
		, 'ConsumerKey' => 'Varchar(50)'
		, 'ConsumerSecret' => 'Varchar(50)'
	);

	public static $has_one = array( 'Organizer' => 'PenName', 'Profile'=>'Profile'
		,'Attract' => 'Mode', 'LoggedIn' => 'Mode'
	);
        static $required=false;
	        
	function setOrganizer($o) {
		if (! $o instanceOf Organizer ) {
			$o = $o-> newClassInstance('Organizer');
			$o ->write(); // expand to Organizer Class
		}
		$this->OrganizerID = $o->ID;
		return $o;
	}

	static function getTheConsumerSecret(){
		global $WantedSubDomain;
		$domain=NiceData::getOne('SubDomain','Title',$WantedSubDomain);
		return $domain->ConsumerSecret;
	}

	static function getTheConsumerKey(){
		global $WantedSubDomain;
		$domain=NiceData::getOne('SubDomain','Title',$WantedSubDomain);
		return $domain->ConsumerKey;
	}

	function fortemplate(){
		return $this->Title;
	}
	function setProfileName($p){
		 $profile = NiceData::getOne('Profile', 'Name', $p);
		 $this->ProfileID=$profile->ID;
	}
	function setOrganizerName($o){
		$organizer = NiceData::getOne('Organizer','screen_name',$o);
		$this ->OrganizerID=$organizer->ID;
	}

	function linkToSubdomain(){
		/*
		 * linkToSubdomain -- form correct absolute links to this subdomain
		 */
		global $thisDomain;
		return 'http://' . (($this->Title == 'all')?'':($this->Title . '.')) . $thisDomain . '/';
	}

        function requireDefaultRecords() {
		 error_log("Require SubDomain Records");
	         if(self::$required ) return;
	         self::$required=true;
		 /* insure that jahbini has been created */
		 $p= singleton('Profile');
		 $p-> requireDefaultRecords();
		 /* insure that ModernMarianas has been created */
		 $m= singleton('Organizer');
		 $m-> requireDefaultRecords();


	$SubDomainDef = array (
	'all' => array( 'Salutation' => 'Islander!', 'Location'=>'Saipan,Tinian,Rota'
		,'Slogan' => 'Change me now!', 'HeadLine'=>"Modern Marianas"
	    ,'ProfileName' => 'jahbini','OrganizerName'=>'ModernMarianas','AllowLogin' => false
		,'Attract' => <<<JSON
{ClassName:"Mode",  Panes:[
	{ClassName: "Pane", userKey: "Places", width: 3, 
		Queries: [
		{ClassName: "SearchRelayQuery",Mobi:false, Title: "CNMI Buzz", keywords: "Saipan,Rota,Tinian,Article12", location:"spn" },
		{ClassName: "FriendsRelayQuery",query:"#Organizer#",authority:"Organizer", Title: "Friends of #Organizer#"},
		{ClassName: "SearchRelayQuery",Mobi:false, Title: "Saipan Places", keywords: "laulau,laulau beach",negativeWords:"BA,london", location:"spn" },
		{ClassName: "SearchRelayQuery",Mobi:false, Title: "OutOfTown", keywords:"San Vicente",negativeWords:"BA,london", location:"spn" }
	]}
	]}
JSON
		, 'LoggedIn' =><<<JSON
{ClassName:"Mode", Panes:[
	{ClassName: "Pane", userKey: "Places", width: 3, 
		Queries: [
		{ClassName: "SearchRelayQuery",Mobi:false, Title: "CNMI Buzz", keywords: "Saipan,Rota,Tinian,Article12", location:"spn" },
		{ClassName: "SearchRelayQuery",Mobi:false, Title: "Saipan Places", keywords: "laulau,laulau beach",negativeWords:"BA,london", location:"spn" },
		{ClassName: "SearchRelayQuery",Mobi:false, Title: "OutOfTown", keywords:"San Vicente",negativeWords:"BA,london", location:"spn" }
	]}
] }

JSON
	) 
	);

		foreach ($SubDomainDef as $name => $values) {
			error_log("creating records for SubDomain <$name>");
			 $subDomain = NiceData::getOne('SubDomain','Title',$name);

			if($subDomain) {
				if($subDomain instanceOf SubDomain ) continue;  //been here done that
				$be= $subDomain->newClassInstance('SubDomain');
				$subDomain ->destroy();
				$subDomain = $be;
			} else {
				$subDomain=new SubDomain();
				$subDomain->Title= $name;
			}
			error_log("Continuing to create records for $name");

			// put the values into the DB too.
			$subDomain->castedUpdate($values);

			$subDomain->write();
			$subDomain->LoggedInID = PaneDef::JsonToObject(str_replace(array('#PID#','#SN#','#MTRID#'),array($this->ID, $name,$this->ID), $values['LoggedIn']))->ID;
			$subDomain->AttractID = PaneDef::JsonToObject(str_replace(array('#PID#','#SN#','#MTRID#'),array($this->ID, $name,$this->ID), $values['Attract']))->ID;
			$subDomain->write();
		}


		return;
	}

}
