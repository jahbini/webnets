<?php
class TwitterIt extends DataObject {
	static $db=array(); // we have no fields of our own, we just need to record that we sent out a tweet
	static $has_one =array("Tweet"=>"Tweet");

	static function TweetOut ($text, $PenNameID, $DestinationName,$now=false) {
		$p= PenName::get($PenNameID);

		if ( !$p ) {
			error_log("NO user for this tweet");
			return;
		}

		$when = strtotime($p->LastTweetOut);
		if ($when + 120 < time() ) $now = true;   // we can send it out immediately
		$tweet = Tweet::newTweet( $text, $p);
		$obj = new TwitterIt();
		$obj->TweetID=$tweet->ID;
		$obj->write();

		$params = array('text'=>$tweet->Title, 'TweetID' => $tweet -> ID,  'PenNameID' => $PenNameID, 'user' => $p->screen_name, 'password' => $p->Password );
		if ($DestinationName) $params['screen_name'] =  $DestinationName; //set as a direct tweet

		if ($now) {
			$obj -> TweetThis( $params);
		} else {
			error_log("scheduling tweet");
			ToDo::addToDoItem('TweetToDo',$obj, 'TweetThis',$params);
			}
		return;
	}

	function TweetThis ($params) {
		$p = PenName::get($params['PenNameID']);
		$theTweet = DataObject::get_by_id('Tweet', $params['TweetID']) ;
	if (isset($params['screen_name']) ) {
		// this is a direct tweet
		$twitter = new SaneRest("http://twitter.com/direct_messages/new.json");
		$twitter -> Authenticate($p);
		$conn = SaneResponse::makeSane( $twitter -> request('','POST', array('screen_name' => $params['screen_name'], 'text'=>$params['text']) ));
	} else {
		$twitter = new SaneRest("http://twitter.com/statuses/update.json");
		$twitter -> Authenticate($p);
		$conn = SaneResponse::makeSane( $twitter -> request('','POST', array('status'=>$params['text']) ));
	}
    		$twitter->returnHeaders();
		$result = $conn->analyze_code(true);
		if($result) {
			error_log("sent twitter message out but reported an error $result " . $conn->getStatus() );
			// return $this;   // we need to do it later
		}
		$p -> LastTweetOut = Date("Y-m-d H:i:s");
		$p->write();
	$body = $conn->getBody();
	$stuff = json_decode($body);
	$theTweet-> StatusID = $stuff->id;
	$theTweet-> published = $stuff->created_at;
	$theTweet -> write();
    
	return false;
	}
}
