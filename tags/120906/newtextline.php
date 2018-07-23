<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;

	$order_id = $_GET['order_id'];
	
	echo $db->newTextLine($order_id);
?>