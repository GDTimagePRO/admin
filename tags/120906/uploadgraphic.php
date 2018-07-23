<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;
	$s = $startup->session;	
	$userId = $s->getUserId();//trim($_POST['userId']);
	//$s->setUserId($userId);
	$file = $_FILES['uploadGraphic'];
	$category = Database::USER_UPLOADED;
	$name = $file['name'];
	$contents =file_get_contents($file['tmp_name']);
	$contents = addslashes($contents);
	
	$db->newLibraryGraphic($name, $category, $contents,$userId);
	
?>