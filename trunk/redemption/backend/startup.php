<?php
	require_once "settings.php";
	require_once "database.php";
	
	class Startup
	{
		/**
		 * @var Startup
		 */
		private static $_instance = null;

		/**
		 * @var Database
		 */
		public $db;

		function __construct()
		{		
			$this->db = new Database(
					Settings::DB_SERVER,
					Settings::DB_USER_NAME,
					Settings::DB_PASSWORD,
					Settings::DB_SCHEMA_NAME
				);
		}
		
		/**
		 * @return Startup
		 */
		public static function getInstance()
		{
			if(is_null(Startup::$_instance))
			{
				Startup::$_instance = new Startup(); 
			}
			return Startup::$_instance;
	  	}
	}
?>