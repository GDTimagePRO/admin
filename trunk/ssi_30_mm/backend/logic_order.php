<?php
	require_once "session.php";
	require_once "logic_design.php";
	

	function selectOrderItem(OrderItem $orderItem)
	{
		global $_session;
		global $_order_db;
		global $_design_db;
				
		if(is_null($orderItem)) return false;

		if(!selectCustomer($orderItem->customerId)) return false;
		
		
		
		$designIds = $_design_db->getSortedDesignIdsByOrderItemId($orderItem->id);
		if(is_null($designIds)) return false;
	
		$barcode = $_order_db->getBarcodeByBarcode($orderItem->customerId, $orderItem->barcode);
		if(is_null($barcode)) return false;
		if($barcode->isUsed()) return false;
		
		if(!openDesignSet($designIds)) return false;

		return true;
	}
		
	function clearSelectedCustomer()
	{
		global $_session;		
		
		clearSelectedOrderItem();
		$_session->setActiveCustomerId("");
	}
	
	function selectCustomer($id)
	{
		global $_session;
		global $_order_db;
		
		clearSelectedCustomer();
		
		$customer = $_order_db->getCustomerById($id);
		if(is_null($customer)) return fasle;
		
		$_session->setActiveCustomerId($customer->id);
		return true;
	}
?>