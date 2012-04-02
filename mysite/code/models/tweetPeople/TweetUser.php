<?php
# vim:ts=3:sw=3:sts=3:ft=php:

class TweetUserMaintenance {

	var $TweetUser;

	function TimeToSchedule($from){
		if($this->TweetUser instanceOf TweetUser) {
			return $this->TweetUser->TimeToSchedule($from);
		}
		return 15*60 + $from;  // in 15 minutes, OK?
	}

	function evaluateUser () {
		while(1) {
			if( DoToDoItem::triggerWatchDog()) {
				error_log("break on watchdog");
				break;
			}
			$tu = DataObject::get_one('TweetUser', '"evaluate"=true');
			if (!$tu) break;
		$this->TweetUser = $tu;
			$tu -> ToDoAccess = $this->ToDoAccess;
			error_log("rating the user:" . $tu->screen_name);
			$tu->rate ();
		}
		return $this;
	}

	function fillOne ($tu=false) {
		if(!$tu) $tu = DataObject::get_one('TweetUser', '"received"=false');
		$this->TweetUser = $tu;
		error_log("rating the user:" . $tu->screen_name);
		$tu -> ToDoAccess = $this->ToDoAccess;
		$tu->rate ();
		if ($tu ->error != 4) {
			return $this;
		}
		// we only try fillMe as a fall back for a protected user
		$tu -> received = false;
		$tu -> fillMe(array('screen_name' => $tu->screen_name));
		return $this;
	}

	function maintain(){
		$this->fillOne();
		$this->evaluateUser();
		return $this;
	}
}

class TweetUserAdmin extends ModelAdmin {
   
  public static $managed_models = array(
      'TweetUser'
   );
 
  static $url_segment = 'TweetUser'; // will be linked as /admin/products
  static $menu_title = 'edit tweet Users';
 
}


class TweetUser extends DataObject {
	static $api_access=array('view'=>array('screen_name','name','location','description','url','twitter_id','last_tweet','profile_image_id','created_at','friends_count', 'followers_count','statuses_count'));

	static $summary_fields= array('screen_name');
	static $create_table_options = array('MySQLDatabase' => 'ENGINE=MyISAM'); //InnoDB does not support fulltext search
	static $db = array (
	       	'name' => 'Varchar(55)'
		,'screen_name' => 'Varchar(35)'
		,'location'=> 'Varchar(50)'
		,'description' => 'Text'
		,'url' => 'Text'
		,'protected' => 'Boolean'
		,'received' => 'Boolean'
		,'twitter_id' => 'Int'
		,'profile_image_url' => 'Text'
		,'last_tweet' => 'Datetime'
		/* new fields as of may 26, 2009 */
		,'created_at' => 'Datetime' // joined Twitter (figure tweets per day?)
		,'friends_count' => 'Int'
		,'followers_count' => 'Int'
		,'statuses_count' => 'Int'
		,'tweet_quality' => 'Float'
		,'follow_worthy' => 'Float'
		,'quality_time' => 'Datetime'
		,'poison' => 'Boolean'
		,'evaluate' => 'Boolean'

		);
	static $searchable_fields = array('screen_name','twitter_id','location');
	static $has_one= array('UserTag'=>'Tag'); // the  screen name of the user this is from (we really should inherit Tag)
	static $has_many= array('Statuses'=>'Tweet'); // the  Tweets from this user
	static $indexes = array ('twitter_id' => true, 'received'=>true, 'evaluate'=>true, 'location' => 'fulltext (location)', 'name'=>true, 'screen_name'=> 'unique (screen_name)' ,  'searchfields' => "fulltext (name,screen_name,location)" );

// key=destination, val = source
	static $show = array ( 'twitter_id' => 'id', 'name'=>'name', 'screen_name'=>'screen_name', 'location'=>'location','description'=>'description'
		, 'url' =>'url', 'protected' =>'protected', 'followers_count'=>'followers_count', 'created_at'=>'created_at'
		, 'friends_count'=>'friends_count', 'statuses_count' => 'statuses_count'
		, 'profile_image_url' => 'profile_image_url'
		,'created_at'=>'created_at'         // ancilliary field
       	);
	static $friendships_create = array ( 'twitter_id' => 'id', 'name'=>'name', 'screen_name'=>'screen_name', 'location'=>'location','description'=>'description'
		, 'url' =>'url', 'protected' =>'protected', 'followers_count'=>'followers_count', 'created_at'=>'created_at'
		, 'friends_count'=>'friends_count', 'statuses_count' => 'statuses_count'
		, 'profile_image_url' => 'profile_image_url'
		,'created_at'=>'created_at'         // ancilliary field
       	);
	static $userCache=array();

//
	static $required=false;

