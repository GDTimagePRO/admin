<?php
	require_once "db_redemption.php";
	
	class Database
	{
		public $connection = NULL; //this stores the connection to the database		
		
		/**
		 * @var RedemptionDB
		 */
		public $redemption	= NULL;

		function __construct($server,$username,$password,$dbname)
		{
			if($this->connection == NULL)
			{
				$this->connection = mysql_connect($server,$username,$password);
				mysql_select_db($dbname);
			}
				
			$this->redemption = new RedemptionDB($this->connection);
		}
	}
?>