<?php
/* Query Page attempts to show the current status of the gaps in the twitter responses
 * to a given query.
 * the user is able to reschedule or delete any gap, which may become stuck or error prone
 */

class QueryStatusPage extends SiteTree {
	
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
		if ($pPage instanceOF QueryStatusPage) return;
		if ($pPage instanceOF Page ) {
			$pPage = $pPage -> newClassInstance('QueryStatusPage');
			$pPage -> write();
		}
		if (!$pPage) {
			$pPage = new QueryStatusPage();
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

class QueryStatusPage_Controller extends ContentController {
	
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
		if (!$this->isAjax()) Director::redirectBack();
		error_log("here at dropGap");
		$GapID=$this->request->requestVar('Gap');
	 	$g=DataObject::Get_by_id('TweetGap',$GapID);
		$g->delete();
		return  "jQuery('tr[class*=gap__{$GapID}]').remove();";
	}

	
	function rescheduleGap(){
		error_log("here at rescheduleGap");
		if (!$this->isAjax()) Director::redirectBack();
		$GapID=$this->request->requestVar('Gap');
	 	$g=DataObject::Get_by_id('TweetGap',$GapID);
		$g->Reschedule();

	
	  	$contents = $this->customise($g)->renderWith(array('Gap'));

		$contents = str_replace("\n",' ',$contents);
		return "jQuery('#gapp".$GapID. "').html('" . $contents ."');";
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
		$contents = str_replace("\n",' ',$contents);
		return "jQuery('#query__".$q->ID . "').html('". $contents ."');";
	}
	

	function dropQuery(){
		//error_log("here at dropQuery");
		//error_log("Ready to drop Query $QueryID");
		$QueryID=$this->request->requestVar('Query');
	 	$q=DataObject::Get_by_id('TwitterQuery',$QueryID);
		$g= $q->gaps();
		$g->removeAll();
		$q->delete();
		return "jQuery('tr[class*=query__{$QueryID}]').remove();";
	}
	
	function index($data =false) {
		Requirements::javascript(THIRDPARTY_DIR. '/jquery/jquery.js');
		$this->JavaScriptDrop();
		error_log("here at index or QueryStatusPage");
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
