<?php
class SubDomain extends DataObject {

	public static $db = array( 'Title' => 'Varchar'
		, 'HeadLine' => 'Varchar'
		, 'Slogan'=>'Varchar'
		, 'Content' =>'HTMLText'
	);

	public static $has_one = array( 'Organizer' => 'Organizer'
	);
        static $required=false;
	        
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

		 $all = niceData::getOne('SubDomain','Title','all');
		 if ($all) return;
		 $all = new SubDomain();
		 $all -> Title = "all";
		 $all -> HeadLine = "Modern Marianas";
		 $all -> Slogan = "Change me now!";
		$o = niceData::getOne('Organizer','screen_name','ModernMarianas');
		 $all->OrganizerID = $o->ID;
		 $all ->write();
		return;
	}

}
