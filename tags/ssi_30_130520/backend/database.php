<?php
	include_once "db_image.php";
	include_once "db_design.php";
	include_once "db_user.php";
	include_once "db_order.php";
	
	class Database
	{
		public $connection = NULL; //this stores the connection to the database		
		public $image	= NULL;
		public $design	= NULL;
		public $user	= NULL;
		public $order	= NULL;
	
		function __construct($server,$username,$password,$dbname)
		{
			if($this->connection == NULL)
			{
				$this->connection = mysql_connect($server,$username,$password);
				mysql_select_db($dbname);
			}
				
			$this->image = new ImageDB($this->connection);
			$this->design = new DesignDB($this->connection);
			$this->user = new UserDB($this->connection);
			$this->order = new OrderDB($this->connection);
		}
	}
?>