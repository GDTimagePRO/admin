<?php
	//TODO: Remove JS references to $('#productName') and the like 
	
	include_once "_common.php";	
	
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
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	<meta name="author" content="Allan Dickinson, Cameron McGuinness" />
	<!--meta name="viewport" content="width=device-width; initial-scale=1.0" /-->
  	
  	<link rel=StyleSheet href="css/ui-lightness/jquery-ui-1.8.18.custom.css" type="text/css" />
	<link rel=StyleSheet href="css/design.css" type="text/css" />
		
	<script src="js/lib/jquery-1.7.1.min.js"></script>
	<script src="js/lib/jquery.json-2.3.min.js"></script>	
	<script src="js/lib/jquery-ui-1.8.20.custom.min.js"></script>

	<script src="js/design/system.js"></script>
	
	<script src="js/design/maps.js"></script>
	<script src="js/design/patterns.js"></script>
	<script src="js/design/drawables.js"></script>
	<script src="js/design/widgets.js"></script>
	<script src="js/design/scene.js"></script>
	<script src="js/design/ui_panel.js"></script>
	
	<script src="js/design/elements/border_element.js"></script>
	<script src="js/design/elements/image_element.js"></script>
	<script src="js/design/elements/line_element.js"></script>
	<script src="js/design/elements/text_element.js"></script>
	
	<script src="js/design/add_graphics_dialog.js"></script>
	
	
	<script language="JavaScript">
		_selectTemplateDialog = {
			selectedTemplate : -1,
			changeTemplate : function()
			{
				if(_selectTemplateDialog.selectedTemplate != -1)
				{
					$.get(
						'design_part/get_template_json.php?template_id=' + _selectTemplateDialog.selectedTemplate,
						function(data)
						{
							_system.saveState();
							_system.setState(jQuery.parseJSON(data));
							_system.scene.redraw();
							closePopup('#selecttemplatepopup');
						}
					);
				}				
			},	
				
			selectTemplate : function selectTemplate(element,template_id)
			{
				$('#selecttemplateblock img').removeClass('imagelistselected');
				$(element).addClass('imagelistselected');
				_selectTemplateDialog.selectedTemplate = template_id;
			},
			
			changeTemplateCategory : function(categoryId)
			{
				$.get('design_part/get_template_list.php?category_id=' + categoryId, function(data) {
					var s = "<legend></legend>";
					var previewSrc = "design_part/get_image.php?id=";					
					var object = jQuery.parseJSON(data);
					for(var i=0;i<object.length;i++)
					{
						s += '<img onClick="_selectTemplateDialog.selectTemplate(this,' + object[i].id +')" src="' + previewSrc + object[i].preview_image_id + '"/>&nbsp;&nbsp;';
					}
					$('#selecttemplateblock').html(s);
				});
			}
		};
	
	
		_addGraphicsDialog.imageCategories = [<?php echo $imageCategorySJArray ?>];	
		_selectTemplateDialog.templateCategories = [<?php echo $templateCategorySJArray ?>];
		
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

			$("#undo").click(function() {
				_system.undo();
			});

			$("#redo").click(function() {
				_system.redo(); 
			});
			
			$("#inkPickerInput").change(function() {
				_system.scene.inkColor = $(this).val();
				_system.scene.redraw();
			});
			
			$("#addText").click(function() {
				var ele = _system.addElement(new TextElement());
				ele.setPosition(-150, -150, 150, 150);
				_system.setSelected(ele); 
				_system.scene.redraw();
			});
			
			$("#addBorder").click(function() {
				var ele = _system.addElement(new BorderElement());
				ele.setPosition(-150, -150, 150, 150);
				_system.setSelected(ele); 
				_system.scene.redraw();
			});
			
			$("#addLine").click(function() {
				var ele = _system.addElement(new LineElement());
				ele.setPosition(-150, -150, 150, 150);
				_system.setSelected(ele); 
				_system.scene.redraw();
			});
			
			$("#showAddGraphicDialog").click(function() {
				showPopup('#addgraphicpopup');
				$('#selectgraphictab').css('display','block');
			});
			
			$("#showSelectTemplateDialog").click(function() {
				showPopup('#selecttemplatepopup');
			});
			
			$("#addGraphic").click(function() {
				closePopup('#addgraphicpopup');				
				ele = _system.addElement(new ImageElement());				
				ele.setPosition(-150, -150, 150, 150);
				ele.loadImage(
					"design_part/get_image.php?id=" +
					_addGraphicsDialog.selectedImage + 
					"&color=" + _system.scene.inkColor, 
					true
				);
			});
			
			$("#clickMe").click(function() {
				$("#hidden").html("<canvas id='backBuffer'></canvas>");
				
				var width = _system.getPageWidth();
				var height = _system.getPageHeight();				
				var backBuffer = $("#backBuffer")[0];			
				backBuffer.style.width = width + "px";
				backBuffer.style.height = height + "px";
				backBuffer.width = width;
				backBuffer.height = height;
				var scene = new Scene("backBuffer", width, height);
				scene.setLayer(Scene.LAYER_FOREGROUND,_system.scene.getLayer(Scene.LAYER_FOREGROUND));
				scene.scale = 1.0;
				scene.redraw(Scene.DISPLAY_GROUP_CONTENT);
 				window.open(backBuffer.toDataURL("image/png"), 'Full Image');
 				$("#hidden").html("");
			});
			
		});
	</script> 	  
