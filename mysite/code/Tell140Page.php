<?php
// vim:sw=3:sts=3:ft=php:    

class Tell140Page extends Page {
   static $db = array(
   );
   static $has_one = array(
   );
}

class View {
	static function unwrapJava($j) {
		Requirements::customScript($j);
	}


static function wrapJava($j){
	Requirements::customScript(<<<JS
// Standard jQuery header
(function($) {
 $(document).ready(function() {
$j
// Standard jQuery footer
return;  })
})(jQuery);
JS
);
}

}
class TweetBoxForm extends Form {
	   function __construct($controller, $name) {
		$tweetBoxTweet= new TextareaField("tweet", "Tweet?", 4, 28);
		$tweetBoxTweet -> setRightTitle("You have <span id='tweet_counter'>140</span> characters left for this ");

		$addressee = new TextField("addressee","destination?");
		//$addressee -> addExtraClass("hidden");
		$hidden_data=HiddenField::create('routing');
		$hidden_data->setValue('tweet');
		$hidden_length = HiddenField::create('maxlength');
		$hidden_length -> setValue(140);
		$penNameID = Session::get('penName');

		$map = $controller->profile->PenNameMap();
		$fields = new FieldSet( $tweetBoxTweet,$addressee ,new DropdownField("penName", "Twitter Screen Name", $map ,$penNameID),$hidden_data,$hidden_length  );
		$actions = new FieldSet(
			$button = new FormAction('smbtTweet', 'Tweet it!')
			//,new FormAction('followFriday', 'Follow Em!')
			);
		$button ->addExtraClass('mainButton'); // use attribut mainButton as helper for javascript to find this

	       parent::__construct($controller, $name, $fields, $actions);

		if( !Director::is_ajax() ){
			$hidden_dataID = $hidden_data->id();
			$tweetBoxTweetID = $tweetBoxTweet->id();
			$myID=$this->FormName();
			if(ANALYTICS  && !Permission::check('ADMIN') && !Session::get('notrack') =="true" ) {
				// do not do analytics tracking for admin user
Requirements::customScript(<<<JS
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {
	var pageTracker = _gat._getTracker("UA-753622-8");
	pageTracker._trackPageview();
	} catch(err) {}
JS
);
		     }
		}

	    }
	    
	      function forTemplate() { return $this->renderWith(array( $this->class, 'TweetForm')); }
}

class Tell140Page_Controller extends Page_Controller {
	var $alsoP = false;
	var $not_ajax = true;
	var $requiresTweetAction = true;
	var $requiresTabAction = true;
	var $tweetBoxClass = "at_home";
	var $extraLogMsg = "";
	var $profile = false;
	var $member = false;
	var $subDomain;
	var $mentor;

