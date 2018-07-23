<?php
	header('Access-Control-Allow-Origin: *');
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;
	$s = $startup->session;
		
	$imageCategorySJArray = "";
	
	if($s->getCurrentItem()=="")
	{
		Header("Location: login.php");
	}
	else
	{
		$categories = $db->getImageCategories();
		$isFirst = true;
		foreach($categories as $category)
		{
			if($isFirst) { $isFirst = false; }
			else $imageCategorySJArray .= ",";
			$imageCategorySJArray .= "[".$category['id'].", \"".$category['category']."\"]" ;
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
	<script src="js/jquery-1.7.1.min.js"></script>
	<script src="js/jquery-ui-1.8.20.custom.min.js"></script>
	
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
	<script language="JavaScript">
	
		var _addGraphicsDialog = {
			
			imageCategories : [<?php echo $imageCategorySJArray ?>],			
			selectedImage : 0,
						
			showTab : function(id)
			{
				//console.log("Showing tab "+id);
				$('#selectgraphictab').css('display','none');
				$('#symboltab').css('display','none');
				$('#uploadgraphictab').css('display','none');
				if(id=="#selectgraphictab"){
					$('#uploadGraphicTabButton').removeClass('tab_button_selected');
					$('#selectSymbloTabButton').removeClass('tab_button_selected');
					$('#selectGraphicTabButton').removeClass('tab_button');
					$('#uploadGraphicTabButton').addClass('tab_button');
					$('#selectSymbolTabButton').addClass('tab_button');
					$('#selectGraphicTabButton').addClass('tab_button_selected');	
				}
				else if(id=="#uploadgraphictab"){
					$('#selectGraphicTabButton').removeClass('tab_button_selected');
					$('#selectSymbolTabButton').removeClass('tab_button_selected');
					$('#uploadGraphicTabButton').removeClass('tab_button');
					$('#selectGraphicTabButton').addClass('tab_button');
					$('#selectSymbolTabButton').addClass('tab_button');		
					$('#uploadGraphicTabButton').addClass('tab_button_selected');
				}
				$(id).css('display','block');
			},
			
			changeImageCategory : function(value)
			{
				var xmlhttp;
				if (window.XMLHttpRequest)
				{// code for IE7+, Firefox, Chrome, Opera, Safari
				  	xmlhttp=new XMLHttpRequest();
				}
				else
				{// code for IE6, IE5
				  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}
				var s = "<legend>Select the stock graphic to use for your "+$('#productName').html()+"</legend>";
				xmlhttp.open("GET","imagelist.php?id="+value+"&user="+$('#user_id').html(),false);
				xmlhttp.send();
				var response = xmlhttp.responseText;
				var object = jQuery.parseJSON(response);
				for(var i=0;i<object.length;i++){
					s += '<img  class="librarygraphic" onClick="_addGraphicsDialog.selectImage(this,'+object[i]+')" onDblClick="newGraphic()" src="image.php?id='+object[i]+'&color=black" />';
				}
				$('#selectimageblock').html(s);
			},
			
			selectImage : function (element,image_id)
			{
				$('#selectimageblock img').removeClass('imagelistselected');
				$(element).addClass('imagelistselected');
				_addGraphicsDialog.selectedImage = image_id;
			},

			uploadGraphicCheck : function(element)
			{
				var fileElement = element;
				var file = element.files[0];
				if(file)
				{
					$('#filename').html("File Name: "+file.name+"<br />File Size: "+(file.size/1024).toFixed(2)+"KB");
				}
			},
			
			uploadProgress : function(e,data){ },
			
			uploadGraphicComplete : function (e,data)
			{
				_addGraphicsDialog.changeImageCategory(1);
				_addGraphicsDialog.showTab('#selectgraphictab');
			},
						
			uploadGraphicSubmit : function ()
			{
				if (browserName!="Microsoft Internet Explorer")
				{// code for IE7+, Firefox, Chrome, Opera, Safari
					var xhr=new XMLHttpRequest();
					var fd = new FormData();
		   			fd.append("uploadGraphic", document.getElementById('uploadGraphicFile').files[0]);		  
		  			xhr.upload.addEventListener("progress", _addGraphicsDialog.uploadProgress, false);
		  			xhr.addEventListener("load", _addGraphicsDialog.uploadGraphicComplete, false);
		  			xhr.open("POST", "uploadgraphic.php");
		  			xhr.send(fd);
				}
				else
		  		{// code for IE6, IE5
				  	var form = document.getElementById('uploadGraphicForm');
				  	var iframe = document.createElement("iframe");
				    iframe.setAttribute("id", "upload_iframe");
				    iframe.setAttribute("name", "upload_iframe");
				    iframe.setAttribute("width", "0");
				    iframe.setAttribute("height", "0");
				    iframe.setAttribute("border", "0");
				    iframe.setAttribute("style", "width: 0; height: 0; border: none;");
		 
					// Add to document...
					form.parentNode.appendChild(iframe);
					window.frames['upload_iframe'].name = "upload_iframe";
		 
		    		iframeId = document.getElementById("upload_iframe");
		 
		    		// Add event...
		    		var eventHandler = function ()
		    		{		 
		            	if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
		            	else iframeId.removeEventListener("load", eventHandler, false);
		            	_addGraphicsDialog.uploadGraphicComplete(null,null);
		 
		            	//document.getElementById(div_id).innerHTML = content;
		 
		            	// Del the iframe...
		            	setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
		        	}		 
					if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
			    	if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);
			 
					// Set properties of form...
			    	form.setAttribute("target", "upload_iframe");
			    	form.setAttribute("action", "uploadgraphic.php");
			    	form.setAttribute("method", "post");
			    	form.setAttribute("enctype", "multipart/form-data");
			    	form.setAttribute("encoding", "multipart/form-data");
			    	
			    	// Submit the form...
			    	form.submit();
				}
			}
		};
		
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
			_system.onInit("canvas","uiPanel","objects", width, height);
			
			
			_addGraphicsDialog.changeImageCategory(_addGraphicsDialog.imageCategories[0][0]);
			
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
				$('#selectgraphictab').css('display','block')
			});
			
			$("#addGraphic").click(function() {
				closePopup('#addgraphicpopup');				
				ele = _system.addElement(new ImageElement());				
				ele.setPosition(-150, -150, 150, 150);
				ele.loadImage(
					"image.php?id=" +
					_addGraphicsDialog.selectedImage + 
					"&color=" + _system.scene.inkColor, 
					true
				);
			});
			
						
			var canvas = document.getElementById("canvas");			
			var context = canvas.getContext("2d");
			canvas.style.width = width + "px";
			canvas.style.height = height + "px";
			canvas.width = width;
			canvas.height = height;
			
			var x1 = -50;
			var y1 = -50;
			var x2 = +50;
			var y2 = +50;
			
			// var ele = null;
			// ele = _system.addElement(new TextElement());
			// ele.setPosition(x1, y1, x2, y2);

			ele = _system.addElement(new ImageElement());
			ele.setPosition(x1, y1, x2, y2);

			// ele = _system.addElement(new BorderElement());
			// ele.setPosition(x1, y1, x2, y2);

			// ele = _system.addElement(new LineElement());		
			// ele.setPosition(x1, y1, x2, y2);
		});
	</script> 	  
