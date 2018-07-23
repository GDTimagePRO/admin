<?php
	//TODO: Make chaning the angle update the of the control (avoid loops)
	include_once "_common.php";	
	$_system->forceLogin();
	if($_design_id == "") $_system->loginRedirect();

	$designIds = $_design_db->getSortedDesignIdsByOrderItemId($_session->getActiveOrderItemId());
	
	$selectedIndex = 0;
	if(isset($_GET['page']))
	{
		$selectedIndex = intval($_GET['page']);
		if($selectedIndex < 0) $selectedIndex = 0;
		if($selectedIndex >= count($designIds)) $selectedIndex = count($designIds) - 1;
		
		$_design_id = $designIds[$selectedIndex];
		$_session->setActiveDesignId($_design_id);
	}
	else
	{
		for($i=0; $i<count($designIds); $i++)
		{
			if($designIds[$i] == $_design_id)
			{
				$selectedIndex = $i;
				break;
			}
		}
	}
	
	if($selectedIndex == 0)
	{
		if($_session->getEnableTemplateBrowser())
		{
			$nav_prev = 'design_template_select.php';
			$nav_prev_warning = true;
		}
		else
		{
			$nav_prev = '';
			$nav_prev_warning = false;
		}
	}
	else
	{
		$nav_prev = 'design_customize.php?page='.($selectedIndex - 1);
		$nav_prev_warning = false;
	}
	
	if($selectedIndex == count($designIds) - 1)
	{
		$nav_next = 'confirm_design.php';
	}
	else
	{
		$nav_next = 'design_customize.php?page='.($selectedIndex + 1);
	}
	
	
	$_simple_mode = $_session->getDesignMode() == Session::DESIGN_MODE_SIMPLE;
	
	$design = $_design_db->getDesignById($_design_id);
	
	$initStateJSON = str_replace("\\", "\\\\", $design->designJSON);
	$initStateJSON = str_replace("'", "\\'", $initStateJSON);
	
	$orderItem = $_order_db->getOrderItemById($_session->getActiveOrderItemId());
	if(is_null($orderItem) || ($orderItem->processingStagesId != ProcessingStage::STAGE_PENDING_CONFIRMATION))
	{
		$_system->loginRedirect();
	}
	
	$orderItemConfig = $orderItem->getConfig(); 
	
	$designConfig = $design->getConfigItem();
	$product = $_order_db->getProductById($designConfig->productId);
	
	if($product->frameWidth < $product->width) $product->frameWidth = $product->width;
	if($product->frameHeight < $product->height) $product->frameHeight = $product->height;
	
	//$outputImageScale = 600/25.4; //600 DPI -> Dots Per Millimeter 
	$outputImageScale_preview = 200/25.4; 
	$outputImageScale_trace = 1200/25.4;
	
	$outputImageWidth_preview = round($product->width * $outputImageScale_preview);
	$outputImageHeight_preview = round($product->height * $outputImageScale_preview);
	
	$outputImageWidth_trace = round($product->width * $outputImageScale_trace);
	$outputImageHeight_trace = round($product->height * $outputImageScale_trace);	
	$outputImageFrameWidth_trace = round($product->frameWidth * $outputImageScale_trace);
	$outputImageFrameHeight_trace = round($product->frameHeight * $outputImageScale_trace);
	
	function writeImageDialogCategories()
	{
		global $_image_db;
		$list = $_image_db->getGroupList();
	
		foreach($list as $item)
		{
			if(!$item->hidden)
			{
				echo sprintf("<li><a href='design_part/image_dialog_service.php?tab=%s'>%s</a></li>",
						urlencode($item->id),
						htmlspecialchars($item->name)
				);
			}
		}
	}	
?>

