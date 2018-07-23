package backend;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;

public class DesignDB
{
	public static final Boolean DEBUG = true;		
	public static final String[] DESIGN_TEMPLATE_FIELDS_NO_JSON = {"id", "name", "category_id", "product_type_id", "config_json"};
	public static final String[] DESIGN_TEMPLATE_FIELDS		 = {"id", "name", "category_id", "product_type_id", "config_json", "design_json"};
	
	private Connection connection;	
	
	public DesignDB (Connection connection)
	{
		connection = this.connection;
	}

	/*public ResultSet getTemplateCategoryList()
	{
		String query = 
			"SELECT dtc.id AS id, dtc.name AS name, c.description AS customer_description " + 
			"FROM design_template_categories AS dtc " + 
			"LEFT JOIN customers AS c ON dtc.customer_id = c.id " +
		    "ORDER BY dtc.customer_id, dtc.id";
		PreparedStatement ps = connection.prepareStatement(query);
		ResultSet rs = ps.executeQuery();

		return rs;
	}
	
	public ResultSet getTemplateList()
	{
		String query = "SELECT id, name FROM design_templates";
		PreparedStatement ps = connection.prepareStatement(query);
		ResultSet rs = ps.executeQuery();

		return rs;
	}
	
	public ResultSet getTemplateListByCategoryId(int category_id)
	{
		String query = String.format("SELECT id, name FROM design_templates WHERE category_id=?";
		PreparedStatement ps = connection.prepareStatement(query);
		ps.setInt(1, category_id);
		ResultSet rs = ps.executeQuery();

		return rs;
	}
	
	public ResultSet getTemplateJSON(int template_id)
	{
		String query = String.format("SELECT design_json FROM design_templates WHERE id=?";
		PreparedStatement ps = connection.prepareStatement(query);
		ps.setInt(1, tempalte_id);
		ResultSet rs = ps.executeQuery();

		return rs;
	}*/
			
	
	//==============================================================================
	// Defualt Design Template
	//==============================================================================
	
	/*public ResultSet getDefualtDesignTemplateList()
	{
		String query = "SELECT * FROM default_design_templates";
		PreparedStatement ps = connection.prepareStatement(query);
		ResultSet rs = ps.executeQuery();

		return rs;
	}
	
	public int getDefualtDesignTemplateId(int productTypeId)
	{			
		String query = String.format("SELECT design_template_id FROM default_design_templates WHERE product_type_id=%d", productTypeId);
		PreparedStatement ps = connection.prepareStatement(query);
		ResultSet rs = ps.executeQuery();
		
		if(!rs.next()) return -1;
		
		return rs.getInt("design_template_id");
	}*/		
	
	//==============================================================================
	// Design Template Category
	//==============================================================================
	public DesignTemplateCategory loadDesignTemplateCategory(ResultSet row)
	{
		DesignTemplateCategory result = new DesignTemplateCategory();
		result.id = row.getInt("id");
		result.customerId = row.getInt("customer_id");
		result.name = row.getString("name");

		return result;
	}
	
