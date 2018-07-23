<?php
	require_once "_common.php";	
	
	$HOME_URL = "http://". Settings::HOME_URL . "enter_barcode_test1.php";
	
	$errorHTML = "";
	
	if(isset($_GET['orderDetails']))
	{
		$designDetails = json_decode($_GET['orderDetails']);
		$orderItem = Common::$orderDB->getOrderItemById($designDetails->orderItemId);
		if(!is_null($orderItem))
		{
			$orderItem->processingStagesId = ProcessingStage::STAGE_PENDING_RENDERING;
			if(Common::$orderDB->updateOrderItem($orderItem))
			{
				Header('location: ' . $HOME_URL);
				exit;
			}
			else
			{
				$errorHTML = "Update failed. <br>";
			}
		}
		else
		{
			$errorHTML = "Order item not found. <br>";
		}
	}
	
	if(isset($_POST['code']))
	{		
		$customer = Common::$orderDB->getCustomerByKey("test1");
		//$customer = Common::$orderDB->getCustomerByKey("*");
		
		$barcode = Common::$orderDB->getBarcodeByBarcode( $customer->id, $_POST['code']);
 		if(!is_null($barcode))
 		{
 			if(!$barcode->isUsed())
 			{
 				Common::$session = Session::create($customer);
 				Common::$session->designEnvironment = DesignEnvironment::createFromBarcode($barcode, Common::$session->sessionId);
 				Common::$session->urlHome = $HOME_URL;
 				Common::$session->urlSubmit = $HOME_URL;
 				Common::$session->urlReturn = $HOME_URL;
 				
 				if(!is_null(Common::$session->designEnvironment))
 				{
 					Common::$session->save();
 					Header("location: http://". Settings::HOME_URL . "design_customize.php?" . Common::queryVars());
 					exit();
 				}
 				else
 				{
 					$errorHTML = "Error: Could not create design environment.";
 				}
 			}
 			else
 			{
 				$errorHTML = "That code has already been used.";
 			}
 		}
 		else
 		{
 			$errorHTML = "That code was not recognised.";
 		}
	}
	
 	include "preamble.php";
	if($errorHTML != "")
	{
		echo '<div id="error">'.$errorHTML.'</div>';
		echo '<div id="blank">&nbsp;</div>';
	}
	
?>
	<div id="code_box">
		<form method="post">
			<div id="interior_code">
				Code: &nbsp;&nbsp;
				<input type="text" name="code" placeholder="Product Code" required="required" value="TEST" />
				<input type="submit" name="submit" value="Next" class="code_submit_button"/>
			</div>
		</form>
	</div>

	
<?php include "postamble.php"; ?>










