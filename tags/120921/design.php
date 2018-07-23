<?php
	//TODO: Remove JS references to $('#productName') and the like 
	
	include_once "_common.php";	
	$_system->forceLogin();
	if($_design_id == "") $_system->loginRedirect();
	
	
	//TODO: The option list should probaly be build via JS
	$imageCategorySJArray = "";
	$imageCategoryOptions = "";	

	//TODO: The option list should probaly be build via JS
	$templateCategorySJArray = "";
	$templateCategoryOptions = "";	

	$initStateJSON = "";

	if($_design_id)
	{
		$initStateJSON = str_replace("'", "\\'", $_design_db->getDesignJSON($_design_id));

		$list = $_image_db->getImageCategoryList();
		$isFirst = true;
		foreach($list as $item)
		{
			if($isFirst) { $isFirst = false; }
			else $imageCategorySJArray .= ",";
			$imageCategorySJArray .= "[".$item['id'].", \"".$item['name']."\"]" ;
			$imageCategoryOptions .= sprintf('<option value="%s">%s</option>',$item['id'],$item['name']);
		}
		$list = $_design_db->getTemplateCategoryList();
		$isFirst = true;
		foreach($list as $item)
		{
			if($isFirst) { $isFirst = false; }
			else $templateCategorySJArray .= ",";
			$templateCategorySJArray .= "[".$item['id'].", \"".$item['name']."\"]" ;
			$templateCategoryOptions .= sprintf('<option value="%s">%s</option>',$item['id'],$item['name']);
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<!--meta name="viewport" content="width=device-width; initial-scale=1.0" /-->
  	
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	<meta name="author" content="Allan Dickinson, Cameron McGuinness" />
  	
  	<link type="text/css" href="css/themes/cupertino/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
	<link type="text/css" href="css/design.css" rel="StyleSheet"/>	
	
	<style>
		*.unselectable
		{
		   -moz-user-select: -moz-none;
		   -khtml-user-select: none;
		   -webkit-user-select: none;
		
		   /*
		     Introduced in IE 10.
		     See http://ie.microsoft.com/testdrive/HTML5/msUserSelect/
		   */
		   -ms-user-select: none;
		   user-select: none;
		}
		
		@font-face
		{
  			font-family: 'test_font';
  			src: url('fonts/test.ttf');
		}
		
		@font-face {
    		font-family: 'Arial';
    		src: url('fonts/arial.ttf');
		}
		
		.ui-button-text-only .ui-button-text
		{
			font-size: 0.8em;
			padding: 0.2em 0.3em;
			width: 120px;
		}
		
	</style>
	
	<script src="js/lib/jquery-1.8.0.min.js"></script>
	<script src="js/lib/jquery-ui-1.8.23.custom.min.js"></script>
	<script src="js/lib/jquery.json-2.3.min.js"></script>	
	
	<script src="js/lib/file_upload/vendor/jquery.ui.widget.js"></script>
	<script src="js/lib/file_upload/jquery.iframe-transport.js"></script>
	<script src="js/lib/file_upload/jquery.fileupload.js"></script>
	<script src="js/lib/file_upload/canvas-to-blob.js"></script>
	
	
	<script src="js/design/system.js"></script>
	
	<script src="js/design/maps.js"></script>
	<script src="js/design/patterns.js"></script>
	<script src="js/design/drawables.js"></script>
	<script src="js/design/widgets.js"></script>
	<script src="js/design/scene.js"></script>
	<script src="js/design/ui_panel.js"></script>
	
	
	<script src="js/design/elements/prototype_element.js"></script>
	
	<script src="js/design/elements/border_element.js"></script>
	<script src="js/design/elements/image_element.js"></script>
	<script src="js/design/elements/line_element.js"></script>
	<script src="js/design/elements/text_element.js"></script>
	
	<script src="js/design/add_graphics_dialog.js"></script>
	<script src="js/design/select_template_dialog.js"></script>
	
	
	<script language="JavaScript">
	
	
		_addGraphicsDialog.imageCategories = [<?php echo $imageCategorySJArray ?>];	
		_selectTemplateDialog.templateCategories = [<?php echo $templateCategorySJArray ?>];

		var _designImageId = <?php echo $_session->getActiveDesignImageId() ?>; 
		var _designImageCategoryId = <?php echo ImageDB::CATEGORY_DESIGN_IMAGE ?>; 
		
		
				
		function showPopup(popup)
		{
			$('#fade').css('display','block');	
			$(popup).css('display','block');
		}
		
		function closePopup(popup)
		{
			$(popup).css('display','none');
			$('#fade').css('display','none');
		}

		$(document).ready(function() {
			var width = 800;
			var height = 500;

			var canvas = $("#canvas")[0];			
			var context = canvas.getContext("2d");
			canvas.style.width = width + "px";
			canvas.style.height = height + "px";
			canvas.width = width;
			canvas.height = height;

			_system.onInit("canvas","uiPanel","objects", width, height);
			
			var _initStateJSON = '<?php echo $initStateJSON; ?>';		
			if(_initStateJSON != "")
			{
				try
				{
					_system.setState(jQuery.parseJSON(_initStateJSON));
					_system.clearStateHistory();
					_system.saveState(true);
				}
				catch(e){};	
			}

			_addGraphicsDialog.changeImageCategory(_addGraphicsDialog.imageCategories[0][0]);
			_selectTemplateDialog.changeTemplateCategory(_selectTemplateDialog.templateCategories[0][0]);

			
			$("#zoom").slider({
				max: 250,
				min: 10,
				value: 100,
				slide: function(event, ui) {
					_system.scene.scale = $(this).slider("value") / 100.0;
					_system.scene.redraw(); 
				}
			});

			$("#undo").button().click(function() {
				_system.undo();
			});

			$("#redo").button().click(function() {
				_system.redo(); 
			});
			
			$("#inkPickerInput").change(function() {
				_system.scene.inkColor = $(this).val();
				_system.scene.redraw();
			});
			

			$("#addTextCircle").button().click(function() {
				var ele = _system.addElement(new TextElement());
				ele.setType(TextElement.TYPE_CIRCLE);
				ele.setPosition(-100, -100, 100, 100);
				_system.setSelected(ele); 
				_system.scene.redraw();
			});

			$("#addTextLine").button().click(function() {
				var ele = _system.addElement(new TextElement());
				var offsetX = _system.getPageWidth() / 2;				
				ele.setPosition(-offsetX, -20, offsetX, 20);
				ele.setType(TextElement.TYPE_LINE);
				_system.setSelected(ele); 
				_system.scene.redraw();
			});
			
			
			$("#addBorder").button().click(function() {
				var ele = _system.addElement(new BorderElement());

				var offsetX = _system.getPageWidth() / 2;
				var offsetY = _system.getPageHeight() / 2;
				ele.setPosition(
					-offsetX, -offsetY,
					offsetX, offsetY
				);

				if(_system.getPageType() == System.PAGE_TYPE_CIRCLE)
				{
					ele.setType(BorderElement.TYPE_BOX);	
				}
				else
				{
					ele.setType(BorderElement.TYPE_CIRCLE);	
				}
				
				_system.setSelected(ele); 
				_system.scene.redraw();
			});
			
			$("#addLine").button().click(function() {
				var ele = _system.addElement(new LineElement());
				ele.setPosition(-100, -100, 100, 100);
				_system.setSelected(ele); 
				_system.scene.redraw();
			});
			
			$("#showAddGraphicDialog").button().click(function() {
				showPopup('#addgraphicpopup');
				$('#selectgraphictab').css('display','block');
			});
			
			$("#showSelectTemplateDialog").button().click(function() {
				showPopup('#selecttemplatepopup');
			});
			
			$("#addGraphic").button().click(function() {
				closePopup('#addgraphicpopup');				
				ele = _system.addElement(new ImageElement());				
				ele.setPosition(-100, -100, 100, 100);
				ele.loadImage(
					"design_part/get_image.php?id=" +
					_addGraphicsDialog.selectedImage + 
					"&color=" + _system.scene.inkColor, 
					false
				);
			});
			

			$("#help").button();	
			$("#order").button();

			$("#next").button().click( function() {

				//send the current state to the server
				_system.setDesignJSON(_system.getStateJSON());
				
				var width = _system.getPageWidth();
				var height = _system.getPageHeight();				
				var backBuffer = $("<canvas></canvas>")[0];				
				backBuffer.style.width = width + "px";
				backBuffer.style.height = height + "px";
				backBuffer.width = width;
				backBuffer.height = height;
				
				var scene = new Scene(backBuffer, width, height);
				scene.backgroundColor = "#FFFFFF";				

				scene.setLayer(Scene.LAYER_FOREGROUND,_system.scene.getLayer(Scene.LAYER_FOREGROUND));
				scene.scale = 1.0;
				scene.redraw(Scene.DISPLAY_GROUP_CONTENT);

				$("#saveProgressBar").progressbar( "value" , 0);
				showPopup("#savingProgressDialog");
				
 				_system.saveCanvasAsImage(
 					backBuffer, 
 					_designImageId, 
 					_designImageCategoryId,
					function() 
					{
 						closePopup('#savingProgressDialog')
 						window.location = "confirm_design.php";
					}, 
					function(value)
					{
						$("#saveProgressBar").progressbar( "value" , Math.floor(value * 100.0));
					}
				);
			});
			
			$("#addGraphic").button();
			$("#uploadGraphic").button();
			$("#selectTemplateButton").button();
			$("#startWithoutTemplate").button();
			$("#saveProgressBar").progressbar();
			
			$("#centerSelected").button().click( function() {
				var selected = _system.getSelected();
				
				if(selected)
				{
					selected.center();
					_system.saveState();
					_system.scene.redraw();
				}
			});
			
			$("#wrapSelected").button().click( function() {
				var selected = _system.getSelected();

				if(selected)
				{
					var offsetX = _system.getPageWidth() / 2;
					var offsetY = _system.getPageHeight() / 2;
					selected.setPosition(
						-offsetX, -offsetY,
						offsetX, offsetY
					);

					selected.center();
					_system.saveState();
					_system.scene.redraw();
				}
			});
});
	</script> 	  
</head>	

<body unselectable="on" class="unselectable">

	<div id="error_checking"></div>
	<div id="hidden" class="hidden"></div>
	
	<table width="100%" border="0">
		<tr>
			<td width="300px">
				<button id="help" Title="Coming soon">HELP</button>				
			</td>
			<td width="60%">
				<div id="title">Create Your Design</div>
			</td>
			<td align="right">
				<button id="order" Title="Coming soon">ORDER INFO</button>
			</td>
		</tr>
		<tr>
			<td>
				<div id="tools">
					<span >Zoom:</span>				
					<div id="zoom"></div>
					<label style="float: left">Smaller</label>
					<label style="float: right">Larger</label>
					<!-- <input type="text" id="currentzoom" value="100%" size="4" onChange="zoom(this.value)"/>-->
				</div>
			</td>
			<td>
				<table width="40%" border="0" align="center">
					<tr>
						<td width="25%">
							<button id="addTextLine">Text Line</button>
						</td>
						<td width="25%">
							<button id="addBorder">Add Border</button>
						</td>
						<td width="25%">
							<button id="showAddGraphicDialog">Add Graphic</button>							
						</td>
					</tr>
					<tr>
						<td >
							<button id="addTextCircle">Text Circle</button>
						</td>
						<td style="display: block;"> 
							<button id="addLine">Add Line</button>
						</td>
						<td >
							<button id="showSelectTemplateDialog">View Templates</button>
						</td>
					</tr>
					<tr>
						<td >
						</td>
						<td style="display: block;"> 
							<button id="centerSelected">Center</button>
						</td>
						<td >
							<button id="wrapSelected">Wrap</button>
						</td>
					</tr>
					
					
				</table>
				<div id="blank">You are currently working on a 
					<span id="productName">
				 	<?php
					// $barcode = $db->getBarCode($s->getCurrentItem());
					// $product_id = $db->getProductId($barcode);
					// $product = $db->getProduct($product_id);
					// echo $product['longname'];
					?>
					</span>
				</div>
			</td>
			<td></td>			
		</tr>
		<tr>
			<td class="objectbox">	
				<button id="undo" >Undo</button>
				<button id="redo">Redo</button>
				<div id="colour" class="objectleft" >		    	
		    		<label>Ink colour for your stamp:</label>
			    	<select id="inkPickerInput">
					  <option value="black" selected="selected" >Black Ink</option>
					  <option value="blue">Blue Ink</option>
					  <option value="red">Red Ink</option>
					  <option value="green">Green Ink</option>
					  <option value="purple">Purple Ink</option>					  
					</select> 					
				</div>					
				<div id="objects">					
				</div>
				<div id="uiPanel"></div>
				
				<div id="grid" class="objectleft">
					<label>Grid Lines</label>	
					<select id="gridLines" name="Grid lines" value="gridlines" type="checkbox" onchange="toggleGrid()">
						<option>None</option>
						<option>Small</option>
						<option>Large</option>
					</select> 					
				</div>	
				<div id="ruler" class="objectleft">
					<label>Show Ruler</label>	
					<input name="Ruler" value="showrler" type="checkbox" onclick="toggleRuler(this)"> 					
				</div>	
				<div> 
			</td>
			<td align="center" valign="top">
				<canvas id="canvas" width="400" height="400"></canvas>				
			</td>
			<td class="featurebox">	
				<table id="advancedfeatures">					
					<th colspan="4" >
						<h3>Advanced Features</h3>
						<p>Addition parameters for selected item</p>
					</th>														
				</table>
			</td>
		</tr>
		<tr class="textlinebox">
			<td colspan="3">
				<div id="textlines">
				<table width="100%" border="0" id="textlineTable">
					<thead>
						<th>#</th>
						<th>Type</th>
						<th>Text</th>
						<th>Font</th>
						<th>Size</th>
						<th>Bold</th>
						<th>Italic</th>
						<th>Align</th>
						<th>Select</th>
						<th>Delete</th>
					</thead>
					<tbody>				
					</tbody>
				</table>
				</div>	
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right">
				<button id="next">NEXT</button>				
			</td>
		</tr>	
	</table>

	<div id="fontselect" class="hidden">
		<select id="font" onchange="changeTextLineFont('#LINENUM',this)">
				<?php
					$fonts = $_settings['fonts'];
					foreach($fonts as $font){
						echo '<option>'.$font.'</option>';
					}
				?>	
				</select>	
	</div>

	<div id="fontsizeselect" class="hidden">
		<select id="fontsize" onchange="changeTextLineFontSize('#LINENUM',this)">
			<?php
				$min_size = $_settings['min font size'];
				$max_size = $_settings['max font size'];
				$step = $_settings['font size step'];
				for($i=$min_size;$i<=$max_size;$i+=$step)
				{
					echo '<option>'.round($i,1).'</option>';
				}
			?>
		</select>
	</div>
	<div id="fade"></div>
	
	
	<!----------------------------------------------------------------------------------------
		Saving Process Dialog (Begin)
	----------------------------------------------------------------------------------------->

	<div id="savingProgressDialog" style="width: 100%; height: 100%; display: none; position: absolute; left: 0; top: 0; z-index: 99999;">
		<div style="width: 250px; margin: 0 auto; position:relative; top: 30%; border-radius: 10px; border: 6px solid; border-color: #000; color: #000000; background: #FFFFFF;text-align: center;">
			<h1>Saving Design</h1>
			<div id="saveProgressBar"></div>
		</div>
	</div>
	
			
		
	<!----------------------------------------------------------------------------------------
		Saving Process Dialog (End)
	----------------------------------------------------------------------------------------->
	
	
	<!----------------------------------------------------------------------------------------
		Add Graphic Dialog (Begin)
	----------------------------------------------------------------------------------------->	
	<div id="addgraphicpopup" class="popup">	
		<img src="images/close.jpg" id="closebtn" onClick="closePopup('#addgraphicpopup')" style="cursor:pointer">
    	<h1>Add a Graphic</h1>
	    
	   	<article class="tabs">
	   		<section class="tabs_bar">
	   			<div style="height: 5px;">&nbsp;</div>
			    <div class="tabs_bar">
			    	<span class="tab_button"  id="uploadGraphicTabButton" onClick="_addGraphicsDialog.showTab('#uploadgraphictab')"> Upload a Graphic </span>
			    	<span class="tab_button" id="selectGraphicTabButton" onClick="_addGraphicsDialog.showTab('#selectgraphictab')"> Select a Graphic </span>
			    	<span class="tab_button" id="selectSymbolTabButton" Title="Coming soon"> Select a Symbol </span>		    	
			    </div>
			    <div style="height: 5px;">&nbsp;</div>
			</section>
		
	    	<!-- SELECT A SYMBOL -->
			<section id="symboltab" class="graphictab">
		        <div>
		        	<p>
			 			<label for="tableRow" >Font:
			       			<select id="symbolFont" name="tableRow" class="table">
			       				<option value="Webdings">Webdings</option>
							  	<option value="Wingdings">Wingdings</option>				  	
								<option value="Wingdings2">Wingdngs 2</option>
								<option value="Wingdings3">Wingdngs 3</option>
							 	<option value="MSReference1">MS Reference 1</option>
							 	<option value="MSReference2">MS Reference 2</option>
							 	<option value="BookshelfSymbol7">Bookshelf Symbol 7 </option>
							</select> 
						</label>
					</p>
					<br />
					<p>
						<fieldset>
						<legend>Select the symbol to use for your 'Product Name'</legend>
				   			<table class="symbolTable" >
								<tr><td></td></tr>	   				
				   			</table>
			    		</fieldset>
		    		</p>
		    		<button style="float: right; padding: 10px; margin: 40px; " onClick="closePopup('#addgraphicpopup')">SAVE And CLOSE</button>
		        </div>
	        </section>
	        
	        <!-- SELECT A LIBRARY GRAPHIC -->
	        <section id="selectgraphictab" class="graphictab">	
	        	<div>
	        		<button id="addGraphic" style="float: right; padding: 5px;">Add Graphic</button>
	        		<div>
			           	<p>
				 			<label for="tableRow" >Category:</label>
							<select name="tableRow" onChange="_addGraphicsDialog.changeImageCategory(this.value)">
								<?php echo $imageCategoryOptions ?>						
							</select>					
						</p>
					</div>					
					<p><fieldset id="selectimageblock"></fieldset></p>
		    	</div>
	        </section>    
	        
	        <!-- SELECT UPLOAD A GRAPHIC -->
	        <section id="uploadgraphictab" class="graphictab">
	        	<div>
	        		<label for="uploadGraphic">File:</label>
	        		<input type="file" name="file" id="uploadFile" onChange="_addGraphicsDialog.uploadGraphicCheck(this)"/>
	        		<div id="filename"></div>
	        		<button id="uploadGraphic" onClick="_addGraphicsDialog.uploadGraphicSubmit()">Upload</button>
	        		<div id="uploadFileResult"></div>
				</div>
	    	</section>
		</article>			
	</div>	
	<!----------------------------------------------------------------------------------------
		Add Graphic Dialog (End)
	----------------------------------------------------------------------------------------->
	
	<!----------------------------------------------------------------------------------------
		Add Template (Begin)
	----------------------------------------------------------------------------------------->
	<div id="selecttemplatepopup" class="popup">
		<img src="images/close.jpg" id="closebtn" onClick="closePopup('#selecttemplatepopup')" style="cursor:pointer">
	    <header>
	      <h1>Pick a Template</h1>
	    </header>
		<div>
			<div style="float: left; width: 30% ">
				<label for="tableRow" >Category:
					<select onChange="_selectTemplateDialog.changeTemplateCategory(this.value)" >
						<?php echo $templateCategoryOptions ?> 						
					</select>
				</label>			
			</div>
			<button id="selectTemplateButton" style="float: right; padding: 5px; margin: 0px 20px; " onClick="_selectTemplateDialog.changeTemplate()">Select Template</button>
	    	<button id="startWithoutTemplate"  style="float: right; padding: 5px; margin: 0px 140px 20px 20px; width: 200px;" 
	    		Title="Click to skip the template and start from a blank canvas" onClick="closePopup('#selecttemplatepopup')">
	    		Start without Template    		
			</button> 
			
			<div style="float: left; clear: both;">
				<legend>Select the template to use for your  
					TODO: add this !!
				</legend> 
			</div>
			<div style="float: Left; clear: both; width: 100%; ">	
				<fieldset id="selecttemplateblock" style="background-color: silver">asdasdasd</fieldset> 
	    	</div>
		</div>
	</div>
	<!----------------------------------------------------------------------------------------
		Add Template (End)
	----------------------------------------------------------------------------------------->	
</body>
</html>