	protected function interpolate($string) {
		return str_replace(array("#mentor#", "#mentee#") , array($this->mentor->screen_name, ($this->mentee?$this->mentee->screen_name:'unknown') ) , $string);
	}
   function init() {
      error_log("Page controller init");
	   parent::init();
	   global $MentorLocation;
	   global $WantedSubDomain;
	   Mentor::setMentorLocation($MentorLocation);
	   error_log("wanted SubDomain = $WantedSubDomain");
	   $this -> subDomain= DataObject::get_one('SubDomain','"Title"=\''.$WantedSubDomain."'");
	   $this->mentor = $this->subDomain->Organizer();
	   error_log("Mentor is " . $this->mentor->screen_name);
	   global $userState;
	   $this->RequestedTweet = false;
	   if(isset( $_REQUEST['s'] ) ) {
		   $codedID = $_REQUEST['s'];
		   $tweetID = BigTweet::any2dec($codedID);
		   ERROR_LOGGER("looking for big tweet -- $tweetID");
		   $this->RequestedTweet = DataObject::get_by_id('Tweet',$tweetID);
	   }

	   $loginID = Session::get('loggedInAs');
	   $userState =  self::sessionInfo('UserState');
	   error_log(">>>> User state before = $userState, Log in ID = $loginID");
	   $userState =  self::sessionInfo('UserState',$loginID,($loginID?'loggedIn':'unknown' ) );
	   error_log(">>>> User state after = $userState");
	   switch ($userState) {
	   case 'unknown': $userState = self::sessionInfo('UserState',true,'anonymous');
	   case 'anonymous':
	   	self::sessionInfo('socialiteProfileID',true,0);
	   	self::sessionInfo('loggedInAs',true,0);
		$this->mentee = false;
		$this->profile=Singleton('Profile');
		$this->profile->Name = "unknown user";
	       	error_log("State = anonymous, Got Socialite profile id = NULL" );
		break;
	   case 'mentored':
		   $menteeID = Session::get('socialiteProfileID');
		   $this->profile = DataObject::get_by_id('Profile',$menteeID); 
		   $this->mentee = TweetUser::getTweetUser($this->profile->Name);
		   if ( ! $this->profile->MemberID) {
			   // the user does NOT have an existing account with us
			   error_log("State = mentored, Got Socialite profile id = " . $menteeID . " " . $this->mentee->screen_name );
			   break;
		   }
		   $member = DataObject::get_by_id('Member', $this->profile->MemberID);
		   $member->logIn();
		   $userState = self::sessionInfo('UserState', true, 'loggedIn');
		   break;
	   case 'loggedIn':
		   $this ->profile= DataObject::get_one('Profile', 'MemberID=' . $loginID);
		   //Debug::show($this->profile);
		   if ($this->profile){
		   $this->mentee = TweetUser::getTweetUser($this->profile->Name);
		   self::sessionInfo('socialiteProfileID',true,0);
		   } else {
		      /* not logged in, stale cookie! */
	   	self::sessionInfo('socialiteProfileID',true,0);
	   	self::sessionInfo('loggedInAs',true,0);
		$this->mentee = false;
		$this->profile=Singleton('Profile');
		$this->profile->Name = "unknown user";
	       	error_log("State = anonymous, Got Socialite profile id = NULL" );
		   }
		   break;
	   default:
		   error_log("unknown State = $userState" );
		   Debug::show($_SESSION);
		   die();

	   }
	   $sesslog = Session::get('loggedInAs');
	       	error_log(">>>> State = $userState, Member ID = ". $this->profile->MemberID ." name=". $this->profile->Name . " session login is ". $sesslog);
      
/*
	    if (!$this->profile instanceOf Profile) {
		    if (!Session::get("request_token")) {
			    // if we are not in process of getting a token from twitter,
			    //  conclude that we have logged out as an old user
			    //  and need to destroy the old session info
			 session_destroy();
			 session_start();
			 $_SESSION = array();
		    }
		 $this->profile = Singleton('Profile');
	    } 
*/

	$pid= $this ->profile->ID;
	$ip_addr = $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR') ?$_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
	error_log($this->ClassName . " Got ProfileID = #".(int)$pid . "  IP = $ip_addr ". $this->extraLogMsg);

	$this->not_ajax = ! Director::is_ajax();
       if($this->not_ajax) {	
	  error_log("Requireing javascript");
	       Requirements::javascript('mysite/javascript/jquery-1.3.2.js');
	      Requirements::block(THIRDPARTY_DIR. '/jquery/jquery.js');
	      Requirements::javascript(THIRDPARTY_DIR . '/jquery/plugins/form/jquery.form.js');
	      if($this->requiresTabAction){
		   Requirements::javascript('mysite/javascript/tools.tabs-1.0.1.js');
		      View::wrapJava('$("ul.tabs").tabs("div.panes > div");');
		      Requirements::css('mysite/css/tabs.css');
		      Requirements::css('mysite/css/tweets.css');
	      }
	      if($this->requiresTweetAction) {
		      self::RequireTweetAction();
	      }
       }
   }
	function myHostName() {
		$absoluteLink = Director::protocolAndHost();
		preg_match("/\/\/([^\/?]+)/", $absoluteLink, $matches) ;
		return $matches[1];
	}

   static $session_defaults = array ('filter' => "", 'limit' => 25, 'tweet_sort' => 'newest', 'user_sort' => 'newest_tweet');

