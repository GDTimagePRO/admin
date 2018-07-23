<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;
	$d = null;
	if(get_magic_quotes_gpc()){
	  $d = stripslashes($_POST['s']);
	}else{
	  $d = $_POST['s'];
	}	
	echo $db->setOrderItemDesign($startup->session->getCurrentItem(), $d);
?>