	function getCMSFields(){
		$fields = parent::getCMSFields();
		return formUtility::removeFields($fields, array('twitter_id','profile_image_url','last_tweet','created_at','friends_count','followers_count','statuses_count','tweet_quality','follow_worthy','quality_time','poison','evaluate','protected','evaluate','UserTagID','received'));
	}

	function requireDefaultRecords() {
		if(self::$required ) return;
		self::$required=true;	
		parent::requireDefaultRecords();
		ToDo::insureToDoItem('InternalToDo', 'TweetUser', 'maintain');
	}

	var $MyScheduler;

	function TimeToSchedule($from){
		if($this->error == 4)  return ($from + 3600* 24*365);  //a year, no authorization!
		if($this->error == 3)  return ($from + 3600* 24*365);  //a year, Twitter say No Can Do, Ever!
		if($this->MyScheduler instanceOf Scheduler) {
			return $this->MyScheduler->TimeToSchedule( 0) + $from; //do not bump MyScheduler's competition count
		}
		return 15*60 + $from;  // in 15 minutes, OK?
	}

	function follows(){
	return false;
	}

	// this is activated automatically by the background "whenever"
	// do any maintenance needed on friends or family
	function maintain($p = false){
		$f = new TweetUserMaintenance();
		$f -> ToDoAccess = $this->ToDoAccess;
		return $f->maintain($p);
	}

	function canView() {
		return true;
	}
	function setScreen_name($name){
		while (substr($name,0,1) == '@' ) $name=substr($name,1);
		if ($this->screen_name == $name) return;
		$this -> setField('screen_name',$name);
		$t=& Tag::getTagByName($name,'UserTag');
		if(!$t) {
			error_log('can not create user tag for screen name '.$name);
			return;
		}
		$this -> setField('UserTagID', $t->ID);
		$this -> write();
		return ;
	}

	protected function onBeforeWrite () {
		if ( isset($this->tweeted_time) ) {
			$myTime = strtotime($this->last_tweet);
			if ($myTime < $this->tweeted_time ) {
				$this->setField('last_tweet', date('Y-m-d H:i:s',$this->tweeted_time));
			}
		}
		parent::onBeforeWrite();
	}

	function setLastTweet($strtime) {
		$t = strtotime($strtime);
	       	if(!isset($this->tweeted_time) ) {
			$this->tweeted_time = $t;
			return;
		}
		if($this->tweeted_time < $t) $this->tweeted_time = $t;
		return;
	}



	static $FollowNeedsCall = true;
	function JavaScriptFollow(){
		self::$FollowNeedsCall = false;
		View::wrapJava(<<<JS

		  $(".alreadyFollow").css({backgroundColor:"#fed"}).livequery('click', function() {
	         b=$(this).attr("class").match(/who__(\S+)/)[1];
			$.getScript( 'home/unfollow/'+ b );
			$(this).css({backgroundColor:'#89a'});
	       return false;
		});

		  $(".canFollow").css({backgroundColor:"#abc"}).livequery('click', function() {
	         b=$(this).attr("class").match(/who__(\S+)/)[1];
text=encodeURIComponent($("#tweet input").val());
			$.getScript( 'home/follow/'+ b + '?tweet=' +text);
		   $(this).css({backgroundColor:'#fb8'});
		       return false;
		});
JS
		);
	}

