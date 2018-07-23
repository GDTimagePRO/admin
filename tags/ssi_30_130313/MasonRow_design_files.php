<?php

	include_once "_common.php";
	include_once "./backend/order_logic.php";
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	$query = 'SELECT * FROM batch_queue WHERE image_id != -1';
	$result = mysql_query($query,$_system->db->connection);
	if(!$result)
	{
		echo "Error reading from queue<br>";
		exit;
	}
	
	while($row = mysql_fetch_assoc($result))
	{
		$image = $_image_db->getImageById($row['image_id']);
		if($image != null)
		{
			//display_name-prodyct_id-order_id-unique_id-order-line
			//104699-7213-1		
			//TODO: prefix -... 
			
			$dirName = 'batch_images/';
			if(substr($row['product_id'], 0, 3) == 'TR-')
			{
				$dirName .= $row['product_id'];
			}
			else
			{
				$dirName .= $row['display_name']; 
			}
			 
			if(!file_exists($dirName) OR !is_dir($dirName)){
				mkdir($dirName);
			}
			
			$fileName = sprintf("%s/%07d-%05d-%d-%s-%s.png",$dirName,$row['order_id'], $row['unique_id'], $row['order_line'], $row['display_name'], $row['product_id']);			
			file_put_contents($fileName, $image->data);
		}
		else
		{
			echo 'emptry row :'.$row['id'].'<br>'; 
		}
	}
	echo 'done<br>';
?>