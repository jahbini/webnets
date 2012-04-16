<?php

class MainPage extends SiteTree {
	
	public static $db = array(
	);
	
	public static $has_one = array(
	);
	
}

class MainPage_Controller extends Page_Controller {
	
	public function init() {
		parent::init();
		if( $this->not_ajax ){
			Tag::RequireTagDrop();
		}
	}

	function tag($data =false) {
		if (!$data) return false;
		if (is_string($data) ) $name = $data;
			else $name = $data->param('ID');
		$tag=& Tag::getTagByName($name);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		$filter=($this->request->requestVar('filter'));
		ERROR_LOGGER("filter is <$filter>");
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
			 // susan boyle will blow the memory so we limit it
		$Tweets = ($tag)?  $Tweets = $tag->myTweets($start,$filter):singleton('ComponentSet');
		$Tweets = new PaginatedList( $Tweets);
		$Tweets -> setPageLength( Page_Controller::sessionInfo('limit'));
		if($filter == "") {
			$total_tweets = $Tweets -> TotalItems();
			$tag->NumTweets=$total_tweets;
			$tag->write();
		}
		$data = array('Tag'=> $tag, 'Tweeties' => $Tweets,  'Title' => 'Tags of a ' . $name . ' kind') ;
	  	return $this->customise($data)->renderWith(array('Tag_results', 'Page'));
	}

	function privateTweets ($data=false) {
		$limit=Page_Controller::sessionInfo('limit');
		$pen= PenName::getSessionPenName();
		$ps = $pen->screen_name;
		$tag =& Tag::getTagByName("Private_$ps");
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		$t = DataObject::get('Tweet',"`recipient_screen_name`='$ps' OR (`author_name`='$ps' AND `recipient_screen_name` IS NOT NULL)",'`published` DESC',"","$start,$limit");
		$t = new PaginatedList($t);
		if($t) $t -> setPageLength($limit);
		$data = array('Tag'=> $tag, 'Tweeties' => $t,  'Title' => 'private') ;
	  	return $this->customise($data)->renderWith(array('Tag_results', 'Page'));
	}

	function index($data =false) {
		ERROR_LOGGER("here at index of Page.php");
		if (is_string($data) ) $name = $data;
			else $name = $data->param('ID');
		$tag=& Tag::getTagByName($name);
		if (!$tag) return $this->publicTweets();
		$filter=($this->request->requestVar('filter'));
		ERROR_LOGGER("filter is <$filter>");
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		if($tag){
			$Tweets = $tag->myTweets($start,$filter);
			$tagName = $tag -> forTemplate();
		} else {
			$Tweets = singleton('ComponentSet');
			$tagName = "unknown";
		}
		$limit=Page_Controller::sessionInfo('limit');
		$Tweets = new PaginatedList($Tweets);
		$Tweets -> setPageLength($limit);
		$data = array('Tag'=> $tag, 'Tweeties' => $Tweets,  'Title' => 'Tags of a $tagName kind') ;
	  	return $this->customise($data)->renderWith(array('Tag_results', 'Page'));
	}

	function author($data = false) {
		if (!$data ) $name = 'jahbini';
			else $name = $data->param('ID');
		if(!$name) {
			$pen= PenName::getSessionPenName();
			$name=$pen->screen_name;
		}
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		$limit=Page_Controller::sessionInfo('limit');
		$Tweets = DataObject::get('Tweet', "`author_name` ='$name' OR `Title` LIKE '@$name %'","published DESC","","$start,$limit");
		if ($Tweets){
			$Tweets = new PaginatedList($Tweets);
			$Tweets -> setPageLength($limit);
		}
		$data = array('Tag'=> '@'.$name, 'Tweeties' => $Tweets,  'Title' => 'Tweets from ' . $name ) ;
	  	return $this->customise($data)->renderWith(array('Tag_results', 'Page'));
	}

