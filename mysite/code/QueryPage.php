<?php

class QueryPage extends SiteTree {
	
	public static $db = array(
	);
	
	public static $has_one = array(
	);

	 static $required=false;
	function requireDefaultRecords(){
		if(self::$required) return;
		self::$required=true;
		parent::requireDefaultRecords();
		$pPage = DataObject::get_one("SiteTree", "URLSegment='gapstatus'");
		if ($pPage instanceOF QueryPage) return;
		if ($pPage instanceOF Page ) {
			$pPage = $pPage -> newClassInstance('QueryPage');
			$pPage -> write();
		}
		if (!$pPage) {
			$pPage = new QueryPage();
		}
		$pPage -> CanViewType = 'LoggedInUsers';
		$pPage -> ShowInSearch = false;
		$pPage ->Title = "Query and Gap Status";
		$pPage ->Content = "";
		$pPage->URLSegment = "gapstatus";
		$pPage->Status="Published";
		$pPage->write();
		$pPage->publish("Stage","Live");
		$pPage->flushCache();
		DB::alteration_message("Query and Gap Status Page installed");
	}
	
}

class QueryPage_Controller extends ContentController {
	
	public function init() {
		$this->requiresTweetAction = false;
		parent::init();
		if (!Permission::check('ADMIN')) {
			Director::redirect(BASE_URL);
		}
		if( $this->not_ajax ){
			Tag::RequireTagDrop();
		}
	}

	function JavaScriptDrop(){
		$javastring=<<<JS

		  $(".gap_drop").css({backgroundColor:"#cbf"}).live('click', function() {
			pp= $(this).parents('tr').attr("class").match(/gap__(\S+)/)[1];
			link='%LINK' + 'dropGap?Gap=' + pp  ;
			$.getScript( link);
		       return false;
		});


		  $(".gap_reschedule").css({backgroundColor:"#cbf"}).live('click', function() {
			pp= $(this).parents('tr').attr("class").match(/gap__(\S+)/)[1];
			link='%LINK' + 'rescheduleGap?Gap=' + pp  ;
			$.getScript( link);
		       return false;
		});

	
		  $(".query_init").css({backgroundColor:"#abc"}).live('click', function() {
			pp= $(this).parents('tr').attr("class").match(/query__(\S+)/)[1];
			link='%LINK' + 'initQuery?Query=' + pp  ;
			$.getScript( link);
		       return false;
		});
	
		  $(".query_drop").css({backgroundColor:"#abc"}).live('click', function() {
			pp= $(this).parents('tr').attr("class").match(/query__(\S+)/)[1];
			link='%LINK' + 'dropQuery?Query=' + pp  ;
			$.getScript( link);
		       return false;
		});
JS
		;
		View::wrapJava(str_replace( '%LINK',$this->RelativeLink(), $javastring));
	}
	
	function dropGap(){
		error_log("here at dropGap");
		$GapID=$this->request->requestVar('Gap');
	 	$g=DataObject::Get_by_id('TweetGap',$GapID);
		$g->delete();
		//FormResponse::add("alert('dropped');");
		FormResponse::add(  "jQuery('tr[class*=gap__{$GapID}]').remove();");
		return FormResponse::respond();	
	}

	
	function rescheduleGap(){
		error_log("here at rescheduleGap");
		$GapID=$this->request->requestVar('Gap');
	 	$g=DataObject::Get_by_id('TweetGap',$GapID);
		$g->Reschedule();

	  	$contents = $this->customise($g)->renderWith(array('Gap'));
		FormResponse::update_dom_id("gapp".$GapID, $contents, false);
		//FormResponse::add("alert('rescheduled');");
		return FormResponse::respond();	
	}

	function initQuery(){
		//error_log("here at initQuery");
		//error_log("Ready to init Query $QueryID");
		$QueryID=$this->request->requestVar('Query');
	 	$q=DataObject::Get_by_id('TwitterQuery',$QueryID);
		$g= $q->gaps();
		$g->removeAll();
		$q->lowestID= (float)0;
		$q->highestID= (float)0;
		$q->write();
		$q->insureInitialGap();
	  	$contents = $this->customise($q)->renderWith(array('OneQuery'));
		FormResponse::update_dom_id("query__".$q->ID, $contents, false);
		//FormResponse::add(  "jQuery('tr[class*=query__{$QueryID}]').remove();");
		//error_log("send response");
		return FormResponse::respond();	
	}
	

	function dropQuery(){
		//error_log("here at dropQuery");
		//error_log("Ready to drop Query $QueryID");
		$QueryID=$this->request->requestVar('Query');
	 	$q=DataObject::Get_by_id('TwitterQuery',$QueryID);
		$g= $q->gaps();
		$g->removeAll();
		$q->delete();
		FormResponse::add(  "jQuery('tr[class*=query__{$QueryID}]').remove();");
		//error_log("send response");
		return FormResponse::respond();	
	}
	
	function index($data =false) {
		$this->JavaScriptDrop();
		error_log("here at index or QueryPage");
		if (is_string($data) ) $name = $data;
			else $name = $data->param('ID');
		Director::set_environment_type('dev');
		$start = ($this->request->requestVar('start')) ? (int)$this->request->requestVar('start'):0;
		$queries=DataObject::get('TwitterQuery',"","Title","","$start,25");
		$queries = new PaginatedList($queries);
		if($queries) $queries -> setPageLength(25);
		$data = array('Queries' => $queries,  'Title' => 'The list of Active Queries to Twitter') ;
	  	return $this->customise($data)->renderWith(array( 'Query_results','Page'));
	}
	
}