	//static $many_many_extraFields = array('Associates'=> array('FriendshipExtendedOn' => 'Date',
	 //      	'IFollowDate' => 'Date', 'TheyFollowDate' => 'Date')); 
	function prettyDate($v){
		if (!isset($this->{$v})) return "Never";
		$n = new Date();
		$n->setValue($this->{$v});
		return $n->Ago() ;
	}

	function Last3(){
		return $this->Statuses("","published DESC","","3");
	}

	function followFactor() {
		if (! $this->selfUser) {
			$pen= PenName::getSessionPenName();
			if (!$pen) return " --- ";
			$this->selfUser = $pen -> Title;
		}
		$selfUser = $this->selfUser;

		if ($this->screen_name == $selfUser ) {
			$text ="Yourself";
			$possible = "nocanFollow";
			$friendText="";
		} else {
			// make sure the script to follow/unfollow is on the page
			if(self::$FollowNeedsCall) $this->JavaScriptFollow();
			$self=& TweetUser::getTweetUser($selfUser);
			if ($self->follows($this) ) {
				$possible = "alreadyFollow";
				$text ="Following";
			} else {
				$possible ="canFollow";
				$text = "Follow";
			}
			if($this->follows($self) ) {
				$friendText="Follows you";
			} else {
				$friendText="Not Your Fan";
			}
		}
		return "<span class=\"following who__{$this->screen_name} $possible\">$text:$friendText</span>";
	}


	function block($screen_name) {
		$old_friend=& TweetUser::getTweetUser($screen_name);
		$twitter = new SaneRest("http://twitter.com/blocks/create/$screen_name.xml");
		$twitter -> Authenticate();
		$params = array('screen_name' => $screen_name);
		//$twitter->setQueryString($params);
		$conn = SaneResponse::makeSane($twitter->request('','POST', '') );
    		error_log(print_r($twitter->returnHeaders(),1));
		$response = $conn->analyze_code();
		error_log("in block 3,ready to block $screen_name");
		if ($response) return false;   // did not work
		return $this->unfollow($screen_name);
	}


	function unfollow($screen_name) {
		$old_friend=& TweetUser::getTweetUser($screen_name);

		$twitter = new SaneRest("http://twitter.com/friendships/destroy.xml");
		$twitter -> Authenticate();
		$params = array('screen_name' => $screen_name);
		//$twitter->setQueryString($params);
		$conn = SaneResponse::makeSane($twitter->request('','POST', $params) );
		$response = $conn->analyze_code();
    		error_log(print_r($twitter->returnHeaders(),1));
		if ($response && !stristr($conn->getBody(), 'not friends') ) return false;
		return true;
	}

	function tweetTo($screen_name,$message) {
		// can ONLY tweet direct to someone who is following me
		$twitter=new SaneRest("http://twitter.com/direct_messages/new.xml");
		//$params=array('text' => $message, 'user' => $screen_name);
		$twitter -> Authenticate();
		$conn = ($twitter->request('','POST', 'user=' . $screen_name . '&text=' . $message) );
		$conn = SaneResponse::makeSane($conn );
		$response = $conn->analyze_code();
    		$twitter->returnHeaders();
		if ($response) return false;   // did not work
		$tweetInfo = $twitter->getRequestValues($conn->xpath('//'.$default.'status')); // ArrayData Objects suk
		if ($tweetInfo->exists()) {
			$tweetInfo = $tweetInfo->First();
			$x=array();
			$x['Title'] = $tweetInfo->getField('text');
			$x['published'] = $tweetInfo->getField('created_at');
			$x['Created'] = $tweetInfo->getField('created_at');
			$x['author_name'] = $new_friend->screen_name;
			$x['ID'] = $tweetInfo->getField('id');
			$new_tweet= Tweet::getasAPITweet($x) ;
		}
		return true;
	}

