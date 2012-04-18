<?php
class TwitterQuery extends DataObject {
	static $db= array ("Title"=>'Varchar(80)'
		,'numberPerHour' => 'Int' // these three fields are for the twitter rate restrictions
		,'NumberLeftThisHour' => 'Int'
		,'HourIsUp' => 'Datetime'
		,'requestString' =>'Varchar(100)' //the stuff after http://twitter.com/
		,'requestKind' => "Enum('proxy,browser','proxy')"  //all requests can be done by proxy, (server) some can be done by browser
		,'authority' => "Enum('none,penName,Organizer,mentee,query','none')"  //used for interpolating the requestString or authority
		,'query'=>'Varchar(100)'
		,'lowestID' => 'Double'
		,'highestID' => 'Double'
		,'TotalTweets' => 'Int'
		,'Mobi' => 'Boolean'
		);
	static $indexes = array ( 'Title'=>true, 'query'=>true );
	static $has_many = array ('Gaps' =>'TweetGap');
	static $belongs_many_many = array('Profiles' => 'Profile');
	static $has_one = array ('QueryTag' =>'Tag','PenName' => 'PenName'); // PenName for authorization

	function onBeforeDelete(){
		$gaps = $this->Gaps();
		foreach($gaps as $g) $g->Delete();
		parent::onBeforeDelete();
	}

	function getPenName(){
		Debug::show("Getting Pen Name in TwitterQuery ID=". $this->PenNameID);
		$p= DataObject::get_by_id('PenName',$this->PenNameID);
		Debug::show($p);
		Debug::show("Getting Pen Name in TwitterQuery ID=". $this->PenNameID);
		return $p;
	}
	function setPenName($p){
		if(is_object($p) ) $this->PenNameID=$p->ID;
		if(is_int($p)) $this->PenNameID=$p;
		return;
	}
	function authorization () {
		switch($this->authority) {
			case "none": return false;
			case "penName": if( $this->penNameID) return $this->penName()->screen_name;
			case "mentee": if ($this->menteeID) return $this->mentee()->screen_name;
			case "Organizer": if ($this->OrganizerID) return $this->Organizer()->screen_name;
Debug::show($this);
Debug::show("No authority!");

				error_log("no valid authorization for TwitterQuery ID=". $this->ID);
				die();
		}
	}

	function proxy() {
		return ($this->requestKind == "Proxy");
	}

// set the StatusMessage handling links
	function setLogging($gap){
		$this->logging = $gap;
	}
	function sayThis($t){
		error_log($t);
		if($this->logging) $this->logging->sayThis($t);
	}

	function __construct() {
		$args =func_get_args() ;
		call_user_func_array('parent::__construct' , $args );
		$this -> range=new TweetRange;
		$this->logging = false;
		$this -> debug=false;
		$this -> queryTag=false;
		return;
	}

	protected function fillQuery ($params,$more) {
		foreach (explode(',,',$params) as $spec) {
			foreach(explode('=',$spec) as $key => $val) $n -> { $key} = $val;
		}
		foreach ($more as $key=>$val) $n -> { $key} = $val;
		$n -> write();
		return $n;
	}


	function lowest() {
		return number_format($this ->lowestID);
	}
	function highest() {
		return number_format($this->highestID);
	}

	function myGaps(){
		return $this->Gaps();
	}

	/**
	 * @return the full http://twitter api (without parameters)
	 */	

	function requestString() {
		// must be overridden
	}

	/**
	 * @return an array of URL parameters ready to be added to a POST or GET
	 * @param param an array to merge new key => value pairs into
	 */
	function requestParams($param = false) {
		if(!$param) $param = array();
		return $param;
	}
	
	/* general clean up function to take raw tweets from API and make then
	 * suitable for the DB or the web client. overridden on a RelayClass by RelayClass basis
	 * @param the (json_decoded) result from a twitter API request
	 */
	public function &clean_all($content) {
		$nw = array();
		if(is_array( $content) ) foreach($content as $d) {
			$t =&  $this->clean_up($d);
			if (!$t) continue;
			$nw[] =& $t;
		}
		return $nw;
	}

	//info structure array( 'penName' => ??, 'service' => 'http://twitter...', 'auth' =>t/f, 'filler'=>'TwitterQuery method', query=>??, );
	static function standardQueryDef($kind, TweetUser $penName,$query=""){
		$sn=$penName->screen_name;
		switch ($kind) {
			case 'mentions':
				return array (
				'penName' => $sn,
				'penNameID' => $penName->ID,
				   "service"=>"http://twitter.com/statuses/mentions",
				   "auth" => "true",
					'filler' => 'fillTweetsFromAPI'
					);
			case 'penFollowers':
				return array (
				'auth'=>true,
				'penNameID' => $penName->ID,
				'penName' => $sn,
				'query' => '',
				'service'=>'http://twitter.com/statuses/followers/'.$sn,
				'filler' => 'fillTweetsFromAPI'
					);
			case 'penDirectSent':  
			case 'penDirect':
				// force a run-time-error
				$x = "wow";
				$x[] = "wow";
				$x = "wow";
				break;
			case 'penFriends':
				$x = "wow";
				$x[] = "wow";
				$x = "wow";
				return array (
				'auth'=>false,
				'penName' => $sn,
				'query' => '',
				'service' => 'http://twitter.com/statuses/friends/'.$sn,
				'filler' => 'fillTweetsFromAPI'
				);
				break;
			case 'sponsorFriends':
				return array (
				'auth'=>false,
				'penName' => $sn,
				'service' => 'http://twitter.com/statuses/friends/'.$Organizer->screen_name,
				'query' => '',
				'filler' => 'fillTweetsFromAPI'
				);
				break;

			case 'penFriendsTimeline':
				$x = "wow";
				$x[] = "wow";
				$x = "wow";
				return array (
				'auth'=>true,
				'penName' => $sn,
				'penNameID' => $penName->ID,
				'service' => 'http://twitter.com/statuses/friends_timeline',
				'query' => '',
				'filler' => 'fillTweetsFromAPI'
				);
				break;
			case 'public':
				return array (
				'auth'=>false,
				'penName' => $sn,
				'service' => 'http://twitter.com/statuses/public_timeline',
				'query' => '',
				'filler' => 'fillTweetsFromAPI'
				);
				break;
			case 'penSent':
				return array (
				'auth'=>false,
				'penName' => $this->Title,
				'service' => "http://twitter.com/statuses/user_timeline/".$sn,
				'query' => '',
				'filler' => 'fillTweetsFromAPI'
				);
				break;
			case 'user':
				return array (
				// this is really tweets 'from' the 'query' field which is the user
				// make sure that the user is in the DataBase
				'penName' => $sn,
				'auth'=>false,
				'service' => "http://twitter.com/statuses/user_timeline/".$sn,
				'filler' => 'fillTweetsFromUser'
				);
				break;
			case 'hash':
			case 'keyword':
			//no rate limiting needed
				return array (
				'penName' => $sn,
				'requestKind' => 'Query',
				'auth'=>false,
				'query' => $query,
				'filler' => 'fillQueriedTweets'
				);
				break;
		}
	}

