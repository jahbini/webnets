<?php
// vim:sw=3:sts=3:ft=php:    
//

class Tag extends DataObject {
	static $db = array ( 'TagText' =>'Varchar(140)','Kind'=> "Enum('Tag,HashTag,UserTag,DeadTag','Tag')"
		,'NumTweets'=>'Int','Classic'=>'Boolean');
   static $indexes = array( 'TagText' => 'unique (TagText)' ,'Kind' => true, 'Classic'=>true);

   static $belongs_many_many = array ( 'Rankin' => 'TagRank', 'Tweets' => 'Tweet', 'TagGroup' => 'TagGroup');

   static $searchable_fields = array('TagText');

   static $tag_filter = '!able!about!after!again!all!also!and!any!are!bad!badder!baddest!beat!beats!been!before!being!best!better!between!bit!both!but!byte!came!can!cause!change!come!coming!could!cover!coverage!did!differ!different!does!doing!don!down!each!end!even!every!far!few!for!form!found!four!from!get!good!great!had!has!have!help!helped!her!here!him!his!how!hurry!into!its!just!keep!let!many!may!might!more!most!much!must!near!need!never!new!next!not!now!off!old!once!one!only!other!our!out!over!part!put!rush!said!same!say!saw!see!seem!set!she!should!side!some!still!such!take!tell!than!that!the!their!them!then!there!these!they!thing!this!three!through!told!too!tried!two!upon!use!very!was!way!went!were!what!when!where!which!while!who!will!with!would!you!your!';