	function follow($screen_name,$firstMessage="",$PenName=false) {
		// 0 every thing may proceed
		//   never return 1 (that is an end of file and is done elsewhere
		// 2 failed, try again later
		// 3 failed, do not attempt later
		// 4 failed authorization
	return followEZ($screen_name,$firstMessage,$PenName); 
	}

	function forTemplate() {
		$result = "";
	       if($this->UserTagID) $result .= "<a href=\"tag/index/{$this->UserTag()->TagText}\" > Tag";
		if ($this->name != "") {$result .= $this->name; }
	       if($this->UserTagID) $result .= "</a>";
		$result .= "(<a target=\"_blank\" href=\"http://twitter.com/{$this->screen_name}\" >{$this->screen_name}</a>)";

		return $result ;
	}

	function fillMe( $params) {
	  	$this->MyScheduler = IPScheduler::get_IPScheduler();
	  	$s=$this->MyScheduler->schedule();
	  	if ($s>0) {
			return $this;
		}
		if(!$this->received ) {
			$service = "http://twitter.com/users/show.json";
			$twitter = new SaneRest($service);
			$twitter ->setQueryString($params);
			$conn = $twitter->request();
// make a container for silverStripe's parser
			$code=$conn->getStatusCode() ;
			$contents = $conn->getBody();
			$this ->MyScheduler-> request_complete($twitter->returnHeaders()) ; 
			if($code != 200)
			{
				if (  stristr($contents, "Rate limit exceeded") ) {
					DoToDoItem::MustDie( "Twitter says: Rate limit exceeded show.xml - " . $twitter->getQueryString() );
					return true;
				}
			if (  stristr($contents, "Not found") ) {
				$this->name=$this->screen_name = $params['screen_name'];
				$this->description = "Not found by Twitter";
				$this->received=true;
				$this -> forceChange();
				$this -> write();
				error_log("Twitter says: Not found");
				return false;
			}
			if (  stristr($contents, "User has been suspended") ) {
				$this->name= $params['screen_name'];
				$this->description = "Suspended";
				$this->received=true;
				$this -> forceChange();
				$this -> write();
				error_log("Twitter says: Suspended");
				return false;
			}

			error_log("Twitter failed to get user, code=".$code);
			error_log("Twitter says: ". $conn->getBody());
			return true;
		}
		$new_user = json_decode($contents);
		PleaseMap::object2object($this,
						TweetUser::$friendships_create, $new_user);
		$this->received=true;
		$this->forceChange();
		$this->write();
		
		error_log("Data for " . $this->screen_name . " retrieved");
	//	print_r($me);
		$this -> write();
	}
	return false;

	}
	function fullInfo(){
		return ($this->statuses_count || $this->followers_count || $this->friends_count);
	}

	static function &getTweetUser($id,$fill=true) {
		$x=array();
		$need_write=false;
		if(! is_numeric($id) && is_string($id)) {
			$real_name="";
			$screen_name=Convert::raw2sql($id);
			if( preg_match('/\((.*)\)/', $screen_name,$matches)) {
				$real_name = $matches[1];
			}
			if( preg_match( '/(^|@+)(\w+)(\b|$)/' , $screen_name,$matches)) {
				$screen_name = $matches[2];
			}
			if (isset(self::$userCache[$screen_name]) ) {
				$u =& self::$userCache[$screen_name];
				$u->unCached=false;
			       	return $u;
			}
			$t = DataObject::get_one('TweetUser','"screen_name"=\'' . Convert::raw2sql($screen_name) ."'");
			//error_log("jah 1 screen name = $screen_name");
			if (!$t) {
				$t = new TweetUser();
			//error_log("jah NEW USER screen name = $screen_name");
				$t->screen_name = $screen_name;
				$t->name = $real_name;
				$t->received=false;
				$need_write=true;
			}
			//error_log("leaving Get Tweet User path1");
		} elseif(is_object($id)) {
			if( $id->screen_name !="" ) {
				$t=& self::getTweetUser( $id->screen_name);
			} elseif ($id->user_id !=0) {
				$t=&  self::getTweetUser( $id->user_id);
			} elseif( $id->user_screen_name !="" ) {
				$t=&  self::getTweetUser( $id->user_screen_name);
			} elseif ($id->id !=0) {
				$t=&  self::getTweetUser( $id->id);
			} else { $t = new TweetUser();
			}
			
		} else {
// not string or object, take id as the twitteruser number
			$t = DataObject::get_by_id('TweetUser',$id);
			if (!$t) {
				$t = new TweetUser();
				$t->received=false;
				if($fill) {
					$t -> fillMe(array('id' => $id));
					error_log("calling  fillMe from getTweetUser -- unusual??");
				}
				$need_write=true;
			}
		}
		if (!$t->UserTagID) {
			//$t->screen_name = $t->screen_name;
			if (@$t->screen_name) {
				$t->setField('UserTagID', Tag::getTagByName('@'.$t->screen_name,'UserTag')->ID);
				$need_write=true;
			}
		}
		if($need_write) $t->write();
		$t->unCached=true;
		if(isset($screen_name) ) self::$userCache[$screen_name] =& $t;
		return $t;
	}

