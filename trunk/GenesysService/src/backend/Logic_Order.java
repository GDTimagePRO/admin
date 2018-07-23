package backend;

public class Logic_Order {
	function selectOrderItem(OrderItem orderItem)
	{
		global _session;
		global _order_db;
		global _design_db;
				
		if(orderItem == null) return false;

		if(!selectCustomer(orderItem.customerId)) return false;
		
		
		
		String designIds = _design_db.getSortedDesignIdsByOrderItemId(orderItem.id);
		if(designIds == null) return false;
	
		barcode = _order_db.getBarcodeByBarcode(orderItem.customerId, orderItem.barcode);
		if(barcode == null) return false;
		if(barcode.isUsed()) return false;
		
		if(!openDesignSet(designIds)) return false;

		return true;
	}
		
	function clearSelectedCustomer()
	{
		global _session;		
		
		clearSelectedOrderItem();
		_session.setActiveCustomerId("");
	}
	
	Boolean selectCustomer(String id)
	{
		global _session;
		global _order_db;
		
		clearSelectedCustomer();
		
		customer = _order_db.getCustomerById(id);
		if(customer == null) return false;
		
		_session.setActiveCustomerId(customer.id);
		return true;
	}
}