<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/<?php echo $_session->getActiveThemeName(); ?>/jquery-ui.custom.min.css?version=<?php echo $_version ?>" rel="stylesheet" />
	<?php if($_is_mobile) {?>
		<link type="text/css" href="css/design_wizard_mobile.css?version=<?php echo $_version ?>" rel="StyleSheet"/>
	<?php } else {?>	
		<link type="text/css" href="css/design_wizard.css?version=<?php echo $_version ?>" rel="StyleSheet"/>	
	<?php } ?>	
		
	<style>
		.control_group
		{
			margin-bottom:7px;
			margin-left:5px;
			margin-right:2px;			
		}
		
		.control_group_header, .control_group_header_selected 
		{
			-khtml-border-top-left-radius: 6px;
			border-top-left-radius: 6px;
			-khtml-border-top-right-radius: 6px;
			border-top-right-radius: 6px;
			-khtml-border-bottom-left-radius: 6px;
			border-bottom-left-radius: 6px;
			-khtml-border-bottom-right-radius: 6px;
			border-bottom-right-radius: 6px;
			
			border: 1px solid #a3a3a3;
			background: #a3a3a3 50% 50% repeat-x;
			font-weight: bold;
			color: white;			
			
			padding-top:3px;
			margin-top:8px;
			
			line-height: 1.3;
			cursor: default;
			
			height:27px;
		}
		
		.control_group_input_selected
		{
			background-color:#C9F3FF;
		}
		
		.control_group_header_selected
		{
			background: #666666 50% 50% repeat-x;
		}
		
		
		.control_group_title
		{
			float:left;
			padding-top:4px;
			padding-left:11px;
			font-size:120%;
		}
		
		.control_group_more_button
		{
			float:right;
			font-size:10px;
			margin-right:5px;
		}
		
		.control_group_lock_button
		{
			float:right;
			font-size:10px;
			margin-right:5px;
			width:25px;
			height:23px;
		}
		
		.control_group_delete_button
		{
			float:right;
			font-size:10px;
			margin-right:5px;
		}
		
		.control_inline_box
		{
			display:inline-block;
			margin-top:6px;
		}
	
		.control_full_line_box
		{
			display:block;
			width:100%;
			margin-top:6px;
		}
		
		.control_input
		{
			width:100%;
		}
		
		.control_label
		{
			display:inline;
			margin-left:14px;
			margin-right:5px;
		}
		
		.control_number_container
		{
			display:inline-block;
			width:30px;
		}
		
		.control_list_container
		{
			display:inline-block;
			width:80px;
		}
		
		.control_long_list_container
		{
			display:inline-block;
			width:136px;
		}
		
		.control_checkbox_container
		{
			display:inline-block;
			width:50px;
		}
		
		
		.control_text_container
		{
			display:inline-block;
			width:305px;
		}
		
		
		.controls_section
		{
			margin: 0px 0px 0px 0px;
			padding: 0px 0px 0px 0px;			
			
			
			width: <?php echo $_simple_mode ? "43%" : "50%"; ?>;
			height: 100%;
			float:left;
		}
				
		.toolbar_pannel
		{
			-khtml-border-top-left-radius: 6px;
			border-top-left-radius: 6px;
		
			-khtml-border-top-right-radius: 6px;
			border-top-right-radius: 6px;
		
			-khtml-border-bottom-left-radius: 6px;
			border-bottom-left-radius: 6px;
		
			-khtml-border-bottom-right-radius: 6px;
			border-bottom-right-radius: 6px;

			margin: 0px 0px 0px 0px;
			padding: 0px 0px 0px 0px;
			
			width: 100%;
			height: 8.5%;
		}
				
		.element_property_pannel
		{
			margin: 0px 0px 0px 0px;
			width: 100%;
			height: 91.5%;
			overflow-y: scroll;
		}

		.preview_section
		{
			margin: 0px 0px 0px 0px;
			padding: 0px 0px 0px 0px;			
			width:  <?php echo $_simple_mode ? "57%" : "50%"; ?>;
			height: 100%;
			float:right;
		}
		
		.preview_canvas
		{
			-khtml-border-top-right-radius: 6px;
			border-top-right-radius: 6px;
		
			margin: 0 auto;
			padding: 0px 0px 0px 0px;			
			width: 100%;
			height: <?php echo $_simple_mode ? "84%" : "80%"; ?>;
			background-color:gray;
		}
		
		.zoom_container_table
		{
			width: 100%;
			padding: 0px 0px 0px 0px;			
		}
		
		.zoom_container_table_icon
		{
			width: 20%;
			padding-left:10px;
			padding-right:10px;
		}
		
		.add_element_button
		{
			width:90px;
			height:58px;
		}	
		
		.class_box_shadow
		{
		    margin: auto;
		    position:relative;
		    box-shadow: 0px 0px 13px rgba(0, 0, 0, 0.80);
		    -moz-box-shadow: 0px 0px 13px rgba(0, 0, 0, 0.80);
		    -webkit-box-shadow: 0px 0px 13px rgba(0, 0, 0, 0.80);
		}
		
		.button_bar_button
		{
			width: 18px;
			height: 18px;
			margin-top:4px;
			margin-left:2px;
			margin-bottom:2px;
			margin-right:2px;
		}
			
		#btnbar_bold
		{
			margin-left: 15px;
		}
				
		.color_selector_container
		{
			text-align:center;
			margin-top:7px;
			vertical-align:middle;
		}
				
		.color_selector_label
		{
			display: inline-block;
			padding-right:10px;
			white-space: nowrap;
			<?php if($_is_mobile) {?>
				font-size: 17px;
				vertical-align:35%;
			<?php } else { ?>
				vertical-align:40%;
			<?php } ?>
		}
		
		.color_selector_box, .color_selector_box_selected
		{
			display: inline-block;
			<?php if($_is_mobile) {?>
				width: 22px;
				height: 22px;
				margin-left: 0px;
				margin-right: 2px;
				margin-top: 7px;
			<?php } else { ?>
				width: 18px;
				height: 18px;
			<?php } ?>
			border-style:solid;
			border-width:4px;
			border-color:#F2F5F7;
		}

		.color_selector_box_selected
		{
			border-color:black;
		}
		
		.color_selector_box:hover
		{
			border-color:silver;
		}
		
		
	</style>
	
	<script src="js/lib/jquery-1.8.0.min.js"></script>
	<script src="js/lib/jquery-ui-1.8.23.custom.min.js"></script>
	<script src="js/lib/jquery.json-2.3.min.js"></script>	
	
	<script src="js/lib/file_upload/vendor/jquery.ui.widget.js"></script>
	<script src="js/lib/file_upload/jquery.iframe-transport.js"></script>
	<script src="js/lib/file_upload/jquery.fileupload.js"></script>
	<script src="js/lib/file_upload/canvas-to-blob.js"></script>
	<script src="js/lib/qtip/jquery.qtip-1.0.0-rc3.js"></script>		
	
	
	<script src="js/design/system.js?version=<?php echo $_version ?>"></script>
	
	<script src="js/design/maps.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/patterns.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/drawables.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/widgets.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/scene.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/script_container.js?version=<?php echo $_version ?>"></script>
	
	
	<?php if($_simple_mode){ ?>
		<script src="js/design/ui_panel_basic.js?version=<?php echo $_version ?>"></script>
	<?php } else { ?>
		<script src="js/design/ui_panel_v2.js?version=<?php echo $_version ?>"></script>
	<?php }?>
	
	<script src="js/design/elements/prototype_element.js?version=<?php echo $_version ?>"></script>
	
	<script src="js/design/elements/border_element.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/image_element.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/line_element.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/text_element.js?version=<?php echo $_version ?>"></script>
	
	<script src="js/browser_check.js?version=<?php echo $_version ?>"></script>	
	
	<script type="text/javascript">

	
		forceModernBrowser();
	
		<?php 
		if($_is_mobile){
			echo "UIPanel.MOBILE_MODE = true;";
		}
		?>
			
		
		System.DESIGN_ID = <?php echo $_design_id; ?>;
		System.ASPECT_RATIO =  Math.round(<?php echo $product->height / $product->width; ?> * 10000);
		Scene.RENDER_SERVICE_URL = "<?php echo $_settings[Startup::SETTING_RENDER_SERVICE]; ?>";
		
		var _designImageId = "<?php echo $design->getPreviewImageId(); ?>"; 
		var _hdImageId = "<?php echo $design->getHighDefImageId(); ?>"; 
		
		var _outputImageWidth_preview = <?php echo $outputImageWidth_preview; ?>;
		var _outputImageHeight_preview = <?php echo $outputImageHeight_preview; ?>;

		var _outputImageWidth_trace = <?php echo $outputImageWidth_trace; ?>;
		var _outputImageHeight_trace = <?php echo $outputImageHeight_trace; ?>;
		var _outputImageFrameWidth_trace = <?php echo $outputImageFrameWidth_trace; ?>;
		var _outputImageFrameHeight_trace = <?php echo $outputImageFrameHeight_trace; ?>;

		var _nav_prev = "<?php echo $nav_prev; ?>";
		var _nav_prev_warning = "<?php echo $nav_prev_warning; ?>";
		var _nav_next = "<?php echo $nav_next; ?>";
		
		var _imageSelectDialog = new function()
		{			
			var tabsCreated = false; 
			var me = this;
			var tabContainerId = "dialog_select_image_tabs"; 
			var dialogContainerId = "dialog_select_image"; 
			var selectedFile = null;
			
			this.selectedImage = -1;
			this.highlighSelected = function()
			{
				var tabContainer = $( "#" + tabContainerId ); 
				tabContainer.find(".image_cell_selected").attr("class", "image_cell");
				tabContainer.find("[imageId=\"" + this.selectedImage + "\"]").attr("class", "image_cell_selected");
			};
			
			this.setSelected = function(id)
			{
				this.selectedImage = id;
				this.highlighSelected();
			};
			
			this.getSelected = function()
			{
				return this.selectedImage;
			};

			this.selectFile = function(file)
			{
				selectedFile = file;
				if(selectedFile)
				{
					var tabContainer = $( "#" + tabContainerId ); 
					tabContainer.find("#dialog_select_image_filename").html(
						"File Name: "+selectedFile.name+"<br />" + 
						"File Size: "+(selectedFile.size/1024).toFixed(2)+"KB"
					);
				}
			};
			
			this.uploadFile = function()
			{
				if(selectedFile)
				{
					_system.uploadImageFiles(
							selectedFile, 
							1, //ImageDB::CATEGORY_USER_UPLOADED
							
							function(id)
							{
								selectedFile = null;
								me.setSelected(id);
								
								var tabContainer = $( "#" + tabContainerId );
								tabContainer.tabs( "load" , tabContainer.tabs( "option", "selected" ) );
							},
							
							function(value)
							{
								//$("#uploadFileProgressBar").progressbar( "value" , Math.floor(value * 100.0));				
							}
						);
				}
				else
				{
					alert("Please select a file to upload.");
				}
			};

			this.removeImage = function(removeSelected)
			{
				if (removeSelected)
				{
					if (confirm("Are you sure you want to delete the image?"))
					{
						$.ajax({
							url: 'design_part/image_dialog_service.php',
							data: {removeImageId: removeSelected},
							type: 'post',
							success: function() {
								var tabContainer = $( "#" + tabContainerId );
								tabContainer.tabs( "load" , tabContainer.tabs( "option", "selected" ) );
								this.selectedImage = -1;
							}
						});
					}
				}
				else
				{
					alert("Please select a file to remove.");
				}
			};
			
			this.show = function(selectedImageId, onSelect)
			{
				if(!tabsCreated)
				{
					$( "#" + tabContainerId ).tabs({
						load: function() { me.highlighSelected(); }
					});
					tabsCreated = true;
				}
				 
				this.setSelected(selectedImageId);
				$( "#" + dialogContainerId ).dialog({
					resizable: false,
					height:600,
					width:800,
					modal: true,
					buttons: {
						"Ok": function() {
							if(me.selectedImage < 0)
							{
								alert("Please select an image and try again.");
								return;
							}
							$( this ).dialog( "close" );
							onSelect(me.selectedImage);
						},
						"Cancel": function() {
							me.selectedImage = -1;
							$( this ).dialog( "close" );
						}
					},
					"Cancel": function() {
						$( this ).dialog( "close" );
					},
				});			
			};
			
			$(document).ready(function() {
			});
			
		};
	
		
		$(document).ready(function() {

			var canvas = $("#canvas");
			var width = canvas.width();
			var height = canvas.height();

			var defaultElementEditAllowMoveState = false;

			
			canvas = canvas[0];
			canvas.width = width;
			canvas.height = height;
	
			_system.onInit("canvas","uiPanel","", width, height);
			
			var _initStateJSON = '<?php echo $initStateJSON; ?>';
			if(_initStateJSON != '')
			{
				_system.setState(jQuery.parseJSON(_initStateJSON));
			}			
			_system.clearStateHistory();
			_system.saveState(true);
			
			_system.ui.onDeleteClick = function(name, element)
			{
				$( "#dialog_delete_element_name" ).text(name);	
				$( "#dialog_delete_element" ).dialog({
					resizable: false,
					height:200,
					width:350,
					modal: true,
					buttons: {
						"Yes": function() {
							_system.removeElement(element);
							$( this ).dialog( "close" );
						},
						"No": function() {
							$( this ).dialog( "close" );
						}
					}
				});
				
			};
				
			
			$("#zoom").slider({
				max: 250,
				min: 35,
				value: 100,
				slide: function(event, ui) {
					_system.scene.scale = $(this).slider("value") / 100.0;
					_system.scene.redraw(); 
				}
			});
	
			$("#help").button().click(function() { } );

			$("#undo").button().click(function() {
				_system.undo();
			});
	
			$("#redo").button().click(function() {
				_system.redo(); 
			});


			var activeAddElementDialog = null;
			
			$("#addElement").button().click(function() {
				activeAddElementDialog = $( "#dialog_add_element" ).dialog({
					resizable: false,
					height:320,
					width:350,
					modal: true,
					buttons: {
						"Cancel": function() {
							$( this ).dialog( "close" );
						}
					}
				});
			} );

			$("#addTextLine").button().click( function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new TextElement();
				var offsetX = _system.getPageWidth() / 2;
				ele.setEditAllowMove(true);
				ele.setPosition(-offsetX, 0, offsetX, 0);
				ele.setType(TextElement.TYPE_LINE);
				ele.setText("");				
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele); 
				_system.scene.redraw();
				
			});
			 
			$("#addTextCircle").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new TextElement();
				ele.setType(TextElement.TYPE_CIRCLE);
				ele.setEditAllowMove(true);
				ele.setPosition(-100, -100, 100, 100, Math.PI*3/2);
				ele.setText("");
				ele.getUIControlGroup().showMore = true;
				
				_system.addElement(ele);
				_system.setSelected(ele); 
				_system.scene.redraw();
				
			});
			 
			$("#addTextEllipse").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new TextElement();
				ele.setType(TextElement.TYPE_ELLIPSE);
				ele.setEditAllowMove(true);
				ele.setPosition(-110, -80, 110, 80, Math.PI*3/2);
				ele.setText("");
				ele.getUIControlGroup().showMore = true;
				
				_system.addElement(ele);
				_system.setSelected(ele); 
				_system.scene.redraw();
				
			});
			
			$("#addBorderRectangle").button().button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new BorderElement();
				ele.setType(BorderElement.TYPE_BOX);
				ele.setEditAllowMove(true);

				var offsetX = _system.getPageWidth() / 2;
				var offsetY = _system.getPageHeight() / 2;
				ele.setPosition(
					-offsetX, -offsetY,
					offsetX, offsetY
				);
				ele.getUIControlGroup().showMore = true;
				
				_system.addElement(ele);
				_system.setSelected(ele); 
				_system.scene.redraw();
				
			});

			 
			$("#addBorderCircle").button().button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new BorderElement();
				ele.setType(BorderElement.TYPE_CIRCLE);
				ele.setEditAllowMove(true);

				var offsetX = _system.getPageWidth() / 2;
				var offsetY = _system.getPageHeight() / 2;
				if(offsetX < offsetY) { offsetY = offsetX; }
				else { offsetX = offsetY; }
				
				ele.setPosition(
					-offsetX, -offsetY,
					offsetX, offsetY
				);
				ele.getUIControlGroup().showMore = true;
				
				_system.addElement(ele);
				_system.setSelected(ele); 
				_system.scene.redraw();
				
			});

			 
			$("#addBorderEllipse").button().button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new BorderElement();
				ele.setType(BorderElement.TYPE_ELLIPSE);
				ele.setEditAllowMove(true);

				var offsetX = _system.getPageWidth() / 2;
				var offsetY = _system.getPageHeight() / 2;
				
				ele.setPosition(
					-offsetX, -offsetY,
					offsetX, offsetY
				);
				ele.getUIControlGroup().showMore = true;
				
				_system.addElement(ele);
				_system.setSelected(ele); 
				_system.scene.redraw();
				
			});
	
			
			$("#addImageElement").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				_imageSelectDialog.show(-1,function(id) { 
				
					var ele = new ImageElement();
					ele.setEditAllowMove(true);
					ele.setMaintainAspectRatio(true);
					ele.setPosition(-60, -60, 60, 60);
					
					ele.loadImage(new ImageSrc(ImageSrc.TYPE_ID, id), false);
					
					ele.getUIControlGroup().showMore = true;
					
					_system.addElement(ele);
					_system.setSelected(ele); 
					_system.scene.redraw();
				
				});
			});
			
			$("#addLineElement").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new LineElement();
				ele.setEditAllowMove(true);
				ele.setPosition(-100, 0, 100, 0);
				ele.getUIControlGroup().showMore = true;
				
				_system.addElement(ele);
				_system.setSelected(ele); 
				_system.scene.redraw();
				
			});			
			
			
			$("#previousButton").button().click( function() {

				if(_nav_prev_warning)
				{
					$( "#dialog_confirm_previous" ).dialog({
						resizable: false,
						height:200,
						width:350,
						modal: true,
						buttons: {
							"Yes": function() {
								$( this ).dialog( "close" );
								window.location = _nav_prev;
							},
							"No": function() {
								$( this ).dialog( "close" );
							}
						}
					});
				}
				else
				{
					window.location = _nav_prev;
				}
			});


			
			$("#saveProgressBar").progressbar();

			
			function renderOffscreen(imageWidth, imageHeight, frameWidth, frameHeight, createTrace)
			{
				var scaleWidth = imageWidth / _system.getPageWidth(); 
				var scaleHeight = imageHeight / _system.getPageHeight(); 

				var backBuffer = $("<canvas></canvas>")[0];
				if(!createTrace)
				{
					backBuffer.style.width = frameWidth + "px";
					backBuffer.style.height = frameHeight + "px";
					backBuffer.width = frameWidth;
					backBuffer.height = frameHeight;
				}
								
				var trace = _system.scene.drawTo(
					backBuffer, 
					frameWidth, 
					frameHeight,
					(scaleWidth < scaleHeight) ? scaleWidth : scaleHeight,
					Scene.DISPLAY_GROUP_CONTENT, 
					"black", 
					"#FFFFFF",
					createTrace
				);
				
				return createTrace ? trace : backBuffer;
			}
			
			
			$("#nextButton").button().click( function() {
	
				$("#save_progress_bar").progressbar( "value" , 0);
				$("#dialog_saving_design").css("visibility","visible");

				//send the current state to the server
				_system.setDesignJSON(_system.getStateJSON());

				_system.invokeWhenReady(function() {					
				
					var hdImageUploaded = true;
					var previewImageUploaded = false;

					$.support.cors = true;
					
					/*
					hdImageUploaded = false;
					var hdScaleWidth = _outputImageWidth_trace / _system.getPageWidth(); 
					var hdScaleHeight = _outputImageHeight_trace / _system.getPageHeight(); 
					var hdScale = Math.min(hdScaleWidth,hdScaleHeight); 

					var hdImageFileName = _hdImageId.substring(_hdImageId.indexOf('/') + 1, _hdImageId.length - 4);
					var hdImageQuery = _system.scene.getRenderServiceQuery(
						_outputImageWidth_trace, 
						_outputImageHeight_trace, 
						"",
						"black", 
						hdScale, 
						Scene.DISPLAY_GROUP_CONTENT,
						hdImageFileName,
						_outputImageFrameWidth_trace,
						_outputImageFrameHeight_trace,
						"#ffffff"
					);					

					$.get(hdImageQuery).done(function() {
						hdImageUploaded = true;
						if(hdImageUploaded && previewImageUploaded) window.location = _nav_next;
					});
					//*/															
					
					
					var previewScaleWidth = _outputImageWidth_preview / _system.getPageWidth(); 
					var previewScaleHeight = _outputImageHeight_preview / _system.getPageHeight(); 
					var previewScale = Math.min(previewScaleWidth,previewScaleHeight); 

					var previewImageFileName = _designImageId.substring(_designImageId.indexOf('/') + 1, _designImageId.length - 4);
					var previewImageQuery = _system.scene.getRenderServiceQuery(
						_outputImageWidth_preview, 
						_outputImageHeight_preview, 
						"web_black.",
						"black", 
						previewScale, 
						Scene.DISPLAY_GROUP_CONTENT,
						previewImageFileName
					);					

					var sendPreviewRenderRequest = function()
					{												
						var didRespond = false;
						$.get(previewImageQuery).done(function(){
							didRespond = true;
							previewImageUploaded = true;
							if(hdImageUploaded && previewImageUploaded) window.location = _nav_next;
						}).fail(function(){
							didRespond = true;
							setTimeout(function() {
								sendPreviewRenderRequest();
							}, 100);
						}).always(function(){
							if(!didRespond)
							{
								window.location = _nav_next;
							}							
						});
					};
					sendPreviewRenderRequest();
				});
			});			
		});
	</script> 	  
