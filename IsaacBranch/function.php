<?php
//changestatus inserts name, status and any comment associated into the studentInfo database
function changestatus($f_id, $f_status, $f_info, $f_returntime) {
	global $db_server;
	$result = $db_server->query("SELECT timestamp FROM events WHERE studentid = '$f_id' ORDER BY timestamp DESC LIMIT 1");
	$rowdata = $result->fetch_array(MYSQLI_BOTH);

    $last = new DateTime($rowdata['timestamp']);
    $now = new DateTime();
	$lastdate = $last->format('Y-m-d');
	$last330 = $lastdate . '15:30:00';
	$lastendofday = new DateTime($last330);
    $nowstamp = $now->getTimestamp();
    $laststamp = $last->getTimestamp();
    $lastendstamp = $lastendofday->getTimestamp();
	if ($nowstamp > $lastendstamp) {
		$minutes = round(($lastendstamp - $laststamp)/60);
		} else {
		$minutes = round(($nowstamp - $laststamp)/60);
		}

	$stmt = $db_server->prepare("UPDATE events SET elapsed = ? WHERE studentid = ? AND timestamp = ?");
	$stmt->bind_param('iss', $minutes, $f_id, $rowdata[0]);
	$stmt->execute(); 		
	$stmt->close();
	
	$stmt = $db_server->prepare("INSERT INTO events (studentid, statusid, info, returntime) VALUES (?, ?, ?, ?)");
	$stmt->bind_param('ssss', $f_id, $f_status, $f_info, $f_returntime);
	$stmt->execute(); 
	$stmt->close();

}

//defines valid time entries for time text boxes
//only allows integers and colons
function validTime($inTime) {
$pattern   =   "/^(([0-9])|([0-1][0-9])|([2][0-3])):?([0-5][0-9])$/";
 if(preg_match($pattern,$inTime)){
   return true;
 }
}

//checks if you've hit any of the submit buttons that are a part of the top form
function isPost(){
if (in_array("Present", $_POST)) {
    return true;
} elseif (in_array("Offsite", $_POST)){
    return true;
} elseif (in_array("Field Trip", $_POST)){
    return true;
} elseif (in_array("Sign Out", $_POST)){
    return true;
} else {
return false;
}
}

?>