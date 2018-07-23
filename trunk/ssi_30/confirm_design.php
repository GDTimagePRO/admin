<?php
	include_once "_common.php";	
	
	$_system->forceLogin();
	
	$orderItem = $_order_db->getOrderItemById($_session->getActiveOrderItemId());	
	if(is_null($orderItem)) $_system->loginRedirect();

	$designIds = $_design_db->getSortedDesignIdsByOrderItemId($orderItem->id);
	$designImageIds = array();
	$designProductNames = array();
	$designColors= array();
	
	for($i=0; $i<count($designIds); $i++)
	{
		$design = $_design_db->getDesignById($designIds[$i]);		
		
		$designColors[] = $design->getInkColor();
		$designImageIds[] = ImageDB::TYPE_THUMBNAIL_COLOR . end($designColors) . '.' . $design->getPreviewImageId();
		
		$designConfig = $design->getConfigItem();
		$designProductId = $designConfig->productId;
		
		$product = $_order_db->getProductById($designProductId);
		$designProductNames[] = $product->longName;
	}	
?>

<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/<?php echo $_session->getActiveThemeName(); ?>/jquery-ui.custom.min.css" rel="stylesheet" />
	<?php if($_is_mobile) {?>
		<link type="text/css" href="css/design_wizard_mobile.css?version=<?php echo $_version ?>" rel="StyleSheet"/>
	<?php } else {?>	
		<link type="text/css" href="css/design_wizard.css?version=<?php echo $_version ?>" rel="StyleSheet"/>	
	<?php } ?>	
  		
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
		
		.product_header{
			padding-left:10px;
			padding-right:10px;
			text-align:left;
			font-size:12pt;
			font-weight:bold;
		}
		
		.product{
			padding-left:10px;
			padding-right:10px;
			font-size:10pt;
			font-weight:bold;
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
			<div class="wizard_header_title"><img src="images/masonrow/logo/MR_LogoHoriz.png" alt="MasonRow" width="400px" height="106px"></div>
			<div class="wizard_header_info">
			Please confirm your design. If you are satisfied, check the following declaration and click 'finish'.
			</div>
		</div>
		<div class="wizard_body ui-widget-content">
		    <table id="submitted_box" style="width:550px; margin-left: auto; margin-right: auto; margin-top: 40px;">
		    	<tr>    	
		    		<td colspan="2" align="center">		    	
<?php
	for($i=0; $i<count($designImageIds); $i++)
	{
		echo '<a href="design_customize.php?page='.$i.'">';
		echo '<img class="preview_image" src="design_part/get_image.php?nocache=true&id='.$designImageIds[$i].'"></a>';
	}

	echo '<br><br><table>';
	echo '<td class="product_header">Product Name</td><td class="product_header">Ink Color</td></tr>';
	for($i=0; $i<count($designProductNames); $i++)
	{
		echo	'<tr><td class="product">'. $designProductNames[$i] .
				'</td><td class="product">'. htmlspecialchars(strtoupper($designColors[$i])) .
				'</td></tr>';
	}
	echo '</table>';
	
	
?>
		    			 <br>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="wizard_header_info">
						Your design will be scheduled for manufacturing and your item will be mailed to you. If you are not
				        satisfied with the design, click 'prev' and fine-tune your design.
				        </div>
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
		    </table>		
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<button id="previousButton" name="previous" class="previous_button" value="previous" onclick="window.location = 'design_customize.php'">Previous</button>
			<button id="nextButton" name="next" class="next_button" value="next">Finish</button>
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



