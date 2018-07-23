<?php 
	include_once "_common.php";
	
	function importImages($categoryId)
	{
		global $_image_db;
		global $_system;
		
		$query = "SELECT * FROM aa_images WHERE category_id = ".$categoryId;
		$result = mysql_query($query,$_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			exit;	
		}	
	
		while($row = mysql_fetch_assoc($result))
		{
			$_image_db->setImageData($row['id'], $row['data']);
		}			
	}
	
	function importBarcodes()
	{
		global $_image_db;
		global $_system;
		
		$result = mysql_query("DELETE FROM barcodes",$_system->db->connection);
		
		$query = "SELECT * FROM aa_barcodes";
		$result = mysql_query($query,$_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			exit;	
		}	
	
		while($row = mysql_fetch_assoc($result))
		{
			global $_order_db;
			
			$b = new Barcode();
			$b->barcode = $row['barcode'];
			$b->dateUsed = $row['date_used'];
			$b->dateCreated = $row['date_created'];
			$b->master = $row['master'];
			
			$ci = new ConfigItem();
			$ci->productId = $row['product_id'];
			$ci->templateCategoryId = ($row['template_category_id'] < 0) ? null : $row['template_category_id'];
			$ci->templateId = ($row['template_id'] < 0) ? null : $row['template_id'];
			
			$c = new Config();
			if(!is_null($ci->templateId) || !is_null($ci->templateCategoryId))
			{
				$c->uiMode = Config::UI_MODE_SIMPLE;
			} 
			
			$c->items = array();
			$c->items[] = $ci;
				
			$b->setConfig($c);
			
			if(!$_order_db->createBarcode($b)) echo "Error!! ";
		}			
	}
	
	function importTemplates()
	{
		global $_image_db;
		global $_system;
		
		$result = mysql_query("DELETE FROM design_templates",$_system->db->connection);
		
		$query = "SELECT * FROM aa_design_templates";
		$result = mysql_query($query,$_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			exit;	
		}	
	
		while($row = mysql_fetch_assoc($result))
		{
			global $_design_db;
			
			$dt = new DesignTemplate();
			$dt->id = $row['id'];
			$dt->name = $row['name'];
			$dt->categoryId = $row['category_id'];
			$dt->designJSON = $row['json'];
			$dt->productTypeId = $row['product_type_id'];
			
			if($_design_db->createDesignTemplate($dt))
			{
				$query = "SELECT data FROM aa_images WHERE id=" . $row['preview_image_id'];
				$result2 = mysql_query($query,$_system->db->connection);
				if(!$result2)
				{
					if(DesignDB::DEBUG) echo mysql_error();
					exit;
				}
				if($row2 = mysql_fetch_assoc($result2))
				{
					$_image_db->setImageData($dt->getPreviewImageId(), $row2['data']);
				}
			}		
		}
	}			
	//importImages(1);
	//importImages(4);
	//importImages(125);
	//importBarcodes();
	//importTemplates();
?>
