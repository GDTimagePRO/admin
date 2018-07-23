<?php
/**
 * @param ThemeInterface $ti
 * @param ConfirmDesign $container
 */
function writeOrderDetails($ti, $container)
{
	for($i=0; $i<count($container->designImageIds); $i++)
	{
		echo '<a href="' . $ti->systemURL('design_customize.php?page=' . $i) . '">';
		echo '<img class="preview_image" src="' . $ti->getImageUrl($container->designImageIds[$i], true) . '"></a>';
	}

	echo '<br><br><table>';
	echo '<td class="product_header">Product Name</td><td></td></tr>';
	for($i=0; $i<count($container->designProductNames); $i++)
	{
		echo	'<tr><td class="product">'. htmlspecialchars($container->designProductNames[$i]) .
				'</td><td class="product">'.
				'</td></tr>';
	}
	echo '</table>';
}

/**
 * @param ThemeInterface $ti
 * @param ConfirmDesign $container
 */
function themeMain($ti, $container)
{
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $ti->getVar('TITLE'); ?></title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	  	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/humanity/jquery-ui.custom.min.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_wizard.css" rel="StyleSheet"/>	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/confirm_design.css" rel="StyleSheet"/>	
	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui-1.10.3.custom.min.js"></script>
	
	<?php $container->writeHead(); ?>	  	
</head>	

<body unselectable="on" class="unselectable">
	<?php $container->writeBodyHeader(); ?>
	
	<div class="wizard_frame">
		<div class="wizard_header">
			<div class="wizard_header_title"><img class="logo" src="<?php echo $ti->HOME_URL; ?>/images/logo/MR_Logo_revised.png" alt="RTNImagePro"></div>
			<div class="wizard_header_info">
			Please confirm your design. If you are satisfied, check the following declaration and click 'finish'.
			</div>
		</div>
		<div class="wizard_body ui-widget-content confirm">
		    <table id="submitted_box" style="width:550px; margin-left: auto; margin-right: auto; margin-top: 5px;">
		    	<tr>    	
		    		<td colspan="2" align="center">		    	
						<?php $container->writeErrors(); ?>
						<?php writeOrderDetails($ti, $container); ?>
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
		            <td>
		            	<div style="border-style:solid; border-color:red; border-width:4px;">
		            		<input id="chechbox" type="checkbox" name="checkbox" />
		            	</div>
		            </td>
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
		            	<div style="border-style:solid; border-color:red; border-width:4px;">
		            		<input id="chechbox2" type="checkbox" name="checkbox2" />
		            	</div>
		            </td>
		            <td>
		            	<p align="left">
		                I agree with the <a target="_blank" href="http://masonrow.com/terms-of-service">terms of service</a>.
		                </p>
		            </td>
		       </tr>
		    </table>		
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<button 
				id="previousButton" 
				name="previous" 
				class="previous_button" 
				value="previous" 
				onclick="window.location = '<?php echo $ti->systemURL('design_customize.php?page=' . (count($container->designEnvironment->activeDesigns) -1)); ?>'"
			>Previous</button>
			<form method="post">		
				<button id="finishButton" name="finishButton" type="submit" class="next_button" value="Finish">Finish</button>
			</form>
		</div>		
	</div>
	
	<?php $container->writeBodyFooter(); ?>
	
</body>
</html>

<?php } ?>
