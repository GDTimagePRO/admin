<?php
	include_once "_common.php";	

	$_system->forceLogin();
	if($_design_id == "") $_system->loginRedirect();
	
	$designImageId = $_session->getActiveDesignImageId();
	$orderItem = $_order_db->getOrderItemById($_session->getActiveOrderItemId());
	$barcode = $_order_db->getBarcodeByBarcode($orderItem->barcode);
	$product = $_order_db->getProductById($barcode->productId);
	
	$imageWidth = round($product->width * 0.0393700787 * 90);
	
	include_once "preamble.php";
?>
	<div id="blank" >          
        <h2>
           <span>Confirm Your Design</span>
       </h2>
	</div>
	
    <table id="submitted_box" width="700px" align="center" >
    	<tr>    	
    		<td colspan="2" border="1" id="previewImage">
    			 <img src="design_part/get_image.php?id=<?php echo $designImageId; ?>&thumbnail=true" width ="<?php echo $imageWidth;?>" /> 
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<p align="left">
		           Please confirm your design. If you are satisfied, check the following declaration and click 'finish'. 
		           Your design will be scheduled for manufacturing and your item will be mailed to you. If you are not
		           satisfied with the design, click 'prev' and fine-tune your design.
		        </p>
	        </td>
        </tr>
        <tr>
            <td><input id="chechbox" type="checkbox" name="checkbox" /></td>
            <td>
            	<p align="left">
                I am satisfied with the design layout (ATTENTION: size on screen may vary from real size!).  
                I have verified that spelling and content are correct.  I understand that my design will be
                manufactured EXACTLY as it appears here and that I assume all responsibility for 
                typographical errors.
                </p>
            </td>
       </tr>
       <tr>
       	<td>
       		<a href="design.php" class="button" style="float: left;">previous</a>
       	</td>
       	<td>
       		<a href="design_submitted.php" class="button" style="float: right">finish</a>
       	</td>
       </tr>
    </table>  
	
<?php include_once "postamble.php"; ?>