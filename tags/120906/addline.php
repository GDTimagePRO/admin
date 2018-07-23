	<img src="images/close.jpg" id="closebtn" onClick="closePopup('#addlinepopup')" style="cursor:pointer">
  <div>
    <header>
      <h1>Add a Line</h1>
    </header>
    <fieldset style="width: 30%; float: left; ">
		<legend>Add a Line</legend>
	    <p>
		    <label style="vertical-align: top" for="lineStyle" class="blocklabel">Line Style :
		    	<select id="lineStyle" name="lineStyle" class="line">
				  <option value="1" selected="selected">Solid</option>
				</select> 
			</label>
		</p>
		<p>
			<label for="lineWidth" class="blocklabel">Line Width :
				<select id="lineWidth" name="lineWidth" class="line">
				 <option selected="selected">2</option>
				  <option>3</option>
				  <option>4</option>
				  <option>5</option>
				  <option>6</option>
				  <option>7</option>
				</select> 
			</label>	
		</p>
		<p >
			<div class ="button addbutton"  onClick="newLine()">Add Line</div>
		</p>			
	</fieldset>  
	<fieldset style="float: right; width: 60%; padding: 0px 20px">
		<legend>Instructions: </legend>
		<p>
			After choosing line style and line width, click on your impression where you would like your 
			line to start (you can move it later) and then drag your mouse to where you want the line to
			end and release mouse.  We will draw the line.  You may redraw the line.  Click "Add Line" to return to your 
			design.  You can move the line by selecting the line.  The line selectors will show on each 
			end and center.  Click on a selector at one end or center to drag to a new location and let go.
		</p>
	</fieldset>  
	<fieldset  class="fsPreview"  ">
		<legend>Preview</legend>
		<div class="divPreview" > 
			<?php
			$width = round($product['width']*0.0393700787 *90);
			$height = round($product['height']*0.0393700787 *90);
			?>
			<canvas id = "linepreview" width="<?php echo $width;?>" height="<?php echo $height;?>" ></canvas>
		</div>
	</fieldset>  


  </div>