	function createDesignTemplateCategory(DesignTemplateCategory $templateCategory)			
	{
		if($templateCategory->id > -1)
		{
			$query = sprintf(
					"INSERT INTO design_template_categories(id, customer_id, name) ".
					"VALUES( %d, %d, '%s' )",
					$templateCategory->id,
					$templateCategory->customerId,
					mysql_real_escape_string($templateCategory->name)						
			);
		}
		else
		{
			$query = sprintf(
					"INSERT INTO design_template_categories(customer_id, name) ".
					"VALUES( %d, '%s' )",
					$templateCategory->customerId,
					mysql_real_escape_string($templateCategory->name)
			);
		}
		
		$result = mysql_query($query,$this->connection);
		if($result)
		{
			$templateCategory->id = mysql_insert_id($this->connection);
			return true;
		}
		else
		{
			$templateCategory->id = -1;
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
	}
	
	function deleteDesignTemplateCategory($id)
	{
		$query = sprintf("DELETE FROM design_template_categories WHERE id=%d",$id);
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
			
	function updateDesignTemplateCategory(DesignTemplateCategory $templateCategory)
	{
		$query = "UPDATE design_template_categories SET ";
		$first = true;
	
		$query = $query.sprintf(" name='%s'", mysql_real_escape_string($templateCategory->name));
		$query = $query.sprintf(" , customer_id=%d", $templateCategory->customerId);

		$query = $query.sprintf(" WHERE id=%d", $templateCategory->id);
	
		if(!mysql_query($query,$this->connection))
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		return true;
	}
	
	function getDesignTemplateCategoryById($id)
	{
		$query = sprintf("SELECT * FROM design_template_categories WHERE id=%d", $id);
		$result = mysql_query($query,$this->connection);
		if(!$result) return NULL;
	
		$row = mysql_fetch_assoc($result);
		if(!$row) return NULL;
	
		return $this->loadDesignTemplateCategory($row);
	}
	
	function getDesignTemplateCategories($customerId = NULL)
	{
		if(is_null($customerId))
		{
			$query = "SELECT * FROM design_template_categories ORDER BY id";
		}
		else
		{
			$query = sprintf(
					"SELECT * FROM design_template_categories WHERE customer_id=%d ORDER BY id", 
					$customerId
				);
		}
						
		$result = mysql_query($query,$this->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return array();
		}
			
		$categories = array();
		while ($row = mysql_fetch_assoc($result))
		{
			$categories[] = $this->loadDesignTemplateCategory($row);
		}
			
		return $categories;
	}
	
	
	public function getTemplateCategoryListForDesign(Design $design)
	{
		$config = $design->getConfigItem();			
		if(!is_null($config->templateCategoryId))
		{
			$query = "SELECT id, name FROM design_template_categories WHERE id=" . intval($config->templateCategoryId);
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
		else
		{
			return $this->getTemplateCategoryList();
		}
	}
	
	
	
	//==============================================================================
	// Design Template
	//==============================================================================
	
	/*function loadDesignTemplate($row)
	{
		$result = new DesignTemplate();
		$result->id = $row['id'];
		$result->name = $row['name'];
		$result->categoryId = $row['category_id'];
		$result->productTypeId = $row['product_type_id'];			
		$result->configJSON = $row['config_json'];
		$result->designJSON = isset($row['design_json']) ? $row['design_json'] : null;
			
		return $result;
	}
	
	function createDesignTemplate(DesignTemplate $designTemplate)
	{
		if($designTemplate->id < 0)
		{			
			$query = sprintf(
					"INSERT INTO design_templates(name, category_id, product_type_id, config_json, design_json) ".
					"VALUES('%s', %d, %d, '%s', '%s')",
					mysql_real_escape_string($designTemplate->name),
					$designTemplate->categoryId,
					$designTemplate->productTypeId,
					mysql_real_escape_string($designTemplate->configJSON),
					mysql_real_escape_string($designTemplate->designJSON)					
				);
		}
		else //TODO: make this one statement
		{
			$query = sprintf(
					"INSERT INTO design_templates(id, name, category_id, product_type_id, config_json, design_json) ".
					"VALUES(%d, '%s', %d, %d, '%s', '%s')",
					$designTemplate->id,
					mysql_real_escape_string($designTemplate->name),
					$designTemplate->categoryId,
					$designTemplate->productTypeId,
					mysql_real_escape_string($designTemplate->configJSON),
					mysql_real_escape_string($designTemplate->designJSON)
			);
		}
	
		$result = mysql_query($query,$this->connection);
		if($result)
		{
			$designTemplate->id = mysql_insert_id($this->connection);
			return true;
		}
		else
		{
			$designTemplate->id = -1;
			if(DesignDB::DEBUG) echo mysql_error();
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

		if(!is_null($designTemplate->productTypeId))
		{
			if($first) { $first = false; } else { $query = $query.", "; }
			$query = $query.sprintf("product_type_id=%d", $designTemplate->productTypeId);
		}				
					
		if(!is_null($designTemplate->configJSON))
		{
			if($first) { $first = false; } else { $query = $query.", "; }
			$query = $query.sprintf("config_json='%s'", mysql_real_escape_string($designTemplate->configJSON));
		}

		if(!is_null($designTemplate->designJSON))
		{
			if($first) { $first = false; } else { $query = $query.", "; }
			$query = $query.sprintf("design_json='%s'", mysql_real_escape_string($designTemplate->designJSON));
		}
		
		$query = $query.sprintf(" WHERE id=%d", $designTemplate->id);
			
		if(!mysql_query($query,$this->connection))
		{
			if(DesignDB::DEBUG) echo mysql_error();
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
	
	function getDesignTemplateByName($name, $withJSON = true)
	{
		$fields = $withJSON ? DesignDB::DESIGN_TEMPLATE_FIELDS : DesignDB::DESIGN_TEMPLATE_FIELDS_NO_JSON;  
		$query = 'SELECT '.$fields.' FROM design_templates WHERE name=\''.mysql_real_escape_string($name).'\'';
		$result = mysql_query($query,$this->connection);
		if(!$result) return NULL;
			
		$row = mysql_fetch_assoc($result);
		if(!$row) return NULL;
			
		return $this->loadDesignTemplate($row);
	}
	
	function getDesignTemplates($categoryId = NULL, $productTypeId = NULL, $withJSON = false)
	{
		$fields = $withJSON ? DesignDB::DESIGN_TEMPLATE_FIELDS : DesignDB::DESIGN_TEMPLATE_FIELDS_NO_JSON;  
		$query = "SELECT ".$fields." FROM design_templates ";
		
		$first = true;

		if(!is_null($categoryId))
		{
			if($first) { $first = false; $query = $query." WHERE ";} else { $query = $query." AND "; }
			$query = $query.sprintf("category_id=%d", $categoryId);
		}
		
		if(!is_null($productTypeId))
		{
			if($first) { $first = false; $query = $query." WHERE ";} else { $query = $query." AND "; }
			$query = $query.sprintf("product_type_id=%d", $productTypeId);
		}
			
		$query = $query." ORDER BY id";
	
		$result = mysql_query($query,$this->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
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
	
	const DESIGNS_FIELDS = 'id, order_item_id, product_type_id, config_json, design_json, UNIX_TIMESTAMP(date_changed) as date_changed, state, product_id, UNIX_TIMESTAMP(date_rendered) as date_rendered'; 
	
	function loadDesign($row)
	{
		$result = new Design();
		
		$result->id = $row['id'];
		$result->orderItemId = $row['order_item_id'];
		$result->productTypeId = $row['product_type_id'];
		$result->configJSON = $row['config_json'];
		$result->designJSON = $row['design_json'];
		$result->dateChanged = $row['date_changed'];
		$result->state = $row['state'];
		$result->dateRendered = $row['date_rendered'];
		
		return $result;
	}
	
	function createDesign(Design $design)
	{
		$query = sprintf(
				"INSERT INTO designs(order_item_id, product_type_id, config_json, design_json, date_changed, state, product_id, date_rendered) " . 
				"VALUES( %d, %d, '%s', '%s', FROM_UNIXTIME(%d), %d, %d, FROM_UNIXTIME(%d))", 
				$design->orderItemId, 
				$design->productTypeId, 
				mysql_real_escape_string($design->configJSON),
				mysql_real_escape_string($design->designJSON),
				time(),
				$design->state,
				$design->productId,
				$design->dateRendered
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
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
	}

	function updateDesign(Design $design)
	{
		$query = "UPDATE designs SET ";
		$first = true;

		$query = $query.'date_changed = FROM_UNIXTIME(' . time() . ')';
			
		if(!is_null($design->orderItemId))
		{
			$query = $query.sprintf(", order_item_id=%d", $design->orderItemId);
		}
	
		if(!is_null($design->productTypeId))
		{
			$query = $query.sprintf(", product_type_id=%d", $design->productTypeId);
		}

		if(!is_null($design->configJSON))
		{
			$query = $query.sprintf(", config_json='%s'", mysql_real_escape_string($design->configJSON));
		}

		if(!is_null($design->designJSON))
		{
			$query = $query.sprintf("d, esign_json='%s'", mysql_real_escape_string($design->designJSON));
		}

		if(!is_null($design->state))
		{
			$query = $query.sprintf(", state=%d", $design->state);
		}

		if(!is_null($design->productId))
		{
			$query = $query.sprintf(", product_id=%d", $design->productId);
		}

		if(!is_null($design->dateRendered))
		{
			$query = $query.sprintf(", date_rendered=FROM_UNIXTIME(%d)", $design->dateRendered);
		}
		
		
		$query = $query.sprintf(" WHERE id=%d", $design->id);
	
		if(!mysql_query($query,$this->connection))
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		return true;
	}
	
	
	function deleteDesign($id)
	{
		$query = sprintf("DELETE FROM designs WHERE id=%d",$id);
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
	
	
	function getDesignById($id)
	{
		$query = sprintf("SELECT %s FROM designs WHERE id=%d",DesignDB::DESIGNS_FIELDS,  $id);
		$result = mysql_query($query,$this->connection);
		if(!$result) return NULL;
			
		$row = mysql_fetch_assoc($result);
		if(!$row) return NULL;
			
		return $this->loadDesign($row);
	}
	
	public function getSortedDesignIdsByOrderItemId($orderItemId)
	{
		$query = sprintf("SELECT id FROM designs WHERE order_item_id=%d ORDER BY id", $orderItemId);
		$result = mysql_query($query,$this->connection);
				
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return NULL;
		}
	
		$return = NULL;
		while($row = mysql_fetch_assoc($result))
		{
			if(is_null($return)) $return = array(); 
			$return[] = $row['id'];
		}
		return $return;
	}		
	
	public function getDesignJSON($design_id)
	{
		$query = sprintf('SELECT design_json FROM designs WHERE id=%d',$design_id);		
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
		$query = sprintf("UPDATE designs SET design_json='%s' WHERE id=%d", mysql_real_escape_string($json) , $design_id);		
		if(!mysql_query($query,$this->connection))
		{
			if(DesignDB::DEBUG) echo mysql_error();			
			return FALSE; 		
		}				
		return TRUE; 
	}*/
	
}
