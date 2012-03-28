<?php
class UserQueryPage extends SiteTree {
   static $db = array(
   );
   static $has_one = array(
   );
}

/* set up a separate page to enter/edit a User's query
 * since Twitter is rather finiky about the actual
 * format of the request.
 */

class UserQueryPage_Controller extends Page_Controller {
	var $editQueryID = 0;

	function Queries () {
		return $this->profileData->TwitterQueries();
	}
	function newQueryForm () {
		$searchTitle = "";
		$searchRequest = "";
		$searchQuery = "";

		if ($this -> editQueryID ) {
			$t = DataObject::get_by_id('TwitterQuery', $this->editQueryID);
			$searchTitle = $t->Title;
			$searchRequest = $t-> requestString;
			$searchQuery = $t->query;
		}
		$hidden_data=HiddenField::create('existingID');
		$hidden_data->setValue($this->editQueryID);

		$fields =  new FieldSet(
			$hidden_data,
			new TextField("Title", "Display Name", $searchTitle),
			new TextareaField("query", "Twittersearch API string",4,28, $searchRequest),
			new TextareaField("dbQuery", "Local DB search",4,28, $searchQuery)
			);

		$actions = new FieldSet( new FormAction('createNewQuery', 'New Query'));
	  	return new Form($this, "newQueryForm", $fields, $actions);
	}

	//function Link($any="What"){ return "Link -- ? $any and Why are we here?"; }

	function createNewQuery($dataArray,$form) {
		include_once ("utility/searcher/parser.class.php");
		$rawData = $form->getData();
		Debug::show($rawData);
		if($rawData['existingID']) {
			Debug::show($rawData['existingID']);
			$t = DataObject::get_by_id('TwitterQuery', $rawData['existingID']);
		}
		$par = new mysql_parser();
		$elements = $par->atomize($rawData['dbQuery']);
		Debug::show($elements);
		$newQ = $par -> parseToSQL($elements );
		Debug::show($newQ);
		$newQ = $par -> parseToTwitter($elements );
		Debug::show($newQ);
		if ($par->error)     {
		   foreach($parser->log as $msg)   {
				echo "\t-$msg\n";
			}
		}


		die();

		
		$Title= $this->xfer($dataArray,'Title',$dataArray,'query');
		$query= $this->xfer($dataArray,'query',$dataArray,'Title');
		Debug::show($query);

		Debug::show($key);
		$query=TwitterQuery::getTwitterQuery( $key ); 
		$this->profileData->addTwitterQueryWatch($query);
		// fill my local database from twitter
		$tag=$query->mysetQueryTag()->forUrl(); 
		return  Director::redirectBack();
	}


	//index -- simply list all the existing queries and put a query form at the top
	function index() {
		$this -> editQueryID = 0;
	      //return $this->renderWith(array( $this->class, 'TweetForm')); }
		return $this -> renderWith(array('UserQueryPage', 'Page') ) ;
	}

	function edit($data) {
		$this -> editQueryID = $data->param('ID');
		return $this -> renderWith(array('UserQueryPage', 'Page') ) ;
	}
}
?>
