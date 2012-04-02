<?php

class Cell176Page extends SiteTree {
	
	public static $db = array(
	);
	
	public static $has_one = array(
	);

   static $required=false;
   function requireDefaultRecords() {
	   if(self::$required) return;
	   self::$required=true;
	   parent::requireDefaultRecords();
	   $home = DataObject::get_one("SiteTree", "URLSegment='cell'");
	   if ($home instanceOF Cell176Page) return;
	   if ($home instanceOF Page ) {
		   $home = $home -> newClassInstance('Cell176Page');
		   $home -> write();
	   }
	   if (!$home) {
		   $home = new Cell176Page();
	   }
	   $home ->Title = "Happenings";
	   $home ->Content = "";
	   $home->URLSegment = "cell";
	   $home->Status="Published";
	   $home->HomepageForDomain='wealltwee.mobi,hnl.wealltwee.mobi,sfo.wealltwee.mobi';
	   $home->write();
	   $home->publish("Stage","Live");
	   $home->flushCache();
	   DB::alteration_message("CellPhone Page installed");
   }
}

class Cell176Page_Controller extends Page_Controller {
	
	public function init() {
		parent::init();
		Requirements::clear();  // no javascript on phones
	}

	function tag($data =false) {
		return false;
	}

	function privateTweets ($data=false) {
		return false;
	}
	function images() {
		return Director::absoluteBaseURL() ."tell140/images";
	}

	function index($data =false) {
		global $Geo;
		global $GeoShort;
		$cat = $this->mentor->Profile()->getModeByUse('Attract');
		$panes = $cat->Panes();
		$iterator = 1;
		foreach($panes as $p) {
		$p->Ord = $iterator;
		$iterator +=1;
		}
		$data = array('Mentor' => $this->mentor, 'GeoShort'=> $GeoShort, 'Geo' => $Geo, 
			'Categories' => $panes,'PageIdentifier'=>$this->profile->Name .' says:') ;
	  	return $this->customise($data)->renderWith(array('Cell176Page','176Page'));
	}

	private function TweetsByRelayQuery($id,$start,$limit) {
		$tag=DataObject::get_by_id('Tag',$id);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		error_log("Tag ID = $id, Tag name=" . $tag->TagText);
		$filter=($this->request->requestVar('filter'));
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
			 // susan boyle will blow the memory so we limit it
		$Tweets = ($tag)?  $Tweets = $tag->myTweets($start,$filter):singleton('ComponentSet');
		$Tweets -> setPageLength(Page_Controller::sessionInfo('limit') );
		if($filter == "") {
			$total_tweets = $Tweets -> TotalItems();
			$tag->NumTweets=$total_tweets;
			$tag->write();
		}
		foreach ($Tweets as $t) {
			//$t->Title = $id;
			$t -> tagID = $id;
			$t -> tagName = $tag -> TagText;
		}
	  	return $Tweets;
	}


	function detail($data) {
		global $Geo;
		global $GeoShort;

		$PaneID = $data->param('ID');
		$pane = DataObject::get_by_id('Pane', $PaneID);

		$cat = $this->mentor->Profile()->getModeByUse('Attract');
		$panes = $cat->Panes();
		$iterator = 1;
		foreach($panes as $p) {
		$p->Ord = $iterator;
		$iterator +=1;
		}


		$visual = $pane->Queries();
		$name=$Geo;
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		$limit=20; // for cell phones
		$vNames = array();
		$RelayQueryTweets = new DataObjectSet();
		foreach ($visual as $v) {
			$vNames[] = $v -> userKey;
			$RelayQueryTweets->merge($this->TweetsByRelayQuery($v->QueryTagID,$start,$limit));
		}
		$RelayQueryTweets->removeDuplicates();
		$RelayQueryTweets->sort('published',"DESC");
		$author = array();
		$limit = 20;
		foreach ($RelayQueryTweets as $v) {
		$v->Ord = $iterator;
		$iterator += 1;
			if ($limit < 1 || 2 < @$author[ $v -> author_name ] ++) { $RelayQueryTweets -> remove($v) ;} 
			$limit -= 1;
		}
		$this->RelayQueryNames = $vNames;

		//$Tweets = DataObject::get('Tweet', "`author_name` ='$name' OR `Title` LIKE '@$name %'","published DESC","","$start,$limit");
		//if ($Tweets)$Tweets -> setPageLength($limit);
		$data = array('Mentor' => $this->mentor, 'GeoShort'=> $GeoShort, 'Geo' => $Geo, 'Categories' => $panes,
		'RelayQueries' => $RelayQueryTweets, 'Names'=>$vNames, 'PageIdentifier' => 'hot id', 'Title' => 'Tweets from ' . $name ) ;
	  	return $this->customise($data)->renderWith(array('176Page'));
	}

	private function grabAndFormatAssociates($data ,$magicRoutine,$labels) {
	  	return false;
	}
	function deadFriends($data=false){
	  	return false;
	}

	function friends($data = false) {
	  	return false;
	}

	function followers($data = false) {
	  	return false;
	}

	function updateFriends($data = false) {
	  	return false;
	}

	function updateFollowers($data = false) {
	  	return false;
	}

	function links() {
	  	return false;
		$link = array('author' => 'Your Tweets','publicTweets' => 'Everybody!', 'privateTweets' => 'what you hear' ,'deadFriends' => 'Unfriendly', 'friends' => 'Those You Follow', 'followers' => 'Your Followers');
		$data = new DataObjectSet();
		foreach ( $link as $key => $value) {
			$data ->push( new ArrayData (array('Link' => $this->RelativeLink($key), 'Title' => $value) ) );
		}
		return $data;
	}
	function publicTweets () {
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		$limit=Page_Controller::sessionInfo('limit');
		$Tweets = DataObject::get('Tweet', "`recipient_screen_name` IS NULL","published DESC","","$start,$limit");
		if ($Tweets)$Tweets -> setPageLength($limit);
		$data = array('Tag'=> 'Public', 'Tweeties' => $Tweets,  'Title' => 'Most Recent Tweets') ;
	  	return $this->customise($data)->renderWith(array('Tag_results', 'Page'));
	}

	function dropTag($data) {
	  	return false;
	}

	function makeClassic($data){
	  	return false;
	}

	function TagList($kind){
	  	return false;
	}
	
}
