<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;

	$order_id = $_GET['order_id'];
	$x = $_GET['x'];
	$y = $_GET['y'];
	$width = $_GET['width'];
	$height = $_GET['height'];
	$type_id = $_GET['type_id'];
	$style_id = $_GET['style_id'];
	$line_width = $_GET['line_width'];
	$radius = $_GET['radius'];
	$sides = $_GET['sides'];
	echo $db->newBorder($order_id,$x,$y,$width,$height,$type_id,$style_id,$line_width,$radius,$sides);
?>