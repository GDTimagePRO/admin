<?php

include_once "db_image.php";
include_once "db_design.php";

class DB
{	
	public $image = NULL;
	public $design = NULL;
	
	private $connection = NULL;
			
	function __construct($server,$username,$password,$dbname)
	{
		if($this->connection == NULL)
		{
			$this->connection = mysql_connect($server,$username,$password);
			mysql_select_db($dbname);
		}
		
		$this->image = new ImageDB($this->connection);
		$this->design = new DesignDB($this->connection);		
	}	
}
?>