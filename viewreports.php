<html>
<head>
	<title>View Reports</title>
	<?php require_once('header.php'); ?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="a/css/jquery.datetimepicker.css" />
	<script src="a/p/js/jquery.datetimepicker.js"></script>
</head>

<body class="view-reports">
	<div id="top_header">
<?php

	//query faclitators from sql to get a list
	$statget = $db_server->query("SELECT * FROM statusdata");
    $statusdata = array();
    while ($stat_row = $statget->fetch_row()) {
		array_push ($statusdata, $stat_row[0]);
    }

if (!empty($_POST['studentselect'])){
    $current_student_id = $_POST['studentselect'];
} elseif(!empty($_GET['id'])) {
	$current_student_id = $_GET['id'];
}

//current students array
$studentquery = "SELECT studentid, firstname, lastname FROM studentdata WHERE current=1 ORDER BY firstname";
$current_users_query = $db_server->query($studentquery);
$current_users_result = array();
while ($student = $current_users_query->fetch_array()) {
	array_push($current_users_result, $student);
}
	?>
	<div class="choose-report">
	<p>View report for:</p>
	<form method='post' id='studentform' class='studentselect' action='<?php echo basename($_SERVER['PHP_SELF']); ?>'>
	<select name='studentselect' class='studentselect'>
	<?php

	foreach($current_users_result as $student) {
		$lastinitial = substr($student['lastname'], 0, 1); ?>
		?>
		<option name='studentselect' value= '<?php echo $student['studentid']; ?>' <?php if (!empty($current_student_id)) { if ($current_student_id == $student['studentid']) { echo 'selected';};} ?>><?php echo $student['firstname']?><?php echo " "?><?php echo $lastinitial?></option>
		<?php
	}
	?>
	</select>
	<input type='submit' name='studentsubmit' class='studentselect'>
	</div>
		<div>
			<a href="index.php">Return to main attendance view</a>
		</div>
	</div>
<div class='report-container'>

<?php
if (!isset($_POST['studentselect']) && (empty($_GET['id']))) {
?>
    Select a name from the dropdown above
	<?php
} //Close if statment
//stats render
//holidays array
$holiday_data_array = array();
$holiday_dt_array = array();
$holidayquery = "SELECT date FROM holidays";
$holiday_result = $db_server->query($holidayquery);
while ($holiday_data = $holiday_result->fetch_array()) {
	array_push($holiday_dt_array, $holiday_data);
}
foreach ($holiday_dt_array as $k) {
	array_push($holiday_data_array, $k[0]);
}

//globals query
$globalsquery = "SELECT * FROM globals";
$globals_result = $db_server->query($globalsquery);
$globalsdata = $globals_result->fetch_array();
$tempstartdate = $globalsdata['startdate'];

// end basic queries ========
if(!empty($_POST['firstdatetimepicker'])){
			$FirstDateFromPicker = $_POST['firstdatetimepicker'];
			$FirstDateFromPicker = new DateTime($FirstDateFromPicker);
			$startbool = 0;
		} else {
			$FirstDateFromPicker = new DateTime($tempstartdate);
			$startbool = 1;
		}


if(!empty($_POST['lastdatetimepicker'])){
			$LastDateFromPicker = $_POST['lastdatetimepicker'];
			$LastDateFromPicker = new DateTime($LastDateFromPicker);
			$todaybool = 0;
		} else {
			$LastDateFromPicker = new DateTime();
			$todaybool = 1;
		}

		$SFirstDateFromPicker = $FirstDateFromPicker->format('Y-m-d'); //add H:i:s to the format to enable time picking as well
		$SLastDateFromPicker = $LastDateFromPicker->format('Y-m-d H:i:s');

		if($todaybool == 1){
			$renderlastdate = "today";
		} else {
			$renderlastdate = $LastDateFromPicker->format('l F jS \a\t g:ia');
		}

		if($startbool == 1){
			$renderstartdate = "start of the school year";
		} else {
			$renderstartdate = $FirstDateFromPicker->format('l F jS \a\t g:ia');
		}
	?>
    <div class="date_info">
        <br>
		<?php
			if(!empty($_POST['statusselect'])){
				$instatid = $_POST['statusselect'];
				$statgetname = $db_server->query("SELECT statusname FROM statusdata WHERE statusid = $instatid");
    			$statusdataname = array();
    			while ($stat_row = $statgetname->fetch_row()) {
					array_push ($statusdataname, $stat_row[0]);
    			}
				$statselect = $statusdataname[0];
			} else {
				$statselect = "";
			}
		  echo "showing " . $statselect . " events between " . $renderstartdate . " and " . $renderlastdate; ?>
    </div>
	<?php


