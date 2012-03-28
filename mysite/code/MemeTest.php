<?php
// vim:sw=3:sts=3:ft=php:    

class MemeTest extends Controller {
	public function init() {
		Director::set_environment_type('dev');
		parent::init();
	}
	public function extractTest() {
		include_once("Tag.php");
		$myresult = Tag::extractTags("wow,@#@#@auser #funnyguy@user#funnyguy eabout things are looking up the la la #US");
		$tagSet=$myresult;
		Debug::Show($tagSet);
		print_r($tagSet->column() );
		return "";
	}
	public function deleteTag($tagname) {
		$tagtext=$tagname->param('ID');
		$tag =&  Tag::getTagByName($tagtext);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		$components = $tag->getManyManyComponents('Tweets');
		$components->removeAll();
		$tag -> delete();
		echo("Done!\n");
	}

	public function grabsome($tagname) {
		$tagtext=$tagname->param('ID');

		echo("get page!\n");
		$p = new Page_Controller();
		echo("got page!\n");
		$p->show($tagname)->Tweets();
		echo("Done!\n");
	}

	public function showTagTweets($tagname) {
		$tagtext=$tagname->param('ID');
		$tag = Tag::getTagByName($tagtext);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		$tag = $tag->getManyManyComponents('Tweets');
		foreach($tag as $tweet) {
			echo($tweet->Title."\n");
		}
	}
}
?>
