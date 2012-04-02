<?php
// vim:sw=3:sts=3:ft=php:    

class GraphPage extends SiteTree {
   static $db = array(
   );
   static $has_one = array(
   );

   static $required=false;
   function requireDefaultRecords() {
	   if(self::$required) return;
	   self::$required=true;
	   parent::requireDefaultRecords();
	   $home = DataObject::get_one("SiteTree", "URLSegment='home'");
	   if ($home instanceOF GraphPage) return;
	   if ($home instanceOF Page ) {
		   $home = $home -> newClassInstance('GraphPage');
		   $home -> write();
	   }
	   if (!$home) {
		   $home = new GraphPage();
	   }
	   $home ->Title = "Home";
	   $home ->MetaTitle = "Social event of the Season";
	   $home ->MetaKeywords = "twitter,social networking,gossip,talk to frineds";

	   $home ->Content = <<<CONTENT
<ul class="tabs">
<li><a href="#why" class="s">Why</a></li>
<li><a href="#what" class="s">What</a></li>
<li><a href="#how" class="s">How</a></li>
<li><a href="#if" class="s">What If</a></li>
<li class="right"><a href="#news" class="m">New! Follow Quality</a></li>
</ul>
<div class="panes">
<div>
<h2>Do You Hate Missing Out on the Latest?</h2>
<p>Twitter is a social event, like a cocktail party or an afternoon tea.  You will have lots of friends that you want to keep up to date with!</p>
<h3>Discover how to keep up more easily</h3>
<p>The tweeparty is actually a free gateway to Twitter.  Twitter is free, TweeParty is free.  Our goal is to be the most effective web based tool to post and scan your tweets, so you can gather more peeps. If you tweet, or are wanting to find out about the fun of twitter, this will be your home. <a href="home/newPenName">Ready?</a> Or do want to know <a href="#what">more</a>?</p>
</div>
<div>
<h2>Do your like to be in control -- for free?</h2>
<p>That is what TweeParty is: your free control panel for Twitter:  We present the newest and latest action for you right at the top of the screen.</p>
<ul>
<li>Fact: We give you the ability to scan and scroll over all of that just with a move of your mouse.</li>
<li>Fact: we give you the instant, mouse-click power to send, follow,  block and interact with all your contacts with a convenient, all in one control panel.</li>
<li>Fact: you can leave your browser pointing to Tweeparty.com for hours to keep a continuing view and not miss a thing!</li>
</ul>
<p><a href="home/newPenName">Ready?</a> Or do want to know <a href="#how">more</a>?</p>
</div>
<div>
<h2>This Amazing Control is Yours Now With These Simple Steps</h2>
<ol>
<li>Click <a href="home/newPenName">here</a>.  You will go to a Twitter page that ask you to authorize TweeParty to link to your account.</li>
<li>click "Accept" on the Twitter page.</li>
</ol>
<h3>Now You Are in Control of the Amazing Universe of Twitter!</h3>
<p>You will be back on our home page and have a new, instant view of your Twitter friends. Enjoy -- roll your mouse around to see what people are saying -- always the newest is on top. If you want to use a tweet? &nbsp;Simple -- just click &nbsp;on it!</p>
</div>
<div>
<h2>Do You Want More?  More Features, More control?</h2>
<p>We are constantly adding new features to make it more fun and useful!  But we also have a full featured way for you to tailor the panes of your front page.  That's also free to you if you <a href="/sign-up">Sign up</a> as a regular subscriber to Mom's Tweeparty.</p>
<p>We have several levels of membership planned, from casual tweeters, to power tweeters, to business and beyond. Are you ready to start?</p>
<h3>Get Access to this Breakthrough service -- <a href="home/newPenName">Do it now.</a></h3>
</div>
<div>
<h2>What's New --<em>Tweet Quality Follow Factor</em></h2>
<p>Announcing our groundbreaking measure of the likely worth of you following a particular person.  If you want to gain followers, you have the problem of who to follow?  Will they follow you back?  Will they likely chat with you?  Will they Re-Tweet your messages?  You know, some big names get huge number of followers and rarely follow back, or are spam tweetbots that will follow you back, but just waste your time.</p>
<p>Tweeparty has come up with a measure to help you identify the most likely to help you gain popularity and followers.  It's our quality factor that is displayed on any tweeter that has sufficient tweets in our extensive database.  If that quality factor does not show up, you can simply click on the '20' button to get 20 more tweets from that tweet user, and we will compute the new measurement.</p>
</div>
</div>
CONTENT;

	   $home->URLSegment = "home";
	   $home->Status="Published";
	   $home->write();
	   $home->publish("Stage","Live");
	   $home->flushCache();

	   $myPage = DataObject::get_one("SiteTree", "URLSegment='myPage'");
	   if ($myPage instanceOF GraphPage) return;
	   if ($myPage instanceOF Page ) {
		   $myPage = $myPage -> newClassInstance('GraphPage');
		   $myPage -> write();
	   }

	   if (!$myPage) {
		   $myPage = new GraphPage();
	   }

	   $myPage ->Title = "My Page";
	   $myPage ->MetaTitle = "Social event of the Season";
	   $home ->MetaKeywords = "twitter,social networking,gossip,talk to frineds";
	   $myPage -> CanViewType = 'LoggedInUsers';
	   $myPage -> ShowInSearch = false;

	   $myPage->URLSegment = "my-page";
	   $myPage->Status="Published";
	   $myPage->write();
	   $myPage->publish("Stage","Live");
	   $myPage->flushCache();

	   DB::alteration_message("Graph Page installed");
   }

}