$student_data_array = array();
if (isset($current_student_id)) {

//displays stats
echo "<div class='reportdiv'>";
echo "<div class='topdiv'>";//doesnt include detailed list of


$student_data_array = array();
$notfulldata = 0;
if(!empty($_POST['statusselect'])){
	$currentselectedstatus = $_POST['statusselect'];
	$statstring = "AND events.statusid = '$currentselectedstatus'" ;
	$notfulldata = 1;
} else {
	$statstring = "";
}
$notfulldatedata = 0;
if(!empty($_POST['firstdatetimepicker'])){
	$notfulldata = 1;
	$notfulldatedata = 1;

}

if(!empty($_POST['lastdatetimepicker'])){
	$notfulldata = 1;
	$notfulldatedata = 1;
}

//fetches most recent data from the events table
//joins with the tables that key student names/status names to the ids in the events table
$result = $db_server->query("SELECT info,statusname,studentdata.studentid,studentdata.firstname,timestamp,returntime,events.eventid, yearinschool
		FROM events
		 	JOIN statusdata ON events.statusid = statusdata.statusid
		RIGHT JOIN studentdata ON events.studentid = studentdata.studentid
		WHERE studentdata.studentid = $current_student_id
		AND timestamp BETWEEN '$SFirstDateFromPicker' AND '$SLastDateFromPicker'
		".$statstring."
		ORDER BY timestamp ASC") or die(mysqli_error($db_server));

while ($student_data_result = $result->fetch_assoc()) {
	array_push($student_data_array, $student_data_result);
}

//gets stats
$getStatsQuery = $db_server->query("SELECT info FROM events WHERE studentid = " . $current_student_id . " AND statusid = 2 AND timestamp BETWEEN '$SFirstDateFromPicker' AND '$SLastDateFromPicker'");
$getStatsResult = array();
while ($stats = $getStatsQuery->fetch_array()) {
	array_push($getStatsResult, $stats);
}

if(count($student_data_array)!=0){


//allotted hours query
$yearinschool = $student_data_array[0]['yearinschool'];
$allottedquery = "SELECT * FROM allottedhours WHERE yis = '$yearinschool'";
$allotted_result = $db_server->query($allottedquery);
$allotted_data_array = $allotted_result->fetch_array();

$starttime = $globalsdata['starttime'];
$endtime = $globalsdata['endtime'];
$startdate = $globalsdata['startdate'];
$lastdate = $globalsdata['enddate'];

$study_all = $allotted_data_array['IShours'] * 60;
$offsite_all = $allotted_data_array['offsitehours'] * 60;
$commhours_all = $allotted_data_array['communityhours'] * 60;

$studyhours_remaining = $study_all;
$offsitehours_remaining = $offsite_all;
$commhours_remaining = $commhours_all;

$query = $db_server->query("SELECT yearinschool FROM studentdata WHERE studentid = $current_student_id");
$tempvar = $query->fetch_assoc();
$studentYis = $tempvar['yearinschool'];

$query = $db_server->query("SELECT startdate FROM studentdata WHERE studentid = $current_student_id");
$tempvar = $query->fetch_assoc();
$studentStartDate = $tempvar['startdate'];

$startDateStamp = new DateTime($studentStartDate);
$yearDateStamp = new DateTime($startdate);

if ($startDateStamp > $yearDateStamp){ // if a student is mid-year, then nerf the number of IS and offsite hours they have
	$remainingDays = daysLeftFromDate($studentStartDate);
	$totalDays = daysLeftFromDate($startdate);

	$remainingDays = $totalDays - $remainingDays; // rip

	$baseHours = $remainingDays / $totalDays;
	$baseISHours = $remainingDays / $totalDays;
	$totalISHours = $study_all / 60;
	$totalHours = $offsite_all / 60;
	$baseHours = $baseHours * $totalHours;
	$baseISHours = $baseISHours * $totalISHours;

	$offsitehours_used = 0;
	$commhours_used = - ($baseHours * 60);
	$studyhours_used = 0;
	$studyhours_remaining = $study_all - $baseISHours * 60;
	$offsitehours_remaining = $offsite_all - $baseHours * 60;
	$commhours_remaining = $commhours_all - $baseHours * 60;

} else { // if not a new student, do nothing
	$offsitehours_used = 0;
	$commhours_used = 0;
	$studyhours_used = 0;
	$studyhours_remaining = $study_all;
	$offsitehours_remaining = $offsite_all;
	$commhours_remaining = $commhours_all;
}

$num_lates = 0;
$num_unexpected = 0;
$num_absent = 0;
$altText = "";

$offsiteremaining = $offsitehours_remaining / 60;
//counts time
//loops through each event for the given student
foreach($student_data_array as $event_key => $event_val) {
	if ($student_data_array[$event_key]['statusname'] != "Not Checked In") {
		if (count($student_data_array) != $event_key) {
			//makes the timestamp into datetime obj
			if (isset($student_data_array[$event_key+1])) {
				if($student_data_array[$event_key+1]['timestamp'] != "0000-00-00 00:00:00"){
					$event_datetime_2 = new DateTime($student_data_array[$event_key+1]['timestamp']);
				} else {
					continue;
				}
			} else {
				break;
			}

			if($event_val['timestamp'] != "0000-00-00 00:00:00"){
				$event_datetime_1 = new DateTime($event_val['timestamp']);
			} else {
				continue;
			}

			//variables for easy comparison
			$early = $event_datetime_1->format('m/d/y') . " " . $starttime;
			$late = $event_datetime_1->format('m/d/y') . " " . $endtime;
			$event_early = new DateTime($early);
			$event_late = new DateTime($late);

			//is event 1 on a weekend or holiday?
			if (($event_datetime_1->format('w') == 0 || $event_datetime_1->format('w') == 6) || (in_array($event_datetime_1->format('Y-m-d'), $holiday_data_array) == True)) {
				continue;
			}

			//is event 1 before the start of school?
			if ($event_datetime_1 <= $event_early){
				$event_datetime_1 = clone $event_early;
			}

			//is event 2 after the end of the school day of the same day of event 1?
			if ($event_datetime_2 >= $event_late){
				$event_datetime_2 = clone $event_late;
			}

			if ($event_datetime_2 > $event_datetime_1) { // without this logic, you can end up with (adjusted) event 1 coming *after* event 2!  (For example, a "checked out" event at 3:45 will result in a 15 minute diff.)
				$elapsed = $event_datetime_2->diff($event_datetime_1);
			} else {
				$elapsed = $event_datetime_1->diff($event_datetime_1);
			}

			//format as total minutes
			$elapsed_minutes = ($elapsed->format('%h')*60) + ($elapsed->format('%i'));

			$thisDateTime = new DateTime($event_val['timestamp']);
			$gracePeriod = $event_early;
			$gracePeriod = $gracePeriod->add(new DateInterval('PT59S'));

			if(statconvert(getEventBefore($event_val['eventid'],$event_val['studentid'])['statusid']) == "Not Checked In" && $thisDateTime >= $gracePeriod){
				// print($thisDateTime->format("Y-M-d H:i:s"));
				// echo "  ";
				// print($event_early->format("Y-M-d H:i:s"));
				$num_unexpected += 1;
				$altText = $altText . $thisDateTime->format("M j, g:i") . "&#13";
			}

			if ($event_val['statusname'] == 'Present' || $event_val['statusname'] == 'Field Trip' || $event_val['statusname'] == 'Independent Study') {
				$commhours_remaining -= $elapsed_minutes;
				$commhours_used += $elapsed_minutes;
				if ($event_val['statusname'] == 'Independent Study') {
					$studyhours_remaining -= $elapsed_minutes;
					$studyhours_used += $elapsed_minutes;
				}
			} else {
				$offsitehours_remaining -= $elapsed_minutes;
				$offsitehours_used += $elapsed_minutes;
				// if (($event_val['statusname'] == 'Not Checked In') && ($event_datetime_1 >= new DateTime($starttime))) {
				// 	$num_unexpected += 1;
				// 	$num_lates += 1;
				// }
				if ($event_val['statusname'] == 'Late') {
					$num_lates += 1;
				}
				if ($event_val['statusname'] == 'Absent') {
					$num_absent += 1;
				}
			}
		}
	}
}

?>


<h1 class='student_name'><?php echo $student_data_array[0]['firstname'] ?></h1> <!--Student name-->


		<div class="timepickers">
            <input type='text' id='firstdatetimepicker' class='firstdatetimepicker' name='firstdatetimepicker' placeholder="select start date">
		  <input type='text' id='lastdatetimepicker' class='lastdatetimepicker' name='lastdatetimepicker' placeholder="select end date">

		<select name='statusselect'><option value=''>All Statuses</option>
        <?php
			foreach ($statusdata as $statusoption) {
				$query = $db_server->query("SELECT statusname FROM statusdata WHERE statusid = $statusoption");
				$tempvar = $query->fetch_assoc();
				$tempstatname = $tempvar['statusname'];
				if($tempstatname != "Not Checked In"){
			?> <option value= '<?php echo $statusoption; ?> '> <?php echo $tempstatname; ?></option> <?php
			}
		}
        ?>
        </select>
		<input type='submit' name='studentsubmit' class='studentselect'>
		</div>

<?php
$daystillend = daysLeft(); ?>


<div class="reported-data">


<?php

//Offsite information echoing
$offsiteHrs_remaining = floor($offsitehours_remaining / 60);
$offsiteMin_remaining = $offsitehours_remaining % 60;

$objstarttime = new DateTime($starttime);
$objendtime = new DateTime($endtime);
$daydiff = $objstarttime->diff($objendtime);
$minutesinday = $daydiff->format('%i');
$hoursinday = $daydiff->format('%h');
$hoursinday = $hoursinday * 60;
$totalminsinday = $hoursinday + $minutesinday;
$totalminsinday = $totalminsinday / 60;

if ($minutesinday < 1){
	$minutesinday = 1;
};

$fulldaysleft = floor($offsitehours_remaining / $minutesinday);

$readable_offsiteleft = "<p class='reporttext'> You have " . $offsiteHrs_remaining . " hours and " . $offsiteMin_remaining . " minutes of offsite left. </p>";
if ($offsitehours_remaining < 0) {
	$readable_offsiteleft = "<p class='reporttext'> You are out of offsite! You are over by " . $offsiteHrs_remaining . " hours and " . $offsiteMin_remaining . " minutes. </p>";
}

if($notfulldata == 1){
	//echo "<p class='reporttext' id='reportnote'> NOTE: The below information is only for the selcted status.</p>";
	if(!empty($_POST['statusselect'])){
		echo "Now displaying " . statconvert($_POST['statusselect']) . " events";
		?> <br> <br> <?php
	}

	if($notfulldatedata == 1){ // DO THIS IF THE USER HAS OPTED TO SHOW ONLY A SUBSET OF DATES

		echo "<p class='reporttext'>During this period:</p>";
		$offsiteHrs_used = floor(($offsitehours_used) / 60);
		$offsiteMin_used = $offsitehours_used % 60;
		echo "<p class='reporttext'> You used " . $offsiteHrs_used . " hours and " . $offsiteMin_used . " minutes of offsite time.</p>";

		//late printout
		echo "<p class='reporttext'> You were late " . $num_lates;

		if ($num_lates == 1) {
			echo " time.</p>"; } else { echo " times.</p>";
		}

		// echo "<p class='reporttext'> You were unexpectedly late " . $num_unexpected;
		// if ($num_unexpected == 1) {

		// 	echo " time.</p>"; } else { echo " times.</p>";
		// }
		//Absent
		echo "<p class='reporttext'> You were absent " . $num_absent;
		if ($num_absent == 1) { echo " time.</p>"; } else { echo " times.</p>"; }
		//Independent Study
		$studyHrs_used = floor($studyhours_used / 60);
		$studyMin_used = $studyhours_used % 60;
		echo "<p class='reporttext'> You used " . $studyHrs_used . " hours and " . $studyMin_used . " minutes of independent study time.</p>";
	}
	?> <br> <br> <?php
} else {

echo $readable_offsiteleft;

$offsiteHrs_used = floor(($offsitehours_used) / 60);
$offsiteMin_used = $offsitehours_used % 60;

// this uses a function from functions.php

if ($daystillend > 0) {
$minutesperday = floor($offsitehours_remaining / $daystillend);
echo "<p class='reporttext'> You have " . $minutesperday . " minutes of offsite per day.</p>";
echo "<p class='reporttext'> School days left: " . $daystillend. "</p>";
} else {
echo "<p class='reporttext'> The school year has ended.</p>";
}

echo "<p class='reporttext'> You have used " . $offsiteHrs_used . " hours and " . $offsiteMin_used . " minutes of your offsite time.</p>";

echo "<p class='reporttext'> You have &asymp; " . round($offsiteHrs_remaining/$totalminsinday, 1) . " full days of offsite left.</p>";

$daysInYear = daysLeftFromDate($globalsdata['startdate']);

$yearPercent = floor(100 - ($daystillend / $daysInYear * 100));

$offsitePercent = floor($offsiteHrs_used / $offsiteremaining * 100);

echo "<p class='reporttext'> The school year is " . $yearPercent . "% complete and you have used " . $offsitePercent . "% of your offsite.</p>";

//Late information echoing
echo "<p class='reporttext'> You have been late " . $num_lates;
if ($num_lates == 1) {
	echo " time.</p>";
} else {
	echo " times.</p>";
}
echo "<p class='reporttext' title='" . $altText . "'> You have been unexpectedly late " . $num_unexpected;
if ($num_unexpected == 1) {
	echo " time.</p>";
} else {
	echo " times.</p>";
}
echo "<p class='reporttext'> You have been absent " . $num_absent;
if ($num_absent == 1) { echo " time.</p>"; } else { echo " times.</p>"; }

//IS information echoing
$studyHrs_remaining = floor($studyhours_remaining / 60);
$studyMin_remaining = $studyhours_remaining % 60;

$readable_studyleft = "<p class='reporttext'> You have " . $studyHrs_remaining . " hours and " . $studyMin_remaining . " minutes of independent study left. </p>";
if ($studyhours_remaining < 0) {
	$readable_studyleft = "<p class='reporttext'> You are out of independent study! You are over by " . $studyHrs_remaining . " hours and " . $studyMin_remaining . " minutes. </p>";
}
echo $readable_studyleft;

$studyHrs_used = floor($studyhours_used / 60);
$studyMin_used = $studyhours_used % 60;
echo "<p class='reporttext'> You have used " . $studyHrs_used . " hours and " . $studyMin_used . " minutes of your independent study time.</p>";
/*}*/
}
?>
</div>



<div class='stats-container'>

<?php
// CHARTS
$uniqueLoc = array();
$siteCount = array();
$totalCount = 0;
foreach ($getStatsResult as $sub) {
	$totalCount += 1;
	if (!in_array($sub['info'], $uniqueLoc)) {

		array_push($uniqueLoc, $sub['info']);
	}
/*echo "<pre>";
print_r($uniqueLoc);
echo "</pre>";
echo "<p>" . $sub['info'] . "</p>";*/
}

$siteCount = array_fill_keys($uniqueLoc, 0);
foreach ($getStatsResult as $child) {
	$siteCount[$child['info']] += 1;
}

/*for ($n = 0; $n < count($uniqueLoc); $n++) {
	$count1 = $siteCount[$uniqueLoc[$n]] / $totalCount;
	$count2 = $count1 * 100;
	$count = number_format($count2, 0);
	echo "<p>" . $uniqueLoc[$n] . " (" . $siteCount[$uniqueLoc[$n]] . ")    " . $count . "%</p>";
	//echo "<p style='font-size:5px'>insertrows.push(['" . $uniqueLoc[$n] . "', " . $siteCount[$uniqueLoc[$n]] . "]);</p>";
}*/
if($notfulldata==false){
?>
<h3>Offsite use</h3>
 <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
var locCount = <?php echo count($uniqueLoc); ?>;
/*var string = <?php echo json_encode("holaaaa"); ?>;
var int = <?php echo json_encode(7); ?>;*/
var d = 0;
var insertrows = [];
//insertrows.push(['new',4]);
<?php
//echo "insertrows.push(['" . $uniqueLoc[0] ."', " . $siteCount[$uniqueLoc[0]] . "]);";
for ($n = 0; $n < count($uniqueLoc); $n++) {
echo "insertrows.push(['" . str_replace("'", "", $uniqueLoc[$n]) . "', " . $siteCount[$uniqueLoc[$n]] . "]);";
}

?>
var rows = new Array();

      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'name');
        data.addColumn('number', 'times');
        data.addRows(insertrows);
	data.sort({column: 1, desc: true});

        // Set chart options
        var options = {
                       'width': '280',
                       'height': '200',
                       'chartArea': {'width': '300%', 'height': '80%'},
                       'legend': {'position': 'right', 'alignment':'center'},
			'backgroundColor':'transparent',
			'sliceVisibilityThreshold': 2/100, // This is equivalent to 0.625 or 62.5% of the chart.
			'pieSliceText': 'percentage',
			};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>

<div id="chart_div"></div>
</div> <!--ending stats-container div-->
<?php } ?>

</div><table class='eventlog' id="viewreports">


<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Info</th>
<?php

$reversed_student_array = array_reverse($student_data_array);
$temp_time = new DateTime();
$lastprettytime = null;
foreach ($reversed_student_array as $event) {

$temp = $event['returntime'];
$displayNiceTime = new DateTime($temp);

if ($event['statusname'] != "Not Checked In"){
	$pretty_time = new DateTime($event['timestamp']);

        if($temp_time->format('D, M') != $pretty_time->format('D, M')) {
            echo '<tr class="' . $event["statusname"] . ' new-date">';
            $temp_time = $pretty_time; ?>

        <?php }
        else { ?>
            <tr class="<?php echo $event['statusname'] ?>">
        <?php
		}
		// this makes it so if multiple events have the same date, it only displays the date for the first one
		$currentprettytime = $pretty_time->format('D, M j');
		if ($currentprettytime != $lastprettytime){
			?> <td><?php echo $currentprettytime ?></td><?php
			$lastprettytime = $currentprettytime;
		} else {
			?> <td></td> <?php
		}
		?>
        <td><?php echo $pretty_time->format('g:i a') ?></td>
        <td><?php echo $event['statusname'] ?></td>
	<td><?php
	$stringdisplay = " ";

if($event['statusname'] == "Field Trip"){
	$stringdisplay = "with ";
} elseif ($event['statusname'] == "Offsite"){
	$stringdisplay = "at ";
}

	if($event['statusname'] != "Present" && $event['statusname'] != "Checked Out" && $event['statusname'] != "Absent"){
		echo $stringdisplay . $event['info'] . " expected " . $displayNiceTime->format('g:i a') ?> </td>
		<?php
			} else {
				echo $event['info'];
			}
			?>
	</tr>
<?php }
}
 ?>
</table>
<?php
} else {
echo "<h1 class='student_name'>" . idtoname($current_student_id) . "</h1>";
echo "There are no events from this time period";
echo "<br>";
?>
<div class='timepickers2'>
<input type='text' id='firstdatetimepicker' class='firstdatetimepicker' name='firstdatetimepicker' placeholder="select start date">
<input type='text' id='lastdatetimepicker' class='lastdatetimepicker' name='lastdatetimepicker' placeholder="select end date">
		<select name='statusselect'><option value=''>All Statuses</option>
        <?php
			foreach ($statusdata as $statusoption) {
				$query = $db_server->query("SELECT statusname FROM statusdata WHERE statusid = $statusoption");
				$tempvar = $query->fetch_assoc();
				$tempstatname = $tempvar['statusname'];
				if($tempstatname != "Not Checked In"){
			?> <option value= '<?php echo $statusoption; ?> '> <?php echo $tempstatname; ?></option> <?php
		}
			}
        ?>
        </select>
		<input type='submit' name='studentsubmit' class='studentselect'>

</div>
<?php
}
}

