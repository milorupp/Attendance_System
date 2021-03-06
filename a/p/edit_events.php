<?php
if (!empty($_POST['studentselect'])) {
    $current_student_id = $_POST['studentselect'];
    header("Location: " . basename($_SERVER['PHP_SELF']) . "?id=" . $current_student_id);
    exit();
}
require_once('../../login.php');
require_once("../../connection.php");
require_once("../../function.php");

if (!empty($_GET['eventid'])) {
    $eventid = $_GET['eventid'];
    $changerow = "inline_edit_" . $_GET['eventid'];
    $deleterow = "inline_delete_" . $_GET['eventid'];
    if (!empty($_POST['edit_submit'])) { // THESE FUNCTIONS UPDATE AN EDITED EVENT
        if (!empty($_POST['status_select'])) {
            $statusid = $_POST['status_select'];
            $update = $db_server->prepare("UPDATE events SET statusid=? WHERE eventid=?");
            $update->bind_param('ii', $statusid, $eventid);
            $update->execute();
            $update->close();
        }
        if (!empty($_POST['info_edit'])) {
            $info = strip_tags($_POST['info_edit']);
            $update = $db_server->prepare("UPDATE events SET info=? WHERE eventid=?");
            $update->bind_param('si', $info, $eventid);
            $update->execute();
            $update->close();
        }
        if (!empty($_POST['returntime_edit'])) {
            $return_date = new dateTime($_POST['stamp_edit']);
            $return_date = $return_date->format('Y-m-d');
            $time = $return_date . " " . $_POST['returntime_edit'];
            $update = $db_server->prepare("UPDATE events SET returntime=? WHERE eventid=?");
            $update->bind_param('ss', $time, $eventid);
            $update->execute();
            $update->close();
        }
        if (!empty($_POST['stamp_edit'])) {
            $stamp = $_POST['stamp_edit'];
            $update = $db_server->prepare("UPDATE events SET timestamp=? WHERE eventid=?");
            $update->bind_param('ss', $stamp, $eventid);
            $update->execute();
            $update->close();
        }
    }
    if (!empty($_POST[$deleterow])) {
        $delete = $db_server->prepare("DELETE FROM events WHERE eventid=?");
        $delete->bind_param('i', $eventid);
        $delete->execute();
        $delete->close();
        }
    elseif (!empty($_POST[$deleterow])) {

    }
}
if (!empty($_POST['new_submit'])) { // TODO require return times for field trip and offsite??
   if (!empty($_POST['new_timestamp']) && !empty($_POST['new_status_id'])) {
      // write to the database
      $return_date = new dateTime($_POST['new_timestamp']);
      $return_date = $return_date->format('Y-m-d');
      $return_with_date = $return_date . " " . $_POST['new_return'];
      $newinfo = strip_tags($_POST['new_info']);
      $stmt = $db_server->prepare("INSERT INTO events (studentid, statusid, info, returntime, timestamp) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param('iisss', $_POST['new_event_student_id'], $_POST['new_status_id'], $newinfo, $return_with_date, $_POST['new_timestamp']);
      $stmt->execute();
      $stmt->close();
   } else {
      // error handling because they didn't provide both a timestamp and a status
      echo "<div class='error'>To add an event, you must supply both a timestamp and a status.</div>";
   }
}

//current students array
$studentquery = "SELECT studentid, firstname, lastname FROM studentdata WHERE current=1 ORDER BY firstname";
$current_users_query = $db_server->query($studentquery);
$current_users_result = array();
while ($student = $current_users_query->fetch_array()) {
    array_push($current_users_result, $student);
}
//status query
$status_result = $db_server->query("SELECT DISTINCT statusname, statusid FROM statusdata");
$status_array = array();
while ($blah = $status_result->fetch_assoc()) {
    array_push($status_array, $blah);
}
//keeps student id in get var
if (!empty($_GET['id'])) {
    $current_student_id = $_GET['id'];
}

?>

<html>

<head>
   <title>Edit Events</title>
   <link rel="stylesheet" type="text/css" href="../css/jquery.datetimepicker.css" />
   <?php require_once('header.php'); ?>
   <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
   <script src="js/scrollTo.js"></script>
   <script src="js/jquery.datetimepicker.js"></script>
   <link rel="stylesheet" type="text/css" href="../css/jquery.datetimepicker.css" />
</head>

<body class="edit-events">

<?php if (!empty($_GET['eventid'])) {
   if (!empty($_POST[$deleterow])) {
      echo "<div class='error'>Event #".$eventid." was deleted.</div>";
   }
} ?>

   <div id="TopHeader" class="">
              <h1 class="edit_events_Myheader">Edit Events</h1>
                </div>
            <div align="center" id="main">
  <!-- <div class="centerr" style="margin: 1em;">
      <a href="../" style="color: #fff;">Temporary link to main admin interface</a>
   </div>-->
   <div class="centerr">
      <form method='post' id='studentform' class='studentselect' action='<?php echo basename($_SERVER['PHP_SELF']); ?>'>
         <select name='studentselect'>
            <?php foreach($current_users_result as $student) { ?>
        <?php $lastinitial = substr($student['lastname'], 0, 1); ?>
               <option value='<?php echo $student['studentid']; ?>' <?php if (!empty($_GET['id'])) { if ($_GET['id'] == $student['studentid']) { echo 'selected';}} ?>><?php echo $student['firstname']?><?php echo " "?><?php echo $lastinitial?></option>
            <?php } ?>
         </select>
         <input type='submit' name='studentsubmit' class='studentselect' value="Load this student's events">
      </form>
   </div>
   <?php

      //globals query
      $globalsquery = "SELECT * FROM globals";
      $globals_result = $db_server->query($globalsquery);
      $globalsdata = $globals_result->fetch_array();
      $tempstartdate = $globalsdata['startdate'];
      $tempenddate = $globalsdata['enddate'];
      $SFirstDateFromPicker = new DateTime($tempstartdate);
      $SLastDateFromPicker = new DateTime($tempenddate);
      $SFirstDateFromPicker = $SFirstDateFromPicker->format('Y-m-d H:i:s');
      $SLastDateFromPicker = $SLastDateFromPicker->format('Y-m-d H:i:s');

      if (isset($current_student_id)) {
         $student_data_array = array();
         //fetches most recent data from the events table
         //joins with the tables that key student names/status names to the ids in the events table
         $result = $db_server->query("SELECT info,statusname,studentdata.studentid,studentdata.firstname,studentdata.lastname,timestamp,returntime,events.eventid, yearinschool
            FROM events
            JOIN statusdata ON events.statusid = statusdata.statusid
            RIGHT JOIN studentdata ON events.studentid = studentdata.studentid
            WHERE studentdata.studentid = $current_student_id
            AND timestamp BETWEEN '$SFirstDateFromPicker' AND '$SLastDateFromPicker'
            ORDER BY timestamp DESC") or die(mysqli_error($db_server));
         while ($student_data_result = $result->fetch_assoc()) {
            array_push($student_data_array, $student_data_result);
         }
         $current_student = $student_data_array[0]['firstname'] . " " . $student_data_array[0]['lastname'];
   ?>
      <h2 class="title">Events for: <span><?php echo $current_student; ?></span></h2>
      <table class='newevent eventlog'>
         <tr>

            <th>Timestamp</th>
            <th>Status</th>
            <th>Info</th>
            <th>Return Time</th>
            <th></th>
         </tr>
         <tr class="new-event">
            <form method="post" name="new_event" action="<?php echo basename($_SERVER['PHP_SELF']); ?>?id=<?php echo $current_student_id; ?>">

                <td>
                  <input type='text' id='new_timestamp' name='new_timestamp' placeholder='Timestamp'>
               </td>
               <td>
                  <select name='new_status_id'>
                     <option value="">Select...</option>
                     <?php foreach($status_array as $status) { ?>
                        <option value='<?php echo $status["statusid"] ?>'><?php echo $status['statusname'] ?></option>
                     <?php } ?>
                  </select>
               </td>
               <td>
                  <input type='text' name='new_info' placeholder="Info">
               </td>
               <td>
                  <input type='text' id="new_return" name='new_return' placeholder="Return time"> <!--MAKE HH:MM-->
               </td>
               <td>
                  <input type='submit' name='new_submit' value='Add New Event'>
               </td>
               <input type="hidden" name="new_event_student_id" value="<?php echo $_GET['id']; ?>">
            </form>
         </tr>
      </table>
      <table class='eventlog'>
         <tr>
            <th>Timestamp</th>
            <th>Status</th>
            <th>Info</th>
            <th>Return Time</th>
            <th>Edit</th>
         </tr>
         <?php
         global $timestamp_to_edit;
         foreach ($student_data_array as $event) {
            if ($event['statusname'] != 'Not Checked In') {
               $postedit = "inline_edit_" . $event['eventid'];
               $nice_timestamp = new DateTime($event['timestamp']);
	       $nice_returntime = new DateTime($event['returntime']);
               if (!empty($_POST[$postedit])) {
                  $timestamp_to_edit = $event['timestamp']; // Capture this to pass to the JS timepicker below
            ?>
            <form method='post' name='inline_edit' action='<?php echo basename($_SERVER['PHP_SELF']); ?>?id=<?php echo $current_student_id; ?>&eventid=<?php echo $event['eventid']; ?>'>
              <tr class="editing-row">
                  <td>
                     <input type='text' id='stamp_edit' name='stamp_edit' value='<?php echo $event['timestamp']; ?>'>
                  </td>
                  <td>
                     <select name='status_select'>
                        <?php foreach($status_array as $status) { ?>
                           <option value='<?php echo $status["statusid"] ?>' <?php if ($status['statusname'] == $event['statusname']) { echo 'selected'; } ?>> <?php echo $status['statusname'] ?></option>
                        <?php } ?>
                     </select>
                  </td>
                  <td>
                     <input type='text' name='info_edit' value='<?php echo $event['info'] ?>'>
                  </td>
                  <td>
                     <?php
                     $modifed_return = new dateTime($event['returntime']);
                     $modifed_return = $modifed_return->format('h:i:s');
                     ?>
                     <input type='text' name='returntime_edit' id='returntime_edit' value='<?php if ($event['statusname'] == 'Offsite' || $event['statusname'] == 'Field Trip' || $event['statusname'] == 'Late' || $event['statusname'] == 'Independent Study') {echo $modifed_return;} ?>'>
                  </td>
                  <td>
                     <input type='submit' name='edit_submit' value='Save'>
                     <input type='submit' name='cancel_submit' value='Cancel'>
                  </td>
              </tr>
            </form>
            <?php } else { ?>
            <tr class="<?php echo $event['statusname'] ?>">
               <td><?php echo $nice_timestamp->format('D, M j ');?>&nbsp;&nbsp;&nbsp;<?php echo $nice_timestamp->format(' g:i a');?></td>
               <td><?php echo $event['statusname'] ?></td>
               <td><?php echo $event['info'] ?></td>
               <td><?php if ($event['statusname'] == 'Offsite' || $event['statusname'] == 'Field Trip' || $event['statusname'] == 'Late' || $event['statusname'] == 'Independent Study') {echo $nice_returntime->format(' g:i a');} ?></td>
               <td>
                  <form method='post' class='edit_interface' action='<?php echo basename($_SERVER['PHP_SELF']); ?>?id=<?php echo $current_student_id; ?>&eventid=<?php echo $event['eventid']; ?>'>
                   <input name='eventid' type='hidden' value='<?php echo $event['eventid'] ?>'>
                   <input type='submit' name="inline_edit_<?php echo $event['eventid']?>" value='Edit'>
                   <input type='submit' name="inline_delete_<?php echo $event['eventid']?>" value='Delete' onclick="return confirm('Are you sure you want to delete this event?');">
                  </form>
               </td>
            </tr>
            <?php
              } //end else
            } // end if not Not Checked in
         } // end foreach event
      } // end if isset studentid

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

		  	$TimeQuery = $db_server->query("SELECT starttime,endtime FROM globals");
			$TimeQuery = $TimeQuery->fetch_array();
			$globalstarttime = new DateTime($TimeQuery['starttime']);
			$globalendtime = new DateTime($TimeQuery['endtime']);
			$globalstarttime = $globalstarttime->format("H:iA");
			$globalendtime = $globalendtime->format("H:iA");
      ?>
      </table>
   <script type="text/javascript"> // This is down here so that the appropriate record's timestamp can be used as default value
      $(document).ready(function(){
         $('#stamp_edit').datetimepicker({
            onGenerate:function( ct ){
               jQuery(this).find('.xdsoft_date.xdsoft_weekend')
                  .addClass('xdsoft_disabled');
            },
            minDate:<?php echo(json_encode($startdateforpicker)); ?>,
            maxDate:<?php echo(json_encode($enddateforpicker)); ?>,
            format:'Y-m-d H:i:s',
            value: '<?php echo $timestamp_to_edit; ?>',
            step: 5,
         });
         $('#returntime_edit').datetimepicker({
            datepicker: false,
            format:'H:i:s',
            minTime:<?php echo(json_encode($globalstarttime)); ?>,
            maxTime:<?php echo(json_encode($globalendtime)); ?>,
            step: 5,
         });
         $('#new_timestamp').datetimepicker({
            onGenerate:function( ct ){
               jQuery(this).find('.xdsoft_date.xdsoft_weekend')
                  .addClass('xdsoft_disabled');
            },
            minDate:<?php echo(json_encode($startdateforpicker)); ?>,
            maxDate:<?php echo(json_encode($enddateforpicker)); ?>,
            format:'Y-m-d H:i:s',
            step: 5,
         });
         $('#new_return').datetimepicker({
            datepicker: false,
            format:'H:i:s',
            minTime:<?php echo(json_encode($globalstarttime)); ?>,
            maxTime:<?php echo(json_encode($globalendtime)); ?>,
            step: 5,
         });
     $('body').scrollTo('.editing-row',{duration:'1000', offsetTop : '150'});
      });
   </script>
</body>
</html>
