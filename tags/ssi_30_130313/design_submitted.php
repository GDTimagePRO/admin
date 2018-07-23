<?php
	include_once "_common.php";	
	include_once "./backend/order_logic.php";

	$errorHTML = "";
	
	$_system->forceLogin();
	if($_design_id == "") $_system->loginRedirect();
	
	if(submitOrderItem($_session->getActiveOrderItemId()))
	{
		$designImageId = $_session->getActiveDesignImageId();
		clearSelectedOrderItem();
		$returnUrl = $_session->getReturnUrl();
		if($returnUrl != "")
		{
			Header("location: ".$returnUrl."?OrderId=".$designImageId);
			exit;
		}
	}
	else
	{
		$errorHTML = "Update failed.";		
	}
	
	include_once "preamble.php";
	if($errorHTML != "")
	{
		echo '<div id="error">'.$errorHTML.'</div>';
		echo '<div id="blank">&nbsp;</div>';
	}
	
?>
	<div id="blank">          
        <h2>
           <span>Confirm Your Design</span>
       </h2>
	</div>
	<section id="submitted_box">
        <p>
            Your order has been sent to manufacturing and a confirmation email has been sent to you. Thank you for using Create Your Own (CYO).
        </p> 
        <div>
        	<a href="login.php">Home Page</a>
        </div>   	
     </section>
     
<?php include_once "postamble.php"; ?>