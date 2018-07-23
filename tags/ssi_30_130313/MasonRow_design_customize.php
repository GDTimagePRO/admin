<?php
/*
	Williams needs the name changed
	Space replace " " to "  " for address lines 1 & 2 on PREDDER
	Put order id first in the name, and line number second
	
	Torun:
	
*/

	include_once "_common.php";
	include_once "./backend/order_logic.php";
	
	//login
	$email = "a@a.a";
	$password = "asd";
	
	if (($user = $_user_db->getUserByEmail($email)) != NULL)
	{
		if ($password == $user->password)
		{
			$_session->setActiveUserId($user->id);
		}
		else
		{
			echo "Wrong password";
			exit;
		}
	}
	else
	{
		echo "Failed to find user";
		exit;
	}

	$query = 'SELECT * FROM batch_queue WHERE image_id = -1 LIMIT 0, 1';
	
	$result = mysql_query($query,$_system->db->connection);
	if(!$result)
	{
		echo "Error reading from queue";
		exit;
	}
	
	$row = mysql_fetch_assoc($result);
	if(!$row)
	{
		echo "Queue is empty";
		exit;
	}
	
	$batchItemId = $row['id'];

	$order = getActiveOrder($user->id, true);	
	if(!$order)
	{
		echo "Failed to create / get new order";
		exit;
	}
		
	$isTrio = substr($row['product_id'], 0, 3) == 'TR-'; 
	
	$orderItem = createOrderItem($order->id, $row['product_id']);
	if(!$orderItem)
	{
		echo "Error creating order item";
		exit;
	}
	
	selectOrderItem($orderItem);
	$_user_id	= $_session->getActiveUserId();
	$_design_id	= $_session->getActiveDesignId();
	
	$template = $_design_db->getDesignTemplateByName($row['display_name']);
	if(!$template)
	{
		echo "Error loading template";
		exit;
	}
	
	$_design_db->setDesignJSON($_design_id, $template->json);
	
	$barcode = $_order_db->getBarcodeByBarcode($row['product_id']);
	$product = $_order_db->getProductById($barcode->productId);
	if(!$product)
	{
		echo "Error reading product";
		exit;
	}
	
	$keyValuePairs = array();
	for($i=1; $i<=15; $i++)
	{
		$keyValuePairs[] = array($row['p'.$i.'_id'], $row['p'.$i.'_value']);
	}
	
	if($product->frameWidth < $product->width) $product->frameWidth = $product->width;
	if($product->frameHeight < $product->height) $product->frameHeight = $product->height;
	
	$outputImageScale = 1200/25.4; //600 DPI -> Dots Per Millimeter
	$outputImageWidth = round($product->width * $outputImageScale);
	$outputImageHeight = round($product->height * $outputImageScale);
	$outputImageFrameWidth = round($product->frameWidth * $outputImageScale);
	$outputImageFrameHeight = round($product->frameHeight * $outputImageScale);


	$_simple_mode = false;
	
	$initStateJSON = str_replace("\\", "\\\\", $_design_db->getDesignJSON($_design_id));
	$initStateJSON = str_replace("'", "\\'", $initStateJSON);
	$templateKeyValuePairJSON = str_replace("\\", "\\\\", json_encode($keyValuePairs));
	$templateKeyValuePairJSON = str_replace("'", "\\'", $templateKeyValuePairJSON);
		
	function writeImageDialogCategories()
	{
		global $_image_db;
		$list = $_image_db->getImageCategoryList();
	
		foreach($list as $item)
		{
			echo sprintf("<li><a href='design_part/image_dialog_service.php?tab=%d'>%s</a></li>",
					$item['id'],
					htmlspecialchars($item['name'])
			);
		}
	}	
?>

