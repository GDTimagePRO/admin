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
	
		$orderItem->processingStagesId = ProcessingStage::STAGE_PENDING_RENDERING;
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
		$orderItem->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE];
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
				$_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE],
				$barcodeStr
		);
	
		if(count($activeOrderItemArray) > 0)
		{
			return $activeOrderItemArray[count($activeOrderItemArray) - 1];
		}
		
		return NULL;
	}

	function deleteActiveOrderItem($orderId, $barcodeStr)
	{
		global $_order_db;
		global $_settings;
	
		return $_order_db->deleteOrderItemsByOrderId(
				$orderId,
				$_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE],
				$barcodeStr
		);
	}
		
// 	function selectOrderItemByBarcode($orderId, $barcodeStr, $createNew = false)
// 	{
// 		global $_settings;
		
// 		$activeOrderItem = getActiveOrderItem($orderId, $barcodeStr);
// 		if(is_null($activeOrderItem) && $createNew)
// 		{
// 			$orderItem = new OrderItem();
// 			$orderItem->orderId = $orderId;
// 			$orderItem->designId = $design->id;
// 			$orderItem->barcode = $barcode->barcode;
// 			$orderItem->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE];
			
// 			//TODO:
// 			$orderItem->plasticCategoryId = 99999;
// 			$orderItem->color = "black";
// 			$orderItem->shapeId = "O_o";
				
// 			if($_order_db->createOrderItem($orderItem))
// 			{
// 				if(!$barcode->isMaster())
// 				{
// 					$barcode->dateUsed = time();
// 					$_order_db->updateBarcode($barcode);
// 				}
			
// 				$_session->setActiveOrderId($order->id);
// 				$_session->setActiveOrderItemId($orderItem->id);
// 				$_session->setActiveDesignId($design->id);
// 				$_session->setActiveDesignImageId($design->imageId);
			
// 				Header("Location: http://".$_url."design_template_select.php");
// 				exit();
// 			}
// 			else
// 			{
// 				$errorHTML = "Internal processing error 1. Please try again.";
// 			}
// 		}
		
// 		return true;
// 	}
	
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