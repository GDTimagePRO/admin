<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;

	$line_id = $_GET['id'];
	
	$db->deleteTextLine($line_id);
?>