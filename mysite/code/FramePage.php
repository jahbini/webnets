<?php
// vim:sw=3:sts=3:ft=php:    

class FramePage extends SiteTree {
   static $db = array(
   );
   static $has_one = array(
   );

   static $required=false;
   function requireDefaultRecords() {
	   if(self::$required) return;
	   self::$required=true;
	   parent::requireDefaultRecords();
	   $frameHome = DataObject::get_one("SiteTree", "URLSegment='iFrame'");
	   if ($frameHome instanceOF FramePage) return;
	   if ($frameHome instanceOF Page ) {
		   $frameHome = $frameHome -> newClassInstance('FramePage');
		   $frameHome -> write();
	   }
	   if (!$frameHome) {
		   $frameHome = new FramePage();
	   }
	   $frameHome ->Title = "iFrame";
	   $frameHome ->MetaTitle = "";
	   $frameHome ->MetaKeywords = "twitter,social networking,gossip,talk to frineds";
	   $frameHome->URLSegment = "iFrame";
	   $frameHome->Status="Published";
	   $frameHome -> ShowInSearch = false;
	   $frameHome -> ShowInMenus = false;
	   $frameHome->write();
	   $frameHome->publish("Stage","Live");
	   $frameHome->flushCache();

	   DB::alteration_message("iFrame Page installed");  
   }

}

class FramePage_Controller extends GraphPage_Controller {

	function Queries () {
	      $currentMode = 'Attract';
	      $mode = $this->Organizer->getModeByUse($currentMode);
	      //Debug::show($mode);
	      $p = $mode -> Panes("",'"userKey" DESC');
		return $this->showPanes($p);
	}

	function index() {
	      Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
	      Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::css('mysite/css/typography.css');
		   Requirements::javascript('mysite/javascript/tools.tabs-1.0.1.js');
		      View::wrapJava('$("ul.tabs").tabs("div.panes > div");');
		      Requirements::css('mysite/css/tabs.css');
		      Requirements::css('mysite/css/tweets.css');
		$data=array();

		return $this->customise($data)->renderWith(array('FramePage'));
	}
}
