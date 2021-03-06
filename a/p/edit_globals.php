<?php require_once('../../login.php'); ?>
<html>
<head>
	<title>Edit Globals</title>
	<?php require_once('header.php'); ?>
    
    	    <script type="text/javascript">
			$(document).ready(function(){
				$('#endtime').timepicker({ 'scrollDefaultNow': true, 'minTime': '7:00am', 'maxTime': '5:30pm', 'timeFormat': 'H:i', 'step': 5 });
				$('#starttime').timepicker({ 'scrollDefaultNow': true, 'minTime': '7:00am', 'maxTime': '5:30pm', 'timeFormat': 'H:i', 'step': 15 });
			});
		</script>
    
</head>
<body class="adminpage edit-globals">
<?php
// Header Info
$HeaderStatus = null;
$HeaderInfo = "Update Globals";
    // EDIT (UPDATE) GLOBALS
if (isset($_POST['save'])) {
 $editstartdate = strtotime($_POST['editstartdate']);
 $editenddate = strtotime($_POST['editenddate']);
 $newStuTimeout = $_POST['EditStudentTimeout'];
 $newAdmTimeout = $_POST['EditAdminTimeout'];
 $stmt = $db_server->prepare("UPDATE globals SET startdate = FROM_UNIXTIME(?) , enddate = FROM_UNIXTIME(?) , starttime = ? , endtime = ? , adminTimeout = ? , studentTimeout = ? WHERE id = ?");
$stmt->bind_param('ssssiii', $editstartdate, $editenddate, $_POST['starttime'], $_POST['endtime'], $newAdmTimeout, $newStuTimeout,  $_POST['id']); 
	  $stmt->execute(); 
	  $stmt->close();
	} 

	

// GET THE LIST OF GLOBALS
$globalsresult = $db_server->query("SELECT * FROM globals ORDER BY startdate");
    ?>	
                <div id="TopHeader" class="<?php echo $HeaderStatus; ?>">
              <h1 class="Myheader"><?php echo $HeaderInfo; ?></h1>
                </div>
<div align="center" id="main">
<div class="admintable">
<table class="global_table">
    
   <tr>
      <th>Start Date</th>
      <th>End Date</th>
      <th>Start Time</th>
      <th>End Time</th>
      <th>Admin Login Cookie Duration (Days)</th>
      <th>Student Login Cookie Duration (Days)</th>
	  <th>Edit</th>
   </tr>
<?php
// Make list of globals
while ($list = mysqli_fetch_assoc($globalsresult)) { ?>

<form action="" method="post">
<input type="hidden" name="id" value="<?php echo $list['id']; ?>">
	<tr>
		<?php $editme = "edit-" . $list['id'];
			if (isset($_POST[$editme])) { 
			$adjustedstartdate = new DateTime($list['startdate']);
			$adjustedenddate = new DateTime($list['enddate']);
		?> 
        <td><input type="text" name="editstartdate" id="EStartDate" value="<?php echo $adjustedstartdate->format('m-d-Y'); ?>" required size="15"></td>
		<td><input type="text" name="editenddate" id="EEndDate" value="<?php echo $adjustedenddate->format('m-d-Y'); ?>" required size="15"></td>
		<td><input type="text" name="starttime" id="starttime" value="<?php echo $list['starttime']; ?>" required size="10"></td>
                <td><input type="text" name="endtime" id="endtime" value="<?php echo $list['endtime']; ?>" required size="10"></td>
<td><input type="number" name="EditAdminTimeout" value="<?php echo $list['adminTimeout']; ?>" required min="1" max="35"></td>
<td><input type="number" name="EditStudentTimeout" value="<?php echo $list['studentTimeout']; ?>" required min="1" max="35"></td>
		<td><button type="submit" name="save" value="<?php echo $list['id']; ?>">Save</button></td>
		<?php } else { ?>
		<td><?php echo $list['startdate']; ?></td>
		<td><?php echo $list['enddate']; ?></td>
        <td><?php echo $list['starttime'] ?></td>
		<td><?php echo $list['endtime'] ?></td>
        <td><?php echo $list['adminTimeout'] ?></td>
		<td><?php echo $list['studentTimeout'] ?></td>
		<td><input type="submit" name="edit-<?php echo $list['id']; ?>" value="Edit"></td>
		<?php } ?>
	</tr>
</form>
<?php 
} // end while
?>
</table>
</div>    
                    </div>
  <!-- date picker javascript -->          
<script src="js/pikaday.js"></script>
<script>
    var picker = new Pikaday({ field: document.getElementById('EEndDate') });
</script>
<script src="js/pikaday.js"></script>
<script>
    var picker = new Pikaday({ field: document.getElementById('EStartDate') });
</script>
</body>
</html>