<html>
<body>
<h1 class="headerr">Edit Globals</h1>
       
<?php

    // EDIT (UPDATE) GLOBALS
if (isset($_POST['save'])) {
 $editstartdate = strtotime($_POST['editstartdate']);
 $editenddate = strtotime($_POST['editenddate']);
 $stmt = $db_server->prepare("UPDATE globals SET startdate = FROM_UNIXTIME(?) , enddate = FROM_UNIXTIME(?) , starttime = ? , endtime = ? WHERE id = ?");
	  $stmt->bind_param('ssssi', $editstartdate, $editenddate, $_POST['starttime'], $_POST['endtime'], $_POST['id']); 
	  $stmt->execute(); 
	  $stmt->close();
	} 

	

// GET THE LIST OF GLOBALS
$globalsresult = $db_server->query("SELECT * FROM globals ORDER BY startdate");
    ?>	
    
<div class="globals">
<table>
    
   <tr>
      <th>Start Date</th>
      <th>End Date</th>
      <th>Start Time</th>
      <th>End Time</th>
	  <th>Edit</th>
   </tr>
<?php
// Make list of globals
while ($list = mysqli_fetch_assoc($globalsresult)) { ?>

<form action="?p=Globals" method="post">
<input type="hidden" name="id" value="<?php echo $list['id']; ?>">
	<tr>
		<?php $editme = "edit-" . $list['id'];
			if (isset($_POST[$editme])) { 
			$adjustedstartdate = new DateTime($list['startdate']);
			$adjustedenddate = new DateTime($list['enddate']);
		?> 
        <td><input type="text" name="editstartdate" id="editstartdate" value="<?php echo $adjustedstartdate->format('m d Y'); ?>" required></td>
		<td><input type="text" name="editenddate" id="editenddate" value="<?php echo $adjustedenddate->format('m d Y'); ?>" required></td>
		<td><input type="text" name="starttime" value="<?php echo $list['starttime']; ?>" required></td>
                <td><input type="text" name="endtime" value="<?php echo $list['endtime']; ?>" required></td>
		<td><button type="submit" name="save" value="<?php echo $list['id']; ?>">Save</button></td>
		<?php } else { ?>
        <?php $pretty_end_time = new DateTime($list['endtime']); ?>
        <?php $pretty_start_time = new DateTime($list['starttime']); ?>
		<td><?php echo $list['startdate']; ?></td>
		<td><?php echo $list['enddate']; ?></td>
        <td><?php echo $pretty_start_time->format('g:i a') ?></td>
		<td><?php echo $pretty_end_time->format('g:i a') ?></td>
		
		<td><input type="submit" name="edit-<?php echo $list['id']; ?>" value="Edit"></td>
		<?php } ?>
	</tr>
</form>
<?php 
} // end while
?>
</table>
</div>            
  <!-- date picker javascript -->          
<script src="js/pikaday.js"></script>
<script>
    var picker = new Pikaday({ field: document.getElementById('editstartdate') });
</script>
<script src="js/pikaday.js"></script>
<script>
    var picker = new Pikaday({ field: document.getElementById('editenddate') });
</script>
</body>
</html>