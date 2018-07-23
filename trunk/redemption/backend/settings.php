<?php 
class Settings
{
	const CUSTOMER_ID							= 1; 
	const SYSTEM_NAME							= 'Redemption'; 
	
	const ADMIN_LOGIN							= 'admin'; 
	const ADMIN_PASSWORD						= 'woot';
	
	const DB_SERVER								= 'localhost';
	const DB_USER_NAME							= 'root';
	const DB_PASSWORD							= 'abc123';
	const DB_SCHEMA_NAME						= 'redemption_db';

	const HOME_URL								= 'http://localhost/redemption/';
	
	const SERVICE_GENESYS_UPDATE_ORDER_ITEM		= 'http://localhost/ssi_30_mm/services/update_order_item.php';
	const SERVICE_GENESYS_INIT					= 'http://localhost/ssi_30_mm/SetUp.php';
	const SERVICE_GET_IMAGE						= 'http://localhost:8080/ARTServer/GetImage';
	const SERVICE_SEND_MAIL						= 'http://localhost:8080/ARTServer/SendMail';
	
	const ERROR_CODE_OK = 0;
	
	const EMAIL_FROM_EMAIL						= 'redeem@masonrow.com';
		
	public static function genesysUpdateOrderItem($orderItemId, $confirm = FALSE, $externalOrderId = NULL, $externalUserId = NULL, $externalOrderStatus = NULL, $shippingDetails = NULL)
	{
		$url = Settings::SERVICE_GENESYS_UPDATE_ORDER_ITEM;
		$url .= '?orderItemId=' . $orderItemId;
		
		if($confirm) $url .= '&confirm=true';

		if(!is_null($externalOrderId)) $url .= '&externalOrderId=' . urlencode($orderItemId);
		if(!is_null($externalUserId)) $url .= '&externalUserId=' . urlencode($externalUserId);
		if(!is_null($externalOrderStatus)) $url .= '&externalOrderStatus=' . urlencode($externalOrderStatus);
		if (!is_null($shippingDetails)) {
			$output = new stdClass();
			$output->first_name = $shippingDetails->firstName;
			$output->last_name = $shippingDetails->lastName;
			$output->address_1 = $shippingDetails->unit . ' ' . $shippingDetails->street;
			$output->address_2 = '';
			$output->city = $shippingDetails->city;
			$output->state_province = $shippingDetails->region;
			$output->zip_postal_code = $shippingDetails->zip;
			$output->country = $shippingDetails->country;
			$output->email = $shippingDetails->email;
			
			$url .= '&shippingInfo=' . urlencode(json_encode($output));
		}
		$response = file_get_contents($url);
		return json_decode($response);
	}
	
	public static function getImageUrl($imageId, $noCaching = false)
	{
		if($noCaching)
		{
			return Settings::SERVICE_GET_IMAGE . '?nocache=true&id=' . urlencode($imageId);
		}
		else
		{
			return Settings::SERVICE_GET_IMAGE . '?id=' . urlencode($imageId);
		}
	}
}
?>
