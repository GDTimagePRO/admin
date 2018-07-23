<?php
include_once "../Backend/startup.php";
	$startup = Startup::getInstance("../");
	$db = $startup->db;
	//echo $_POST['s'];
	if(get_magic_quotes_gpc()){
	  $d = stripslashes($_POST['s']);
	}else{
	  $d = $_POST['s'];
	}
	$name = $_POST['name'];
	$category =$_POST['category'];
	$data = $_POST['data'];
	echo $db->saveTemplate($name,$category,$d,$data);
?>
