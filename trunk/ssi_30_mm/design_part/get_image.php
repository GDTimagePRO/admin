<?php
	require_once '../backend/settings.php';	
	
	$url = Settings::SERVICE_GET_IMAGE . '?id=' . urlencode($_GET['id']);
	if(isset($_GET['nocache']))
	{
		$url .= '&nocache=' . urlencode($_GET['id']);
	}
	
	Header("location: " . $url); 
?>