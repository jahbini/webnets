<?php

class TweetMaintenance {

	function maintain(){
		//   we will totally snuff tweet maintenance from the background processor
		//   if there is some reason to do periodic sweeps of the tweets
		//   (like culling old tweets, for example), we would return '$this' rather than false
		return false;
	}
}

class Tweet extends DataObject {
	static $db = array (
		'author_name' => 'Varchar(55)' 
		, 'Title'=> 'Varchar(150)'
		, 'published' =>'SSDatetime'
		, 'StatusID' => 'Varchar(30)'
		, 'apiTweet' => 'Boolean'
		, 'recipient_screen_name' => 'Varchar(30)'
		);
	static $has_one = array ('TweetUser' => 'TweetUser');
	static $many_many = array ('SimpleTags' => 'Tag');
	static $indexes = array ( 'StatusID' => 'unique (StatusID)', 'searchfields' => "fulltext (author_name,Title)" );
	static $create_table_options = array('MySQLDatabase' => 'ENGINE=MyISAM'); //InnoDB does not support fulltext search

	
	static $required=false;
	function requireDefaultRecords() {
		if(self::$required ) return;
		self::$required=true;	
		parent::requireDefaultRecords();
		ToDo::insureToDoItem('InternalToDo', 'Tweet', 'maintain');
	}
	function maintain($param=false){
		$f =new TweetMaintenance;
		$f -> ToDoAccess = $this->ToDoAccess;
		return $f ->maintain($param);
	}

	
	function PublishedOn(){
		$xx=new SSDatetime();
		$xx->setValue($this->published);
		return $xx->Format('M j,\'y g:ia');
	}

	function prettyDatetime($v){
		//return $this->PublishedOn();
		if (!isset($this->{$v})) return "cant say when";
		$n = new SSDatetime();
		$n->setValue($this->{$v});
		return $n->Ago();
	}

	function setAuthor_name($screen_name){
		// some names come from twitter search queries line scrn (first last)
		if( preg_match( '/(^|@+)(\w+)(\b|$)/' , $screen_name,$matches)) {
			$screen_name = $matches[2];
		}
		if ($this -> ID == 0) {
			$this -> forceChange();
			$this ->write();
		}
		if($this->TweetUserID !=0 && $this->getField('author_name') == $screen_name) return; // already done
		//error_log("setting author $screen_name TweetID=". $this->ID);
		$this->setField('author_name', $screen_name);
		$t= $this->SimpleTags("1=0"); // insure empty
		$uID=TweetUser::getTweetUser($screen_name,false) ->ID;
		$this->TweetUserID=$uID;
		//error_log("setting TweetUserID =" . $uID );
		$t->add(Tag::getTagByName($screen_name,'UserTag')->ID); //  add it to the simpletags
		$t->write();
		$this-> write();
	}


	function setRecipient_screen_name($screen_name){
		// setting the recipient marks this as a direct  message!
		error_log("setting recipient screen name TweetID =". $this->ID);
		if($this->getField('recipient_screen_name') == $screen_name) return; // already done
		$this->setField('recipient_screen_name', $screen_name);
		$t= $this->SimpleTags("1=0");
		$uID=TweetUser::getTweetUser($screen_name,false)->ID;
		$t->add(Tag::getTagByName($screen_name,'UserTag')->ID ); //  add it to the simpletags
		$t->write();
		$this-> write();
	}


	public function TweetAuthor(){
			$tuser=$this->TweetUser();
			if (!$tuser || $this->author_name != $tuser->screen_name ) { 
				$rsn= $this->author_name;
				if( preg_match( '/(^|@+)(\w+)(\b|$)/' , $screen_name,$matches)) {
					$rsn = $matches[2];
				}
				$tuser =& TweetUser::getTweetUser($rsn);
				$this->TweetUser =& $tuser;
				$this->write();
				$this->TweetUser =& $tuser;
			}
			return $tuser->forTemplate(); 
	}

