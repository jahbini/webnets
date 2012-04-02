<?php

class TagCloud extends SiteTree {
	
	public static $db = array(
	);
	
	public static $has_one = array(
	);
	
}

class TagCloud_Controller extends ContentController {
	
	public function init() {
		parent::init();

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
		$searchText = isset($_REQUEST['Tweets']) ? $_REQUEST['Tweets'] : 'Tweets';
		$fields = singleton('Tweet')->getSearchFields();
		$actions = new FieldSet(
			new FormAction('results', 'Tweets')
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
		$protoTweet = singleton('Tweet');
		$data = $protoTweet->reformSearch($data);

		$context = $protoTweet->getCustomSearchContext();
		$query = $context->getQuery($data,null,$restrict);
		$records = $context->getResults($data,"Created DESC",$restrict);
		if($records) {
			$records->setPageLimits($start,$limit,$query->unlimitedRowCount());
		}

	  	$data = array(
			'Results' => $records,
			'Query' => $form->getSearchQuery(),
			'Title' => 'Tweets Results'
	  	);
		//print_r($data);
	  	//print_r($this->customise($data)->renderWith(array('Page_results', 'Page')));
	  	return $this->customise($data)->renderWith(array('Tweet_results', 'Page'));
	}

	function index() {
		$result=DataObject::get('Tags',"`Tag`.`ID` = `Tweet_SimpleTags`.`TagID` AND `Tag`.`ClassName`!='Tag'","COUNT(`TagID`) DESC","`Tweet_SimpleTags`","100"); 
		print_r($result);
		die();
	}

	/**
	 * restful access to tweets
	 */
	
}
