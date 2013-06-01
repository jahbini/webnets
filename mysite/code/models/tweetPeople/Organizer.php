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
	static $db = array ('GeoLocation'=>'Varchar', 'FaceBookName' => 'Varchar');
	static $has_many = array('Contests' => 'Contest');
	static $belongs_to = array('SubDomain'=> 'SubDomain');

	static $indexes = array ('GeoLocation' => true);
	
	protected static $Organizer_location = "";
  function getCMSFields(){              
	 $fields = parent::getCMSFields();
	 return $fields;
	}

	static function setOrganizerLocation($location) {
		if(strlen($location) == 2) {
			//$location = 'lang:' . $location;
			// ToDo set the i18n system for an alternate language!!!
		}
		Organizer::$Organizer_location = $location;
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
	 DB::alteration_message("Organizers created?","created");
		if(self::$required ) {
	 DB::alteration_message("Organizers already created - - early return","created");
			return;
	}
		self::$required=true;	
		/* insure that jahbini has his profile */
		$p = singleton('Profile');
		$p->requireDefaultRecords();

	$OrganizerDef = array (
	'ModernMarianas' => array( 'Location'=>'Saipan,Tinian,Rota')
	);


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
			$Organizer = TweetUser::getTweetUser($name);
			if($Organizer instanceOf Organizer ) continue;  //been here done that

			$be= $Organizer->newClassInstance('Organizer'); //promote to Organizer
			$Organizer ->destroy();
			$Organizer = $be;

			$values['GeoLocation'] = '|' . join('|', explode(',',$values['Location']) ).'|' ;

			// put the values into the DB too.
			$Organizer->castedUpdate($values);

			$Organizer->write();
			Debug::show($Organizer);
	 DB::alteration_message("Organizer $name created","created");
			//PaneDef::JsonToObject(str_replace(array('#PID#','#SN#','#MTRID#'),array($this->ID, $name,$this->ID), $values['LoggedIn']),$Organizer->LoggedIn());
			//PaneDef::JsonToObject(str_replace(array('#PID#','#SN#','#MTRID#'),array($this->ID, $name,$this->ID), $values['Attract']),$Organizer->Attract());
			//Debug::show($Organizer);
			//$Organizer->write();
		}

	}
}
