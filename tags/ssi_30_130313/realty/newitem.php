<?php
	include_once "../Backend/startup.php";
	$startup = Startup::getInstance("../");
	$db = $startup->db;
	//echo $_POST['s'];
	$user_id = $_POST['userid'];
	$s = $startup->session;
	$s->setUserId($user_id);
	
	$image = $_POST['im'];
	
	$item_id = $db->newOrderItem($_POST['code']);
	
	$db->updateOrderData($item_id,$image);
	
	echo $item_id;
		
?>