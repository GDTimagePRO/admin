<?php

	class DesignTemplateCategories
	{
		public $id = -1;
		public $name;
	}
	
	
	class DesignTemplate
	{
		public $id = -1;
		public $name = NULL;
		public $categoryId = NULL;
		public $previewImageId = NULL;
		public $json = NULL;
		public $productTypeId = NULL;
	}
	
	class Design
	{
		public $id = -1;
		public $json = "";
		public $dateChanged;
		public $imageId;
		public $productTypeId;
	}
	
	class DesignDB
	{
		const DEBUG = TRUE;		
		const DESIGN_TEMPLATE_FIELDS_NO_JSON = "id, name, category_id, preview_image_id, product_type_id";
		const DESIGN_TEMPLATE_FIELDS		 = "id, name, category_id, preview_image_id, product_type_id, json";
		
		private $connection = NULL;		
		
		
		
		function __construct($connection)
		{
			$this->connection = $connection;
		}
	
		public function getTemplateCategoryList()
		{
			$query = "SELECT id, name FROM design_template_categories";
			$result = mysql_query($query,$this->connection);
	
			if(!$result)
			{
				if(DesignDB::DEBUG) echo mysql_error();
				return NULL;
			}
	
			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $row;
			}
			return $return;
		}
		
		public function getTemplateList($category_id)
		{
			$query = sprintf('SELECT id, name, preview_image_id FROM design_templates WHERE category_id=%d',$category_id);		
			$result = mysql_query($query,$this->connection);
	
			if(!$result)
			{
				if(DesignDB::DEBUG) echo mysql_error();
				return NULL;
			}
	
			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $row;
			}
			return $return;
		}
		
		public function getTemplateJSON($template_id)
		{
			$query = sprintf('SELECT json FROM design_templates WHERE id=%d',$template_id);		
			$result = mysql_query($query,$this->connection);
	
			if(!$result)
			{
				if(DesignDB::DEBUG) echo mysql_error();
				return NULL;
			}
			
			$row = mysql_fetch_row($result);
			if($row) return $row[0];
			return NULL;
		}
		
		
		//==============================================================================
		// Defualt Design Template
		//==============================================================================
		
		function getDefualtDesignTemplateId($productTypeId)
		{			
			$query = sprintf("SELECT design_template_id FROM default_design_templates WHERE product_type_id=%d", $productTypeId);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
			
			$row = mysql_fetch_assoc($result);
			if(!$row) return -1;
			
			return $row['design_template_id'];
		}		
		
		//==============================================================================
		// Design Template
		//==============================================================================
		
		function loadDesignTemplate($row)
		{
			$result = new DesignTemplate();
			$result->id = $row['id'];
			$result->name = $row['name'];
			$result->categoryId = $row['category_id'];
			$result->previewImageId = $row['preview_image_id'];
			$result->json = isset($row['json']) ? $row['json'] : null;
			$result->productTypeId = $row['product_type_id'];
						
			return $result;
		}
		
		function createDesignTemplate(DesignTemplate $designTemplate)
		{
			$query = sprintf(
					"INSERT INTO design_templates(name, category_id, preview_image_id, json, product_type_id) ".
					"VALUES('%s', %d, %d, '%s', %d)",
					mysql_real_escape_string($designTemplate->name),
					$designTemplate->categoryId,
					$designTemplate->previewImageId,
					mysql_real_escape_string($designTemplate->json),
					$designTemplate->productTypeId
				);
		
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$designTemplate->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$designTemplate->id = -1;
				if(UserDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function deleteDesignTemplate($id)
		{
			$query = sprintf("DELETE FROM design_templates WHERE id=%d",$id);
			$result = mysql_query($query,$this->connection);
		
			if($result)
			{
		
				return true;
			}
			else
			{
				return false;
			}
		
		}
		

		function updateDesignTemplate(DesignTemplate $designTemplate)
		{
			$query = "UPDATE design_templates SET ";
			$first = true;

			if(!is_null($designTemplate->name))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("name='%s'", mysql_real_escape_string($designTemplate->name));
			}

			if(!is_null($designTemplate->categoryId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("category_id=%d", $designTemplate->categoryId);
			}

			if(!is_null($designTemplate->previewImageId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("preview_image_id=%d", $designTemplate->previewImageId);
			}

			if(!is_null($designTemplate->json))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("json='%s'", mysql_real_escape_string($designTemplate->json));
			}

			if(!is_null($designTemplate->productTypeId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("product_type_id=%d", $designTemplate->productTypeId);
			}				
			
			$query = $query.sprintf(" WHERE id=%d", $designTemplate->id);
				
			if(!mysql_query($query,$this->connection))
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
			return true;
		}
		
		function getDesignTemplateById($id, $withJSON = true)
		{
			$fields = $withJSON ? DesignDB::DESIGN_TEMPLATE_FIELDS : DesignDB::DESIGN_TEMPLATE_FIELDS_NO_JSON;  
			$query = sprintf("SELECT ".$fields." FROM design_templates WHERE id=%d", $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
				
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
				
			return $this->loadDesignTemplate($row);
		}
		
		function getDesignTemplates($categoryId = NULL, $withJSON = false)
		{
			$fields = $withJSON ? DesignDB::DESIGN_TEMPLATE_FIELDS : DesignDB::DESIGN_TEMPLATE_FIELDS_NO_JSON;  
			$query = "SELECT ".$fields." FROM design_templates ";
			
			$first = true;

			if(!is_null($categoryId))
			{
				if($first) { $first = false; $query = $query." WHERE ";} else { $query = $query." AND "; }
				$query = $query.sprintf("category_id=%d", $categoryId);
			}
			
			$query = $query." ORDER BY id";
		
			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return array();
			}
		
			$designTemplate = array();
			while($row = mysql_fetch_assoc($result))
			{
				$designTemplate[] = $this->loadDesignTemplate($row);;
			}
			return $designTemplate;
		}
		
		
		
		//==============================================================================
		// Design
		//==============================================================================
		
		function loadDesign($row)
		{
			$result = new Design();
			
			$result->id = $row['id'];
			$result->json = $row['json'];
			$result->dateChanged = strtotime($row['date_changed']);
			$result->imageId = $row['image_id'];
			$result->productTypeId = $row['product_type_id'];
			
			return $result;
		}
		
		function createDesign(Design $design)
		{
			$query = sprintf(
					"INSERT INTO designs(json, date_changed, image_id, product_type_id) VALUES('%s', NOW(), %d, %d)", 
					mysql_real_escape_string($design->json),
					$design->imageId,
					$design->productTypeId
				);

			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$design->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$design->id = -1;
				if(UserDB::DEBUG) echo mysql_error();
				return false;
			}
		}

		function getDesignById($id)
		{
			$query = sprintf("SELECT * FROM designs WHERE id=%d", $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
				
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
				
			return $this->loadDesign($row);
		}
		
		public function getDesignJSON($design_id)
		{
			$query = sprintf('SELECT json FROM designs WHERE id=%d',$design_id);		
			$result = mysql_query($query,$this->connection);
	
			if(!$result)
			{
				if(DesignDB::DEBUG) echo mysql_error();
				return NULL;
			}
			
			$row = mysql_fetch_row($result);
			if($row) return $row[0];
			return NULL;
		}
		
		public function setDesignJSON($design_id, $json)
		{
			$query = sprintf("UPDATE designs SET json='%s' WHERE id=%d", mysql_real_escape_string($json) , $design_id);		
			if(!mysql_query($query,$this->connection))
			{
				if(DesignDB::DEBUG) echo mysql_error();			
				return FALSE; 		
			}				
			return TRUE; 
		}
		
	}
?>