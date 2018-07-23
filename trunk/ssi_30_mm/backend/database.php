<?php
	require_once "db_design.php";
	require_once "db_order.php";
	require_once "db_shipping.php";
	
	class Database
	{
		public $connection = NULL; //this stores the connection to the database		
		
		/**
		 * @var DesignDB
		 */
		public $design	= NULL;

		/**
		 * @var OrderDB
		 */
		public $order	= NULL;
		
		/**
		 * @var ShippingDB
		 */
		public $shipping = NULL;
	
		function __construct($server,$username,$password,$dbname)
		{
			if($this->connection == NULL)
			{
				$this->connection = mysql_connect($server,$username,$password);
				mysql_select_db($dbname);
			}
				
			$this->design = new DesignDB($this->connection);
			$this->order = new OrderDB($this->connection);
			$this->shipping = new ShippingDB($this->connection);
		}
	}
?>