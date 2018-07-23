	<img src="images/close.jpg" id="closebtn" onClick="closePopup('#addtemplatepopup')" style="cursor:pointer">
    <header>
      <h1>Pick a Template</h1>
    </header>
	<div>
       	<div style="float: left; width: 30% ">
 			<label for="tableRow" >Category:
 					<select onChange="changeTemplateCategory(this.value)" > 
 						<?php
 							$categories = $db->getTemplateCategories();
							foreach($categories as $category){
								echo sprintf('<option value="%s">%s</option>',$category['id'],$category['category']);
							}
 						?> 						
 					</select>
			</label>			
		</div>
		<div class ="button" style="float: right; padding: 5px; margin: 0px 20px; " onClick="changeTemplate()">Select Template</div>
    	<div class ="button" style="float: right; padding: 5px; margin: 0px 140px 20px 20px; width: 200px;" 
    			Title="Click to skip the template and start from a blank canvas" onClick="closePopup('#addtemplatepopup')">Start without Template</div> 
		
		<div style="float: left; clear: both;">
			<legend>Select the template to use for your  
			<?php
			$barcode = $db->getBarCode($s->getCurrentItem());
			$product_id = $db->getProductId($barcode);
			$product = $db->getProduct($product_id);
			echo $product['longname'];
			?>
			</legend> 
		</div>
		<div style="float: Left; clear: both; width: 100%; ">	
			<fieldset id="selecttemplateblock" style="background-color: silver">  </fieldset>
 
    </div>