   /*
    *  sessionInfo, get and set information in session variables
    *  get the session variable, or set it to a default value and return that
    */
   static function sessionInfo($what,$setNewValue=false,$nvalue = false) {
      if ($setNewValue) {
	 session::set($what,$nvalue);
      }
      $value=session::get($what);
      error_log("Session info for $what is |$value|");
      if (!$value) {
	 if(isset( self::$session_defaults[$what] )) session::set($what, $value=self::$session_defaults[$what]);
	   else session::set($what,$value= $nvalue);
      }
      return $value;
   }

  /* get a new pen name authority from  Twitter.  First we put this link into the view.
   * it links to the newPenName routine of this controller
   */ 
	function newPenNameForm ($text = "Get Additional Pen Name Authority from Twitter") {
		return "<a href=' " . $this->Link("newPenName") . "' >$text</a>";
	}
   /* The user has clicked on a link and requested a new authorization code
    *  send the user off to twitter to gain an access token. The user will access the ProfilePage with 
    *  new credentials, we then will splice the new pen name into their profile data 
    */
	function xxxnewPenName(){
		global $consumer_key;
		global $consumer_secret;
		Session::set('pre_auth',$_SERVER['HTTP_REFERER']);
		include_once('auth/twitterOAuth.php');
		$askForAuth = new TwitterOAuth($consumer_key, $consumer_secret);
		$newCredentials = $askForAuth -> getRequestToken();
		Session::set('request_token', $newCredentials['oauth_token'] );
		Session::set('request_token_secret', $newCredentials['oauth_token_secret'] );
		Director::Redirect($askForAuth -> getAuthorizeURL($newCredentials['oauth_token']));
	}


	/**
	 * New queries are created by this form
	 */

	function newPenName(){ //phase one
	error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		// The user has clicked on a link and requested a new authorization code
		global $consumer_key;
		global $consumer_secret;
		Session::set('pre_auth',$_SERVER['HTTP_REFERER']);
		include_once('auth/twitterOAuth.php');
		$askForAuth = new TwitterOAuth($consumer_key, $consumer_secret);
		$newCredentials = $askForAuth -> getRequestToken();
		if (!isset($newCredentials['oauth_token'] ) ) {
			Debug::show($askForAuth);
			print_r($askForAuth);
			die();
		}
		Session::set('request_token', $newCredentials['oauth_token'] );
		error_log('Setting request_token as ' . $newCredentials['oauth_token'] );
		Session::set('request_token_secret', $newCredentials['oauth_token_secret'] );
		Director::Redirect($askForAuth -> getAuthorizeURL($newCredentials['oauth_token']));
	}

	function existingQueryForm() {
		if (!Permission::check('QUERY_WATCHER') ) return "Socialite accounts may not create queries";
		$drop = $this->profile -> TwitterQueries();
		if (!$drop->exists()) return "No queries yet";
		//$drop = DataObject::get('TwitterQuery');
		$dropGroup = $drop -> groupBy('Title');
		$map = array();
		foreach ($dropGroup as $key => $set ){
			$id = $set-> first() ->ID;
			$map[ (string)$id ] = $key ;
		}
		//$map = $dropGroup-> toDropDownMap('ID','Title');
		asort($map);

		$fields = new FieldSet( new DropdownField("TwitterQueries", "Existing Queries", $map  ));
		$actions = new FieldSet( new FormAction('showQueries', 'Existing Queries'));
	  	return new Form($this, "existingQueryForm", $fields, $actions);
	}

	function showQueries($data, $form){
		$key=($this->request->requestVar('TwitterQueries'));
		if($key==0) { return $this->index(singleton('HTTPRequest'));}
		$tag=TwitterQuery::getExistingQuery($key)->mysetQueryTag()->forUrl();
		return  Director::redirect($this->RelativeLink("tag/$tag"));
	}
	

// handle the 'logout'  link on the top right of the page 
	function logout () {
		ProfileLoginForm::logout(false);
		Director::redirect("");
	}

