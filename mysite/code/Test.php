<?php
//
// vim:sts=3:sw=3:ts=3:filetype=php:
//
class Test extends CliController {

function TestRate() {
$x = new rateUser();
$u= $x -> rate("jahbini");
error_log("return from filling Tweets " . $u->reschedule);

}
function listFollowers($data) {
		$name = $data->param('ID');
		echo(" Got name = $name \n");
		$p = PenName::get_by_name($name);
		echo(" Got name = " . $p ->name . "\n");
		$start = 0;
		$total = 0;
		do {
		$f=$p->Followers($start);
		$start += 25;
		foreach($f as $fol) {
			$total += 1;
			echo("  followed by " . $fol->screen_name . " (" . $fol->ID . ") follow factor = " .$fol -> follow_worthy . "\n");
		}
		} while ($f ->exists() );
		echo("Done user ". $p->screen_name . " (". $p->ID .") Total Followers = $total \n");
}


function listFriends($data) {
		$name = $data->param('ID');
		echo(" Got name = $name \n");
		$p = PenName::get_by_name($name);
		echo(" Got name = " . $p ->name . "\n");
		$start = 0;
		$total = 0;
		$tenth = 0.1;
		do {
		$f=$p->Friends($start,25,"`follow_worthy` ASC");
		$totalItems = $f->TotalItems();
		$start += 25;
		foreach($f as $fol) {
			$total += 1;
			if ($total/$totalItems > $tenth) {
				echo (" $tenth Reached \n\n");
				$tenth += 0.1;
			}
			echo("  follows " . $fol->screen_name . " (" . $fol->ID . ") follow factor = " .$fol -> follow_worthy . "\n");
			if (!$fol->follow_worthy) {
				$fol ->rate();
			echo("     updated follow factor " . $fol->screen_name . " (" . $fol->ID . ") follow factor = " .$fol -> follow_worthy . "\n");
			}
		}
		} while ($f ->exists() );
		echo("Done user ". $p->screen_name . " (". $p->ID .") Total Friends = $total \n");
}
function follow_factor ($data) {
		$name = $data->param('ID');
		echo(" Got name = $name \n");
		$u = TweetUser::getTweetUser($name,false);
		echo(" Got name = " . $u ->name . "\n");
		$u ->rate();
		echo("     updated follow factor " . $u->screen_name . " (" . $u->ID . ") follow factor = " .$u -> follow_worthy . "\n");

}
function addFriend($data) {
		$name = $data->param('ID');
		echo(" Got name = $name \n");
		$p = PenName::get_by_name($name);
		$who = $data->param('who');
		echo(" add $who as friend \n");
		$x= $p -> followEZ($who);
		$nf = TweetUser::getTweetUser($who,false);
		echo(" added " . $nf -> name . " (" . $nf ->ID . ")  $who as friend \n");
		echo("Done\n");
}

function fillOne($data){
	$screen_name = $data->param('ID');
	$user  = DataObject::get_one('TweetUser', "`received`=0");
	print_r($user);
	$user->fillMe(array('screen_name' => $user->screen_name) );

}

function TestExtract() {
$r=Tag::extractTags("hello x #bodu,@usertag%#hash generaltag");
print_r($r);
}
function FillLocation () {
	Location::get_codes();
}
function showMentor($data) {
	$name = $data->param('ID');
	$user = TweetUser::getTweetUser($name);
	$profile = $user->Profile();
	$mode=$profile->getModeByUse('Attract');
	print_r($mode);
	$panes=$mode->Panes();
	print_r($panes);
}
}
?>
