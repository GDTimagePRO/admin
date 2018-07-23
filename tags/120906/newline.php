<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;

	$order_id = $_GET['order_id'];
	$x = $_GET['x'];
	$y = $_GET['y'];
	$x2 = $_GET['x2'];
	$y2 = $_GET['y2'];
	$type_id = $_GET['type_id'];
	$line_width = $_GET['line_width'];
	echo $db->newLine($order_id,$x,$y,$x2,$y2,$type_id,$line_width);
?>