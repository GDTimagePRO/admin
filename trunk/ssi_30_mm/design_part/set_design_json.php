<?php
	require_once '../backend/session.php';

	if(!isset($_POST['index']) || !isset($_POST['json']) || !isset($_GET['sid']))
	{
		header("HTTP/1.0 500 Internal Server Error");
		exit();		
	}	
	
	$session = Session::load($_GET['sid']);
	$designEnvironment = is_null($session) ? NULL : $session->designEnvironment; 
	if(is_null($designEnvironment))
	{
		header("HTTP/1.0 500 Internal Server Error");
		exit();		
	}
	
 	/* @var $activeDesign ActiveDesign */
	$activeDesign = $designEnvironment->activeDesigns[$_POST['index']];
	$activeDesign->design->designJSON = $_POST['json'];
	$session->save();
	
	echo 'true';
?>