<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/cupertino/jquery-ui-1.8.23.custom.css?version=<?php echo $_version ?>" rel="stylesheet" />
	<link type="text/css" href="css/design_wizard.css?version=<?php echo $_version ?>" rel="StyleSheet"/>	
	
	<style>
		<?php include "css/design_fonts.css"; ?>
		
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
			
			border: 1px solid #2694E8;
			background: #3BAAE3 url(css/themes/cupertino/images/ui-bg_glass_50_3baae3_1x400.png) 50% 50% repeat-x;
			font-weight: bold;
			color: white;			
			
			padding-top:3px;
			margin-top:8px;
			
			line-height: 1.3;
			cursor: default;
			
			height:27px;
		}
		
		.control_group_header_selected
		{
			background: #5d9bbb url(css/themes/cupertino/images/ui-bg_glass_50_3baae3_1x400_selected.png) 50% 50% repeat-x;
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
			vertical-align:40%;
			padding-right:10px;
			white-space: nowrap;
		}
		
		.color_selector_box, .color_selector_box_selected
		{
			display: inline-block;
			width: 18px;
			height: 18px;
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
	
	
		System.ASPECT_RATIO =  Math.round(<?php echo $product->height / $product->width; ?> * 10000);
		
		var _designImageId = <?php echo $_session->getActiveDesignImageId(); ?>; 
		var _designImageCategoryId = <?php echo ImageDB::CATEGORY_DESIGN_IMAGE; ?>; 
		var _outputImageWidth = <?php echo $outputImageWidth; ?>;
		var _outputImageHeight = <?php echo $outputImageHeight; ?>;
		var _outputImageFrameWidth = <?php echo $outputImageFrameWidth; ?>;
		var _outputImageFrameHeight = <?php echo $outputImageFrameHeight; ?>;



		
		
		var _imageSelectDialog = new function()
		{			
			var me = this;
			var tabContainerId = "dialog_select_image_tabs"; 
			var dialogContainerId = "dialog_select_image"; 
			var selectedFile = null;
			
			this.selectedImage = -1;
			this.highlighSelected = function()
			{
				var tabContainer = $( "#" + tabContainerId ); 
				tabContainer.find(".image_cell_selected").attr("class", "image_cell");
				tabContainer.find("#image_" + this.selectedImage).attr("class", "image_cell_selected");
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
				$( "#" + tabContainerId ).tabs({
					load: function() { me.highlighSelected(); }
				});
			});
			
		};

		var setTextElementText = function(name, value)
		{
			if(name == "") return;
			
			for(var i in _system.elements)
			{
				var e = _system.elements[i]; 
				if((e.className == "TextElement") && (e.getUIControlGroup().title == name))
				{
					e.setText(value);
				} 
			}			
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
			var _templateKeyValuePairJSON = '<?php echo $templateKeyValuePairJSON; ?>';
			var _templateKeyValuePairs = jQuery.parseJSON(_templateKeyValuePairJSON);

			_system.setState(jQuery.parseJSON(_initStateJSON));
			
			for(var iKVP in _templateKeyValuePairs)
			{
				var kvp = _templateKeyValuePairs[iKVP];
				setTextElementText(kvp[0], kvp[1]);
			} 
			setTimeout( function() {
				$("#nextButton").click();
			}, 500);

			
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
					
					ele.loadImage({type:1, id: id}, false);
					
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

				$( "#dialog_confirm_previous" ).dialog({
					resizable: false,
					height:200,
					width:350,
					modal: true,
					buttons: {
						"Yes": function() {
							$( this ).dialog( "close" );
							window.location = "design_template_select.php";
						},
						"No": function() {
							$( this ).dialog( "close" );
						}
					}
				});
			});


			
			$("#saveProgressBar").progressbar();

			$("#nextButton").button().click( function() {
				
				$("#save_progress_bar").progressbar( "value" , 0);
				$("#dialog_saving_design").css("visibility","visible");

				//send the current state to the server
				_system.setDesignJSON(_system.getStateJSON());
				$("#save_state_message").text("Waiting for images.");
				
				_system.invokeWhenReady(function() {					
					$("#save_state_message").text("Safety margine");
					setTimeout( function() {
								
						$("#save_state_message").text("Rendering.");
					
						var scaleWidth = _outputImageWidth / _system.getPageWidth(); 
						var scaleHeight = _outputImageHeight / _system.getPageHeight(); 
						
						var width = _outputImageFrameWidth;
						var height = _outputImageFrameHeight; 				
		
						var backBuffer = $("<canvas></canvas>")[0];				
						backBuffer.style.width = width + "px";
						backBuffer.style.height = height + "px";
						backBuffer.width = width;
						backBuffer.height = height;
						
						_system.scene.drawTo(
							backBuffer, 
							width, 
							height,
							(scaleWidth < scaleHeight) ? scaleWidth : scaleHeight,
							Scene.DISPLAY_GROUP_CONTENT, 
							"black", 
							"#FFFFFF"
						);
<?php echo $isTrio ? '//*' : '/*'; ?>						
//*						
						if(_outputImageWidth < _outputImageFrameWidth)
						{
							var paddingSize = Math.min(
								_outputImageFrameWidth - _outputImageWidth, 
								_outputImageFrameHeight - _outputImageHeight 
							) / 2 ;
		
							
							var context = backBuffer.getContext('2d');
							var lineWidth = Math.min(10, paddingSize);
							context.lineWidth = 1;
							context.fillStyle = 'black';
							context.fillRect( 0, 0, width, lineWidth );
							context.fillRect( 0, 0, lineWidth, height );
							context.fillRect( width - lineWidth, 0, width, height );
							context.fillRect( 0, height - lineWidth, width, height );
						}						
//*/
						$("#save_state_message").text("Uploading.");
						//window.open(backBuffer.toDataURL());
						_system.saveCanvasAsImage(
								backBuffer, 
								_designImageId, 
								_designImageCategoryId,
							function() 
							{
									$("#save_state_message").text("Done...");						
									//$("#dialog_saving_design").css("visibility","hidden");
									window.location = "MasonRow_design_submitted.php?orderItemId=<?php echo $orderItem->id; ?>&batchItemId=<?php echo $batchItemId; ?>";
							}, 
							function(value)
							{
								$("#save_progress_bar").progressbar( "value" , Math.floor(value * 100.0));
							}
						);						
					}, 1000);						
				});
			});			
		});
			
		//force fontloading
		(function() {
			
			var backBuffer = $("<canvas></canvas>")[0];				
			backBuffer.style.width = "100px";
			backBuffer.style.height = "100px";
			backBuffer.width = 100;
			backBuffer.height = 100;

			var context = backBuffer.getContext("2d");
			for(var i in TextElement.FONTS)
			{
				context.font = '36px "' + TextElement.FONTS[i].name + '"';
				context.fillText(".", 50, 50);				
			}

			$(document).ready(function(){
				//var counter = 0;
				var updateTimer = setInterval(function()
					{

						_system.scene.redraw(Scene.DISPLAY_GROUP_ALL);
						//counter++;
						//if(counter > 50) clearInterval(updateTimer); 
					},
					300
				);			
			});
		})();		

	</script> 	  