</head>	

<body unselectable="on" class="unselectable">
	<div class="wizard_frame">
		<div class="wizard_header">
			<?php if($_is_mobile) { ?>
				<div class="wizard_header_title"><img src="images/masonrow/logo/MR_LogoHorizM.png" alt="MasonRow" width="282px" height="75px"></div>
				<div class="wizard_header_info">You are working on a <?php echo htmlspecialchars($product->longName) ?>.</div>
			<?php } elseif($_simple_mode){ ?>
				<div class="wizard_header_title"><img src="images/masonrow/logo/MR_LogoHoriz.png" alt="MasonRow" width="400px" height="106px"></div>
				<div class="wizard_header_info">You are working on a <?php echo htmlspecialchars($product->longName) ?>.</div>			
			<?php } else { ?>
				<div class="wizard_header_title"><img src="images/masonrow/logo/MR_LogoHoriz.png" alt="MasonRow" width="400px" height="106px"></div>
				<div class="wizard_header_info">You are working on a <?php echo htmlspecialchars($product->longName) ?>.</div>
			<?php } ?>
						
		</div>
		<div class="wizard_body ui-widget-content">			
			<div class="controls_section">
				<div class="toolbar_pannel ui-widget-header">			
					<!-- <button style="margin-top:4px; width: 80px; margin-left:5px; float:left;" id="help">Help</button> -->					
					<button style="margin-top:4px; width: 80px; margin-left:8px; float:left;" id="undo">Undo</button>
					<button style="margin-top:4px; width: 80px; margin-left:5px; float:left;" id="redo">Redo</button>
					<?php if(!$_simple_mode){ ?>
						<button style="margin-top:4px; width: 120px; margin-right:7px; float:right;" id="addElement">Add Element</button>					
					<?php } ?>
					
					</div>
				<div id="uiPanel" class="element_property_pannel">			
				</div>
			</div>
			<div class="preview_section">
				<canvas id="canvas" class="preview_canvas"></canvas>
				<?php if(!$_is_mobile) { ?>
					<table class="zoom_container_table">
					<tr>
						<td align="right" class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomout"></span></td>
						<td valign="middle"><div id="zoom"></div></td>
						<td align="left" class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomin"></span></td>
					</tr>
					</table>
				<?php }?>
				<?php if(count($designIds) > 1) { ?>
				<div class="color_selector_container" id="ink_color_palette">				
					<div class="color_selector_label">Ink Colour</div>
					<div class="color_selector_box" id="black" style="background-color:black" onclick="_system.changeInkColour('black')"></div>
					<div class="color_selector_box" id="FireBrick" style="background-color:FireBrick"  onclick="_system.changeInkColour('FireBrick')"></div>
					<div class="color_selector_box" id="RoyalBlue" style="background-color:RoyalBlue" onclick="_system.changeInkColour('RoyalBlue')"></div>
					<div class="color_selector_box" id="Crimson" style="background-color:Crimson"  onclick="_system.changeInkColour('Crimson')"></div>					
					<div class="color_selector_box" id="PaleVioletRed" style="background-color:PaleVioletRed" onclick="_system.changeInkColour('PaleVioletRed')"></div>
					<div class="color_selector_box" id="LimeGreen" style="background-color:LimeGreen" onclick="_system.changeInkColour('LimeGreen')"></div>
					<div class="color_selector_box" id="DodgerBlue" style="background-color:DodgerBlue" onclick="_system.changeInkColour('DodgerBlue')"></div>
					<div class="color_selector_box" id="Sienna" style="background-color:Sienna" onclick="_system.changeInkColour('Sienna')"></div>
					<div class="color_selector_box" id="SlateBlue" style="background-color:SlateBlue" onclick="_system.changeInkColour('SlateBlue')"></div>
					
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<?php if($nav_prev != '') { ?>
			<button id="previousButton" name="previous" class="previous_button" value="previous">Previous</button>
			<?php }?>
			<button id="nextButton" name="next" class="next_button" value="next">Next</button>
		</div>
	</div>
	

	<div id="fade"></div>


	<div id="dialog_add_element" title="Add element" class="hidden">						
		<table style="margin-left:auto; margin-right:auto; margin-top:15px;">
		<tr>
			<td><button class="add_element_button" id="addTextLine">Text<br>Line</button></td>
			<td><button class="add_element_button" id="addTextCircle">Text<br>Circle</button></td>
			<td><button class="add_element_button" id="addTextEllipse">Text<br>Ellipse</button></td>
		</tr>
		
		<tr>
			<td><button class="add_element_button" id="addBorderRectangle">Pattern<br>Box</button></td>
			<td><button class="add_element_button" id="addBorderCircle">Pattern<br>Circle</button></td>
			<td><button class="add_element_button" id="addBorderEllipse">Pattern<br>Ellipse</button></td>
		</tr>
		
		<tr>
			<td><button class="add_element_button" id="addImageElement">Image or<br>Symbol</button></td>
			<td><button class="add_element_button" id="addLineElement">Line</button></td>
			<td></td>
		</tr>
		</table>
	</div>	

	<div id="dialog_select_image" title="Select image" class="hidden">
		<div id="dialog_select_image_tabs" style="width:100%;">
			<ul>
				<?php writeImageDialogCategories(); ?>
			</ul>
		</div>
	</div>		

	
	<div id="dialog_confirm_previous" title="Revert to template" class="hidden">						
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Returning to the template selection screen will reset your design to the selected template.<br><br> Are you sure you wish to continue ?</p>
	</div>
	
	<div id="dialog_delete_element" title="Remove element" class="hidden">						
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to remove the "<span id="dialog_delete_element_name"></span>" element from your design.<br><br> Are you sure you wish to continue ?</p>
	</div>
	
	<div id="dialog_saving_design" style="visibility:hidden;width: 100%; height: 100%; display: block; position: absolute; left: 0; top: 0; z-index: 99999;">
		<div style="position:absolute; left: 0; top: 0; width:100%; height:100%; background-color: #000000; opacity: .50;"></div>
		<div style="width: 250px; margin: 0 auto;  position:relative; top: 30%; border-radius: 10px; border: 6px solid; border-color: #000; color: #000000; background: #FFFFFF;text-align: center;">
			<h2>Your design is being saved, please stand by.</h2>
			<div id="save_progress_bar"></div>			
		</div>
	</div>
	
	<div id="uiPanelSlider" class="class_box_shadow" style="position:absolute; height:100px;"></div>
</body>
</html>