</head>	


<body>
	<div id="error_checking"></div>
	
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
					<div id="zoom">					
					</div>
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
							<div id="table" class="button" Title="Coming soon">Add Table</div>							
						</td>
						<td >
							<div id="template" class="button" onclick="showPopup('#addtemplatepopup')">View Templates</div>
						</td>
					</tr>
				</table>
				<div id="blank">You are currently working on a 
					<span id="productName">
					 	<?php
						$barcode = $db->getBarCode($s->getCurrentItem());
						$product_id = $db->getProductId($barcode);
						$product = $db->getProduct($product_id);
						echo $product['longname'];
						?>
					</span>
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
				<?php
				$width = round($product['width']*0.0393700787 *90);
				$height = round($product['height']*0.0393700787 *90);
				?>
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
					$fonts = $startup->settings['fonts'];
					foreach($fonts as $font){
						echo '<option>'.$font.'</option>';
					}
				?>	
				</select>	
	</div>

	<div id="fontsizeselect" class="hidden">
		<select id="fontsize" onchange="changeTextLineFontSize('#LINENUM',this)">
			<?php
			$min_size = $startup->settings['min font size'];
			$max_size = $startup->settings['max font size'];
			$step = $startup->settings['font size step'];
			for($i=$min_size;$i<=$max_size;$i+=$step){
				echo '<option>'.round($i,1).'</option>';
			}
			?>
		</select>
	</div>
	<div id="productwidth" class="hidden"><?php
		$width = round($product['width']*0.0393700787 *90);
		echo $width;
		?>
	</div>
	
	<div id="productheight" class="hidden"><?php
		$width = round($product['height']*0.0393700787 *90);
		echo $width;
		?>
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
								<?php
									$categories = $db->getImageCategories();
									foreach($categories as $category){
										echo sprintf('<option value="%s">%s</option>',$category['id'],$category['category']);
									}
								?>						
							</select>					
						</p>
					</div>					
					<p><fieldset id="selectimageblock"></fieldset></p>
		    	</div>
	        </section>    
	        
	        <!-- SELECT UPLOAD A GRAPHIC -->
	        <section id="uploadgraphictab" class="graphictab">
	        	<div>
	        		<form id="uploadGraphicForm" enctype="multipart/form-data" method="post" action="uploadgraphic.php">
		        		<label for="uploadGraphic">File:</label>
		        		<input type="file" name="uploadGraphic" id="uploadGraphicFile" onChange="_addGraphicsDialog.uploadGraphicCheck(this)"/>
		        		<div id="filename"></div>
		        		<div class="button" onClick="_addGraphicsDialog.uploadGraphicSubmit()">Upload</div>
	        		</form>
				</div>    	       	
	    	</section>
		</article>			
	</div>	
	<!----------------------------------------------------------------------------------------
		Add Graphic Dialog (End)
	----------------------------------------------------------------------------------------->
	
	<div id="addtablepopup" class="popup"> <?php include "addtable.php"; ?>	</div>	
	<div id="addborderpopup" class="popup"> <?php include "addborder.php"; ?> </div>	
	<div id="addlinepopup" class="popup"> <?php include "addline.php"; ?> </div>
	<div id="addcolourpopup" class="popup"> <?php include "addcolour.php"; ?> </div>	
	<div id="addmaterialpopup" class="popup"> <?php include "addmaterial.php"; ?> </div>	
	<div id="addtemplatepopup" class="popup"> <?php include "addtemplate.php"; ?> </div>	
	
	
	<div id="orderitem_id" class="hidden">
		<?php
		echo $s->getCurrentItem();
		?>
	</div>
	
	<div id="user_id" class="hidden">
		<?php
		echo $s->getUserId();
		?>
	</div>
	<div id="textColor" class="hidden">
		<?php echo $db->getColor($s->getCurrentItem());?>
	</div>
	
	<div id="productType" class="hidden">
		<?php echo $product['shape_id']; ?>
	</div>
	
	<div id="textlinesdb" class="hidden">
		<?php
		$product = array();
		$textlines = $db->getTextLines($s->getCurrentItem());
		$images = $db->getImages($s->getCurrentItem());
		$borders = $db->getBorders($s->getCurrentItem());
		$lines = $db->getLines($s->getCurrentItem());
		$tables =$db->getTables($s->getCurrentItem());
		$product['textlines'] = $textlines;
		$product['images'] = $images;
		$product['borders'] = $borders;
		$product['lines'] = $lines;
		$product['tables'] = $tables;
		$s = json_encode($product);
		echo $s;
		?>
	</div>	
</body>
</html>