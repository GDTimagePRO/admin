<?php
/**
 * @param ThemeInterface $ti
 * @param ConfirmDesign $container
 */
function writeOrderDetails($ti, $container)
{
	echo '<div style="text-align:center">';		
	for($i=0; $i<count($container->designImageIds_L); $i++)
	{
		$url = $ti->systemURL('design_customize.php?page=' . $i);
		echo '<a href="' . $url . '">';
		echo '<img class="preview_image" src="' . $ti->getImageUrl($container->designImageIds_L[$i], true) . '"></a>';
		//echo '<a href="' . $url . '" class="small-btn" id="change-stamp" style="margin-left:auto;margin-right:auto;"></a>';
	}
	echo '</div>';		
}

/**
 * @param ThemeInterface $ti
 * @param ConfirmDesign $container
 */
function themeMain($ti, $container)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Peerless</title>

	<link href="<?php echo $ti->HOME_URL; ?>/css/global.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $ti->HOME_URL; ?>/css/layout.css" rel="stylesheet" type="text/css" />
	
	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/cupertino/jquery-ui.custom.min.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_wizard.css" rel="StyleSheet"/>	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/confirm_design.css" rel="StyleSheet"/>	
	
	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui-1.8.23.custom.min.js"></script>
	
	<?php $container->writeHead(); ?>
	
</head>

<body unselectable="on" class="unselectable">
	<div id="wrapper">
	
	<?php $container->writeBodyHeader(); ?>
	
	<?php include 'nav_bar.php'; ?>
		
  <div id="content">
    <h1>Confirm Stamp</h1>
    <div class="septr mb4"></div>
    <div id="bxt"></div>
    <div id="bxbg">
    
    
    

	<div>
		<div class="wizard_body">
 			
			<table id="submitted_box" style="width:550px; margin-left: auto; margin-right: auto;">
		    	<tr>    	
		    		<td>		    	
						<br><br><br>
		    			<h4 class="cs2" style="text-align: center;">Client Stamp Proof</h4>
					</td>
				</tr>
				<tr>    	
		    		<td align="center">		    	
						<?php $container->writeErrors(); ?>
						<?php writeOrderDetails($ti, $container); ?>
						<br>
					</td>
				</tr>
		        <tr>
		            <td style="text-align: center;">
		            	 <div style="border-style:solid; border-color:red; border-width:2px;margin-right:30px;padding:3px;display:inline-block;">
		            		<input id="chechbox" type="checkbox" name="checkbox" />
		            	</div>
		                Verify as correct
		            </td>
		       </tr>
		    </table>		
		</div>
		<div>
    		<a onclick="TI.onPreviousButton();" class="big-btn pull-left mt5" id="previous"></a>
			<a onclick="TI.onFinishButton();" class="big-btn pull-right mt5" id="finish"></a>
    	</div>
	</div>
    	
    
    
    
    
    
    
    
    
    
      <div class="clear"></div>
    </div>
    <div id="bxb"></div>
  </div>
  <div id="footer-box">Give us a try for you next closing gift. We guarantee you and your client will be very pleased.</div>
  <div class="clear"></div>
  <div id="copy">&copy; 
    <script language="JavaScript">
    <!--
    today=new Date();
    year0=today.getFullYear();
    document.write(year0);
    //-->
    </script> Peerless</div>
  <div id="design"><a href="http://www.ndic.com/" target="_blank">Web design & development by NDIC</a></div>
  <div class="clear"></div>
	  
	  
	
	</div>
	<?php $container->writeBodyFooter(); ?>
</body>
</html>

<?php } ?>