	<img src="images/close.jpg" id="closebtn" onClick="closePopup('#addgraphicpopup')" style="cursor:pointer">
    <h1>Add a Graphic</h1>
    
   	<article class="tabs">
   		<section class="tabs_bar">
   			<div style="height: 5px;">&nbsp;</div>
		    <div class="tabs_bar">
		    	<span class="tab_button"  id="uploadGraphicTabButton" onClick="showTab('#uploadgraphictab')">
		    		Upload a Graphic
		    	</span>
		    	<span class="tab_button" id="selectGraphicTabButton" onClick="showTab('#selectgraphictab')">
		    		Select a Graphic
		    	</span>
		    	<span class="tab_button" id="selectSymbolTabButton" Title="Coming soon">
		    		Select a Symbol
		    	</span>		    	
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
        		<div class ="button" style="float: right; padding: 5px;  " onClick="newGraphic()">Add Graphic</div>
        		<div>
		           	<p>
			 			<label for="tableRow" >Category:</label>
						<select name="tableRow" onChange="changeImageCategory(this.value)">
							<?php
								$categories = $db->getImageCategories();
								foreach($categories as $category){
									echo sprintf('<option value="%s">%s</option>',$category['id'],$category['category']);
								}
							?>						
						</select>					
					</p>
				</div>	
				
				<p>	
					<fieldset id="selectimageblock"></fieldset>
		    		
	    		</p>
	    	</div>
        </section>    
        
        <!-- SELECT UPLOAD A GRAPHIC -->
        <section id="uploadgraphictab" class="graphictab">
        	<div>
        		<form id="uploadGraphicForm" enctype="multipart/form-data" method="post" action="uploadgraphic.php">
	        		<label for="uploadGraphic">File:</label>
	        		<input type="file" name="uploadGraphic" id="uploadGraphicFile" onChange="uploadGraphicCheck(this)"/>
	        		<div id="filename"></div>
	        		<div class="button" onClick="uploadGraphicSubmit()">Upload</div>
        		</form>
			</div>    	       	
    	</section>
	</article>
  