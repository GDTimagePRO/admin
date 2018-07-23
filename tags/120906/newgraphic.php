<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;

	$order_id = $_GET['order_id'];
	$image_id = $_GET['image_id'];
	echo $db->newGraphic($order_id,$image_id);
?>