//globals query
$globalsquery = "SELECT * FROM globals";
$globals_result = $db_server->query($globalsquery);
$globalsdata = $globals_result->fetch_array();
$startdateforpicker = $globalsdata['startdate'];
$enddateforpicker = $globalsdata['enddate'];
$startdateforpicker = new DateTime($startdateforpicker);
$enddateforpicker = new DateTime($enddateforpicker);
$startdateforpicker = $startdateforpicker->format('Y/m/d');
$enddateforpicker = $enddateforpicker->format('Y/m/d');

?>
</div>
</form>
<script>
	$('#firstdatetimepicker').datetimepicker({
            onGenerate:function( ct ){
               jQuery(this).find('.xdsoft_date.xdsoft_weekend')
                  .addClass('xdsoft_disabled');
            },
            minDate:<?php echo(json_encode($startdateforpicker)); ?>,
            maxDate:<?php echo(json_encode($enddateforpicker)); ?>,
            format:'Y-m-d H:i:s',
			defaultTime:<?php echo(json_encode(substr($globalsdata['starttime'],0,-3))); ?>,
            step: 5,
         });
</script>
<script>
	$('#lastdatetimepicker').datetimepicker({
            onGenerate:function( ct ){
               jQuery(this).find('.xdsoft_date.xdsoft_weekend')
                  .addClass('xdsoft_disabled');
            },
            minDate:<?php echo(json_encode($startdateforpicker)); ?>,
            maxDate:<?php echo(json_encode($enddateforpicker)); ?>,
            format:'Y-m-d H:i:s',
			defaultTime:<?php echo(json_encode(substr($globalsdata['starttime'],0,-3))); ?>,
            step: 5,
         });
</script>
</body>
</html>
