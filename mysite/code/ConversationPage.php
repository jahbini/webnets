<?php

class ConversationPage extends SiteTree {
	
	public static $db = array(
	);
	
	public static $has_one = array(
	);
	
}

class ConversationPage_Controller extends Page_Controller {
	
	public function init() {
		$self->requiresTweetAction = false;
		parent::init();
	}

	public function TweetBox() { return false; }	
	public function SearchForm() { return false; }	
	
	function index() {
	  	return $this->renderWith(array('Page'));
	}

	function Form () {
		error_log("Conversation form");
		if( !Director::is_ajax() ){
	View::wrapJava(<<<JS
	$('#Form_Conversation_action_converse').live('click', function(){
		var form = $('#Form_Conversation');
		var formAction = form.attr('action') + '?' + $(this).fieldSerialize();
		// Post the data to save
		$.post(formAction, form.formToArray(), function(result){ jQuery('#Form_Conversation_tweet_1').attr('value',result); });
		return false;
});
JS
);
		}

		$ResponderPenNameID = Session::get('ResponderPenName');
		$InitiatorPenNameID = Session::get('InitiatorPenName');
		$map = $this->profileData->PenNameMap();
		$fields = new FieldSet(
			new DropdownField("InitiatorPenName", "Initiator Screen Name", $map ,$InitiatorPenNameID), 
			new DropdownField("ResponderPenName", "Your Screen Name", $map ,$ResponderPenNameID) );

		for ($i=1;$i<10;$i++){
			$sf= new SizedTextField("tweet_".$i, "Tweet?", "", 140);
			$sf -> setMaxSize(140);
			$fields->Push( $sf );
		}

		$actions = new FieldSet(
		//	new AjaxFormAction('converse', 'Tweet it!','ajaxtweet')
			new FormAction('converse', 'Tweet it!')
			);
	  	return new Form($this, "Form", $fields, $actions);
	}

	function converse ( $data, $form) {
		$data = $form->getData();

		$InitiatorPenNameID = Session::get('InitiatorPenName');
		$InitiatorPenNameID = $data['InitiatorPenName']?$data['InitiatorPenName']:$InitiatorPenNameID;
		$ResponderPenNameID = Session::get('ResponderPenName');
		$ResponderPenNameID = $data['ResponderPenName']?$data['ResponderPenName']:$ResponderPenNameID;

		Session::set('ResponderPenName',$ResponderPenNameID);	
		Session::set('InitiatorPenName',$InitiatorPenNameID);	
		$initiator = DataObject::get_by_id('PenName', $InitiatorPenNameID);
		if (! $initiator ) {
			FormResponse::add("Initiator User does not exist in DB");
			return FormResponse::respond();	
		}
		$responder = DataObject::get_by_id('PenName', $ResponderPenNameID);
		if (! $responder ) {
			FormResponse::add("Responder User does not exist in DB");
			return FormResponse::respond();	
		}
		$initiator = '@' . $initiator -> screen_name . ' ';
		$responder = '@' . $responder -> screen_name . ' ';

		error_log("ajax?? " . Director::is_ajax()?"Yes, ajax":"No, not ajax");
		Director::set_status_code(200);

		for($i=1;$i<10;$i++){
			$n = 'tweet_'.$i;
			$tag=$data[$n];
			if (!$tag || $tag=="" || $tag =="submitted") break;

			if($i & 1)   TwitterIt::TweetOut($responder . $tag, $InitiatorPenNameID);
				else TwitterIt::TweetOut($initiator . $tag, $ResponderPenNameID);
		}

		FormResponse::add("submitted");
		return FormResponse::respond();	
	}
}
