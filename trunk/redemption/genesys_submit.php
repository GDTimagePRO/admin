<?php
	require_once 'backend/session.php';
	if( !isset($_GET['sid']) || !isset($_GET['orderDetails']))
	{
		Header("location: index.php");
		exit();
	} 
	
	$genesysSID = $_GET['sid'];	
	
	/* @var $orderDetails  GenesysOrderDetails */
	$orderDetails = json_decode($_GET['orderDetails']);
		
	$session = Session::create($genesysSID . '_' . $orderDetails->orderItemId);
	
	$attachment = json_decode($orderDetails->attachment);
	unset($orderDetails->attachment); 
	
	$session->orderDetails = $orderDetails;
	$session->code = $attachment[0]; 
	$session->customerId = $attachment[1];
	
	$session->save();
	
	Header('location: shipping.php?sid=' . urlencode($session->sessionId));
?>