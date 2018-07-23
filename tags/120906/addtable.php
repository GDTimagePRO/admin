<img src="images/close.jpg" id="closebtn" onClick="closePopup('#addtablepopup')" style="cursor:pointer">
      <h1>Add a Table</h1>
    </header>
	   	<fieldset>
     		<legend>Pick your table attributes</legend>
			<fieldset style="width: 46%; float: left; ">
    		<legend>Size</legend>
				<label for="tableRow" class="blocklabel" >ROWs:
        			<select id="tableRow" name="tableRow" class="table">
					  <option value="1">1</option>
					  <option value="2">2</option>
					  <option value="3">3</option>
					  <option value="4">4</option>
					  <option value="5">5</option>
					</select> 
				</label>
				<br/>
				<label for="tablecolumn" class="blocklabel">COLUMNs:
        			<select id="tableColumn" name="tableColumn" class="table">
					  <option value="1">1</option>
					  <option value="2">2</option>
					  <option value="3">3</option>
					  <option value="4">4</option>
					  <option value="5">5</option>
					</select> 
					<br/>
				</label>
				<input id="tableBorder" name="tableBorderNo" value="1" type="checkbox"> 
				<label for="tableBorderNo">Border</label>	
				<p>
				 	<div class ="button addbutton" onClick="newTable()">Add Table</div>
				</p>   			
			</fieldset>
			<!--<fieldset style="width: 96%; float: left;">
				<legend>Table Size</legend>
				<p>
	       			<input name="cbTableWidth" value="cbTableWidth" type="checkbox"> 
        			<label for="tableWidth">Specify Width: </label>
        		    <input type="text" name="txtTableWidth"  id="txtTableWidth"/>
          			<input type = "radio"
			                 name = "rdTableWidthInches"
			                 id = "rbTableWidthInches"
			                 value = "rbTableWidthInches"
			                 checked = "checked" />
					<label for = "rbTableWidthInches">In Inches</label>					          
					<input type = "radio"
					 	name = "rbTableWidthCM"
					 	id = "rbTableWidthCM"
						value = "rbTableWidthCM" />
					<label for = "rbTableWidthCM">In CM</label>
					<input type = "radio"
						name = "rbTableWidthPerc"
						id = "rbTableWidthPerc"
						value = "rbTableWidthPerc" />
					<label for = "rbTableWidthPerc">In Percentage</label>
					
					<label for="tableHozFloat" style="padding-right: 25px; float: right" >Float:
	    			<select id="tableFloat" name="tableHozFloat" >
					  <option value="Left">Left</option>
					  <option value="Right">Right</option>
					  <option value="Centre">Centre</option>
					</select> 
					</label>
				</p>
				<p>							    
    				<input name="cbTableHeight" value="cbTableHeight" type="checkbox"> 
        			<label for="tableWidth">Specify Height: </label>
        			<input type="text" name="txtTableHeight" id="txtTableHeight" />
			        	<input type = "radio"
			                 name = "rdTableHeightInches"
			                 id = "rbTableHeightInches"
			                 value = "rbTableHeightInches"
			                 checked = "checked" />
			          	<label for = "inInches">In Inches</label>
			          	<input type = "radio"
			                 name = "rbTableHeightCM"
			                 id = "rbTableHeightCM"
			                 value = "rbTableHeightCM" />
			          	<label for = "rbTableHeightCM">In CM</label>
			          	<input type = "radio"
			                 name = "radSize"
			                 id = "inPercentage"
			                 value = "In Percentage" />
			          	<label for = "inPercentage">In Prercentage</label>
			          	<label for="tableVerFloat" style="padding-right: 25px; float: right" >Float:
	    				<select id="tableFloat" name="tableVerFloat" >
						  <option value="Top">Top</option>
						  <option value="Bottom">Bottom</option>
						  <option value="Centre">Centre</option>
						</select> 
						</label>
				</p>       
			</fieldset> -->

		</fieldset> 
		<fieldset>
			<legend>Preview</legend>
			<div style="text-align: center; padding: 10; background: #417BAF;"> 
				<canvas id = "tablepreview" width="242" height="107"></canvas>
			</div>
		</fieldset>