	function &getUserTag() {
		if (!$u =& $this->UserTag()) {
			if (@$this->screen_name) {
				$u=& Tag::getTagByName('@'.$this->screen_name,'UserTag');
				if (!$u) error_log("Illegal tag generated for TweetUser Record -- E R R O R " .$this->ID);
				$this->setField('UserTag', $u);
			}
		}
		return $u;
	}

	var $failed=true;
	var $error;

	function rate($params=array()) {
		$addresses=0;
		$carbons=0;
		$retweets=0;
		$linking =0;
		$trends=0;

//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		$fol =$this->followers_count;
		$fri =$this->friends_count;
		$tweets = (float)$this->statuses_count;
		if (!$this->tweet_quality || ! $this->follow_worthy ) {
			$this->quality_time =0;
		}
//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		if( $this->quality_time ) {
			$when = strtotime($this->quality_time);
			if($when + (3600*24*14) > time() ) { // we calculated the quality in the last 2 weeks
	$this-> evaluate = false;
				return $this;
			}
		}

//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		$elapsedTime = time();
		$recentTweets =& $this->Statuses("","`published` DESC","" ,"100");

		if ($this->received && $recentTweets -> count() > 10 && $fol>0 && $fri >0 && $tweets >0) {
			// we have enough info to rate this user
//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		} else {
	
//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
			$service = "http://twitter.com/statuses/user_timeline/". $this->screen_name ;
	//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
	$this->MyScheduler = IPScheduler::get_IPScheduler();
	$s=$this->MyScheduler->schedule();
			if ($s>0) {
				return $this;
			}
	//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
			$service .= '.json';
//			error_log("getting user sent tweets for rating SERVICE = " . $service);
			$twitter = new SaneRest($service );
		
			$params['routine'] = __METHOD__;
			$params['count'] = 100; // 100 latest tweets should be enought for rating

			$twitter->setQueryString($params);
//	error_log($twitter->getQueryString(). " User follow Factor evaluation");
			$conn = SaneResponse::makeSane($jsdata=$twitter->request());
//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
			$this ->MySheduler-> request_complete($twitter->returnHeaders()) ; 
			$response = $conn->analyze_code();
			$this->error = $response;
			if ($response){
				$this->follow_worthy = 1;
				$this->tweet_quality = 1.01010101;
				$this->quality_time = date("Y-m-d H:i:s");
//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ ."  Bad response code=". $response);
				 if($response == 4 || $response == 3 ) {
					 // 3 usually means "not found"
					 // 4 indicates a protected user, and we must use 'fillOne' to get it
					 $this->error=0;
					$this-> received = true;
					$this-> evaluate = false;
					$this-> write();
				 }
				return $this;  // it did not work
			}
			$jsdata = $jsdata->getBody();
			$msgs = json_decode($jsdata);
	//error_log(print_r($msgs,1));
	//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );


			$twitter_id=0;
			$this->failed=false;
			$elapsedTime = time();
			$update_user=true;
	//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
			if ($msgs && is_array( $msgs)) foreach  ($msgs as $entry) {
	//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
				if( DoToDoItem::triggerWatchDog()) {
					error_log("break on watchdog");
					break;
				}
				// process the tweet
				$t=Tweet::getTweet($entry->id,false,true);
				//error_log("new Tweet");
				if ($t -> fromDB == false && !$t->apiTweet) { // this tweet needs updating,  and author processing
					$t->apiTweet = true; // we have done the full user processing on this guy
					$u = $entry -> user;
					$t->Title      = $entry->text;
					$t->author_name= $u->screen_name;
					$t->published  = $entry->created_at;
					//error_log("new Tweet writing");
					$t->write();
					//error_log("new Tweet written");
					if ($update_user){
						$update_user = false;
					PleaseMap::object2object($this,
						TweetUser::$friendships_create, $u);
					}
				}
			}
		$recentTweets =& $this->Statuses("","`published` DESC","","100");
		}		
		$tweetsProcessed = 0;
		if ($recentTweets && $recentTweets->exists() ) foreach($recentTweets as $t) {
			$tweetsProcessed += 1;
			$txt = $t->Title;
//	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__  ."   Text = $txt");
			$addresses += preg_match("/^@/", $txt);
			$linking +=  preg_match_all("/https?:\/\//" , $txt,$dummy);
			$carbons += preg_match_all("/@/" , $txt,$dummy);
			$retweets += preg_match("/RT @/", $txt);
			$trends +=  preg_match_all("/#/" , $txt,$dummy);
		}
		
//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
	$fol =$this->followers_count;
	$fri =$this->friends_count;
	$tweets = (float)$this->statuses_count;

