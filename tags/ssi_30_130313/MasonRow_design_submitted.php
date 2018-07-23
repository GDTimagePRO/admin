<?php
	include_once "_common.php";	
	include_once "./backend/order_logic.php";
	
	function doWork()
	{
		global $_order_db;
		global $_design_db;
		global $_system;
		global $_GET;
		
		$orderItemId = $_GET['orderItemId'];
		
		$batchItemId = $_GET['batchItemId'];
		
		if(!submitOrderItem($orderItemId, false)) return 'false';
		 
		$orderItem = $_order_db->getOrderItemById($orderItemId);
		if(is_null($orderItem)) return 'false';
			
		$design = $_design_db->getDesignById($orderItem->designId);
		if(is_null($design)) return 'false';
	
		$query = 'UPDATE batch_queue SET image_id = '.$design->imageId.' WHERE id = '.$batchItemId;
		$result = mysql_query($query,$_system->db->connection);
		if(!$result) return 'false';
		
		return null;
	}
	
	$error = doWork();
	if($error == null)
	{
		Header("location: MasonRow_design_customize.php");
	}
	else
	{
		echo $error;
	}

?>