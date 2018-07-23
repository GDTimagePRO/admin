<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;

	$image_id = $_GET['id'];
	
	$db->deleteGraphic($image_id);
?>