	function userState($which) {
	   global $userState;
	   return $userState == $which;
	}
	function security($data) {  _e("this is not a good idea"); Director::redirect('Security/login'); }

	function ProfileLoginForm() {

	   global $userState;
	   switch ($userState) {
	      default:
	      case 'anonymous':
		    $name = $this->mentor->Salutation;
		     $form=  "Hello $name, have we met?"
		  ."  <a href='" . Director::baseURL() 
		  ."Security/login" . "'>Login?</a>";
		 break;
	      case 'loggedIn':
		   $name = $this->profile->Name;
		   $form= "Welcome back, $name |<a href='"
		     . Director::baseURL() . "user-profile"
		     . "'>Your Profile</a>|<a href='".Director::baseURL()
		     .'home/logout' ."'>Logout.</a>";
		 break;
	      case 'mentored':
		  $name = $this->profile->Name;
		  $form=  "Hello $name, would you like to <a href='"
		     . Director::baseURL() . "sign-up'>Sign up</a>"
		     . "|<a href='".Director::baseURL()
		     .'home/logout' ."'>Logout.</a>";
		 break;
	      default:
	      }
	  return $form;
	}

	
	/**
	 * Site search form 
	 */ 
	function SearchForm() {
		$searchText = "";
		$fields =  new FieldSet( new TextField("Title", "Tweet?", $searchText),new DropdownField('limit',"Number Per Page", array(10=>'10',25=>'25',50=>'50',100=>'100'),self::sessionInfo('limit') ) );
		$actions = new FieldSet( new FormAction('results', 'Tweets'));
	  	return new SearchForm($this, "SearchForm", $fields, $actions);
	}


	static function xfer($a1,$x1,$a2,$x2=false) {
		if (isset($a1[$x1]) && $a1[$x1] && $a1[$x1] != "" ) return  $a1[$x1];
		if (is_array($a2)) {
			if ($x2) return $a1[$x1] = @$a2[$x2];
		}
		return $a1[$x1]= @$a2;
	}

	function reformSearch($dataArray) {
		$Title= self::xfer($dataArray,'Title',$dataArray,'query');
		$query= self::xfer($dataArray,'query',$dataArray,'Title');
		$search = array();
	       $search['query']=$search['author_name'] = $query;
		// fill my local database from twitter
		return $search;
	}

	/**
	 * Process and render search results
	 */
	function results($data, $form){
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		$values = $form->getData();
		$limit = self::sessionInfo('limit',true, $values['limit'] );
		$restrict =array('start'=>$start,'limit'=>$limit);
		$data = $this->reformSearch($data);

		$context = $this->getCustomSearchContext();
		$query = $context->getQuery($data,null,$restrict);
		$records = $context->getResults($data,"Created DESC",$restrict);
		if($records) {
			$rowcount= $query->unlimitedRowCount();
			$records->setPageLimits($start,$limit,$rowcount);
		}

		$data['Search']= $data['query'];
	  	$data = array(
			'Tweeties' => $records,
			'Query' => $form->getSearchQuery($data),
			'Title' => 'Tweets Results'
			,'Tag' => $form->getSearchQuery($data)
	  	);

		$custom =$this->customise($data);
		$result = $custom->renderWith(array('Tag_results', 'Page'));
	  	return $result;
	}

