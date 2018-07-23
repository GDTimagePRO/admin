<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;
	//echo $_POST['s'];
	if(get_magic_quotes_gpc()){
	  $d = stripslashes($_POST['s']);
	}else{
	  $d = $_POST['s'];
	}
	//echo $d;
	$d = str_replace(",]","]",$d);
	$d = str_replace("undefined","1",$d);
	//echo $d;
	$json = json_decode(rawurldecode($d),true);
	$order_id = $json['orderitem_id'];
	$color = $json['color'];
	$db->updateColor($color,$order_id);
	//echo $order_id;
	$textlines = $json['textlines'];
	$images = $json['images'];
	$borders = $json['borders'];
	$lines = $json['lines'];
	$tables = $json['tables'];
	$data = $json['data'];
	//echo sizeof($order_id." ".$textlines);
	$s = "";
	foreach($textlines as $textline){
		echo "Updating textline ".$textline['text']."<br>";
		echo $db->updateTextLine($textline);
		
	}
	
	//echo "IMAGE LENGTH ".sizeof($images);
	foreach($images as $image){
		$s.= $db->updateImage($image);
		
	}
	//echo "BORDER LENGTH ".sizeof($borders);
	//$s = "";
	foreach($borders as $border){
		$s.= $db->updateBorder($border);
	}
	
	foreach($lines as $line){
		$s.= $db->updateLine($line);
	}
	foreach($tables as $table){
		$s.= $db->updateTable($table);
	}
	
	$s.= $db->updateOrderData($order_id,$data);
	
	//echo $s;
	//echo $d;
?>