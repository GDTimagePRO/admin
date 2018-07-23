<?php
	require_once "_common.php";	
	
	$errorHTML = "";
	
	if(isset($_POST['code']))
	{		
		//$customer = Common::$orderDB->getCustomerByKey(Customer::KEY_INTERNAL);
		//$customer = Common::$orderDB->getCustomerByKey("Masonrow US");
		//$customer = Common::$orderDB->getCustomerByKey("*");
		$customer = Common::$orderDB->getCustomerByKey("Mason Row");
		
		$barcode = Common::$orderDB->getBarcodeByBarcode( $customer->id, $_POST['code']);
 		if(!is_null($barcode))
 		{
 			if(!$barcode->isUsed())
 			{
 				Common::$session = Session::create($customer);
 				Common::$session->designEnvironment = DesignEnvironment::createFromBarcode($barcode, Common::$session->sessionId);
 				Common::$session->urlHome = "_nop_sim.php";
 				Common::$session->urlSubmit = "_nop_sim.php";
 				Common::$session->urlReturn = "enter_barcode.php";
 				
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
				<input type="text" name="code" placeholder="Product Code" required="required" value="<?php echo htmlspecialchars(getPost('code')); ?>" />
				<input type="submit" name="submit" value="Next" class="code_submit_button"/>
			</div>
		</form>
	</div>

	
<?php include "postamble.php"; ?>