class PseudoRelayQuery  {
// a dummy
	function __construct ($action, $kind='local') {
		//error_log("constructing PseudoRelayQuery on " . $action);
		$this -> Authentication = 'none';
		$this -> requestKind = $kind;  // Query or local  (via proxy)
		$this->RequestString = $action;
		return;
	}

	function createAction($username){
		$this -> RequestString = str_replace('#','%23',$this->RequestString);
		if("$username" != '') {
			$this -> Authentication = 'PenName';
		    $this->RequestString =   "http://" . RELAY_HOST . "/relay/" .$this->RequestString. ".json?count=100";
		}
		//Debug::show($this);
	}
};

class GraphPage_Controller extends Tell140Page_Controller {
	var $profile = false;
	var $alsoP = false;
   function init() {
global $WantedSubDomain;
      parent::init();

      if( $this->not_ajax ) {
	      //Requirements::javascript('tell140/javascript/tools.tooltip-1.0.2.js');
	      Requirements::javascript('tell140/javascript/hoverIntent.min.js');
   }
}

function SearchForm() { return ""; } /* no search form on visual page */
function existingQueryForm () { return ""; }
/*
 */
function Queries () {
	error_log("IN Q U E R I E S");
	$refresh = "<div id='refresh'> Refresh Cycle: " . "<form class='refresher'>"
		. "<span class='refresher'><input type='radio' name='refreshx' value='auto'>&nbsp;automatic</span>"
		. "<span class='refresher'><input type='radio' name='refreshx' value='once'>&nbsp;refresh once</span>"
		. "<span class='refresher'><input type='radio' name='refreshx' value='off'>&nbsp;no refresh</span>"
		. "</form>" . "</div>";
	$currentMode = 'LoggedIn';
	if (!$this->profile->ID) { // we are not logged in
		//Debug::show("no profile -- using mentor");
	      $currentMode = 'Attract';
	      $mode = $this->mentor-> Profile() ->getModeByUse($currentMode);
	      //Debug::show($mode);
	      $p = $mode -> Panes("",'"userKey" DESC');
	} else {
	      $p = $this->profile-> getModeByUse('LoggedIn') -> Panes("",'"userKey" DESC');
//Debug::show($this->profile);
	  }	
	if (! $p->exists() ){
	//   Debug::show($this->mentor);
	   $p = $this->mentor -> Profile();
	   //Debug::show($p);
	   if (!$p->ID) $p->write();
	   $p =  $p-> getModeByUse('mentored');
	   //Debug::show($p);
	   if (! $p->ID) $p ->write();
	   //Debug::show($p);
	   $p= $p -> Panes("",'"userKey" ASC');
	   //Debug::show($p);
	//	return "<h1> Please visit your Profile Page and design your Queries Display " . $this->mentor->name ." </h1>";

	}
	$count=1;
	$result ='';
	foreach ($p as $pane ) {
		error_log("Got a pane ID =" . $pane -> ID );
		$payloads = array();
		//$v = $pane->Queries();
		 $queries= $pane->Queries();
		foreach ($queries as $q) {
		       if($this->mentor) $q ->setMentor($this->mentor);
		       if($this->mentee) $q ->setMentee($this->mentee);
			//error_log("Visible ID = ". $q->ID . " request " . $q->requestString());
			if ($who = $q->authorization()){
				$pv = new PseudoRelayQuery($q->requestString(),$q->requestKind);
				$pen = DataObject::Get_by_id('PenName', $q->PenNameID);
				$pv -> RequestString  = 'index/' . $q->ID;
				$pv ->createAction($who);
			} else {
			   // no authentication needed
				$pv = new PseudoRelayQuery($q->requestString().'.json?'. http_build_query($q->requestParams(),'','&'),$q->requestKind);
				//Debug::show($pv);
			//	$pen = DataObject::Get_one('PenName', '"screen_name" = 'hotinhnl'" );
			//	$pv -> RequestString  = 'index/' . $q->ID;
			//	$pv ->createAction($pen->screen_name);
				$pv ->createAction('');
			}

		       $pv -> RelayDebugID = $q->ID;
			$payloads[]=$pv;
		}
	$result .= $this->divContents('Waterfall_'.$count, $this->interpolate($pane->userKey) , $pane->width,$payloads);
	$count++;
	}
	//error_log("Finished building Waterfalls");
	return $refresh. $result;
}

private function divContents($divname,$Title,$sticks,$visuals) {
	$pre = "<div id ='Holder_{$divname}' class='iconBox'>";
	$mid = "<div class='hidden tweetcommand'>" .json_encode($visuals)."</div>";
	return $pre . $mid 
		. "<h2>$Title</h2>"
		. "<div id='$divname' class='stick_holder sticks__$sticks'></div>"
	        . "</div>";
     }

