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
		

		
		function selectBarcode($barcodeStr, $userId)
		{
			//Get barcode record
			$barcode = $this->db->order->getBarcodeByBarcode($barcodeStr);
			if(is_null($barcode)) return "That code was not recognised.";
			
			//Check if barcode has been used
			if($barcode->isUsed() && (!$barcode->isMaster()))
			{
				return "That code has already been used.";
			}
			
			//Get the active order for this user
			$order = NULL;
			$orderArray = $this->db->order->getOrdersByUserId(
					$userId,
					$_settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE]
				);
			if(count($orderArray) != 0) $order = $orderArray[0];
				
			if(is_null($order))
			{
				$order = new Order();
				$order->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE];
				$order->startDate = time();
				$order->submitDate = 0;
				$order->userId = $_user_id;
				if(!$_order_db->createOrder($order)) $order = NULL;
			}
				
			$orderItem = $this->db->order->getOrderItemById();
			if(is_null($orderItem)) return false;
			
			$design = $this->db->order->getOrderItemById($orderItem->designId);
			if(is_null($design)) return false;
				
			
			$this->session->setActiveOrderId($orderItem->orderId);
			$this->session->setActiveOrderItemId($orderItem->id);
			$this->session->setActiveDesignId($design->id);
			$this->session->setActiveDesignImageId($design->imageId);
			
			return NULL;
		}
				
		function logoutUser()
		{
			$this->session->setActiveUserId("");
			$this->clearSelectedOrderItem();
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