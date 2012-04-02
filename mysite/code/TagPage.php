<?php

class TagPage extends SiteTree {
	
	public static $db = array(
	);
	
	public static $has_one = array(
	);
	
}

class TagPage_Controller extends ContentController {
	
	public function init() {
		parent::init();
		Tag::RequireTagDrop();
		// Note: you should use <% require %> tags inside your templates instead of putting Requirements calls here.  However
		// these are included so that our older themes still work
		Requirements::themedCSS("layout"); 
		Requirements::themedCSS("typography"); 
		Requirements::themedCSS("form"); 
	}
	
	/**
	 * Site search form 
	 */ 
	function SearchForm() {
		$searchText = "";
		$fields = singleton('TwitterQuery')->getSearchFields($searchText);
		$actions = new FieldSet(
			new FormAction('queries', 'TwitterQueries')
			,new FormAction('results', 'Tweets')
			);
	  	return new SearchForm($this, "SearchForm", $fields, $actions);
	}
	
	/**
	 * Process and render search results
	 */
	function results($data, $form){
		$start = ($this->request->getVar('start')) ? (int)$this->request->getVar('start'):0;
		$limit = 25;
		$restrict =array('start'=>$start,'limit'=>$limit);
		Director::set_environment_type('dev');
		$protoTweet = singleton('TwitterQuery');
		$data = $protoTweet->reformSearch($data);

		error_log("search reformed");
		$context = $protoTweet->getCustomSearchContext();
		error_log("custom search reformed");
		$query = $context->getQuery($data,null,$restrict);
		error_log("query got");
		$records = $context->getResults($data,"Created DESC",$restrict);
		error_log("results got");
		if($records) {
		error_log("records!");
			$rowcount= $query->unlimitedRowCount();
		error_log("row count!");
			$records->setPageLimits($start,$limit,$rowcount);
		error_log("page limit!");
		}
		error_log("search ready,  now searching");

	  	$data = array(
			'Results' => $records,
			'Query' => $form->getSearchQuery(),
			'Title' => 'Tweets Results'
	  	);

		error_log("got search,  now customizing");
		$custom =$this->customise($data);
		error_log("customized");
		$result = $custom->renderWith(array('Tweet_results', 'Page'));
		error_log("finished");
	  	return $result;
	}

	
	/**
	 * Process and render search results
	 */

	function queries($data, $form){
		error_log("in queries");
		Director::set_environment_type('dev');
		$tag=($this->request->getVar('TwitterQueries'));
		if($tag==0) { return $this->index(singleton('HTTPRequest'));}
		error_log("queries tag Id -" .$tag);
		$tag=TwitterQuery::getExistingQuery($tag);
		error_log("queries tag Id -" .$tag-> TagText);
		//Director::redirect("tag/index/" . $tag->Title);
		return $this->index($tag->Title);
	}


	function index($data =false) {
		if (is_string($data) ) $name = $data;
			else $name = $data->param('ID');
		Director::set_environment_type('dev');
		$tag=& Tag::getTagByName($name);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		$start = ($this->request->getVar('start')) ? (int)$this->request->getVar('start'):0;
			 // susan boyle will blow the memory so we limit it
		$Tweets = ($tag)?  $Tweets = $tag->Tweets('','','',$start . ",25"):singleton('ComponentSet');
		$Tweets -> setPageLength(25);
		$data = array('Tag'=> $tag, 'Tweeties' => $Tweets,  'Title' => 'Tags of a ' . $name . ' kind') ;
	  	return $this->customise($data)->renderWith(array('Tag_xxresults', 'Page'));
	}

	function dropTag($data) {
		//print_r($id);
		if(!Director::is_ajax() ) return "";
		$name = $data->param('ID');
		$tag=& Tag::getTagByName($name);
		if (!$tag) $tag =& Tag::getTagByName("illegal Tag");
		$erasedID=$tag->erase();
		return("jQuery('span[class=tag_{$erasedID}]').remove();");
	}

	/**
	 * restful access to tweets
	 */
	
}
