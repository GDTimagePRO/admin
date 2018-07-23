<?php
/**
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
function themeMain($ti, $container) {
	$container->scriptUiPanelBasic = 'ui_panel_basic_v2.js';
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
		
		.shadow_box 
		{ 
			padding: 2px;
			border: solid 1px #E5E5E5;
			outline: 0;
			background: #FFFFFF left top repeat-x;
			background: -moz-linear-gradient(top, #FFFFFF, #EEEEEE 1px, #FFFFFF 25px);
			box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
			-moz-box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
			-webkit-box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
		}

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
	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/jquery.simple-color-picker.css" rel="StyleSheet"/>	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/jquery.simple-image-picker.css" rel="StyleSheet"/>	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery.simple-color-picker.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery.simple-image-picker.js"></script>
	
	<script type="text/javascript">
	<?php 
		
		$configItem = $container->activeDesign->design->getConfigItem();
		$pconfigItem = json_decode($container->activeDesign->product->configJSON);
		$colors = $pconfigItem;
		$misc = $configItem->misc;
		
		if(is_null($colors))
		{
			$colors = NUll;
		}
		else
		{
			$colors = str_replace("\\", "\\\\", json_encode($colors));
			$colors = str_replace('"', '\"', $colors);
		
		}
		if(is_null($colors))
		{
			echo 'var _colors = null;'."\n";
		}
		else
		{
			echo 'var json_colors = "' . $colors . '";' . "\n";
		}

		if(is_null($misc))
		{
			$misc = NULL;
		}
		else
		{
			$misc = str_replace("\\", "\\\\", json_encode($misc));
			$misc = str_replace('"', '\"', $misc);
		}
		
		if(is_null($misc))
		{
			echo 'var _config = null;'."\n";
		}
		else
		{
			echo 'var _config = "' . $misc . '";' . "\n";
		}
		
		$rid = ResourceId::fromId(DesignTemplate::previewImageId(12345));
		$rid->type = ResourceManager::TYPE_THUMBNAIL;
		echo 'var _templatePreviewSrc = "' . Settings::getImageUrl($rid->getId()) . '";' . "\n";
		echo 'var _imagePreviewSrc = "' . Settings::SERVICE_GET_IMAGE . '?id=' . ResourceManager::TYPE_THUMBNAIL .'.";' . "\n";
	?>
	Scene.DEFAULT_BACKGROUND_COLOR = "#f4f0ec";
	var _colors = null;
	var j = jQuery.parseJSON(json_colors);
	if (j.colors !== undefined) {
		_colors = [];
		var i;
		for (i = 0; i < j.colors.length; i++) {
			_colors.push(j.colors[i].value);
		}
	}
	
	function createColorPicker(paletteId)
	{		
		var element = $('<button id="color_picker_' + paletteId +'"/>');
		element.html('<div id="value"></div><div id="color" style="background-color: #000000; display:block; width:15px; height:15px;"></div>');

		var colors = _colors[paletteId].palette;
		var colorValues = [];		
		for(var i in colors) colorValues.push(colors[i][1]);		

		var setValue = function(color, saveState)
		{
			elementValue = element.find("#value");
			elementColor = element.find("#color");
			
			for(var i in colors) 
			{
				var kv = colors[i];
				if(kv[1] == color)
				{
					elementValue.text(kv[0]);
					elementColor.css("background-color", kv[1]);
					_system.setPaletteColour(paletteId, kv[0], kv[1], saveState); 
					return;
				}
			} 
		};

		var defaultColor = _system.getPaletteColor(paletteId);
		if(defaultColor != null) defaultColor = defaultColor.value;
		else defaultColor = colors[0][1]; 
		
		setValue(defaultColor, false);
		
		element.button();
		element.simpleColorPicker({
			//showEffect: 'fade',
			hideEffect: 'fade',
			colors: colorValues,
			onChangeColor: function(color) { setValue(color, true); }
		});
		return element;
	}

	var _allParams = [ResourceId.PARAM_LINEAR_TINT + "70421440DA", ResourceId.PARAM_MIRROR_HORIZONTAL, ResourceId.PARAM_MIRROR_VERTICAL];

	function findElementById(id)
	{
		for(var i in _system.elements)
		{
			if(_system.elements[i].id == id)
			{
				return _system.elements[i];
			}
		}
		return null;
	}
	
	function setImageParams(elementId, paramIndex, value)
	{
		var image = findElementById(elementId);
		if(image == null) return;

		var path = image.getImageId();
		var params = ResourceId.getParams(path);
		if(params == null) params = [];
		var newParams = [];
		for(var i in _allParams)
		{
			newParams.push(params.indexOf(_allParams[i]) >= null ? _allParams[i] : null);
		}

		newParams[paramIndex] = value ? _allParams[paramIndex] : null;

		var rid = ResourceId.setParams(path, newParams);
		if(image.getImageId() != rid)
		{ 
			image.setImageId(rid);
			_system.saveState(true);
		}
	}
	
	function createTemplateSwapButton(templateId)
	{
		var element = $('<image style="border-style:solid;border-width:1px;margin:3px;max-width:50px;max-height:50px" src="' + _templatePreviewSrc.replace('12345', templateId) + '">');
		element.click(function() {
			TI.onTemplateSelectDefaultHandler(templateId, false);
		});
		return element;
	}

	function createImageParamButton(imageId, paramIndex, newValue)
	{
		var element = $('<button/>'); 
		element.button();
		element.text(paramIndex + " -> " + newValue);
		element.click(function(){
			setImageParams(imageId, paramIndex, newValue);
		});
		return element; 
	}

	function createImageSwapButton(container, buttonId, imageId, rid)
	{
		var element = $("<button/>");
		element.html('<image style="border-style:solid;border-width:1px;margin:3px;max-width:50px;max-height:50px" src="' + _imagePreviewSrc + encodeURIComponent(rid) + '">');
		element.button();
		element.click(function() {
			var image = findElementById(imageId);
			if(image != null)
			{
				if(image.getImageId() != rid)
				{ 
					image.setImageId(rid);
					_system.saveState(true);
				}
			}
		});
		return element;
	}
	
	function createImagePalette(container, imageId, rid, colors)
	{
		var makeHandler = function(rid)
		{
			return function()
			{
				var image = findElementById(imageId);
				if(image != null)
				{
					if(image.getImageId() != rid)
					{ 
						image.setImageId(rid);
						_system.saveState(true);
					}
				}
			};
		};
	
		var image = findElementById(imageId);
		var cv = colors[0].trim().toUpperCase();
		if(cv.charAt(0) == '#') cv = cv.substring(1);
		var rc = ResourceId.setParams(rid, [ResourceId.PARAM_GRADIENT + cv + 'FFFFFF']);
		image.setImageId(rc);
		_system.saveState(true);
		
		var container = $("<div/>");
		for(var i in colors)
		{
			var colorValue = colors[i].trim().toUpperCase();
			if(colorValue.charAt(0) == '#') colorValue = colorValue.substring(1);
			var ridColor = ResourceId.setParams(rid, [ResourceId.PARAM_GRADIENT + colorValue + 'FFFFFF']);
			var src = _imagePreviewSrc + encodeURIComponent(ridColor);
			var image = $('<img  style="border-style:solid;border-width:1px;margin:3px;max-width:50px;max-height:50px" src="' + src + '"/>');
			image.click(makeHandler(ridColor));
			container.append(image);			
		}
		return container;
	}

	//System.ENABLE_HD_IMAGES = true;	
	
	function makeDynamicUI()
	{
		
		Scene.DISPLAY_GROUP_ANY = Scene.DISPLAY_GROUP_CONTENT;
		if(_system.scene)
		{
			_system.scene.getLayer(Scene.LAYER_WIDGETS).visible = false;
			_system.scene.getLayer(Scene.LAYER_BACKGROUND).visible = false;
		}
		
		var container = $("#themeDynamicUIContrainer");
		container.html("");

		if(_config)
		{
			if(_config.template_set_1)
			{
				container.append($("<div>Template Selector</div>"));
				for(var i in _config.template_set_1)
				{
					container.append(createTemplateSwapButton(_config.template_set_1[i]));
				}		
				container.append("<br>");	
			}
			if (_colors == null) {
				_colors = _config.image_palette_1_colors;
			}
			if(_config.image_palette_1_rid && _colors)
			{
				container.append($("<div>Pattern Color</div>"));
				if(findElementById("background_image") != null)
				{
					container.append(createImagePalette(container, "background_image", _config.image_palette_1_rid, _colors));
					container.append("<br>");	
				}
			}
		}
	}
	
	$(function() {

		//if(_colors) _colors = jQuery.parseJSON(_colors);
		if(_config) _config = jQuery.parseJSON(_config);

		_system.onSetState.setHandler({}, makeDynamicUI);
		makeDynamicUI();
		//$('input#color4').simpleColorPicker({ showEffect: 'fade', hideEffect: 'slide' });
		//$('button#color5').simpleColorPicker({ onChangeColor: function(color) { $('label#color-result').text(color); } });
		//var colors = ['#000000', '#444444', '#666666', '#999999', '#cccccc', '#eeeeee', '#f3f3f3', '#ffffff'];
	});	
	
	
	</script>		  	
</head>	

<body unselectable="on" class="unselectable" id="<?php echo ($container->simpleMode ? 'simple_mode' : ''); ?>">
	<?php $container->writeBodyHeader(); ?>
		
	<div class="wizard_frame">
		<div class="wizard_header">
			
			<?php if($container->simpleMode){ ?>
				<div class="wizard_header_title"><img class="logo" src="<?php echo $ti->HOME_URL; ?>/images/logo/RTN_LogoHoriz.png" alt="RTNImagePro"></div>
			<?php } else { ?>
				<div class="wizard_header_title"><img class="logo" src="<?php echo $ti->HOME_URL; ?>/images/logo/RTN_LogoHoriz.png" alt="RTNImagePro"></div>
			<?php } ?>

			<div class="wizard_header_info"><?php echo $ti->getVar('PRODUCT_NAME') ?>.</div>			
		</div>
		<div class="wizard_body ui-widget-content">
			<div class="controls_section">
				<div class="toolbar_pannel ui-widget-header">			
					<button style="margin-top:4px; width: 55px; margin-left:5px; float:left;" id="undo">Undo</button>
					<button style="margin-top:4px; width: 55px; margin-left:5px; float:left;" id="redo">Redo</button>
					
					<?php if($container->allowTemplateSelect) { ?>
						<?php if($container->simpleMode) { ?>
							<button style="margin-top:4px; width: 115px; margin-right:7px; float:right;" id="template">Select Design</button>
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
				<br>
				<div id="themeDynamicUIContrainer" style="padding-left:9px;">
				</div>
			</div>
			<div class="preview_section">
				<canvas id="canvas" class="preview_canvas" style="cursor: move;"></canvas>
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
