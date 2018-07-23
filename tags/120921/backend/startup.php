<?php
	include_once "session.php";
	include_once "database.php";

	class Startup
	{
		const SETTING_DEFAULT_ORDER_PROCESSING_STAGE		= "default order processing stage";
		const SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE	= "default order item processing stage";
		
		const SETTING_DB_SERVER			= "server";
		const SETTING_DB_USER_NAME		= "username";
		const SETTING_DB_PASSWORD		= "password";		
		const SETTING_DB_NAME			= "database name";
		
		const SETTING_HOME_URL			= "url";

		const SETTING_FONTS				= "default order processing stage";
		const SETTING_MIN_FONT_SIZE		= "min font size";
		const SETTING_MAX_FONT_SIZE		= "max font size";
		const SETTING_FONT_SIZE_STEP	= "font size step";
		
		public $db;
		public $session;
		public $settings;
		public $processingstages;
		private static $instance;

		function __construct($directory)
		{		
			$settings_file = $directory."/backend/settings.txt";
			$settings_file_content = file_get_contents($settings_file);
			
	        $this->settings = json_decode($settings_file_content, true);
			
			$this->db = new Database(
					$this->settings[Startup::SETTING_DB_SERVER],
					$this->settings[Startup::SETTING_DB_USER_NAME],
					$this->settings[Startup::SETTING_DB_PASSWORD],
					$this->settings[Startup::SETTING_DB_NAME]
				);
			
			$this->session = new Session();
		}
		

		function loginUser($userId)
		{
			$this->session->setActiveUserId($userId);
			$this->session->setActiveOrderId("");
			$this->session->setActiveOrderItemId("");
			$this->session->setActiveDesignId("");
			$this->session->setActiveDesignImageId("");
				
			$activeOrderArray = $this->db->order->getOrdersByUserId(
					$userId,
					$this->settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE]
			);
	
			if(count($activeOrderArray) > 0)
			{
				$activeOrder = $activeOrderArray[count($activeOrderArray) - 1];
				$this->session->setActiveOrderId($activeOrder->id);
					
				$activeOrderItemArray = $this->db->order->getOrderItemsByOrderId(
						$activeOrder->id,
						$this->settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE]
				);
					
				if(count($activeOrderItemArray) > 0)
				{
					$activeOrderItem = $activeOrderItemArray[count($activeOrderItemArray) - 1];
					$design = $this->db->design->getDesignById($activeOrderItem->designId);
					
					$this->session->setActiveOrderItemId($activeOrderItem->id);
					$this->session->setActiveDesignId($activeOrderItem->designId);				
					$this->session->setActiveDesignImageId($design->imageId);
				}
			}			
		}
		
		function loginRedirect()
		{
			Header("location: http://".$this->settings[Startup::SETTING_HOME_URL]."login.php");
			exit();
		}
		
		function forceLogin()
		{
			if($this->session->getActiveUserId() =="")
			{
				$this->loginRedirect();
			}
		}
		
		
		
		
		public static function getInstance($directory)
		{
			return new Startup($directory);
	  	}
	}
?>