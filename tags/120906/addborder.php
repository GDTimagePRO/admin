<img src="images/close.jpg" id="closebtn" onClick="closePopup('#addborderpopup')" style="cursor:pointer">
    <header>
      <h1>Add A Border</h1>
    </header>
    <form>
    	 <fieldset style="width: 47%; float: left; ">

    		<legend>Pick your border</legend>
    		<p>
		 		<label for="borderType">Border Type:</label>
		 		<select id="borderType" name="borderType" class="border" onChange="SelectedBorder.show=true; borderPreviewDraw();">
				  <option selected="selected">Rectangular</option>
				  <option>Circular</option>
				</select>
		 	</p>
		    <p>
		    <label style="vertical-align: top" for="borderStyle" class="blocklabel">Border Style :
		    	<select id="borderStyle" name="borderStyle"size="1" class="border" onChange="SelectedBorder.show=true; borderPreviewDraw();">
				  <option value="0" selected="selected">Solid</option>
				  <option value="1">Rope</option>
				  <option value="2">Hash</option>
				  <option value="3">Stars</option>
				  <!--<option value="dotted">.........</option>
				  <option value="star">********</option>
				  <option value="hash">#######</option>
				  <option value="curly">~~~~~~~~</option>
				  <option value="x">XXXXXXXX</option>-->
				</select>
			</label>
			</p>
			<p>
			<label for="borderWidth" class="blocklabel">Border Width :
				<select id="borderWidth" name="borderWidth" class="border" onChange="SelectedBorder.show=true; borderPreviewDraw();">
				  <option selected="selected">2</option>
				  <option>3</option>
				  <option>4</option>
				  <option>5</option>
				  <option>6</option>
				  <option>7</option>
				</select> 
			</label>	
			</p>
			<p>
				<label>Rounded Border Radius</label>
				<input id="borderRadius" type="text" size="2" value="0" onChange="SelectedBorder.show=true; borderPreviewDraw();"/>
			</p>
			<!--<p>
			<label for="borderArt" class="blocklabel" >Border Art:
			    <select id="borderArt" name="borderArt" class="border">
			   		<option value="none" selected="selected"></option>
				  <option value="pumkin">pumkins</option>
				  <option value="flower">flower</option>
				  <option value="berries">berries</option>
				  <option value="icecream">ice cream cone</option>
				  <option value="pine tree">pine tree</option>
				  <option value="heart">heart</option>
				</select> 
			</label>
			</p>-->
			<!--<p>
			<label >Border Sides:</label>
			<input id="topSide" name="topSide" value="1" type="checkbox" checked="checked"> 
				<label for="topSide">Top</label>
			<input id="rightSide" name="rightSide" value="1" type="checkbox" checked="checked"> 
				<label for="rightSide">Right</label>
			<input id="bottomSide" name="bottomSide" value="1" type="checkbox" checked="checked">
				<label for="bottomSide">Bottom</label>			
			<input id="leftSide" name="leftSide" value="1" type="checkbox" checked="checked"> 
			 	<label for="leftSide">Left</label>	
		 </p>-->
		 	<p>
		 		<label>Frame entire product:</label>
		 		<input id="borderFrame" name="borderFrame" value="1" type="checkbox" onChange="SelectedBorder.show=true; borderPreviewDraw();"><br>
		 		If this option is checked the border will be along the outside of the product.
		 	</p>
		 	<p>
				<div class ="button addbutton" onClick="newBorder()">Add Border</div>
			</p>
		</fieldset>
	<fieldset style="float: right; width: 43%; padding: 0px 20px">
		<legend>Instructions: </legend>
		<p>
			After choosing your parameters, click on the preview where you would like your top left corner of your 
			border to be (you can move it later) and then drag your mouse to where you want the bottom right corner 
			of your border to be and release the mouse click.  We will draw the border.  You may redraw the border.  
			Click "Add Border" to return to your design.  You can move the border by selecting the border.  The 
			border selectors will show on each corners or four points on a circle.  Click on a selector to drag to 
			a new location and let go.
		</p>
	</fieldset>    
	<fieldset  class="fsPreview" >
		<legend>Preview</legend>
		<div class="divPreview" > 
			<?php
			$width = round($product['width']*0.0393700787 *90);
			$height = round($product['height']*0.0393700787 *90);
			?>
			<canvas id = "borderpreview" width="<?php echo $width;?>" height="<?php echo $height;?>" ></canvas>
		</div>
	</fieldset>

	</form>
