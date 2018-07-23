<?php
class Genesys_Redirect_Order_Observer
{
   
	public function export_new_order($observer)
    {
		$debug = false;
		$event = $observer->getEvent();
		$model = $event->getOrder();
		$genesysUrl = "http://" . Mage::getStoreConfig('Genesys_Redirect/genesys_group/genesys_url') . "/services/update_order_item.php?confirm=true&externalOrderId=%s&orderItemId=%d&shippingInfo=%s";
		$shipping = $model->getShippingAddress();

		$shippingObject = new stdClass;
		$shippingObject->first_name = $shipping->getFirstname();
		$shippingObject->last_name = $shipping->getLastname();
		$shippingObject->address_1 = $shipping->getStreet1();
		$shippingObject->address_2 = $shipping->getStreet2();
		$shippingObject->city = $shipping->getCity();
		$shippingObject->state_province = $shipping->getRegion();
		$shippingObject->zip_postal_code = $shipping->getPostcode();
		$shippingObject->country = $shipping->getCountry();
		$shippingAddress = urlencode(json_encode($shippingObject));
		$orderItems = $model->getAllItems();
		
		foreach ($orderItems as $item) {
			$info = $item->getProductOptions();
			$info = $info['info_buyRequest'];
			if (isset($info['orderDetails'])) {
				$genesys_data = json_decode($info['orderDetails'], true);
				$orderItemId = $genesys_data['orderItemId'];
				$externalOrderId = $model->getId();
				$jsonUrl = sprintf($genesysUrl, $externalOrderId, $orderItemId, $shippingAddress);
				$json = file_get_contents($jsonUrl);
				$json_object = json_decode($json, true);
				if ($json_object['errorCode'] != 0) {
					Mage::log('Error communicating with genesys');
					Mage::log($json_object);
					Mage::log($jsonUrl);
				} else if ($debug) {
					Mage::log('Success');
				}
			}
		}
	}

}
?>