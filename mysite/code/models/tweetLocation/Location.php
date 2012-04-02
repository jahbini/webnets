<?php
class Location extends DataObject {
	static $db= array('code4' => 'Varchar(4)',
		'code3' => 'Varchar(3)',
		'Airport'=>'Varchar',
		'Town' => 'Varchar',
		'Country' => 'Varchar',
		'Latitude' => 'Double',
		'Longitude' => 'Double');
	static $indexes = array('code3' => true );

	static $required=false;
	function requireDefaultRecords() {
		if(self::$required) return;
		parent::requireDefaultRecords();
		self::$required=true;
		$f = file ("airport.txt",FILE_USE_INCLUDE_PATH);
		foreach($f as $line) {
			$c = explode(':', $line);
			$nl = array(
				'code4' => $c[0],
				'code3' => $c[1],
				'Airport' => $c[2],
				'Town' => $c[3],
				'Country' => $c[4],
				'Latitude' => ($c[5] + $c[6]/60.0 + $c[7]/3600.0) * ($c[8]=='S'?-1:1),
				'Longitude' => ($c[9] + $c[10]/60.0 + $c[11]/3600.0) *($c[12]=='E'?1:-1),
				);
			$loc = DataObject::get_one('Location',"`code3`='". $c[1] . "'");
			if ($loc instanceOf Location) continue;
			$loc = new Location($nl);
			$loc->write();
		}
	}

	/*
	 * return a region by the airport code
	 */
	function get_by_location($loc) {
		return DataObject::get_one('Location', "`code3`='" . $loc . "'");
	}

	/*
	 * return a lat/long string formatted for twitter search
	 * default to 20 miles near the selected airport
	 */
	function forSearch($radius="20mi"){
		if($this->Latitude == 0 && $this->Longitude == 0 ) return "";
		return number_format($this->Latitude, 3) . ',' . number_format($this->Longitude,3) . ',' . $radius;
	} 

}