	if( $this->created_at ) {    // honeymoon period
		$when = strtotime($this->created_at);
		if($when + (3600*24*14) > time() ) { // this user joined only 2 weeks prior
			$fol = 100;
			$fri = 101;
		}
	}


	if ($fol == 0 ||  $fri  == 0) {
		$follow_worthy =0.01;
	} else {
		$fol=(float)$fol;
		$fri=(float)$fri;
		error_log("fol = $fol, fri = $fri");
		$factor = 0.3 + abs($fol/$fri - $fri/$fol) ;
		$follow_worthy = sqrt( sqrt( 10000 * log($tweets) /$factor ) );
		}
error_log("follow_worthy = $follow_worthy");
error_log("retweets = $retweets, addresses = $addresses, linking = $linking, carbons = $carbons, trends = $trends");
	$tweet_quality = (float)$tweetsProcessed/3.00 + $addresses + (float)$retweets/2.0;
error_log("tweet_quality = $tweet_quality");
	$tweet_quality += ($linking+$carbons-$addresses)/3.00;
error_log("tweet_quality = $tweet_quality");
	$tweet_quality = ($tweetsProcessed?($tweet_quality/($tweetsProcessed/2)):0);
	if ($this->last_tweet) {
			$when = strtotime($this->last_tweet);
			if($when + (3600*24*30) < time() ) { // this user has not tweeted for a month
				$tweet_quality *= 0.2;   // nearly worthless
			}
	}
error_log("tweet_quality = $tweet_quality");
if($tweet_quality < 0.1) $tweet_quality = 0.1;
if($follow_worthy < 0.1) $follow_worthy = 0.1;
	$this->tweet_quality = $tweet_quality;
	$this-> follow_worthy = $tweet_quality*$follow_worthy;
	$this->quality_time = date("Y-m-d H:i:s");
	$this-> received = true;
	$this-> evaluate = false;
	$this-> write();
	error_log("new user written");



		$elapsedTime = time() - $elapsedTime;
		$log_msg = "processed $tweetsProcessed in $elapsedTime seconds";
		error_log($log_msg);
		
//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ ." return");
		return $this;
	}

}
