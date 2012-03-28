<?php
class TagRank extends DataObject {
	static $db = array('DateOfRank' => 'Datetime');
	static $has_many = array('Tags'=>'Tag');
	static $many_many_extraFields = array('Tags'=> array('Order' => 'Int')); // Order 0 to 20 trend ranking

	function addTagAs($tag,$order) {
		$myTags = $this->Tags();
		$myTags->add($tag,array('Order' => $order) );
	}

	static function addRanking($trendInfo){
		foreach ($trendInfo as $date => $trends) {
			$t = new TagRank(array('DateOfRank'=> $date));
			$popularity = 1;
			foreach($trends as $t){
				$tag =getTagBySearcher($t);
				$t -> addTagAs($tag,$popularity);
				$popularity += 1;
			}
			$t -> write();
		}
		return;
	}
}
class RankTags extends Controller{
	function index() { // get the days rankings
		$twitter = new SaneRest("http://search.twitter.com/trends/daily.json");
		$params=array(); // no params yet, although twitter does support some
		$twitter->setQueryString($params);

		$conn= $twitter->request();
		$data= json_decode($conn->body);
		TagRank::addRanking($data->trends);
		return "";
	}
}
?>
