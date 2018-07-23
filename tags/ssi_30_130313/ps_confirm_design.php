<?php
	include_once "_common.php";	
	
	$_system->forceLogin();
	if($_design_id == "") $_system->loginRedirect();
	
	$design = $_design_db->getDesignById($_design_id);
	$color = $design->getInkColor();

	$designImageId = $_session->getActiveDesignImageId();
	$orderItem = $_order_db->getOrderItemById($_session->getActiveOrderItemId());
	$barcode = $_order_db->getBarcodeByBarcode($orderItem->barcode);
	$product = $_order_db->getProductById($barcode->productId);
	
	$imageWidth = round($product->width * 0.0393700787 * 90);
	// width ="<?php echo $imageWidth;
	//600/25.4
?>

<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/humanity/jquery-ui-1.10.1.custom.css" rel="stylesheet" />
	<link type="text/css" href="css/ps_design_wizard.css" rel="StyleSheet"/>
	
	<style>
		.preview_image{
			background-repeat:no-repeat;
			background-position:center;
			min-width:110px; 
			min-height:80px; 
			background-image:url('images/loading.gif');
			border-width: 1px;
			border-style:solid;
			border-color:silver; 
		}
	</style>
	
	<script src="js/lib/jquery-1.8.0.min.js"></script>
	<script src="js/lib/jquery-ui-1.8.23.custom.min.js"></script>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$( "#previousButton" ).button();			
			$( "#nextButton" ).button().click( function () {
				if(!$( "#chechbox" ).is(':checked'))
				{
					$( "#termsAccepted" ).dialog({
						modal: true,
						buttons: {
							Ok: function() {
								$( this ).dialog( "close" );
							}
						}
					});
				}
				else
				{
					window.location = "design_submitted.php";
				}
			});
		});
		
	</script> 	
</head>	

<body unselectable="on" class="unselectable">
	<div class="wizard_frame">
		<div class="wizard_header">
			<div class="wizard_header_title"><img src="images/ps/ps_logo.gif" alt="Paper Source" width="362px" height="46px"></div>
			<div class="wizard_header_info">
			Please confirm your design. If you are satisfied, check the following declaration and click 'finish'.
			</div>
		</div>
		<div class="wizard_body ui-widget-content">
		    <table id="submitted_box" style="width:650px; margin-left: auto; margin-right: auto; margin-top: 20px;">
		    	<tr>
					<td colspan="2">
						<div class="wizard_header_info">
						Your personalized product will be processed as shown below.<br /> 
						If you are satisfied check the box below, then <b>FINISH</b> to proceed to check out. <br />
						Need to make a change? Click PREVIOUS to go back and edit your design. <br /><br />
				        </div>
			        </td>
		        </tr>
		    	<tr>    	
		    		<td colspan="2" align="center">		    	
		    			 <img class="preview_image" src="design_part/get_image.php?max_size=200&id=<?php echo $designImageId; ?>&thumbnail=true&nocache=true&color=<?php echo $color; ?>"/>
		    			 <br>
		    			 <span style="font-size:12pt;font-weight:bold">Ink Color : <?php echo $color; ?></span><br>
		    			 <span style="font-size:12pt;font-weight:bold"><?php echo htmlspecialchars($product->longName); ?></span><br>
					</td>
				</tr>
		        <tr>
		            <td><input id="chechbox" type="checkbox" name="checkbox" /></td>
		            <td>
		            	<div class="wizard_header_info">
		            	<p align="left" style="font-size: 12px">
		                I am satisfied with the design layout (ATTENTION: size on screen may vary from real size!).  
		                I have verified that spelling and content are correct.  I understand that my design will be
		                manufactured EXACTLY as it appears here and that I assume all responsibility for 
		                typographical errors.
		                </p>
		                </div>
		            </td>
		       </tr>
		    </table>		
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<button id="previousButton" name="previous" class="previous_button" value="previous" onclick="window.location = 'ps_design_customize.php'"><span class="ui-button-text-head"><b>Previous</b></span></button>
			<button id="nextButton" name="next" class="next_button" value="next"><span class="ui-button-text-head"><b>Finish</b></span></button>
		</div>		
	</div>
	
	<div id="termsAccepted" title="Terms not accepted" class="hidden">
	    <p>
	        <span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 50px 0;"></span>
	        You must accept the terms and conditions before you can continue.
	    </p>
	</div>
</body>
</html>