	public function decoratedTitle(){
		$text = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/~\-_\.]*(\?\S+)?)?)?)@', '<a target="_blank" href="$1">$1</a>', $this->Title );
		return '<span class="tweet_contents tweet_contents_style screen_name__'.
			$this->TweetUser()->screen_name . 
			'" >' .
			$text .
			"</span>";
	}
	public function forTemplate(){
		$text = $this->Title;
		$o=error_reporting(0);
		$text = mb_convert_encoding($this->Title, "ISO-8859-9", "auto");
		$text = mb_convert_encoding($text, "UTF-8", "auto");
		error_reporting($o);
		$text = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/~\-_\.]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', htmlentities($text) );
		$text = preg_replace('/&amp;([^&;]+;)/','&$1',$text);
		$text = preg_replace('/&amp;([^&;]+;)/','&$1',$text);
		return $text;
	}

	public function tagMe($extraTag=false) {
		//error_log("entering tagme");
		$myTags = $this->SimpleTags();
		$need_write=false;
		if($extraTag) {
			$need_write=true;
			$myTags-> add($extraTag);
		}
		if($need_write)$this->write();
		//error_log("leaving tagme");
		return $myTags;
	}

	public function setTweetUser($user) {
		if(is_object($user)) {
			if (! $user->exists()) $user ->write();
			$this ->setField("TweetUserID",$user->ID);
		} else {
			$this->setField("TweetUserID",$user);
		}
	}

	public function getTitle() {
		return $this -> getField('Title');
	}

	public function &getTweetUser() {
		if (($id=$this->getField("TweetUserID")) ==0 ) {
			$id = $this->author_name();
			$tu = TweetUser::getTweetUser($id);
			$this->setField("TweetUserID", $tu->ID);
			$this -> write();
			return $tu;
		}
		return TweetUser::getTweetUser($id);
	}

	static function getasAPITweet($id,$extraTag=false) {
		$x=array();
		if(is_object($id)) {
			$StatusID =$id->id;
			$x['Title'] = $id->text;
			$x['published'] = $id->created_at;
			$x['Created'] = $id->created_at;
			$x['author_name'] = $id -> user_screen_name;
		} elseif (is_array($id) ) {
			$x=$id;
			$StatusID=$x['StatusID'];
		} else	{
			$StatusID = $id;
		}

		//error_log("API Tweet Looking for Tweet # $ID");
		DB::query('LOCK TABLES "Organizer" WRITE,"Mentor" WRITE, "BigTweet" WRITE, "Tweet" WRITE,"Tweet_SimpleTags" WRITE, "Tag" WRITE, "TweetUser" WRITE, "PenName" WRITE, "UsersPenName" WRITE');
		try{
		$t = DataObject::get_one('Tweet','"StatusID"=\''. $StatusID. "'");
		if (!$t ) {
			$t = new Tweet();
			$t->StatusID=$StatusID;
			$t -> forceChange();
			$t -> fromDB=false;
			$apiTweet = true;
			$t -> apiTweet = true;
		} else {
			$apiTweet = $t -> apiTweet;
			$t -> fromDB=true;
		}
		// copy in any new values
		if (is_array($x) ) foreach ($x as $key => $value) {
			if($key== 'ID') continue;
			$t -> {$key} = $value;
		}
		if($extraTag) $t->tagMe($extraTag);
		$t->write();
		} catch (Exception $e) {
			error_log("EXCEPTION in Tweet storage to DB " . __FUNCTION__ . " msg " . $e->getMessage());
		}
	
		DB::query('UNLOCK TABLES');
		$t->apiTweet = $apiTweet;
		return $t;
	}

	static function newTweet($text,TweetUser $user,$extraTag=false){
	$spec = array('Created' => 'published', 'published' => 'published', 'Title' => 'Title' );
		if (strlen($text) >140 ) {
			return BigTweet::newTweet($text, $user, $extraTag);
		}
		DB::query('LOCK TABLES "Organizer" WRITE,"Mentor" WRITE, "BigTweet" WRITE, "Tweet" WRITE,"Tweet_SimpleTags" WRITE, "Tag" WRITE, "TweetUser" WRITE, "PenName" WRITE, "UsersPenName" WRITE');
		try {
		$idVal=$x['StatusID'];
		$t = new Tweet($id);
		$t->Title = $text;
		$t -> TweetUserID = $user->ID;
		$t->author_name = $user->screen_name;
		$t->StatusID=-1;
		$t->write();
		$t->StatusID = - $t->ID;
		$t->write();
		$t->tagMe($extraTag);
		$t->write();
		$t -> fromDB=false;
		} catch (Exception $e) {
			error_log("EXCEPTION in Tweet storage to DB " . __FUNCTION__ . " msg " . $e->getMessage());
		}
		DB::query('UNLOCK TABLES');
		return $t;
	}

	static function getTweet($id,$extraTag=false) {
	$spec = array('Created' => 'published', 'StatusID' => 'StatusID' , 'published' => 'published', 'Title' => 'Title', 'author_name' => 'author_name' );
		$x=array();
		if(is_array($id) ) {
			if(isset($id['status_id']) && !isset($id['StatusID']) ) $id['StatusID'] = $id['status_id'];
			PleaseMap::Array2Array($x,$spec,$id);
			$idVal=$x['StatusID'];
		} elseif(is_object($id)) {
			if(isset($id->status_id) && !isset($id->StatusID) ) $id ->StatusID = $id->status_id;
			if(!isset($id->StatusID)) _e("very bad parameters to getTweet");
			PleaseMap::Object2Array($x,$spec,$id);
			$idVal=$x['StatusID'];
		} else {
			$idVal = $id;
		}
		$ID = explode(":",$idVal);
		$idVal = $ID[sizeof($ID)-1];

		$x['StatusID']=$idVal;

		DB::query('LOCK TABLES "Organizer" WRITE,"Mentor" WRITE, "BigTweet" WRITE, "Tweet" WRITE,"Tweet_SimpleTags" WRITE, "Tag" WRITE, "TweetUser" WRITE, "PenName" WRITE, "UsersPenName" WRITE');
		try {
		$t = DataObject::get_one('Tweet','"StatusID"=\''. $idVal. "'");
		unset($x['ID']);
		unset($x['id']);
		if (!$t) {
			$t = new Tweet();
			$t->StatusID=$idVal;
			$t -> forceChange();
			$t -> fromDB=false;
		} else {
			$t -> fromDB=true;
		}
		if (is_array($x) ) foreach ($x as $key => $value) {
			if($key== 'ID') continue;
			$t -> {$key} = $value;
		}
		$t->tagMe($extraTag);
		$t->write();
		} catch (Exception $e) {
			error_log("EXCEPTION in Tweet storage to DB " . __FUNCTION__ . " msg " . $e->getMessage());
		}
		DB::query('UNLOCK TABLES');
		return $t;
	}
}
