<?php
	require_once "settings.php";
	require_once "utils.php";
	require_once "database.php";
	require_once "resource_manager.php";
	
	class Startup
	{
		const URL_REDIRECT_NO_DESIGN_ENVIRONMENT	= "missing_design_environment.php";
		const URL_REDIRECT_NO_CUSTOMER				= "missing_customer.php";
		
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
				
		function errorRedirect($to)
		{
			if(isset($_COOKIE["redirect"]) && ($_COOKIE["redirect"] != ""))
			{
				Header("location: " . $_COOKIE["redirect"]);				
			}
			else
			{
				Header("location: http://". Settings::HOME_URL .$to);
			}			
			exit();
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