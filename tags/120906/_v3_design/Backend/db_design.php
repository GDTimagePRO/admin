<?php

class DesignDB
{
	const DEBUG = TRUE;		
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
	
	public function createDesign($json)
	{
		$query = sprintf("INSERT INTO designs(json) VALUES('%s')", mysql_real_escape_string($json));		
		if(!mysql_query($query,$this->connection))
		{
			if(DesignDB::DEBUG) echo mysql_error();			
			return -1; 		
		}
		return mysql_insert_id($this->connection);
	}
}
?>