</head>	

<body unselectable="on" class="unselectable">
	<div class="wizard_frame">
		<div class="wizard_header">
			
			<?php if($_simple_mode){ ?>
				<div class="wizard_header_title">Customize your design</div>			
			<?php } else { ?>
				<div class="wizard_header_title">Advanced design page</div>						
			<?php } ?>
			<div class="wizard_header_info">You are working on a <?php echo htmlspecialchars($product->longName) ?>.</div>			
		</div>
		<div class="wizard_body ui-widget-content">			
			<div class="controls_section">
				<div class="toolbar_pannel ui-widget-header">			
					<button style="margin-top:4px; width: 80px; margin-left:5px; float:left;" id="help">Help</button>					
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
				<table class="zoom_container_table">
				<tr>
					<td align="right" class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomout"></span></td>
					<td valign="middle"><div id="zoom"></div></td>
					<td align="left" class="zoom_container_table_icon"><span class="ui-icon ui-icon-zoomin"></span></td>
				</tr>
				</table>

				<div class="color_selector_container" id="ink_color_palette">				
					<div class="color_selector_label">Ink Colour</div>
					<div class="color_selector_box" id="black" style="background-color:black" onclick="_system.changeInkColour('black')"></div>
					<div class="color_selector_box" id="grey" style="background-color:grey"  onclick="_system.changeInkColour('grey')"></div>
					<div class="color_selector_box" id="blue" style="background-color:blue" onclick="_system.changeInkColour('blue')"></div>
					<div class="color_selector_box" id="silver" style="background-color:silver" onclick="_system.changeInkColour('silver')"></div>
					<div class="color_selector_box" id="red" style="background-color:red" onclick="_system.changeInkColour('red')"></div>
					<div class="color_selector_box" id="green" style="background-color:green" onclick="_system.changeInkColour('green')"></div>
					<div class="color_selector_box" id="violet" style="background-color:violet" onclick="_system.changeInkColour('violet')"></div>
					<div class="color_selector_box" id="yellow" style="background-color:yellow" onclick="_system.changeInkColour('yellow')"></div>
				</div>
			</div>
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<?php if($_session->getEnableTemplateBrowser()) { ?>
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
	
	<div id="dialog_saving_design" style="visibility:visible;width: 100%; height: 100%; display: block; position: absolute; left: 0; top: 0; z-index: 99999;">
		<div style="position:absolute; left: 0; top: 0; width:100%; height:100%; background-color: #000000; opacity: .50;"></div>
		<div style="width: 250px; margin: 0 auto;  position:relative; top: 30%; border-radius: 10px; border: 6px solid; border-color: #000; color: #000000; background: #FFFFFF;text-align: center;">
			<h2>Your design is being saved, please stand by.</h2>
			<div id="save_state_message">waiting for fonts</div>
			<div id="save_progress_bar"></div>			
		</div>
	</div>
	
	<div id="uiPanelSlider" class="class_box_shadow" style="position:absolute; height:100px;"></div>
</body>
</html>



