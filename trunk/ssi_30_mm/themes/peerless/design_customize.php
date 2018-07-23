<?php
/**
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
function themeMain($ti, $container) {
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
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/design_customize.css" rel="StyleSheet"/>	
	
	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui-1.8.23.custom.min.js"></script>
	
	<?php $container->writeHead(); ?>
	
</head>

<body unselectable="on" class="unselectable" id="<?php echo ($container->simpleMode ? 'simple_mode' : ''); ?>">
	<?php $container->writeBodyHeader(); ?>
	
	<div id="wrapper">
	
	<?php include 'nav_bar.php'; ?>
			
  <div id="content">
    <h1>Customize Stamp</h1>
    <div class="septr mb4"></div>
    <div id="bxt"></div>
    <div id="bxbg">
    
    
    
    
    
		<div class="wizard_body">
			<div class="controls_section">
				<div style="padding-bottom:55px;">			
					<button style="margin-top:4px; width: 80px; margin-left:5px; float:left;" id="undo" >Undo</button>
					<button style="margin-top:4px; width: 80px; margin-left:5px; float:left;" id="redo">Redo</button>
					
					<?php if($container->allowTemplateSelect) { ?>
						<button style="margin-top:4px; width: 120px; margin-right:7px; float:right;" id="template">Template</button>					
					<?php }?>
					
					<?php if(!$container->simpleMode){ ?>
						<button style="margin-top:4px; width: 120px; margin-right:7px; float:right;" id="addElement">Add Element</button>					
					<?php } ?>
					
					</div>
				<div id="uiPanel" class="element_property_pannel">			
				</div>
			</div>
			<div class="preview_section">
				<canvas id="canvas" class="preview_canvas"></canvas>
				<table class="zoom_container_table">
				<tr>
					<td class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomout" style="float:right"></span></td>
					<td><div id="zoom"></div></td>
					<td class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomin"></span></td>
				</tr>
				</table>
			</div>
		</div>
		<div>
    		<a onclick="TI.onPreviousButton();" class="big-btn pull-left mt5" id="previous"></a>
			<a onclick="TI.onNextButton();" class="big-btn pull-right mt5" id="next"></a>
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
