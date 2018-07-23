<?php
/**
 * @param ThemeInterface $ti
 * @param ConfirmDesign $container
 * @param DesignEnvironment $designEnvironment
 */
function themeOnConfirm($ti, $container, $designEnvironment)
{
	$selectedStamp = $_GET["stamp"];
	$designEnvironment->activeDesigns = array(
			$designEnvironment->activeDesigns[$selectedStamp],
			$designEnvironment->activeDesigns[4]
		);
}


/**
 * @param ThemeInterface $ti
 * @param ConfirmDesign $container
 */
function writeOrderDetails($ti, $container)
{
	echo '<div style="text-align:center">';		
	$selectedStamp = $_GET["stamp"];
	
	$url = $ti->systemURL('design_customize.php?stamp='.$_GET["stamp"]);
	echo '<a href="' . $url . '">';
	echo '<img class="preview_image" src="' . $ti->getImageUrl($container->designImageIds_L[$selectedStamp], true) . '"></a><br>';

	$url = $ti->systemURL('design_customize.php?page=4&stamp='.$_GET["stamp"]);
	echo '<a href="' . $url . '">';
	echo '<img class="preview_image" src="' . $ti->getImageUrl($container->designImageIds_L[4], true) . '"></a>';
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
	
	<script type="text/javascript">
		function onPrevious()
		{
			window.location.href = "<?php echo $ti->systemURL("design_customize.php?page=4&stamp=".$_GET["stamp"]); ?>";
		}
	</script>
	<script>

	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){

	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),

	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)

	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	 

	  ga('create', 'UA-65742416-1', 'auto', {'allowLinker': true});

	  ga('require', 'linker');

	  ga('linker:autoLink', ['peerlesstamp.com'] );

	  ga('send', 'pageview');

	</script>
		
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
    		<a onclick="onPrevious();" class="big-btn pull-left mt5" id="previous"></a>
			<a onclick="TI.onFinishButton();" class="big-btn pull-right mt5" id="finish"></a>
    	</div>
	</div>
    
      <div class="clear"></div>
    </div>
    <div id="bxb"></div>
  </div>
  <div id="footer-box">PEERLESS&trade; &ndash; The Number One Hand Stamp&trade;, the closing gift that doubles as a marketing tool!</div>  
  <div class="clear"></div>
  <div id="copy">&copy; 
    <script language="JavaScript">
    <!--
    today=new Date();
    year0=today.getFullYear();
    document.write(year0);
    //-->
    </script> Peerless</div>
  <div class="clear"></div>
	  
	  
	
	</div>
	<?php $container->writeBodyFooter(); ?>
</body>
</html>

<?php } ?>