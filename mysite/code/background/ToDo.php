<?php
//
//vim:ts=2:sts=3:sw=3:filetype=php:
//

class ToDo extends DataObject {
	static $db = array("Suspend" => "Datetime", "TheObject" => "Varchar" , "ObjectID" => 'int', "operation" => "Varchar", "params" => "Text" );
	static $indexes = array("Suspend" => true );

	function firstSuspend() {
		//error_log("easy first suspension");
		return 1*60;
	}

	function execMethod ( $operation, $params=false) {
		// update the method to call for this todo item 
		$this->setField('operation', $operation);
		$this->setField('params' ,  base64_encode(serialize($params)) );
		$this->write();
		return $this;
	}

	function exists ( $operation ){
		$item = DataObject::get('ToDo',NiceData::formQuery('TheObject', $this->TheObject)  
					. ' AND ' . NiceData::formQuery('ObjectID', $this->ObjectID)
					.' AND ' . NiceData::formQuery( 'operation' ,$operation )  );
		if (!$item) return 0;
		return $item->TotalItems();
	}

	// make sure a todo item is really in the database, but don't put it in twice
	// we are only interested in the classkind and object, not the specific operation
	static function insureToDoItem ($classKind,$object,$operation,$params=false) {
		if (is_object($object)) {
			$class = $object->class;
			$id = $object->ID;
		} else {
			$class = $object;
			$id=0;
		}
		$item = DataObject::get($classKind,NiceData::formQuery('TheObject' , $class)  . ' AND ' . NiceData::formQuery('ObjectID', $id ) );
		if (!$item) self::addToDoItem ($classKind, $object, $operation, $params);
	}

	static function addToDoItem ($classKind,$object, $operation, $params=false) {
		$todo = new $classKind();
if (is_object($object)) {
		$cn = $object->class;
		$todo->setField( 'TheObject' , $cn);
		$todo->setField('ObjectID', $object->ID);
} else {
		$todo->setField( 'TheObject' , $object);
		$todo->setField('ObjectID', 0);
}
		error_log("figuring first suspension");
		$todo->Suspend = date('Y-m-d H:i:s',time() + $todo->firstSuspend());
		$todo->execMethod($operation,$params);
		return $todo;
	}
	static function getNextToDoItem($kind) {
		// get the oldest entry in the ToDo list
		echo("To Do Item of kind " . $kind );
		$o = DataObject::get_one($kind,'"Suspend"<now()',true,'"Suspend" ASC') ;
		if ($o instanceOf ToDo) {
			echo(" found " . $o->ID . " kind " . $o->operation );
		}
		echo("\n");
		return $o;
	}
	// if we really have run out of things to do, then we can dip into the future a bit
	static function getAnyToDoItem($kind) {
		// get the ANY entry in the ToDo list that is less than 18  hours in the future
		//   -- we do this when the number  of 'mid' gaps is "largeish"
		return DataObject::get_one($kind,'"Suspend"<now()+18*60*60',true,'"Suspend" ASC') ;
	}
	function test () {
		//Tweet::fixName();
		//ToDo::addToDoItem("Tag", "removeJejuneTags");
		//ToDo::addToDoItem('ToDo',"Tweet", "fixName");
		//ToDo::addToDoItem("TweetUser", "fillOne");
		//error_log("test activated previously on " . $this->LastEdited );
		return $this;
	}

	function activate () {
		$op = $this->operation;
			$this->Suspend = date('Y-m-d H:i:s',time() );
			$this->write();
		error_log("Activating Class " . $this->TheObject . " ID#". $this->ObjectID. " with operation " . $op);
	 	$objCl = $this->TheObject;
		if ($this->ObjectID >0 ) {
			$the_object = DataObject::get_by_id($objCl, $this->ObjectID);
		} else {
			try {
			$the_object = ($objCl!= "" )?($objCl instanceof DataObject?singleton($objCl):new $objCl):false;
			} catch ( Exception $e) {
				$the_object=false;
			}
		}
		if(!$the_object) {
			error_log("The object did not exist");
			// if the object has been deleted (manually by the admin?) just go away
			$this->delete();
			return false;
		}

		// add "ToDoAccess" to the callee, so that it can update the entry point mo'bettah
		$the_object -> ToDoAccess = $this;

		try {
			error_log("calling $op on class ". $the_object->ClassName . " ID = " . $the_object->ID );
		$rv = $the_object-> $op ( unserialize(base64_decode($this->params)) ) ;
		} catch (Exception $e) {
			error_log ("it Blew up" . $e ->getMessage()) ;
			$x = $this-> newClassInstance("DeadToDo");
			$x->write();
			return $x;

		}
		error_log("backfrom " . $op);
		if ($rv) {
			if(method_exists($rv,'TimeToSchedule') ) {
			$this->Suspend = date('Y-m-d H:i:s', $rv->TimeToSchedule(time()) );  //returns an absolute time, based on 'when'
			error_log("TimeToSchedule Says " . $rv->TimeToSchedule(0) );
			error_log("TimeToSchedule Called updating time stamp to ". $this->Suspend);
			$this->write();
			return $this;
			}
			$this->Suspend = date('Y-m-d H:i:s',time() + 60*60); // an hour?
			error_log("TimeToSchedule NOT Called updating time stamp to ". $this->Suspend);
			$this->write();
			return $this;
			}
		$this->delete();
		return false;
	}
}
?>