</head>	


<body>
	<div id="error_checking"></div>
	<div id="hidden" class="hidden"></div>
	
	<table width="100%" border="0">
		<tr>
			<td width="300px">
				<div id="help" class="button" Title="Coming soon">HELP</div>				
			</td>
			<td width="60%">
				<div id="title">Create Your Design</div>
			</td>
			<td align="right">
				<div id="order" class="button" Title="Coming soon">ORDER INFO</div>
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
							<div id="addText" class="button">Add Text</div>
						</td>
						<td width="25%">
							<div id="addBorder" class="button">Add Border</div>
						</td>
						<td width="25%">
							<div id="showAddGraphicDialog" class="button">Add Graphic</div>							
						</td>
					</tr>
					<tr>
						<td >
							<div id="addLine" class="button">Add Line</div>
						</td>
						<td style="display: block;"> 
							<div id="clickMe" class="button" Title="Coming soon">!!Click Me!!</div>							
						</td>
						<td >
							<div id="showSelectTemplateDialog" class="button">View Templates</div>
						</td>
					</tr>
				</table>
				<div id="blank">You are currently working on a 
					<span id="productName"> TODO: add this !!</span>
				</div>
			</td>
			<td></td>			
		</tr>
		<tr>
			<td class="objectbox">	
				<div id="undo" class="button " >
					<label title="Coming Soon">Undo</label>
				</div>
				<div id="redo" class="button " >
					<label title="Coming Soon">Redo</label>
				</div>
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
			<td align="center">
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
				<div id="next" class="button">
					<a href="confirm.php">NEXT</a>
				</div>				
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
		Add Graphic Dialog (Begin)
	----------------------------------------------------------------------------------------->
	<script language="JavaScript">
	</script>
	
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
	        		<div id="addGraphic" class ="button" style="float: right; padding: 5px;  ">Add Graphic</div>
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
	        		<form id="uploadGraphicForm" enctype="multipart/form-data" method="post">
		        		<label for="uploadGraphic">File:</label>
		        		<input type="file" name="file" id="uploadFile" onChange="_addGraphicsDialog.uploadGraphicCheck(this)"/>
		        		<div id="filename"></div>
		        		<div class="button" onClick="_addGraphicsDialog.uploadGraphicSubmit()">Upload</div>
		        		<div id="uploadFileResult"></div>
	        		</form>
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
			<div class ="button" style="float: right; padding: 5px; margin: 0px 20px; " onClick="_selectTemplateDialog.changeTemplate()">Select Template</div>
	    	<div class ="button" style="float: right; padding: 5px; margin: 0px 140px 20px 20px; width: 200px;" 
	    		Title="Click to skip the template and start from a blank canvas" onClick="closePopup('#selecttemplatepopup')">
	    		Start without Template    		
			</div> 
			
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