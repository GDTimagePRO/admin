<?php
	include_once "_common.php";	
	include_once "./backend/order_logic.php";
		
	$_system->forceLogin();
	
	$errorHTML = "";	
	
	if(isset($_POST['code']))
	{		
 		$barcode = $_order_db->getBarcodeByBarcode($_POST['code']);		
 		if(!is_null($barcode))
 		{
 			if(!$barcode->isUsed())
 			{
 				$order = getActiveOrder($_user_id, true);
 				$orderItem = getActiveOrderItem($order->id, $barcode->barcode);
 				$isNewOrderItem = $orderItem == NULL; 
 				if($isNewOrderItem) $orderItem = createOrderItem($order->id, $barcode->barcode);
 				
 				if(!is_null($orderItem))
 				{
 					if(selectOrderItem($orderItem))
 					{
						if($_session->getEnableTemplateBrowser() && $isNewOrderItem)
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
						$errorHTML = "Error: Could not select new order item.";
					}
 				}
 				else
 				{
 					$errorHTML = "Error: Could not create new order item.";
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
	<script type="text/javascript">
		forceModernBrowser();
	</script>

	<div id="code_box">
		<form method="post" action="">
			<div id="interior_code">
				Code: &nbsp;&nbsp;
				<input type="text" name="code" placeholder="Product Code" required="required" value="<?php echo htmlspecialchars(getPost('code')); ?>" />
				<input type="submit" name="submit" value="Next" class="code_submit_button"/>
			</div>
		</form>
	</div>

	
<?php include "postamble.php"; ?>










