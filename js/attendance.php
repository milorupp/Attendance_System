<!DOCTYPE html>
<html>
	<link rel="stylesheet" type="text/css" href="attendance.css">
<head>
		<title>attendance system tests</title>
</head>
<body>
	<div>
<!-- Form that manages status -->
		<form method='post' action='<?php echo basename($_SERVER['PHP_SELF']); ?>' id='main'>
<!-- Present form -->
	<div>
		<input type="submit" value="Present" name="present">
	</div>
<!-- Offsite form -->
	<div>
		<input type="submit" value="Offsite" name="offsite">
		<input type="text" name="location" placeholder='Location'>
		<input type="time" name="offtime" placeholder='Return time'>
	</div>
<!-- Field trip form -->
	<div>
		<input type="submit" value="Field Trip" name="fieldtrip">
<?php


// SETTING UP ALL THE MySQL STUFF
	require_once("connection.php");

//function document
	require_once("function.php");

// grabs facilitator data for the field trip dropdown
	$result = $db_server->query("SELECT * FROM facilitators ORDER BY facilitatorname ASC");
	$fac_rows = $result->num_rows;

// puts each facilitator into an array
	$facilitators = array();
    while ($row = $result->fetch_row()) {
		array_push ($facilitators, $row[0]);
    }

?>
<!-- Creates the dropdown entries -->
		<select name='facilitator'><option value=''>Select Facilitator</option>
<?php
			foreach ($facilitators as $facilitator_option) {
?> 
				<option value= '<?php echo $facilitator_option; ?> '> <?php echo $facilitator_option; ?></option>
<?php
			}
?>
        </select>
<!-- Field trip return time -->
        <input type="time" name="fttime" placeholder='Return time'>
	</div>

<!-- Sign out form -->
	<div>
		<input type="submit" value="Sign Out" name="signout">
	</div>
	
	</form>
	</div>
		
<?php

//requires checkboxes to be checked
if (!empty($_POST['person']) && isPost()){

//top present form querying -- "1" refers to "Present" in statusdata table
	if (!empty($_POST['present'])) {
		$name = $_POST['person'];
		foreach ($name as $student)
		{
			changestatus($student, '1', '');
		}
	}

//offsite querying and validation -- "2" refers to "Offsite" in statusdata table
	if (!empty($_POST['offsite'])) {
		$name = $_POST['person'];
		$status = "at " . $_POST['location'] . " returning at " . $_POST['offtime'];
		if (!empty($_POST['location'])){
			if (validTime($_POST['offtime'])){
				foreach ($name as $student){
				changestatus($student, '2', $status);
				}
			} else {
			echo "that's not a valid time";
			}
		} else {
			echo "you need to fill out the location box before signing out to offsite";
		}
	}

//Fieldtrip querying and validation -- "3" refers to "Field Trip" in statusdata table
	if (!empty($_POST['fieldtrip'])) {
		$name = $_POST['person'];
		$status = "with " . $_POST['facilitator'] . " returning at " . $_POST['fttime'];
		if (!empty($_POST['facilitator'])){
			if (validTime($_POST['fttime'])){
				foreach ($name as $student){
				changestatus($student, '3', $status);
				}
			} else {
				echo "that's not a valid time";
			}
		} else {
			echo "you need to chose a facilitator before signing out to field trip";
		}
	}

//Sign out querying -- "4" refers to "Checked Out" in statusdata table
	if (!empty($_POST['signout'])) {
		$name = $_POST['person'];
		foreach ($name as $student)
		{
			changestatus($student, '4', '');
		}
	}

//error message when no boxes are checked
} else if(isPost() && empty($_POST['person'])) {
	echo "please choose a student";
}

//individual present button querying -- "1" refers to "Present" in statusdata table
if (!empty($_POST['present_bstudent'])) {
	$name = $_POST['present_bstudent'];
	changestatus($name, '1', '');
}

//late status querying -- "5" refers to "Late" in statusdata table
if (!empty($_POST['Late'])) {
	$name = $_POST['late_student'];
	$status = "arriving at " . $_POST['late_time'];
	changestatus($name, '5', $status);
}

