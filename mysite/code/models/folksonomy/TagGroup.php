<?php
// vim:ts=3:sts=3:sw=3:ft=php:
function trim_and_lower ($s) {
	return strtolower(trim($s));
}
class TagGroup extends DataObject {
	static $db = array('TagString'=>'Text');
	static $many_many = array('myTags' => 'Tag','myPairs'=>'TagPair');
	static $api_access = true;


	function gather_pairs($orderedTagArray) {
		//returns upper right triangle of the matrix of tag X tag from the orderedTagArray
		$pairs=array();
		for ($i=0; $i<sizeof($orderedTagArray); $i++) {
			$tag=$orderedTagArray[$i];
			for ($j=$i+1; $j<sizeof($orderedTagArray) ; $j++) {
				$jtag=$orderedTagArray[$j];
				$pairs[]=array('index'=>"{$tag->ID},{$jtag->ID}",'taga'=>$tag,'tagb'=>$jtag);
			}
		}
		return $pairs;
	}
	function getPairs() {
		$pairs=$this->myPairs();
		foreach ($pairs as $p) {
			$result[] = "{$p->taga()->TagText},{$p->tagb()->TagText}";
		}
		return join(";",$result);
	}
	function setTagString($new) {
		$this->setField('TagString',$new);
	      $names = array_map(trim_and_lower,array_unique(explode(',',$new)));
	      sort($names);
	      // no duplicates and all in proper ascending alphabetical order
	      
	      $needMap=array();
	      // now we update the tag list
	      foreach ($names as $n) {
		      $needMap[$n] = true; 
	      }

	      //  tag all the Tags that are already in our myTags
	      $tags_to_keep=array();
	      $cs = $this -> myTags();
	      foreach($cs as $tag) {
		      $name = $tag->tagText;
		      if (isset($needMap[$name])) {
			      $needMap[$name]=false;
			      $tags_to_keep[] = $tag->ID;
		      } 
	      }

	      foreach($needMap as $name => $needed) {
		      if($needed) {
			      $tag =& Tag::getTagByName($name);
			      if (!$tag) continue;
			      $tags_to_keep[] = $tag->ID; 
		      }
	      }

	      $cs->setByIDList($tags_to_keep);
	      $cs->write();
	      $cs->sort('ID');


	      // which pairs will we need in our pairings?
	      //   This is only the tags in pairs, not the 'pairs'
	      $pairs=$this->gather_pairs($cs->toArray());
	      foreach ($pairs as $pair) {
		      $needPair[$pair['index'] ] = $pair;
	      }
	      $pairs_to_keep=array();
	      $cluster = $this-> myPairs();
	      //  tag all the pairs that are already in our TagPairs
	      //  so that we will keep them
	      foreach($cluster as $pair) {
		      $idx = "{$pair->taga},{$pair->tagb}";
		      if($needPair[$idx]) {
			      $needPair[$idx]=false;
			      $pairs_to_keep[]=$pair->ID;
		      }
	      }
	      foreach($needPair as $idx=>$value) {
		      if ($value) {
					$pair = TagPair::getPair($value['taga'],$value['tagb']);
					$pairs_to_keep[]=$pair->ID;
		      }
	      }
	      $cluster->setByIDList($pairs_to_keep);
	      $cluster->write();
	   }
}
