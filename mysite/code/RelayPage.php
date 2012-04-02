<?php
// :vim:sw=3:sts=3:ft=php:    

class RelayPage extends Page {
   static $db = array(
   );
   static $has_one = array(
   );

   static $defaults = array(
	   "ShowInMenus" => 0,
	   "ShowInSearch" => 0
   );

   static $required=false;
   function requireDefaultRecords() {
	   if(self::$required) return;
	   self::$required=true;
	   parent::requireDefaultRecords();
	   $home = DataObject::get_one("SiteTree", "URLSegment='relay'");
	   if ($home instanceOF RelayPage) return;
	   if ($home instanceOF Page ) {
		   $home = $home -> newClassInstance('RelayPage');
		   $home -> write();
	   }
	   if (!$home) {
		   $home = new RelayPage();
	   }
	   $home ->Title = "Relay for XXXYYYY";
	   $home ->Content = "";
	   $home->URLSegment = "relay";
	   $home->Status="Published";
	   $home->write();
	   $home->publish("Stage","Live");
	   $home->flushCache();
	   DB::alteration_message("Relay Page installed");
   }

}

class RelayPage_Controller extends Page_Controller {
	var $profile = false;
	var $profileData = false;
	var $alsoP = false;
	// var $highStatus = 00.0;
	var $since_id = false;

   function init() {
      parent::init();
   }
	/*
	 * Handle Ajax requests to proxy twitter queries from a browser
	 * the request will be for a twitter API as defined in a RelaySearch Class object
	 * we need to get the request from twitter (or from cache)
	 * @todo get the query from local data base, if possible
	 * @param request an HTTPRequest object, defining the actual request
	 */
   function index($request) {
	   global $consumer_key;
	   global $consumer_secret;
	   ContentNegotiator::disable();
	   $this->getResponse()->addHeader('Content-Type', 'application/json; charset="utf-8"');
	   $v = $request->getVars();
	   unset($v['url']);
	   if(isset($v['since_id'] ) ) $this -> since_id = $v['since_id'];
	   $callback = $v['callback'];
	   // remove the callback entirely
	   $v['callback']='jsonp12345654321';
	   unset($v['callback']);
	   $v['count']=199;

	   $RelayQueryID = $request->param('ID');
	   $relayQuery = DataObject::get_by_id('RelayQuery',$RelayQueryID);
	   if ( !$relayQuery) {
		   return('[{error:"no query"}]');
	   }
	   if($this->mentee) $relayQuery->setMentee($this->mentee);
	   if($this->mentor) $relayQuery->setMentor($this->mentor);
	   $p = DataObject::get_by_id('PenName', $relayQuery->PenNameID);

	   $rQn = new rqNurse($relayQuery);
	   if($this->mentor) $rQn->setMentor($this->mentor);
	   if($this->mentee) $rQn->setMentee($this->mentee);
	   $wait=$rQn->preSchedule();
	   $requestString = $relayQuery->requestString();

	   $content = $rQn->get_from_cache();
	   if ($content || $wait) {
		   // cache access was successful 90 seconds old is still OK
	      return $callback . '({fromCache:'.($content?'true':'false' ).', since_id:' . ($this->since_id?$this->since_id:'false')
		      . ',request: "' .$requestString . '", wait:' . $wait
		      . ',content:' .($content?$content:'[]') . '});';
	   }

	   $content = $rQn ->go_to_twitter($v);

	   $decoded = json_decode($content);

	   $new_content = array();

	$o=error_reporting(0 );
	$new_content = $relayQuery-> clean_all($decoded);
	error_reporting($o);
	   // we may wish to keep track of highStatus if ($this->highStatus != 0 ) $this->since_id = $this->highStatus;
	   $wait = $rQn-> postSchedule();
	   $rQn->save_to_cache($content);

	   $rv = $callback . '({fromCache:false, since_id:' 
		   .  ($this->since_id?$this->since_id:'false') 
		   . ', request: "' . $requestString . '", wait:'
		   . $wait . ',content:' .$content . '});';
	   return( $rv);
   }
}