//Gets student names/id

//OK TO DELETE
//$result = $db_server->query("SELECT studentid,firstname,lastname FROM studentdata WHERE current=1");

$current_users_result = $db_server->query("SELECT studentid FROM studentdata WHERE current=1 ORDER BY firstname");
	


//OK TO DELETE
//Number of student names
//$rows = $result->num_rows;
//blank array to store each name from query
//$current_users = array();
//iterates through query, adding each name to $current_users array
//for ($j = 0 ; $j < $rows ; ++$j)
//		{
//		$namedata = $result->fetch_array(MYSQLI_NUM);
//		array_push($current_users, $namedata[0]);
//		}
      
?>
<!-- table display for current student status -->
<table style="width:80%" class='data_table'>
    <tr>
        <th class='data_table'></th>
        <th class='data_table'>Student</th>
        <th class='data_table'>Status</th>
        <th class='data_table'>Info</th>
    </tr>
    <?php
	
	while ($current_student_id = $current_users_result->fetch_assoc()) { // LOOPS THROUGH ALL OF THE CURRENT STUDENTS
				
		$result = $db_server->query("SELECT firstname,statusname,events.studentid,info,timestamp
									 FROM events 
									 JOIN studentdata ON events.studentid = studentdata.studentid 
									 JOIN statusdata ON events.statusid = statusdata.statusid
									 WHERE events.studentid = $current_student_id[studentid] 
									 ORDER BY timestamp DESC
									 LIMIT 1") 
									 or die(mysqli_error($db_server));
		while ($latestdata = $result->fetch_assoc()) { // LOOPS THROUGH THE LATEST ROWS FROM THE EVENTS TABLE
		
			if ($latestdata['statusname'] != 'Present') {
			?>
			<tr>
				<td class='data_table'>
					<!-- checkbox that gives student data to the form at the top -->
					<input type='checkbox' name='person[]' value='<?php echo $latestdata['studentid']; ?>' form='main' class='c_box'>
					<!-- present button, passes hidden value equal to the current student -->
					<form action='<?php echo basename($_SERVER['PHP_SELF']); ?>' method='post'>
						<input type='submit' value='P' class='p_button' name='present_button'>
						<input type='hidden' name='present_bstudent' value='<?php echo $latestdata['studentid']; ?>'>
					</form>
					<?php
					//checks whether or not the late button should appear (only if the student is not checked in)
					//gets the day that entry was entered
					$day_data = new DateTime($latestdata['timestamp']);
					//the day it was yesterday
					$yesterday = new DateTime('yesterday 23:59:59');
					if ($day_data < $yesterday) {
                    echo $student;
                    changestatus($student, '0','');
					?>
					<!-- Late button with time input next to it -->
					<form action='<?php echo basename($_SERVER['PHP_SELF']); ?>' method='post'>
						<input type='submit' value='Late' name='Late' class='l_button'>
						<input type='time' name='late_time' placeholder='Return time'>
						<input type='hidden' name='late_student' value='<?php echo $latestdata['studentid']; ?>'>
					</form>
					<?php } ?>
				</td>
			<?php
            } else {
            //displays user data without any buttons when they are present
            ?>
			<tr>
				<td class='data_table'>
					<!-- checkbox that gives student data to the form at the top -->
					<input type='checkbox' name='person[]' value='<?php echo $latestdata['studentid']; ?>' form='main'/>
				</td>
			<?php }// CLOSES IF/ELSE CONDITIONAL ?>
            <!-- displays current rows student name, that students status and any comment associated with that status -->
				<td class='data_table'><?php print $latestdata['firstname']; ?></td>
				<td class='data_table'><?php echo $latestdata['statusname']; ?></td>
				<td class='data_table'><?php echo $latestdata['info'] ?></td>
			</tr>
<?php		
		} // FINISHES THE WHILE LOOP THAT GOES THROUGH THE LATEST ROWS FROM THE EVENTS TABLE

	} // FINISHES THE WHILE LOOP THAT GOES THROUGH THE CURRENT STUDENTS
?>

</table>

</body>
</html>