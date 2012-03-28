<?php

class RelayQuery extends TwitterQuery {
	// static $has_one=array('Profile'=> 'Profile','Mentor'=>'Mentor');  // Mentor is the sponsor, maitre de, and majordomo for the page
	static $defaults = array("requestKind" => "proxy", "authority" => "penName") ;
	static $belongs_many_many=array('Panes' => 'Pane');
	protected $mentee = false;
	protected $mentor = false;
	protected $authenticator = false;

	function __construct() {
		 $args =func_get_args() ;
		 call_user_func_array('parent::__construct', $args );
		//$this->requestKind= 'proxy';
		//$this->authority='penName';
	}

	function ToDoType() {
		return 'APIToDo';  // scheduled on User Authority rate limiting by Twitter
	}


	function setMentee ($m) {
		$this->mentee = $m;
	}

	function setMentor ($m) {
		$this->mentor = $m;
	}

	function forcePenName() {
		//Debug::show("Query ID = ". $this->ID . " authority=" . $this->authority);
		switch($this->authority) {

			case 'none':
			case 'penName':
				if(!$this->PenNameID) {
					$user = TweetUser::GetTweetUser($this->query,false, "TwitterQuery = ". $this->ID);
					//$user = TweetUser::GetTweetUser($this->query);
					$this->PenNameID=$user->ID;
					$this->write();
					$q=$user->screen_name;
					$this ->authenticator = $this->user;
				} else {
					$q= $this ->authenticator = $this->PenName();
					$q=$q->screen_name;
				}
				break;
			case 'mentor': $q=$this->mentor->screen_name;
					$this ->authenticator = $this->mentor;
				break;
			case 'mentee': $q=$this->mentee->screen_name;
					$this ->authenticator = $this->mentee;
				break;
			case 'query': $q=$this->query;
					$user = TweetUser::GetTweetUser($this->query);
					$this ->authenticator = $this->user;
				break;
		}
		return $q;
	}

	function Authenticate() {
		if($this->authority == 'none') return false;
		$this->forcePenName();
		$this->authenticator;
	}

	function requestString(){
		$s= $this->requestString;
		if ($this->mentee) {
			$s = str_replace('#mentee#',$this->mentee->screen_name, $s);
		}
		if ($this->mentor) {
			$s = str_replace('#mentor#',$this->mentor->screen_name, $s);
		}
		return "http://twitter.com/" . $s;
	}

	/*
	 * do any special handling for a new tweet being entered into the system
	 *
	 */
	function processNewTweet(Tweet $t) {
		return;
	}

	/*
	 * grab tweets for all authenticated twitter requests
	 */
	function grabMoreTweets($param=array() ) {
		$this -> nurse = new rqNurse($this);
		$range = $this->range;
		$range->reschedule = $this->nurse->preSchedule();
		if($range->reschedule > 30 ) return $range;
		$q= $this->requestString();
		if($this->debug) error_log("grabbing tweets for query " . $q . ' Type= '. $this->ClassName);
		$this->forcePenName();
		$range = $this->fillTweets($param,$this->requestString(), $this->Authenticate() );
		$range->reschedule = $this->nurse-> postSchedule();
		$this -> TotalTweets += $range->accepted_tweets;
		$this->write();
		return $range;
	}

	
	/*
	 * Go get some tweets using the search API.
	 * Stuff them into the DataBase.
	 * This code is set to use the 'nurse' class rather than SilverStripe's
	 * Restful class
	 */
	function fillTweets($inp=array()) {
		// on rate limiting -- the search API does not use any limiting strategy as of July 14, 2009
		//  this may change if and when Twitter updates the code
		//$this -> range=new TweetRange;
		$range= $this->range;
		$this->setDebug(false);

		$inp['routine'] = __METHOD__;
		$jsdata = $this -> nurse -> go_to_twitter( $this->requestParams($range->setParams($inp)));
		$msgs = json_decode($jsdata);

		$range->requestOK();
		$elapsedTime = time();

		$range-> contents = $contents = $this->clean_all($msgs);

		$elapsedTime = time() - $elapsedTime;
		DoToDoItem::triggerWatchDog();
		if(isset($contents->results) ) $tweetsProcessed = count($contents->results);
		  else $tweetsProcessed = count($contents);
		
		$log_msg = "processed $tweetsProcessed in $elapsedTime seconds";
		$this->containsRange($range); // update and write the range in this twitterQuery
		$range->log_me($this->query); // enter the log message
		$range->log_me($log_msg);
		return $range;
	}


