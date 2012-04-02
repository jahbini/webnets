<?php
class Vote extends DataObject {
	static $db = array('Data'=>'VarChar');
	static $has_one = array ('Tweet' => 'Tweet', 'Voter' => 'TweetUser', 'Contestant' => 'TweetUser', 'Contest' => 'Contest');
}