	function getCustomSearchContext () {
		// tell the sapphire search mechanism we are searching for 'tweet' objects
		$tweetEntity = singleton('Tweet');

		$fields = $tweetEntity ->scaffoldSearchFields(array(
			'restrictFields' => array('Title','author_name'),
		));
		
		$filters = array (
			'query' => new PartialMatchFilter('Title'),
			'to' => new StartsWithFilter('Title'),
			'author_name' => new PartialMatchFilter('author_name'),
		);
		
		$the_context= new SearchContext(
			$tweetEntity->class,
			$fields,
			$filters
		);
		$the_context->connective = 'OR';
		return $the_context;
	}
	var $userInfo=array('screen_name', 'location','description','url','twitter_id','last_tweet','created_at','friends_count','followers_count','statuses_count','profile_image_url');
	var $tweetInfo=array('Title', 'published','recipient_screen_name','StatusID');
	private function grab_some($what, $converter ,$filter,$input,$count=1,$sort=""){
		$result=array();
		$t = DataObject::get($what, $filter . '"'. Convert::raw2sql($input) . '"',"",$sort,$count);
		$converter=$this->{$converter};
		if ($t -> exists()) foreach($t as $s) {
					$info=array();
					foreach($converter as $key) {
						$info[$key] = $s->{$key};
					}
					$result[] = $info;
				}
		return $result;
	}
	function asjs(){
		$screen_name=($this->request->requestVar('screen_name'));
		$result=array();
		if($screen_name) {
			$ua = $this->grab_some('TweetUser','userInfo','`screen_name`=', $screen_name);
			if($ua) {
			       	$result = $ua[0];
				$tweetcount=($this->request->requestVar('sent'));
				if($tweetcount) {
					$result['sent'] = $this->grab_some('Tweet','tweetInfo','`author_name`=',$screen_name, $tweetcount);
				}
				$tweetcount=($this->request->requestVar('to'));
				if($tweetcount) {
					$result['to'] = $this->grab_some('Tweet','tweetInfo','`Title` LIKE','@'.$screen_name. '%', $tweetcount);
				}
				$tweetcount=($this->request->requestVar('specific'));
				if($tweetcount) {
					$results['specific'] = $this->grab_some('Tweet','tweetInfo','recipient_screen_name=',$screen_name, $tweetcount);
				}
			}
		}
		
		echo(json_encode(array('user'=>$result)));
		FormResponse::add(json_encode($result));
		FormResponse::respond();
	}

	function follow($data) {
		if( !Director::is_ajax() ) return "";
		$name = $data->param('ID');
		$boss = PenName::getSessionPenName();
		if(!$boss) 
			return("jQuery('span[class*=who__{$name}]').text('PenNameError');");
		$tweet=$data->requestVar('tweet');
		$boss->follow($name,$tweet); // can only tweet those who follow me!
		return("jQuery('span[class*=who__{$name}]').text('Unfollow').removeClass('canFollow').addClass('alreadyFollow');");
	}

	function actOn($data) {
		//ERROR_LOGGER("acton Active");
		//	if( !Director::is_ajax() ) return "";
		$tweetID = $data->param('ID');
		//ERROR_LOGGER("acton Active ID=" . $tweetID);
		$tweet=DataObject::get_by_id('Tweet',$tweetID);
		//ERROR_LOGGER("ready to acton Active ID=" . $tweetID);
       //   $tweet->actOn();
		//ERROR_LOGGER("acted Active ID=" . $tweetID);
		//FormResponse::add( "jQuery('span[class*=acton__{$tweetID}]').text('Acted');");
		FormResponse::add( "jQuery('span[class*=acton__{$tweetID}]').find('a').text('Acted');");
		return FormResponse::respond();	
	}

static function RequireTweetAction(){
   Requirements::javascript('mysite/javascript/hoverIntent.min.js');
   Requirements::javascript('mysite/javascript/maxlength.js');
   if(Director::isLive() && ! Session::get('notrack') =="true" ) {
      //Requirements::javascript('mysite/javascript/waterfall.min.js');
      Requirements::javascript('mysite/javascript/waterfall.js');
   } else {
      Requirements::javascript('mysite/javascript/waterfall.js');
   }
}


	function TweetBox () {
		ERROR_LOGGER("entering TweetBox");
		$penNameID = Session::get('penName');

	  	$theForm =  new TweetBoxForm($this, "TweetBox");
	  	return $theForm;
	}

	function changePenName($data) {
		$name = $data->param('ID');
		$pen = PenName::getSessionPenName( $name,true);
		//FormResponse::add("submitted");
		return FormResponse::respond();	
	}

