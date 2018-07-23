<?php 
	include_once "_common.php";	
	include_once "./backend/user_logic.php";
	include_once "./backend/order_logic.php";
	
	$barcodeStr = $_GET['code'];	
	$email = $_GET['emailUs'];	
	$siteName = $_GET['sName'];	
	$returnUrl = $_GET['url'];

	if(!isset($barcodeStr))
	{
		echo "Missing barcode.";
		exit;
	}
	
	if(!isset($email))
	{
		echo "Missing Email.";
		exit;
	}
	
	if(!isset($siteName))
	{
		echo "Missing Email.";
		exit;
	}
	
	if(!isset($returnUrl))
	{
		echo "Return URL.";
		exit;
	}
	
	$user = loginNOPUser($email, $returnUrl);
	$barcode = $_order_db->getBarcodeByBarcode($barcodeStr);
	if(!is_null($barcode))
	{
		if(!$barcode->isUsed())
		{
			$order = getActiveOrder($user->id, true);
			deleteActiveOrderItem($order->id, $barcodeStr);
			$orderItem = createOrderItem($order->id, $barcode->barcode);
				
			if(!is_null($orderItem))
			{
				if(selectOrderItem($orderItem))
				{
					if($_session->getEnableTemplateBrowser())
					{
						Header("location: http://".$_url."design_template_select.php");
					}
					else
					{
						Header("location: http://".$_url."design_customize.php");
					}
					exit();
				}
				else
				{
					echo "Error: Could not select new order item.";
				}
			}
			else
			{
				echo "Error: Could not create new order item.";
			}
		}
		else
		{
			echo "That code has already been used.";
		}
	}
	else
	{
		echo "That code was not recognised.";
	}
?>