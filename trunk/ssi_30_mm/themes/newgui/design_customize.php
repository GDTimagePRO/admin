<?php
/**
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
 
 
function themeMain($ti, $container) {
	$container->scriptUiPanelBasic = 'ui_panel_basic_v2.js';
	$tooltips = file_get_contents(dirname(__FILE__) . "\\tooltips.json");
?>


<!DOCTYPE html>
<html>
<head>
	<title><?php echo $ti->getVar('TITLE'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/humanity/jquery-ui.min.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/humanity/jquery-ui.theme.min.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_wizard.css" rel="StyleSheet"/>
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/design_customize.css" rel="StyleSheet"/>

    <?php if($container->simpleMode){ ?>
        <link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_customize_simple.css" rel="StyleSheet"/>
    <?php } else { ?>
        <link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_customize_normal.css" rel="StyleSheet"/>
    <?php } ?>
	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui.js"></script>
	<script src="js/design/ui_components_v1.js"></script>
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
		global $product;
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
			echo 'var json_colors = null;'."\n";
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
		if (isset($tooltips)) {
			echo "var tooltips = JSON.parse(" . json_encode($tooltips) .");";
		}
	?>
	
	var home_url = '<?php echo $ti->HOME_URL; ?>';
	var _colors = [];
	var cur_selected = null;
	var deletePress = true;
	var components;
    if (json_colors) {
        var j = jQuery.parseJSON(json_colors);
        if (j.colors !== undefined) {
            _colors = [];
            var i;
            for (i = 0; i < j.colors.length; i++) {
                _colors.push(j.colors[i].value);
            }
        }
    }
	
	var _allParams = [ResourceId.PARAM_LINEAR_TINT + "70421440DA", ResourceId.PARAM_MIRROR_HORIZONTAL, ResourceId.PARAM_MIRROR_VERTICAL];
	
	
	
	function makeDynamicUI()
	{
		//Scene.DISPLAY_GROUP_ANY = Scene.DISPLAY_GROUP_CONTENT;
		if(_system.scene)
		{
			_system.scene.getLayer(Scene.LAYER_WIDGETS).visible = true;
			_system.scene.getLayer(Scene.LAYER_BACKGROUND).visible = true;
		}
		

		if(_config)
		{
			if (_colors == null) {
				_colors = _config.image_palette_1_colors;
			}
		}
	}
    
    function rebuildAndSelect() {
        rebuildElementList();
        components.setInitialSelect();
    }
	
	function rebuildElementList() {
		$('#select-element-list').children().remove();
		for (var i = 0; i < _system.elements.length; i++) {
			var ele =_system.elements[i];
			if (ele.className != "ImageElement" || ele.id == "user_upload") {
                if (TI.simpleMode) {
                    var text = ele.getUIControlGroup().title;
                    if (!text || text == "") {
                        text = i + ": " + ele.className;
                    }
					$('<option />', {value: i, text: text}).appendTo('#select-element-list');
                } else {
                    $('<option />', {value: i, text: i + ": " + ele.className}).appendTo('#select-element-list');
                }
			}
		}
		$('#select-element-button').imagemenu('refresh');
	}
	
	function onChange(element) {
		rebuildElementList();
		
		if (element.className == "TextElement") {
			if (element.getType() == TextElement.TYPE_LINE) {
				$('#rleft').hide("slow");
				$('#rright').hide("slow");
				$('#mhoriz').hide("slow");
				if (element.config.controls && element.config.controls.indexOf('flipVertical') > -1) {
					$('#mvert').show("slow");
				} else {
					$('#mvert').hide("slow");
				}
			} else {
                if (element.getEditAllowMove()) {
                    $('#rleft').show("slow");
                    $('#rright').show("slow");
                } else {
                    $('#rleft').hide("slow");
                    $('#rright').hide("slow");
                }
				$('#mhoriz').hide("slow");
				if (element.config.controls && element.config.controls.indexOf('flipVertical') > -1) {
					$('#mvert').show("slow");
				} else {
					$('#mvert').hide("slow");
				}
			}
		} else if (element.className == "BorderElement") {
			$('#mhoriz').hide("slow");
			$('#mvert').hide("slow");
			$('#rleft').hide("slow");
			$('#rright').hide("slow");
		} else if (element.className == "ImageElement") {
            if (element.getEditAllowMove()) {
                $('#rleft').show("slow");
                $('#rright').show("slow");
            } else {
                $('#rleft').hide("slow");
                $('#rright').hide("slow");
            }
			if (element.config.controls && element.config.controls.indexOf('flipHorizontal') > -1) {
				$('#mhoriz').show("slow");
			} else {
				$('#mhoriz').hide("slow");
			}
			if (element.config.controls && element.config.controls.indexOf('flipVertical') > -1) {
				$('#mvert').show("slow");
			} else {
				$('#mvert').hide("slow");
			}
		}
	}
	
	$(function() {
		$.widget( "custom.imagemenu", $.ui.selectmenu, {
			_drawButton: function() {
				var that = this;
				this.buttonText = $('#hidden');
				this.button = $(this.element);
				this.element = $(this.element.children()[1]);
				this.button.attr({tabindex: this.options.disabled ? -1 : 0,
					id: this.ids.button,
					role: "combobox",
					"aria-expanded": "false",
					"aria-autocomplete": "list",
					"aria-owns": this.ids.menu,
					"aria-haspopup": "true"});
				this.element.hide();
				this._on( this.button, this._buttonEvents );
				this.button.one( "focusin", function() {
					// Delay rendering the menu items until the button receives focus.
					// The menu may have already been rendered via a programmatic open.
					if ( !that.menuItems ) {
						that._refreshMenu();
					}
				});
				this._focusable( this.button );
			},
			close: function( event ) {
				if ( !this.isOpen ) {
					return;
				}

				this.isOpen = false;
				this._toggleAttr();
				this.focusIndex = this.element[ 0 ].selectedIndex;
				this.range = null;
				this._off( this.document );

				this._trigger( "close", event );
			},
			refresh: function() {
				this._refreshMenu();
			}
		});
		
		$('#select-element').imagemenu({
			select: function(e, ui) {
                _system.setSelected(_system.elements[ui.item.value]);
                components.selectElement(_system.elements[ui.item.value]);
			}
		});
		
		components = new UIComponents($('#component_box'), $('#canvas'), tooltips, onChange);
		components.createToolTip($('#select-element-button'), "elementSelect");
		System.ENABLE_SAVE_STATE = false;
		//if(_colors) _colors = jQuery.parseJSON(_colors);
		if(_config) _config = jQuery.parseJSON(_config);

		_system.onSetState.setHandler({}, makeDynamicUI);
		makeDynamicUI();
		
		
		
		$('#undo').click(function(e) {
			components.refreshComponent();
			components.resetWidgetDots();
		});
		components.createToolTip($('#undo'), "undo");
		
		$('#redo').click(function(e) {
			components.refreshComponent();
			components.resetWidgetDots();
		});
		components.createToolTip($('#redo'), "redo");
		
		$('#delete').click(function(e) {
			components.deleteElement();
			rebuildElementList();
		});
		components.createToolTip($('#delete'), "delete");
		
		$('#copy').click(function(e) {
			components.copyElement();
		});
		components.createToolTip($('#copy'), "copy");
		
		$('#front').click(function(e) {
			components.moveElementForward();
		});
		components.createToolTip($('#front'), "front");
		
		$('#back').click(function(e) {
			components.moveElementBack();
		});
		components.createToolTip($('#back'), "back");
		
		
		$('#rleft').click(function(e) {
			components.rotateElement(-0.524);
		});
		components.createToolTip($('#rleft'), "rleft");
		
		$('#rright').click(function(e) {
			components.rotateElement(0.524);
		});
		components.createToolTip($('#rright'), "rright");
		
		$('#mvert').click(function(e) {
			components.verticalMirror();
		});
		components.createToolTip($('#mvert'), "mvert");
		
		$('#mhoriz').click(function(e) {
			components.horizontalMirror();
		});
		components.createToolTip($('#mhoriz'), "mhoriz");
		
		
		
		$('#grid').click(function(e) {
			if (components.toggleGrid()) {
				$(this).addClass("active-button");
			} else {
				$(this).removeClass("active-button");
			}
		});
		components.createToolTip($('#grid'), "grid");

		
		if (!TI.simpleMode) {
			$('#addText').imagemenu({
				appendTo:'#addText',
				select: function(e, ui) {
					components.createTextElement(ui.item.value);
					$('.ui-selectmenu-text').css("font-family", "\'" + _system.getSelected().getFont() + "\'");
				}
			});
			components.createToolTip($('#addText-button'), "addText");
			
			$('#addImage').imagemenu({
				appendTo:'#addImage',
				select: function(e, ui) {
					components.createImageElement(ui.item.value);
				}
			});
			components.createToolTip($('#addImage-button'), "addImage");
			
			$('#addBorder').imagemenu({
				appendTo:'#addBorder',
				select: function(e, ui) {
					components.createBorderElement(ui.item.value);
				}
			});
			components.createToolTip($('#addBorder-button'), "addBorder");
		}
		
		if (!TI.simpleMode || TI.colorModel != '1_BIT') {
			$('#backgroundColor').simpleColorPicker({
				colors: _colors,
                colorsPerLine: 11,
				onChangeColor: function(color) {
					components.setCanvasBackground(color);
				}
			});
			components.createToolTip($('#backgroundColor'), "backgroundColor");
		}
		
		$('.wizard_body').on('click', '.button-image', function() {
			$(this).addClass('animate-button');
			$(this).one('webkitAnimationEnd oanimationend msAnimationEnd animationend',
			function (e) {
				$(this).removeClass('animate-button');
			});
		});
		
		$('.buttonize').button();
		
		$('body').keydown(function (e) {
			if (e.keyCode == 46 && deletePress) {
				$('#delete').trigger("click");
				deletePress = false;
			}
		});
		
		$('body').keyup(function (e) {
			if (e.keyCode == 46) {
				deletePress = true;
			}
		});
		/*if (!TI.simpleMode) {
			_system.scene.scale = 0.95;
		}*/
        _system.scene.colors.highlight.value = '50FF3333';
		_system.onSetState.setHandler("theme", rebuildAndSelect);
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

			<div class="wizard_header_info">
				<p class="wizard_info"><?php echo $ti->getVar('PRODUCT_NAME') ?></p>
				<button id="nextButton" name="next" class="next_button" value="next">Next</button>
				<button id="previousButton" name="previous" class="previous_button" value="previous">Previous</button>
				<a id="helpButton" target="_blank" name="previous" class="help_button buttonize" value="previous" href="<?php echo $ti->HOME_URL; ?>/GENESYS USER HELP.pdf">Help</a>
			</div>		
			
		</div>
		<div class="wizard_body">
			<div class="left">
				<div class="controls">
					<button type="submit" id="undo" class="buttonize button-image"> 
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/undo.png" class="icon" alt="Undo" />
					</button>
					<button type="submit" id="redo" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/redo.png" class="icon" alt="Redo" />
					</button>
					<?php if(!$container->simpleMode) { ?>
					<button type="submit" id="delete" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/delete.png" class="icon" alt="Delete" />
					</button>
					<button type="submit" id="front" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/front.png" class="icon" alt="Front 1" />
					</button>
					<button type="submit" id="back" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/back.png" class="icon" alt="Back 1" />
					</button>
                    <?php } ?>
					<button type="submit" id="rleft" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/rotate left.png" class="icon" alt="Rotate left" />
					</button>
					<button type="submit" id="rright" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/rotate right.png" class="icon" alt="Rotate right" />
					</button>
                    <?php if(!$container->simpleMode) { ?>
					<button type="submit" id="copy" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/copy.png" class="icon" alt="Copy" />
					</button>
					<?php } ?>
					<button type="submit" id="mhoriz" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/mirror horizontally.png" class="icon" alt="Mirror horizontally" />
					</button>
					<button type="submit" id="mvert" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/mirror vertically.png" class="icon" alt="Mirror vertically" />
					</button>
					<button type="submit" id="select-element" class="button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/Select.png" class="icon" alt="Select element" />
						<select id="select-element-list">
							<option value="">No Elements</option>
						</select>
					</button>
					<?php if(!$container->simpleMode) { ?>
					<button type="submit" id="grid" class="buttonize button-image">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/Grid.png" class="icon" alt="Toggle grid" />
					</button>
					<?php } ?>
				</div>
				<div class="preview_section">
					<canvas id="canvas" class="preview_canvas" style="cursor: move;"></canvas>
				</div>
			</div>
			<div class="right">
				<div class="add-elements">
				<?php if(!$container->simpleMode) { ?>
					<div id="addText" class="add-text button-dropdown">
						<img id="addText-img" src="<?php echo $ti->HOME_URL; ?>/images/icons/Add text options.png" alt="Add text options" class="element-button" />
						<select id="addText-select">
							<option value="0">Text Line</option>
							<option value="2">Text Circle</option>
							<option value="1">Text Ellipse</option>
						</select>
					</div>
					<div id="addImage" class="add-image button-dropdown">
						<img id="addImage-img" src="<?php echo $ti->HOME_URL; ?>/images/icons/Add Image.png" alt="Add Image" class="element-button" />
						<select id="addImage-select">
							<option value="0">Image upload</option>
							<option value="1">Image Library</option>
						</select>
					</div>
                    
					<div id="addBorder" class="add-border button-dropdown">
						<img id="addBorder-img" src="<?php echo $ti->HOME_URL; ?>/images/icons/Add Border options.png" alt="Add Border options" class="element-button" />
						<select id="addBorder-select">
							<option value="0">Square Border</option>
							<option value="2">Circular Border</option>
							<option value="1">Elliptical Border</option>
						</select>
					</div>
				<?php } ?>
                <?php if (!$container->simpleMode || $product->colorModel != '1_BIT') { ?>
					<div id="backgroundColor" class="background-color button-dropdown">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/Background Color.png" alt="Background Color" class="element-button" />
					</div>
                <?php } ?>
				</div>
                <div id="component_box_wrapper" class="modify-element-box-wrapper">
                    <div id="component_box" class="modify-element-box">
                        <p class="empty-appender">Add Design Elements or click the Help button for more details.</p>
                    </div>
                </div>
				<?php if ($container->simpleMode && $product->colorModel != '1_BIT') { ?>
					<!--<div id="backgroundColor" class="background-color-left button-dropdown">
						<img src="<?php echo $ti->HOME_URL; ?>/images/icons/Background Color.png" alt="Background Color" class="element-button" />
					</div>-->
				<?php } ?>
				<?php
					global $activeDesign;
					
					if ($product->colorModel == '1_BIT') {
						$inkColors = $activeDesign->colorPalettes[PaletteColor::COLOR_INK];
						if(count($inkColors) > 1)
						{
							echo '<div class="colour-selection">';
							$container->writeColorSelector();
							echo '</div>';
						}
					}
				?>
			</div>
		</div>
		<div class="wizard_footer">
			
		</div>
		<div id="hidden" style="display:none"></div>
	</div>
	
	<?php $container->writeBodyFooter(); ?>
		
</body>
</html>

<?php } ?>
