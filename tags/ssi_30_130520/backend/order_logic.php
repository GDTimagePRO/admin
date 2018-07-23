<?php
	include_once "design_logic.php";

	//TODO: Using a barcode should deselect the order item	
	
	function submitOrderItem($orderItemId)
	{
		global $_order_db;
		global $_design_db;
		global $_image_db;
		
		$orderItem = $_order_db->getOrderItemById($orderItemId);
		if(is_null($orderItem)) return false;
	
		//if($sendEmail) sendDesignImageEmail($orderItemId);
	
		$designIds = $_design_db->getSortedDesignIdsByOrderItemId($orderItem->id);
		$designImageIds = array();
		
		for($i=0; $i<count($designIds); $i++)
		{
			$design = $_design_db->getDesignById($designIds[$i]);
				
			$designChanged = new Design();
			$designChanged->id = $design->id;
			$designChanged->state = Design::STATE_PENDING_SCL_RENDERING;
			$_design_db->updateDesign($designChanged);
				
			$designImageIds[] = ImageDB::TYPE_WEB_COLOR . $design->getInkColor() . '.' . $design->getPreviewImageId();
		}
		$_image_db->createCollage($orderItem->getPreviewImageId(), $designImageIds, 400, 400);
		
		$barcode = $_order_db->getBarcodeByBarcode($orderItem->barcode);
		if(is_null($barcode)) return false;
		if(!$barcode->isMaster())
		{
			$barcode->dateUsed = date("Y-m-d H:i:s", time());
			$_order_db->updateBarcode($barcode);
		}
	
		$orderItem->processingStagesId = ProcessingStage::STAGE_PENDING_CART_ORDER;
		return $_order_db->updateOrderItem($orderItem); 
	}
	
	
	function selectOrderItem(OrderItem $orderItem)
	{
		global $_session;
		global $_order_db;
		global $_design_db;
				
		if(is_null($orderItem)) return false;
			
		$designIds = $_design_db->getSortedDesignIdsByOrderItemId($orderItem->id);
		if(is_null($designIds)) return false;
	
		$design = $_design_db->getDesignById($designIds[0]);
		if(is_null($design)) return false;
		
		$barcode = $_order_db->getBarcodeByBarcode($orderItem->barcode);
		if(is_null($barcode)) return false;
			
		if($barcode->isUsed()) return false;
	
		$_session->setActiveOrderId($orderItem->orderId);
		$_session->setActiveOrderItemId($orderItem->id);
		$_session->setActiveDesignId($design->id);
		
		$designConfig = $design->getConfigItem();
		$_session->setEnableTemplateBrowser(is_null($designConfig->templateId));
				
		$config = $orderItem->getConfig();
		if($config->uiMode == Config::UI_MODE_SIMPLE)
		{
			$_session->setDesignMode(Session::DESIGN_MODE_SIMPLE);
		}
		else
		{
			$_session->setDesignMode(Session::DESIGN_MODE_FULL);
		}
		
		return true;
	}

	
	function createOrderItem($orderId, $barcodeStr, $returnURL)
	{
		global $_order_db;
		global $_design_db;
		global $_settings;
		
		$order = $_order_db->getOrderById($orderId);
		if(is_null($order)) return NULL;
		
		$barcode = $_order_db->getBarcodeByBarcode($barcodeStr);
		if(is_null($barcode)) return NULL;
		
		$barcodeConfig  = $barcode->getConfig();		
		if(is_null($barcodeConfig->items)) return NULL;
		
		
		$orderItem = new OrderItem();
		$orderItem->orderId = $order->id;
		$orderItem->processingStagesId = ProcessingStage::STAGE_PENDING_CONFIRMATION;
		$orderItem->barcode = $barcode->barcode;
		$orderItem->manufacturerId = $_order_db->getManufacturerIdForURL($returnURL);		
		
		$items = $barcodeConfig->items;
		$barcodeConfig->items = null;
		$orderItem->setConfig($barcodeConfig);
		
		if(!$_order_db->createOrderItem($orderItem)) return NULL;
				
		foreach($items as $configItem)
		{
			$design = createDesign($orderItem->id, $configItem);
			if(is_null($design)) return NULL;
		}
				
		return $orderItem; 
	}
	
	
	function getActiveOrderItem($orderId, $barcodeStr)
	{
		global $_order_db;
		global $_settings;
	
		$activeOrderItemArray = $_order_db->getOrderItemsByOrderId(
				$orderId,
				ProcessingStage::STAGE_PENDING_CONFIRMATION,
				$barcodeStr
		);
	
		if(count($activeOrderItemArray) > 0)
		{
			return $activeOrderItemArray[count($activeOrderItemArray) - 1];
		}
		
		return NULL;
	}

	function isOrderItemSelected()
	{
		global $_session;
		return
			($_session->getActiveOrderId() != "") &&
			($_session->getActiveOrderItemId() != "") &&
			($_session->getActiveDesignId() != "") &&
			($_session->getActiveDesignImageId() != "");
	}
	
	function clearSelectedOrderItem()
	{
		global $_session;
		
		$_session->setActiveOrderId("");
		$_session->setActiveOrderItemId("");
		$_session->setActiveDesignId("");
		$_session->setDesignMode("");
		$_session->setEnableTemplateBrowser("");
		$_session->setSelectedTemplateId("");
		
		return true;
	}
	
	function cancelActiveOrderItem($orderId, $barcodeStr)
	{
		global $_order_db;
		global $_settings;
	
		$_order_db->cancelActiveOrderItemsByOrderId(
				$orderId,
				$barcodeStr
		);
		clearSelectedOrderItem();
	}
	
	function getActiveOrder($userId, $createNewOrder = false)
	{
		global $_settings;
		global $_order_db;
		
		$activeOrderArray = $_order_db->getOrdersByUserId(
				$userId,
				$_settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE]
		);

		if(count($activeOrderArray) > 0)
		{
			return $activeOrderArray[count($activeOrderArray) - 1];
		} 
		
		if(!$createNewOrder) return NULL;

		$order = new Order();
		$order->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE];
		$order->startDate = time();
		$order->submitDate = 0;
		$order->userId = $userId;
		
		if(!$_order_db->createOrder($order)) return NULL;
		
		return $order;
	}

	
?>