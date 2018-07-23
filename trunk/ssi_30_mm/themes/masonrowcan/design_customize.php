<?php
/**
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
function themeMain($ti, $container) {
?>


<!DOCTYPE html>
<html>
<head>
	<title><?php echo $ti->getVar('TITLE'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/humanity/jquery-ui.custom.min.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_wizard.css" rel="StyleSheet"/>	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/design_customize.css" rel="StyleSheet"/>	
	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui-1.10.3.custom.min.js"></script>
	
	<style>
		input, textarea 
		{ 
			padding: 5px;
			border: solid 1px #E5E5E5;
			outline: 0;
			font: normal 13px/100% Verdana, Tahoma, sans-serif;
			width: 250px;
			background: #FFFFFF left top repeat-x;
			background: -moz-linear-gradient(top, #FFFFFF, #EEEEEE 1px, #FFFFFF 25px);
			box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
			-moz-box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
			-webkit-box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
		}

	</style>
	<?php $container->writeHead(); ?>	  	
</head>	

<body unselectable="on" class="unselectable" id="<?php echo ($container->simpleMode ? 'simple_mode' : ''); ?>">
	<?php $container->writeBodyHeader(); ?>
		
	<div class="wizard_frame">
		<div class="wizard_header">
			
			<?php if($container->simpleMode){ ?>
				<div class="wizard_header_title"><img src="<?php echo $ti->HOME_URL; ?>/images/logo/MR_LogoHoriz.png" alt="MasonRow" width="400px" height="106px"></div>
			<?php } else { ?>
				<div class="wizard_header_title"><img src="<?php echo $ti->HOME_URL; ?>/images/logo/MR_LogoHoriz.png" alt="MasonRow" width="400px" height="106px"></div>
			<?php } ?>
			

			<div class="wizard_header_info"><?php echo $ti->getVar('PRODUCT_NAME') ?>.</div>			
		</div>
		<div class="wizard_body ui-widget-content">
			<div class="controls_section">
				<div class="toolbar_pannel ui-widget-header">			
					<button style="margin-top:4px; width: 60px; margin-left:5px; float:left;" id="undo">Undo</button>
					<button style="margin-top:4px; width: 60px; margin-left:5px; float:left;" id="redo">Redo</button>
					
					<?php if($container->allowTemplateSelect) { ?>
						<?php if($container->simpleMode) { ?>
							<button style="margin-top:4px; width: 160px; margin-right:7px; float:right;" id="template">Select Design</button>
						<?php } else {?>
							<button style="margin-top:4px; width: 90px; margin-right:7px; float:right;" id="template">Template</button>
						<?php } ?>
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
					<td align="right" class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomout"></span></td>
					<td valign="middle"><div id="zoom"></div></td>
					<td align="left" class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomin"></span></td>
				</tr>
				</table>
				<?php $container->writeColorSelector(); ?>
			</div>
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<button id="previousButton" name="previous" class="previous_button" value="previous">Previous</button>
			<button id="nextButton" name="next" class="next_button" value="next">Next</button>
		</div>
	</div>
	
	<?php $container->writeBodyFooter(); ?>
		
</body>
</html>

<?php } ?>
