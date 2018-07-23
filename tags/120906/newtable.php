<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;

	$order_id = $_GET['order_id'];
	$x = $_GET['x'];
	$y = $_GET['y'];
	$width = $_GET['width'];
	$height = $_GET['height'];
	$rows = $_GET['rows'];
	$columns = $_GET['columns'];
	$border = $_GET['border'];
	echo $db->newTable($order_id,$x,$y,$width,$height,$rows,$columns,$border);
?>