	function bork($t,$info) {
		$x = $t->ID;
		return;
	}

	function QueryTagOK() {
		if(isset($this->QueryTag) && $this->QueryTag instanceOf Tag ) return $this->QueryTag;
		if ($this->QueryTagID) return $this->QueryTag = $this->QueryTag();
		return false;
	}

	function mysetQueryTag($name,$kind='Tag') {
		$this->QueryTag = Tag::getTagByName($name,$kind);
		$this->QueryTagID = $this->QueryTag->ID;
		$this->forceChange();
		$this->write();
		return $this->QueryTag;
	}

	public function getKeywordQueries(){
		return  new FieldSet(
			new DropdownField("TwitterQueries", "Existing Queries", DataObject::get('TwitterQuery')->column('Title')  )
			);
	}
	function containsRange($range) {
		$this->setField('highestID',$range->getHighestID($this->highestID) );
		$this->setField('lowestID',$range->getLowestID($this->lowestID) );
		//error_log("Setting Lowest ID of " .$this->query ." to " . $this->lowestID);
		//error_log("Setting Highest ID of " .$this->query ." to " . $this->highestID);
		$this->write();
	}
	function setDebug($val=false){
		$this->debug=$val;
	}

	// AddAGap ties the newly created init,top,mid or low gap to it's parent (this)
	function addAGap($g){
		$myGaps=$this->Gaps();
		$myGaps->add($g);
		//error_log("My Parent query ID = ". $this ->ID);
		$g->write(true);
		$this->write(true);
	}

	// insureTopGap insures that a gap we are searching has a real top gap in the todo list
	function insureTopGap(){
		$myGaps=$this->Gaps();
		foreach($myGaps as $g) {
			if ($g->gapKind == 'top')  return ;
		}
		$hi =new TweetGap();
		$hi-> setAsTopGap($this,$this->highestID );
	}

	/* Insures that a gap we are searching has a real low gap in the todo list
	 */
	function insureLowGap($range){
		// the range is from the original tweet request,but we may not log it very well
		$myGaps=$this->Gaps();
		foreach($myGaps as $g) {
			if ($g->gapKind == 'low')  return ;
		}
		$low=new TweetGap();
		$low -> setAsLowerGap($this,$this->lowestID);
	}

	function insureInitialGap(){
		$myGaps=$this->Gaps();
		foreach($myGaps as $g) {
			if ($g->gapKind == 'initial')  return $g ;
		}
		$g=new TweetGap();
		//setAsInitialGap will attempt to grap the tweets from twitter
		//   it is only fired off during the initialization phase
		//   if twitter is over capacity at this moment, the initial
		//   gap will  be queued to run later
		$g -> setAsInitialGap($this);
		return $g;
	}

	function updateGapFromBottom($newBottom) {
		$this->LowestID = $newBottom;
		$this->write();
	}

	static function getTwitterQuery( $key,$mustBe='TwitterQuery' ) {
		$range=false;
		$Title=$key['Title'];
		if (!isset($key['query']) ) $key['query'] = $Title;
		$query = $key['query'];
		//print_r($key); //JAH
		$q = DataObject::get_one($mustBe, "`Title`='". Convert::raw2sql( $Title) ."'");
		if ($q && ($q->lowestID === 0 || $q->highestID === 0) ) {
			$q->delete();
			unset($q);
			}
		if (! $q) {
			$q= new TwitterQuery($key);
			if(isset($key['PenNameID'])) $q-> PenNameID = $key['PenNameID'];
			$q -> write();
			$q->setDebug(false);
			$q->mysetQueryTag();
			$q -> write();
		}
		// the following can take a long time on query creation
		// so it is driven by the polling background system
		if($q->Mobi) $q->insureInitialGap();
		return $q;
	}

	function setMobi($value) {
		// mobi tags get thrown into the tweet acquisition system
		if (!$this->ID) $this->write();
		$this->setField('Mobi', $value);
		if($value) $this->insureInitialGap();
	}

	static function getExistingQuery($id){
		$q=DataObject::get_by_id('TwitterQuery',$id);
		return $q->normalizeQuery();
	}
	function normalizeQuery($range=false) {
		$this->mysetQueryTag();


		$this->insureLowGap($range);
		$this->insureTopGap();
		return $this;
		}
	}
