<?php
/*
 * Scheduler will give a best guess if the proposed request can be fulfilled
 * It will update it's state to show a future time for the request, or a 0 to allow
 * the request to proceed
 */
class Scheduler extends DataObject {
	static $db = array('incomplete' => 'Int','competition' => 'Int', 'last_request_attempt' => 'Int', 'last_request_ok' => 'Int', 'remaining' => 'Int' , 'zero_hour' => 'Int', 'rate' => 'Int','per' => 'Int');
	var $estimate = 75;	
	static function get_scheduler($id) {
		$s = false;
		// DB::query('LOCK TABLES `Scheduler` WRITE');
		if ($id) $s = DataObject::get_by_id('Scheduler',$id);
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
		$s = new Scheduler;
		$s -> rate = 150; // rate is 150 
		$s -> per = 3600; // per hour
		$s -> remaining =1;
		$s -> write();
		return $s;
	}

	function TimeToSchedule($from) {
		return $from+ $estimate;
	}

	function schedule( $bump=1 ) {
		// this routine answers the question: when can we schedule this transaction?
		// It checks the number of requests allowed in the current time to zero_hour
		//  and gives an estimate of when we can run  this transaction.  The return
		//  value is the number of seconds you must wait.
		//
		// divide up the time from the last request to the zero_hour as told by twitter
		// and see if this request is later than the acceptable throttle time
		$remaining = ($this->remaining>2)?$this->remaining-1:($this->zero_hour?0:1);   // the amount remaining is off by one from twitter
		if(($remaining+1)  == 00) {
		error_log('remaining = '. $remaining . ', throttle = ' . $throttle . ' oktime = ' . $oktime . ' now = ' . time() . ' competition time =' . floor($oktime+ 2*$throttle)  );
			error_log($this->ID);
			error_log ("Bad juju in scheduler");
			error_log(print_r( $remaining,1) );
		}
		try { 
		$throttle = floor( ($this-> zero_hour - $this -> last_request_ok  ) / ($remaining +1 )) ;
		} catch (Exception $e ) {
			error_log ("Bad juju in scheduler");
			error_log ($e -> getMessage());
			error_log(print_r( $remaining,1) );
		$throttle = floor( ($this-> zero_hour - $this -> last_request_ok  ) ) ;
		}

		$oktime = $this-> last_request_ok + $throttle;
		error_log('remaining = '. $remaining . ', throttle = ' . $throttle . ' oktime = ' . $oktime . ' now = ' . time() . ' competition time =' . floor($oktime+ 2*$throttle)  );
		$this -> competition += $bump;
		if ($oktime==0 || ($oktime <  time() && $remaining > 0)) {
			if(($oktime + 2* $throttle)  < time()) $this->competition =1; // if 3 time slots have passed then no competition
			$this -> remaining = $remaining - 1;
			$this -> last_request_attempt = time();
			// incomplete is to detect when the system blows up and we never get to the  request_complete routine
			$this -> incomplete += $bump;
			$this -> write();
		// DB::query('UNLOCK TABLES');
			return 0;  // that is,  schedule now
		}
		if ($bump == 1) error_log("schedule says to defer first call ");
		$this -> write();
		// DB::query('UNLOCK TABLES');
		$time_estimate = time() + $this->competition * $throttle;  // give an estimate of a future slot
		if ($time_estimate > $this-> zero_hour) {
			// recompute on the basis of 'rate'  requests per hour
			$time_estimate = $this->zero_hour +( 3600/($this->rate?$this->rate:150))*($this->competition - $this->remaining ) ;
		}

		$estimate =floor($time_estimate -time());   // a relative time to attempt this request
		$this->estimate = $estimate;
		if ($estimate < 60 && $remaining >  15) $estimate = 0;  // plunge ahead if the wait and risk are small 
		if ($estimate < 0 ) $estimate =0;
		return $estimate;   // a relative time to attempt this request
	}

	function request_complete( $headers ) {
		// DB::query('LOCK TABLES `Scheduler` WRITE');
		// we look at the result headers from the transaction to see if the
		// request was successful.  If so, then we update the object and go away
		// If not, we re-compute a time for re-scheduling this request
		$this -> estimate = 60*30;  // thirty minutes?
		if(!isset($headers['Status'])) {
			// most likely a twitter misconnect or time out, we should reschedule in 30 to 90 seconds
			$this -> estimate = 75;
			return -1;   // we have no idea what  happened
		}
		$http_status = explode(' ', $headers['Status']);
		$completion_code = $http_status[1];
		$this -> competition -= 1;
		$this -> incomplete -= 1;
		
		if (isset($headers['X-RateLimit-Reset'] )) {
			// This is the kind of request that we were built to handle!
			// get the rate parameters and update this object
			$this->zero_hour = $headers['X-RateLimit-Reset'];
			$this->remaining = $headers['X-RateLimit-Remaining'];
			$this->rate = $headers['X-RateLimit-Limit'];

			// decay the competition at least once an hour
			if ($this-> remaining == $this->rate) $this->competition = floor($this->competition/2);
			if ($completion_code == 200) {
				// this was a good request
				$this-> last_request_ok = time();
				$this->write();
		// DB::query('UNLOCK TABLES');
				return $this->schedule(0) ; // OK give an estimate of when is good to try more
			}
			if ($completion_code == 400 && $this->remaining == 0) {
				// this request did not complete due to exceeding Twitter's rate-limit
				// we need to update the db, and return a possible time for rescheduling
				$this->write();
				return $this->schedule();
			}
			$this -> write();
		// DB::query('UNLOCK TABLES');
			return -1;   // we have no idea what  happened
			
		}
		// this call was NOT rate limited and we should not bump the competition, nor lower the remaining
		$this -> remaining += 1;

		if ($completion_code == 200) {
			// this was a good request
			$this-> last_request_ok = time();
			$this->write();
		// DB::query('UNLOCK TABLES');
			return 0; // OK  No rate limiting is needed
		}
		$this -> write();
		// DB::query('UNLOCK TABLES');
		return -1;   // we have no idea what  happened
	}
}
?>
