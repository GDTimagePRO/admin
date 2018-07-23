<?php 
	include_once "_common.php";
	
	$loaded = $_order_db->getOrderItemsByOrderId(
			1,
			$_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE]
		);
	
// 	$loaded = $_order_db->getOrdersByUserId(
// 			100,
// 			$_settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE]
// 		); 
?>