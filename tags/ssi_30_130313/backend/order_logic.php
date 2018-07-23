<?php
	include_once "email.php";
	include_once "design_logic.php";

	//TODO: Using a barcode should deselect the order item	
	
	function submitOrderItem($orderItemId, $sendEmail = true)
	{
		global $_order_db;
	
		$orderItem = $_order_db->getOrderItemById($orderItemId);
		if(is_null($orderItem)) return false;
	
		if($sendEmail) sendDesignImageEmail($orderItemId);
	
		$barcode = $_order_db->getBarcodeByBarcode($orderItem->barcode);
		if(is_null($barcode)) return false;
		if(!$barcode->isMaster())
		{
			$barcode->dateUsed = date("Y-m-d H:i:s", time());
			$_order_db->updateBarcode($barcode);
		}
	
		$orderItem->processingStagesId = 9999; //TODO: fix this
		return $_order_db->updateOrderItem($orderItem); 
	}
	
	
	function selectOrderItem($orderItem)
	{
		global $_session;
		global $_order_db;
		global $_design_db;
		
		if(is_null($orderItem)) return false;
			
		$design = $_design_db->getDesignById($orderItem->designId);
		if(is_null($design)) return false;
	
		$barcode = $_order_db->getBarcodeByBarcode($orderItem->barcode);
		if(is_null($barcode)) return false;
			
		if($barcode->isUsed()) return false;
	
		$_session->setActiveOrderId($orderItem->orderId);
		$_session->setActiveOrderItemId($orderItem->id);
		$_session->setActiveDesignId($design->id);
		$_session->setActiveDesignImageId($design->imageId);
		
		if($barcode->templateId >= 0)
		{		
			$_session->setDesignMode(Session::DESIGN_MODE_SIMPLE);
			$_session->setEnableTemplateBrowser(false);
		}
		else if($barcode->templateCategoryId >= 0)
		{
			$_session->setDesignMode(Session::DESIGN_MODE_SIMPLE);
			$_session->setEnableTemplateBrowser(true);
		} 
		else
		{
			$_session->setDesignMode(Session::DESIGN_MODE_FULL);
			$_session->setEnableTemplateBrowser(true);
		}		
		
		return true;
	}

	
	function createOrderItem($orderId, $barcodeStr)
	{
		global $_order_db;
		global $_design_db;
		global $_settings;
		
		$order = $_order_db->getOrderById($orderId);
		if(is_null($order)) return NULL;
		
		$barcode = $_order_db->getBarcodeByBarcode($barcodeStr);
		if(is_null($barcode)) return NULL;
		
		$templateId = $barcode->templateId;
		if($templateId < 0)
		{
			$product = $_order_db->getProductById($barcode->productId);
			if(is_null($product)) return NULL;
			
			$templateId = $_design_db->getDefualtDesignTemplateId($product->productTypeId);
			if($templateId < 0) return NULL;
		}
		
		$design = createDesign($templateId, $order->userId);
		if(is_null($design)) return NULL;
				
		$orderItem = new OrderItem();
		$orderItem->orderId = $order->id;
		$orderItem->designId = $design->id;
		$orderItem->barcode = $barcode->barcode;
		$orderItem->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE];
		$orderItem->plasticCategoryId = 99999;
		$orderItem->color = "black";
		$orderItem->shapeId = "O_o";
			
		if(!$_order_db->createOrderItem($orderItem))
		{
			deleteDesign($orderItem->designId);
			return NULL;
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
		
	function selectOrderItemByBarcode($orderId, $barcodeStr, $createNew = false)
	{
		global $_settings;
		
		$activeOrderItem = getActiveOrderItem($orderId, $barcodeStr);
		if(is_null($activeOrderItem) && $createNew)
		{
			$orderItem = new OrderItem();
			$orderItem->orderId = $orderId;
			$orderItem->designId = $design->id;
			$orderItem->barcode = $barcode->barcode;
			$orderItem->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE];
			
			//TODO:
			$orderItem->plasticCategoryId = 99999;
			$orderItem->color = "black";
			$orderItem->shapeId = "O_o";
				
			if($_order_db->createOrderItem($orderItem))
			{
				if(!$barcode->isMaster())
				{
					$barcode->dateUsed = time();
					$_order_db->updateBarcode($barcode);
				}
			
				$_session->setActiveOrderId($order->id);
				$_session->setActiveOrderItemId($orderItem->id);
				$_session->setActiveDesignId($design->id);
				$_session->setActiveDesignImageId($design->imageId);
			
				Header("Location: http://".$_url."design_template_select.php");
				exit();
			}
			else
			{
				$errorHTML = "Internal processing error 1. Please try again.";
			}
		}
		
		return true;
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
		$_session->setActiveDesignImageId("");		
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