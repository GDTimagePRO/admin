<?php
	include_once "_common.php";
	include_once "backend/workstation_interface.php"
?>	

<!DOCTYPE html>
<html>
<head></head>
<body>
<table>
<tr>
	<td>submit_time</td>
	<td>order_id</td>
	<td>design_id</td>
	<td>image_id</td>
	<td>product_name</td>
	<td>material</td>
	<td>extras</td>
</tr>
<?php
	$wdb = new WorkstationDB($_system->db->connection);
	$result = $wdb->getItemsByMaterial(-1, ProcessingStage::STAGE_READY);
	foreach($result as $item)
	{
		echo '<tr>';
		echo '<td>'.htmlspecialchars($item->submit_time).'</td>';
		echo '<td>'.htmlspecialchars($item->order_id).'</td>';
		echo '<td>'.htmlspecialchars($item->design_id).'</td>';
		echo '<td><a target="_blank" href="design_part/get_image.php?nocache=true&id='.$item->image_id.'">';
		echo htmlspecialchars($item->image_id).'</a></td>';
		echo '<td>'.htmlspecialchars($item->product_name).'</td>';
		echo '<td>'.htmlspecialchars($item->material).'</td>';
		echo '<td>'.htmlspecialchars($item->extras).'</td>';
		echo '</tr>';
	}
	
	//if(count($result) > 0)
	//{
	//	$wdb->updateStatus(end($result)->order_id, ProcessingStage::STAGE_ARCHIVED);
	//}
?>
</table>
</body>
</html>