   static $tagCache = array();

public function forTemplate() {
   return htmlentities($this->TagText, ENT_QUOTES,null, false);
}

static function requireTagDrop(){
	Requirements::javascript(THIRDPARTY_DIR. '/jquery/jquery.js');
	Requirements::javascript(THIRDPARTY_DIR. '/jquery-livequery/jquery.livequery.js');
	View::wrapJava(<<<JS
$('.makeClassic').livequery('click', function() { 
$(this).css({backgroundColor:'beige'});
link=$(this).attr('href');
$.getScript(link);
return false;
 });

$('.dropTag').livequery('click', function() { 
$(this).css({backgroundColor:'beige'});
link=$(this).attr('href');
$.getScript(link);
return false;
 });

JS
);
}

function dropTagLink($class,$page=""){

	error_log("No such thing as dropTagLink");
	dir();
}
function fixTagTweetCount () {
	// this query takes a couple of minutes to run
	//  we should figure a way to halt access to `Tag` while this is being run
	//$sql = 'UPDATE Tweet SET LastEdited = (SELECT NOW() FROM Tag JOIN Tweet_SimpleTags WHERE Tweet_SimpleTags.TweetID=Tweet.ID AND Tweet_SimpleTags.TagID = Tag.ID AND Tag.Classic LIMIT 1 )';
	//$sql = 'INSERT INTO summaryTag SELECT * FROM Tag WHERE Tag.Classic';
	//$sql = 'UPDATE Tweet SET LastEdited = (SELECT NOW() FROM summaryTag JOIN Tweet_SimpleTags WHERE Tweet_SimpleTags.TweetID=Tweet.ID AND Tweet_SimpleTags.TagID = summaryTag.ID AND summaryTag.Classic LIMIT 1 )';
	//  the next request seems to run at one tweet a second.  Do Not run it
	//$sql = "update `Tag` t SET NumTweets = (SELECT COUNT( TagID ) FROM  `Tweet_SimpleTags` WHERE  t.`ID` =  `Tweet_SimpleTags`.`TagID` GROUP BY  `TagID` )";
		// run the query
	//DB::query($sql);
	$sql = "update `Tag` t SET NumTweets = NumTweets + (SELECT COUNT(*) FROM  `Tweet` WHERE  `Tweet`.`Title` LIKE CONCAT('%',t.`TagText`,'%'))";
	DB::query($sql);
	return;
}
static function BigOnes($type,$number=20){
	return  DataObject::get('Tag', '`Kind`="'.$type.'" AND `NumTweets` >0', "`NumTweets` DESC","",$number);
}

public function asLink ($class,$page="") {
   $t = $this -> forTemplate();
   return "<a class=\"linkTag tag_{$this->ID} $class\" title=\"tag {$this->forTemplate} has ". $this->NumTweets. " tweet(s)\" href=\"$page".urlencode(urlencode($t)) ."\">$t</a>";
}
public function forUrl() {
	return urlencode(urlencode($this->forTemplate()));
}

static function realTag($t) {
	return !stristr(Tag::$tag_filter,"!$t!");
}

function addTweet($t) {
	if ($this->Kind == 'DeadTag') return;
	$t->SimpleTags()->add($this);
	return;
}

function myTweets ($start=0,$filter=false) {
	if(!$filter) {
		return  $Tweets = $this->Tweets('',"published DESC",'',$start . ",25");
	} else {
		return  $Tweets = $this->Tweets('`Title` LIKE "% '.$filter.' %"',"published DESC",'',$start . ",25");
	}
}

function makeClassic() {
	$this->setField('Classic',true);
	$this->write();
}
function erase() {
	$w = $this->Tweets('','','',"5"); // don't get all the Tweets, don't need all
	$w->removeAll();
	$id=$this->ID;
	$this->setField('Kind' ,'DeadTag');
	$this->write();
	return $id;
}

function removeTag($name){
	$t = DataObject::get_one('Tag',"`TagText` = '$name'");
	if (!$t) return false;
	error_log("Removing all trace of tag $name , Tag ID=" . $t->ID);
	$t->erase();
	return true;
}

function removeJejuneTags(){
	$jejune = split('!',Tag::$tag_filter);
	foreach($jejune as $untag) {
		if ($this->removeTag( $untag) ) {
			break;
		}
	}
	return $this;
}

static function extractTags($string) {
	//error_log("extracting tags = $string");
	// remove any links from the tweet
	$string = strtolower($string);
	$text = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_~\-\.]*(\?\S+)?)?)?)@', ' #link ', $string);
	$tags = new DataObjectSet();
	if(preg_match_all('/(#|@)+(\w+)(\b|$)/',$text,$matches) ){
		$kind=$matches[1];
		$values=$matches[2];
		for( $i=0; $i<count($kind); $i++) {
			$f=($kind[$i] =='#')?'HashTag':'UserTag';
			$t= Tag::getTagByName($values[$i],$f);
			if(!$t || $t->Kind=='DeadTag') continue; // dont tag with erased tags
			$tags->push($t);
		}
	}
	//error_log("returning tags");
	return $tags;
	}

   static function &getTagByName ($name,$class='Tag') {
      // should use 'get_called_class' not 'get_class'
	$me=false;
      $name = addslashes( trim ( strtolower($name)));
	if ($name == '' || strlen($name) > 100 ||  !Tag::realTag($name) ) return $me;
      $k=substr($name,0,1);
      if ($k=="@") $class = 'UserTag';
      if($class=="UserTag" && $k !='@' ) { $name = '@' . $name; }
      if ($k=="#") $class = 'HashTag';
      if($class=="HashTag" && $k !='#' ) { $name = '#' . $name; }
      if (isset(self::$tagCache[$name] ) ) return self::$tagCache[$name];
      $me = DataObject::get_one('Tag',"TagText = '". Convert::raw2sql($name) ."'");
      if (! $me) {
         $me = new Tag();
	 $me -> setField('TagText', $name);
	 $me -> setField('Kind', $class);
         $me -> write();
         }
      if ($me->TagText == '#filthyanimal') {
	      unset($worse);
	      $bad = $worse;
      }
      self::$tagCache[$name] =& $me;
      return  $me;
   }

   static function getTagSetByNames($names) {
      $ds=new DataObjectSet();
      if (is_string($names) ) $names = explode(',',$names);
      foreach ($names as $n) {
         $ds-> push( Tag::getTagByName($n) );
      }
      return $ds;
   }

}
