	<img src="images/close.jpg" id="closebtn" onClick="closePopup('#addcolourpopup')"/>
    <header>
      <h1>Add Colour</h1>
    </header>
    <form>
    	<fieldset style="text-align: center">
    	<legend>Select an ink colour for your stamp:</legend>
	    	<select id="color" onChange="changeColorPreview()">
			  <option value="black">Black Ink</option>
			  <option value="blue">Blue Ink</option>
			  <option value="red">Red Ink</option>
			  <option value="green">Green Ink</option>
			  <option value="purple">Purple Ink</option>					  
			</select> 
		</fieldset>
		<fieldset  class="fsPreview"  ">
			<legend>Preview</legend>
			<div class="divPreview" > 
				<?php
				$width = round($product['width']*0.0393700787 *90);
				$height = round($product['height']*0.0393700787 *90);
				?>
				<canvas id = "colourpreview" width="<?php echo $width;?>" height="<?php echo $height;?>" ></canvas>
			</div>
		</fieldset>
		<p>
			<div class ="button addbutton" onClick="changeColor()">Change Color</div>
		</p>
	</form>
	
 