	function smbtTweet ( $data, $form) {
		ERROR_LOGGER("in  submit tweet");
	     $id = Session::get('loggedInAs');
		if (! $id) {
	       $t = Session::get('socialiteProfileID');
	       if(!$t){
			FormResponse::add("Please sign up to send tweets");
			return FormResponse::respond();
			}
		}
		$data = $form->getData();
		$pen = PenName::getSessionPenName( $data['penName']?$data['penName']:false,true);
		$penNameID = $pen->ID;
		$tag=$data['tweet'];

		Director::set_status_code(200);
		$response = 'submitted';

		switch($data['routing']) {
		case 'follow': {
		     $name=$data['addressee'];
		     ERROR_LOGGER("follow $name by user " . $pen->screen_name);
		     $response=$pen->followEZ($name);
		     switch($response) {
		     case 2: $response = "Twitter says try again soon";
			     break;
		     case 3: $response = "This user is blocked or unavailable";
			     break;
		     case 4: $response = "You need to re-authorize tweeparty as your agent";
			     break;
		     default: $response = "You are now following " . $name;
		     }
		     ERROR_LOGGER("follow $name by user " . $pen->screen_name);
		     break;
		     }
	       case 'unfollow': {
		     $name=$data['addressee'];
		     $response=$pen->unfollow($name);
		     $response = "You are no longer following $name";
		     break;
		     }
		case 'block': {
		     $name=$data['addressee'];
		     ERROR_LOGGER("combo block/unfollow $name by user " . $pen->screen_name);
		     $pen->block($name);
		     $response = "You have severed connections with $name";
		     break;
		     }

		case 'FollowAll': {
			$response .= 'now following ' .$this ->followFriday($data,$form);
			break;
		}
	       case 'tweet' : {
		   if($tag &&  $penNameID) TwitterIt::TweetOut($tag, $penNameID, $data['addressee'] );
		   break;
		  }
	       default: {
		  $response = "unknown routing code (" . $data['routing']. ")";
		  }
		}
		
		FormResponse::add($response );
		return FormResponse::respond();	
	}

	function followFriday ( $data, $form) {
		$data = $form->getData();
		$tag=$data['tweet'];
		//ERROR_LOGGER(print_r($data,1));
		$boss = PenName::getSessionPenName($data['penName']?$data['penName']:false,true);
		Director::set_status_code(200);
		$response = "no follow";
		if (preg_match_all("/@+(\w+)(\b|$)/", $tag, $matches) ) {
			$response = "#FF";
			foreach ($matches[1] as $newFriend) {
				error_log($boss-> screen_name . " desires to follow |$newFriend|");
			$boss->followEZ($newFriend); // can only tweet those who follow me!
				$response .= ' '.$newFriend;
			}
		}

		return $response;
		//if($tag && $tag !="submitted" &&  $penNameID) TwitterIt::TweetOut($tag, $penNameID);
		FormResponse::add("$response");
		return FormResponse::respond();	
	}


	function block($data) {
		ERROR_LOGGER("block Active");
		if( !Director::is_ajax() ) return "";
		$name = $data->param('ID');
		$boss = PenName::getSessionPenName();
		$boss->block($name);
		ERROR_LOGGER($boss->screen_name . " has blocked = " . $name);
		return("jQuery('span[class*=who__{$name}]').text('Follow').removeClass('alreadyFollow').addClass('canFollow');");
	}


	function unfollow($data) {
		ERROR_LOGGER("Unfollow Active");
		if( !Director::is_ajax() ) return "";
		$name = $data->param('ID');
		$boss = PenName::getSessionPenName();
		$boss->unfollow($name);
		ERROR_LOGGER("unfollowed = " . $name);
		return("jQuery('span[class*=who__{$name}]').text('Follow').removeClass('alreadyFollow').addClass('canFollow');");
	}


	function massUnfollow(){
		$boss = PenName::getSessionPenName();
		//$boss->unfollow($name);
		return("jQuery('span[class*=who__{$name}]').text('Follow').removeClass('alreadyFollow').addClass('canFollow');");
	}

	function noTracking(){
		Session::set('notrack', 'true' );
		Director::redirect($this->Link());
	}

}
