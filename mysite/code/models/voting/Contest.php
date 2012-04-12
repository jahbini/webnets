<?php
class Contest extends SearchRelayQuery {
	static $db = array('StartTime' => 'Datetime' , 'CutOff' => 'Datetime');
	static $has_many = array ('Votes' => 'Vote');
	static $has_one = array('Organizer' => 'Organizer');

	function processNewTweet(Tweet $t) {
		/*
		 * at this point we really know that this tweet is a possible vote for this
		 * contest.  AddVote actually searches for some "possible" contest -- that's a bit
		 * of overkill, I think.
		 */
		self::addVote($t);
		return;
	}
	function score() {
		/* we need all the votes by the voters, but only the most recent */
		$innerQuery = new SQLQuery();
		$innerQuery -> select = array( "v.ContestantID as Cid","Max(tw.published)");
		$innerQuery -> from = array('Vote v JOIN Tweet tw ON v.TweetID = tw.ID');
		$innerQuery -> groupby = array('v.VoterID');
		$innerText = $innerQuery -> sql();

		$sqlQuery = new SQLQuery();
		$sqlQuery -> select = array( 'tu.screen_name who' , 'COUNT(Tu.ID)  votes' );
		$sqlQuery -> from = array( '(' . $innerText . ') AS summary JOIN TweetUser tu');
		$sqlQuery -> where = array('tu.ID = Cid');
		$sqlQuery -> groupby = array("tu.ID");
		$sqlQuery -> orderby = "votes DESC";
		$sqlQuery -> limit = 10;

		$result = $sqlQuery->execute();
		
		$html = "<ol>";
		foreach ($result as $r) {
			$html .= "<li>" . $r['who'] . " with " . $r['votes']  . "</li>";
		}
		return $html . "</ol>";
	}
	/*
	 * addVote parses the tweet and adds it into the proper contest and vote classes
	 */
	static function addVote(Tweet $t) {
		$m = preg_match('/^\b*@([a-z]+)\b+#vote4_([a-z]*)\b+@([a-z_]+)\b+(.*)$/i', strtolower($t->Title),$matches);
		if (!$m) return;

		/* $match [0] whole match
		 * $match[1] == Organizer screen name (without @)
		 * $match[2] == contest name (without vote4_)
		 * $match[3] == cantidate (without @)
		 * $match[4] == bar, drink, etc
		 */

		$Organizer = DataObject::get_one('Organizer', 'screen_name=' . $match[1]);
		if (! $Organizer instanceOf Organizer) return;  // no Organizer, no contest

		$contests = DataObject::get('Contest', 'Title=' . $match[2] );
		if (!$contests) return; // no Contest ID, no contest

		$found = false;
		foreach($contests as $contest) {
			if ($contest -> OrganizerID == $Organizer -> ID) {
				$found = $contest;
				break;
			}
		}

		if (! $found ) return;   // do we have a matching contest?
		if ( strtotime($contest -> CutOff) < strtotime($t -> published) ) return; //published after the cutoff
		$v=$contest->Votes();
		$nv = new Vote();
		$nv -> Tweet = $t;
		$nv -> voterID = $tweet -> TweetUserID;
		$nv -> contestant = TweetUser::getTweetUser($match[2]);
		$nv -> Data = $match[4];  // add in the bar, drink, etc
		$nv -> write();

		$v->add($nv);
		$v->write();
	}

}
