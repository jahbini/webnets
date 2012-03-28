<?php
/*
 * IPScheduler is exacly like PenName Scheduler,  except it is not associated with any account
 * Like Scheduler it will give a best guess if the proposed request can be fulfilled
 * It will update it's state to show a future time for the request, or a 0 to allow
 * the request to proceed
 */
class IPScheduler extends Scheduler {

	static function get_IPscheduler() {
		$s = DataObject::get_one('IPScheduler'); // there is only one

		if ($s) {
			if ($s->incomplete ) {
				$s-> incomplete /= 2;  // why do we divide by 2?  makes little sense?
				$s->remaining += $s->incomplete;
				$s->competition -= $s->incomplete;
				$s->write();
			}
			if ($s -> zero_hour > time()) {
				$s->zero_hour += $s->per;
				$s->last_request_ok = $s-> zero_hour;
				$s->remaining = $s-> rate;
				$s->write();
			}
			return $s;
		}

		$s = new IPScheduler;
		$s -> rate = 150; // rate is 150 
		$s -> per = 3600; // per hour
		$s -> remaining =1;
		$s -> write();
		return $s;
	}
}
?>