	function fillTweetsFromAPI($params=array(),$service) {
	$debug_hot=false;
		//$this -> range=new TweetRange;
if($debug_hot) error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		if ($penName = $this->Authenticate() ) {
			$scheduler = $penName -> get_scheduler();
		} else {
			$scheduler = IPScheduler::get_IPscheduler();
		}
		$s=$scheduler->schedule();
		if ($s>0) {
			$this->range -> setMessage("Twitter scheduler says to wait until unix time $s");
			$this->range -> reschedule =$s;
			return $this->range;
		}
if($debug_hot) error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
		$service .= '.json';
		error_log("getting API sent tweets SERVICE = " . $service);
		$twitter = new SaneRest($service );
		
		$range=$this->range;
		$params['routine'] = __METHOD__;
		$params = $range->setParams($params);
		$params['count'] = 100;

		$twitter->setQueryString($params);
		$author = $this->Authenticate();  // returns false or a penName
		if($debug_hot) error_log($twitter->getQueryString(). ' Authentication =' . ($author?$author->screen_name:'None Needed'));
		$range ->setRequest($service,$twitter->getQueryString() );
		if ($author) { $twitter->authenticate($author); }
		$conn = SaneResponse::makeSane($jsdata=$twitter->request());
if($debug_hot) error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );
   $this->range->reschedule  = ($scheduler-> request_complete($twitter->returnHeaders())) ; 
		$response = $conn->analyze_code();
		if ($response) return $range;  // it did not work
		$jsdata = $jsdata->getBody();
		$msgs = json_decode($jsdata);

if ($debug_hot) error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		$tag = $this->mysetQueryTag();

		$twitter_id=0;
		$range->requestOK();
		$tweetsProcessed = 0;
		$elapsedTime = time();
		if ($msgs && is_array( $msgs)) foreach  ($msgs as $entry) {
if ($debug_hot) error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ . " TAG = ". $tag->ID );
			$u = $entry;
			$tweet = $entry;
			if (isset($entry->status ) ) {
				//this is a user first API method
				$tweet = $entry->status;
			}
		       if (isset ($entry->user) ) {
				$u = $entry->user;
			}
			if( DoToDoItem::triggerWatchDog()) {
				//error_log("Break on watchdog");
				break;
			}
			// process the tweet
		//	if(!isset($tweet->id) || ! $tweet->id || !isset($u->screen_name) || ! $u->screen_name) {
		//		if($debug_hot) error_log(print_r($entry,1));
		//		continue;
		//	}
			if(isset($tweet->id) &&  $tweet->id && isset($tweet->text) ) {
			$t=Tweet::getTweet($tweet->id,$tag,true);
			$t->tagMe(Tag::getTagByName($this->ClassName . " " .$this->ID));
			//error_log("new Tweet");
			$tweetsProcessed += 1;
			if ($t -> fromDB == false && !$t->apiTweet) { // this tweet needs updating,  and author processing
				$t->apiTweet = true; // we have done the full user proceessing on this guy
				$t->Title      = $tweet->text;
				if(!isset($u->screen_name) || !$u->screen_name) {
					error_log("BAD ENTRY");
					error_log(print_r($entry,1));
					$t->author_name = "unknown";
				} else 
					$t->author_name= $u->screen_name;
				$t->published  = $tweet->created_at;
				//error_log("new Tweet writing");
				$t->write();
				//error_log("new Tweet written");
				}
			} else $t = false;
			if(isset($u->screen_name) && $u->screen_name) {
				$new_friend=& TweetUser::getTweetUser($u->screen_name);
				if($t) $new_friend-> setLastTweet($t->published); 
				if($new_friend -> unCached) {
					//error_log("new user");
					$u->twitter_id = $u->id;
					unset($u->id);
					PleaseMap::object2object($new_friend,  TweetUser::$friendships_create, $u);
					$new_friend->write();
				}
			//error_log("new user written");
			}
			if ($t){ if($range ->containsTweet($t)) { break; }
			}
		}
if($debug_hot) error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ );

		$this->containsRange($range); // update and write the range in this twitterQuery
		$range->log_me("User query - ".$this->query); // enter the log message
		$elapsedTime = time() - $elapsedTime;
		$log_msg = "processed $tweetsProcessed in $elapsedTime seconds";
		error_log($log_msg);
		$range->log_me($log_msg); // enter the log message
		
//error_log("in " . __CLASS__. " Method " . __METHOD__ . " Line=" . __LINE__ ." return");
		return $range;
	}


	static function getRelayQuery( $key, $forceUpdate=false ) {
		$range=false;
		$Title=$key['Title'];
		if (!isset($key['query']) ) $key['query'] = $Title;
		$query = $key['query'];
		//print_r($key); //JAH
		$q = DataObject::get_one('TwitterQuery', "`Title`='". Convert::raw2sql( $Title) ."'");
		if ($q && ($q->lowestID === 0 || $q->highestID === 0) ) {
			$q->delete();
			unset($q);
			}
		//promote the class to the proper type
		//Get the ClassName -- it is the index to the menuJson dataStructure
		$classtype = 'RelayQuery';
		if(isset($key['qAttr'] ) ) {
			$classtype = $key['qAttr'];
		}
		if($q && $q->ClassName != $classtype) {
			$q = $q -> newClassInstance($classtype);
			$forceUpdate=true;
		}
		if (! $q) {
			$q= new RelayQuery($key);
			$q -> write();
			$q->setDebug(false);
			$q->mysetQueryTag( );
			$forceUpdate=true;
		}
		// the following can take a long time on query creation
		// so it is driven by the polling background system
		if($forceUpdate) {
			$q-> castedUpdate($key);
			$q->forceChange();
			$q -> write();
		}
		$q->insureInitialGap();
		return $q;
	}
}
error_log("RelayQuery Finished S O U R C I N G");
?>