	function repostForm () {
		return new Form ($this, "repostForm",
			new FieldSet( new HiddenField("Response"),
					new HiddenField("Request"))
			,new FieldSet (new FormAction ("addData", "Add Data") ));
	}

	function addData ($data, $form) {
		FormResponse::add("OK");
		return FormResponse::respond();	
	}

	function testContest( $contestTitle ){
		return;
		$oldTests = DataObject::get("Contest", "Title LIKE 'Test%'");
		foreach ($oldTests as $t) $t ->delete();
		$t = new Contest();
		$t -> Title = 'Test Contest - bartender';
		$t -> MentorID = 1;
		$t -> CutOff = "2009-10-23 00:00:00";
		$t -> StartTime = "2009-10-13 00:00:00";
		$t -> location = "hnl";
		$t -> keywords = $contestTitle;
		$t -> query = 'Best Test';

		$t -> write();
		$v = $t -> Votes();
		$voters = DataObject::get('Tweet', "author_name LIKE '%p%'");
		$lastID = 33;
		foreach ($voters as $voucher) {
			$aUser = DataObject::get_by_id('TweetUser',$lastID);
			$vote = new Vote();
			$vote -> Data = $voucher-> author_name . " votes  for ". $aUser->screen_name . " as $contestTitle";
			$vote -> TweetID = $voucher->ID;
			$vote -> ContestantID = $lastID;
			$vote -> VoterID = $lastID = $voucher-> TweetUserID;
			$vote-> write();
			$v -> add($vote);
		}
		return $this;

	}
}
