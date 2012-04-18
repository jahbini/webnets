<?php
class SubDomain extends DataObject {

	public static $db = array( 'Title' => 'Varchar'
		, 'HeadLine' => 'Varchar'
		, 'Slogan'=>'Varchar'
		, 'Content' =>'HTMLText'
		, 'ConsumerKey' => 'Varchar(50)'
		, 'ConsumerSecret' => 'Varchar(50)'
	);

	public static $has_one = array( 'Organizer' => 'Organizer'
	);
        static $required=false;
	        
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
        function requireDefaultRecords() {
		 error_log("Require SubDomain Records");
	         if(self::$required ) return;
	         self::$required=true;
		 /* insure that ModernMarianas has been created */
		 $m= new Organizer();
		 $m-> requireDefaultRecords();

		 $all = NiceData::getOne('SubDomain','Title','all');
		 if ($all) return;
		 $all = new SubDomain();
		 $all -> Title = "all";
		 $all -> HeadLine = "Modern Marianas";
		 $all -> Slogan = "Change me now!";
		$o = NiceData::getOne('Organizer','screen_name','ModernMarianas');
		 $all->OrganizerID = $o->ID;
		 $all ->write();
		return;
	}

}
