<form method="get" action="template.php">
	<label for="id">Product:</label>
	<select name="id">
		<?php
		include_once "../Backend/startup.php";
		$startup = Startup::getInstance("../");
		$db = $startup->db;
		$products = $db->getProducts();
		//var_dump($products);
		foreach($products as $product){
			echo "<option value='".$product['id']."'>".$product['longname']."</option>";
		}
		
		?>
	</select><br />
	<input type="submit" value="Next">
</form>
