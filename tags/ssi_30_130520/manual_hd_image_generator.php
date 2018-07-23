<?php
	include_once "_common.php";
	include_once "./backend/order_logic.php";

	$_system->forceLogin();
	
	function run_query($sql)
	{
		global $_system;
		$result = mysql_query($sql,$_system->db->connection);
		if(!$result)
		{
			echo "SQL Error : " . html;
			exit;
		}
		return $result; 
	}
	
	if(isset($_POST['confirm']))
	{
		$designId = $_POST['designId'];
		$orderItemId = $_POST['orderItemId'];
		
		run_query(sprintf(
			'UPDATE designs SET state = %d WHERE id = %d',
			Design::STATE_READY,
			$designId
		));
		
		$row = mysql_fetch_assoc(run_query(sprintf(		
			'SELECT COUNT(*) FROM designs WHERE state < %d AND order_item_id = %d', 
			Design::STATE_READY,
			$orderItemId
		)));
		
		if(end($row) == 0)
		{
			run_query(sprintf(
				"UPDATE order_items 
				SET order_items.processing_stages_id = %d 
				WHERE id = %d",				
				ProcessingStage::STAGE_READY,
				$orderItemId
			));
		}
	}

	$remaining = end(mysql_fetch_assoc(run_query(sprintf(
			'SELECT '.
				'COUNT(designs.id) '.
			'FROM designs, order_items '.
			'WHERE '.
				'designs.order_item_id = order_items.id AND '.
				'designs.state = %d AND '.
				'order_items.processing_stages_id = %d '.
			'ORDER BY order_item_id',
			Design::STATE_PENDING_SCL_RENDERING,
			ProcessingStage::STAGE_PENDING_RENDERING
	))));
	
	$row = mysql_fetch_assoc(run_query(sprintf(
			'SELECT '.
				"designs.id as 'id'".
			'FROM designs, order_items '.
			'WHERE '.
				'designs.order_item_id = order_items.id AND '.
				'designs.state = %d AND '.
				'order_items.processing_stages_id = %d '.
			'ORDER BY order_item_id',
			Design::STATE_PENDING_SCL_RENDERING,
			ProcessingStage::STAGE_PENDING_RENDERING
	)));
	
	if(!$row)
	{
		echo "Its all done ^_^";
		exit;
	}
		
	$design = $_design_db->getDesignById($row['id']);
	
	$initStateJSON = str_replace("\\", "\\\\", $design->designJSON);
	$initStateJSON = str_replace("'", "\\'", $initStateJSON);
	
	$designConfig = $design->getConfigItem();
	$product = $_order_db->getProductById($designConfig->productId);
	
	if($product->frameWidth < $product->width) $product->frameWidth = $product->width;
	if($product->frameHeight < $product->height) $product->frameHeight = $product->height;
	
	$outputImageScale_trace = 1200/25.4;
	
	$outputImageWidth_trace = round($product->width * $outputImageScale_trace);
	$outputImageHeight_trace = round($product->height * $outputImageScale_trace);	
	$outputImageFrameWidth_trace = round($product->frameWidth * $outputImageScale_trace);
	$outputImageFrameHeight_trace = round($product->frameHeight * $outputImageScale_trace);
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/<?php echo $_session->getActiveThemeName(); ?>/jquery-ui.custom.min.css?version=<?php echo $_version ?>" rel="stylesheet" />
	<link type="text/css" href="css/design_wizard.css?version=<?php echo $_version ?>" rel="StyleSheet"/>	
	
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
	<script src="js/design/context_logger.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/scene.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/script_container.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/ui_panel_basic.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/prototype_element.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/border_element.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/image_element.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/line_element.js?version=<?php echo $_version ?>"></script>
	<script src="js/design/elements/text_element.js?version=<?php echo $_version ?>"></script>
	
	<script src="js/browser_check.js?version=<?php echo $_version ?>"></script>	
	
	<script type="text/javascript">

	
		forceModernBrowser();
	
		System.ENABLE_SAVE_STATE = false;
		System.ASPECT_RATIO =  Math.round(<?php echo $product->height / $product->width; ?> * 10000);
		Scene.RENDER_SERVICE_URL = "<?php echo $_settings[Startup::SETTING_RENDER_SERVICE]; ?>";
		
		var _hdImageId = "<?php echo $design->getHighDefImageId(); ?>"; 
		
		var _outputImageWidth_trace = <?php echo $outputImageWidth_trace; ?>;
		var _outputImageHeight_trace = <?php echo $outputImageHeight_trace; ?>;
		var _outputImageFrameWidth_trace = <?php echo $outputImageFrameWidth_trace; ?>;
		var _outputImageFrameHeight_trace = <?php echo $outputImageFrameHeight_trace; ?>;
		
		$(document).ready(function() {

			var canvas = $("#canvas");
			var width = canvas.width();
			var height = canvas.height();

			canvas = canvas[0];
			canvas.width = width;
			canvas.height = height;
	
			_system.onInit("canvas",null,null, width, height);

			var _initStateJSON = '<?php echo $initStateJSON; ?>';
			_system.setState(jQuery.parseJSON(_initStateJSON));
			_system.scene.redraw(true);

			$("#status").html("Initializing ...");
			
			setTimeout(function()
			{
				$("#status").html("Loading images ...");
			

				$("#status").html("Rendering ...");

				hdImageUploaded = false;
				var hdScaleWidth = _outputImageWidth_trace / _system.getPageWidth(); 
				var hdScaleHeight = _outputImageHeight_trace / _system.getPageHeight(); 
				var hdScale = Math.min(hdScaleWidth,hdScaleHeight); 

				var hdImageFileName = _hdImageId.substring(_hdImageId.indexOf('/') + 1, _hdImageId.length - 4);

				_system.invokeWhenReady(function() {
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
						"#ffffff",
						true
						
					);					
	
					$.support.cors = true;
					$.get(hdImageQuery).done(function() {
						$("#form").submit();
						//window.location.replace("manual_hd_image_generator.php");
						//$("#status").html(
						//	'<a target="_blank" href="design_part/get_image.php?nocache=true&id=' + _hdImageId + '">' + 
						//	'<img src="design_part/get_image.php?nocache=true&id=thumbs.' + _hdImageId + '"></a><br>' + 
						//	'<button id="confirmButton" name="confirm" value="confirm">Confirm</button><BR>' +
						//	'<button id="reloadButton">Reload</button>'
						//);
						//$("#confirmButton").button();
						//$("#reloadButton").button().click(function(){
						//	window.location.replace("manual_hd_image_generator.php");
						//});						
					});
				});				
			}, 1);			
		});
	</script> 	  
</head>	

<body unselectable="on" class="unselectable">
	<canvas id="canvas" class="preview_canvas" style="display:none"></canvas>

	<div class="wizard_frame">
			<div class="wizard_header">	
				<div class="wizard_header_title">Generate HD Image</div>			
			<div class="wizard_header_info">Design ID = <?php echo $design->id; ?> ( 1 / <?php echo $remaining; ?>)</div>					
		</div>
		<div class="wizard_body ui-widget-content">			
			<form method="post" id="form">
				<input type="hidden" name="designId" value="<?php echo $design->id; ?>">
				<input type="hidden" name="orderItemId" value="<?php echo $design->orderItemId; ?>">				
				<input type="hidden" name="confirm" value="confirm">				
				<div id="status" style="text-align:center;padding-top:80px; font-size:24px"></div>
			</form>
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
		</div>
	</div>
	

	<div id="fade"></div>
</body>
</html>