	private function grabAndFormatAssociates($data ,$magicRoutine,$labels) {

		$pen = PenName::getSessionPenName($data?$this->request->requestVar('penName'):false);

		ERROR_LOGGER("Getting the accessor for Pen Name  = " . $pen->screen_name);
		$pen -> fillFriends( );
		$pen -> write();

		ERROR_LOGGER("Grabbing Friends");
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		$sorting = ($this->request->requestVar('sorting')) ? (int)$this->request->requestVar('sorting'):'last_tweet DESC';
		$limit=Page_Controller::sessionInfo('limit',true, $this->request->requestVar('limit'));
		$users= $pen->{$magicRoutine}($start,$limit,$sorting);
		$users = new PaginatedList($users);
		$users -> setPageLength($limit);

		$data = array('Tag'=> 'Friends', 'Users' => $users,  'Title' => 'All Friends (upline) of ' . $pen->screen_name ) ;
		$labels['Users'] = $users;
		$labels['Title'] .= $pen->screen_name;
		ERROR_LOGGER("Formatting Friends");
	  	return $this->customise($labels)->renderWith(array('User_results', 'Page'));
	}
	function deadFriends($data=false){
		return $this->grabAndFormatAssociates($data,
		'nonFollowers', array('Tag'=> 'DeadFriends',  'Title' => 'Useless(?) Friends of ' ) );
	}

	function friends($data = false) {
		return $this->grabAndFormatAssociates($data,'OKFriends'
				,array('Tag'=>'Good Friends','Title'=>'Reciprocal Friends of'));
	}

	function followers($data = false) {
		return $this->grabAndFormatAssociates($data,'Followers'
				,array('Tag'=>'Followers','Title'=>'The Folks interested in your tweets'));
	}

	function updateFriends($data = false) {
		$pen = PenName::getSessionPenName($data?$this->requestVar('penName'):false);
		ERROR_LOGGER("Getting the accessor for Pen Name  = " . $pen->screen_name);
		$pen -> fillFriends( );
		Director::redirect($this->RelativeLink('friends'));
	}

	function updateFollowers($data = false) {
		$pen = PenName::getSessionPenName($data?$this->requestVar('penName'):false);
		ERROR_LOGGER("Getting the accessor for Pen Name  = " . $pen->screen_name);
		$pen -> fillFollowers( $pen);
		$pen -> write();
		Director::redirect($this->RelativeLink('followers'));
	}
	function links() {
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
		if ($Tweets){
			$Tweets = new PaginatedList($Tweets);
			$Tweets -> setPageLength($limit);
		}
		$data = array('Tag'=> 'Public', 'Tweeties' => $Tweets,  'Title' => 'Most Recent Tweets') ;
	  	return $this->customise($data)->renderWith(array('Tag_results', 'Page'));
	}
	function dropTag($data) {
		//print_r($id);
		if(!Director::is_ajax() ) return "";
		$name = $data->param('ID');
		$tag=& Tag::getTagByName($name);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		//ERROR_LOGGER("got droptag -". $name);
		$erasedID=$tag->erase();
		//ERROR_LOGGER("Erased ID = " . $erasedID);
		return("jQuery('span[class*=tag_{$erasedID}]').remove();");
	}

	function makeClassic($data){
		//print_r($id);
		if(!Director::is_ajax() ) return "";
		$name = $data->param('ID');
		$tag=& Tag::getTagByName($name);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		ERROR_LOGGER("got Classytag -". $name);
		$classyID=$tag->ID;
		$tag->makeClassic();
		//ERROR_LOGGER("Erased ID = " . $erasedID);
		FormResponse::add("jQuery('.classy_{$classyID}').replaceWith('<span class=\"isClassic tag_{$classyID}\" title=\"Tweets of this tag are Eternal\">&#X269a;</span>');");
		FormResponse::add("jQuery('.drop_{$classyID}').remove();");
		return FormResponse::respond();	
	}

	function TagList($kind){
		return Tag::BigOnes($kind,20);
	}
	
}
