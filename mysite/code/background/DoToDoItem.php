<?php
// vim:sw=3:sts=3:ft=php:    

class DoToDoItem extends Controller {
	public function init() {
		Director::set_environment_type('dev');
		DoToDoItem::setStart();
		@$this->alreadyDone = array();;
		parent::init();
	}
	static $start =false;
	static $urgent_quit = false;

	static function mustDie($msg) {
		error_log("The watchdog will shut down because - $msg");
		self::$urgent_quit = true;
	}
	
	static function setStart() {
		DoToDoItem::$start = time();
		error_log("setting start now");
	}

	static function triggerWatchDog($elapsed=115) {
		if (!(DoToDoItem::$start)) DoToDoItem::setStart();
		return (self::$urgent_quit || time() - DoToDoItem::$start > $elapsed);
	}

	function doItem($item){
		if (!$item) { error_log("To do list empty"); return true; }
		if (!isset( $this->alreadyDone[$item->ID] )) $this->alreadyDone[$item->ID] =0;
		$redundancy = ($this->alreadyDone[$item->ID] +=1);
		if($redundancy >  2) return true;
		if($redundancy == 2) return false;
// temporarily change the class type to DeadToDo so that we will skip a broken todo item
		$className = $item->ClassName;
		$id = $item->ID;
		error_log("To do object " . $item->ClassName . " #" . $id . " action " .$item->operation. " Object # " .$item->ObjectID );
		$result =$item -> activate();
		if (!$result) {
			error_log("To do object " . $item->ClassName . " #" . $id . " removed" );
		} else {
			$item->write();
		//	error_log("To do object" . $item->ClassName . " #" . $id . " retained" );
		}
		return false;
	}

	function debug_it(){
		$fieldList = DB::getConn() -> fieldList('Tag');
		$indexList = DB::getConn() -> indexList('Tag');
		DB::query('DROP TABLE IF EXISTS `myTag` ');
		$mytag=DB::createTable('myTag');
		foreach($fieldList as $f =>$spec) {
			if ($f =="ID") continue;
			DB::createField('myTag', $f, $spec);
		}
		foreach($indexList as $f =>$spec) {
			if ($f =="PRIMARY") continue;
			DB::getConn()->createIndex('myTag', $f, $spec);
		}
		$r= DB::query('INSERT INTO `myTag` SELECT * from `Tag` WHERE `Tag`.`Classic`');
		echo("inserted =  $r\n");
	$sql = 'UPDATE Tweet SET LastEdited = (SELECT NOW() FROM myTag JOIN Tweet_SimpleTags WHERE Tweet_SimpleTags.TweetID=Tweet.ID AND Tweet_SimpleTags.TagID = myTag.ID GROUP BY `Tweet`.`ID` )';
		DB::query($sql);
		echo("Finished !");
	}
	function resurrect(){
		$this->doItem(ToDo::getNextToDoItem("DeadToDo")) ;
		echo("Done");
		return "" ;
	}

	function index() {
		//return "";  // JAH to suppress the automatic tweet fetch system
		$alreadyRan=array();
		//echo("hello");
		//$x=DataObject::request('Tweet','ID=3456');
		//echo("hello2");
		//print_r($x);
		//die();
		$c = DataObject::get("TweetGap","gapKind='mid'");
		$c = !$c? 0 :$c->Count();
		error_log("count of mid tweets =$c");

		error_log("tweet todo");
		$this->doItem(ToDo::getNextToDoItem("TweetToDo")) ;
		error_log("internal todo");
		$this->doItem(ToDo::getNextToDoItem("InternalToDo")) ;
	if( DoToDoItem::triggerWatchDog()) return "";
		$this->doItem(ToDo::getNextToDoItem("APIToDo")) ;
		while ( ! DoToDoItem::triggerWatchDog()) {
			// if there is nothing that has been scheduled and reached it 'time' we can escape
			if($this->doItem(ToDo::getNextToDoItem("SearchToDo"))) break ;
		}
		if($c >3) {
			// if we really have run out of things to do,
			// and there is an urgent need then we can dip into the future a bit
			while ( ! DoToDoItem::triggerWatchDog()) {
				if($this->doItem(ToDo::getAnyToDoItem("SearchToDo"))) break ;
			}
		} 
		while ( ! DoToDoItem::triggerWatchDog()) {
			// otherwise just drain the internals
			error_log("internal todo");
			$task =ToDo::getNextToDoItem("InternalToDo");
			if (!$task) break;
			if ($this->doItem($task)) break ; // if we just keep running the same things
		}
		return "";
	}

	function refreshPen(){
		$pens = DataObject::get('UsersPenName');
		foreach($pens as $p) {
			echo("refreshing ". $p->screen_name . "\n");
			$p -> fillFollowers();
			$p -> fillFriends();
		}
	}

	function autoTweet(){
		$accessors = DataObject::get('PenName');
		foreach($accessors as $penUser) {
			$penUser -> autoTweet();
		}
	}
}
?>
