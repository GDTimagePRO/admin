<?php
	require_once '../backend/startup.php';

	header('Cache-Control: no-store, private, no-cache, must-revalidate');
	header('Access-Control-Allow-Origin: https://checkout.shopify.com');
	header('Cache-Control: pre-check=0, post-check=0, max-age=0, max-stale = 0', false);
	header('Pragma: public');
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
	header('Expires: 0', false);
	header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Pragma: no-cache');

	$result = new stdClass();
	$result->errorCode = 1;
	$result->errorMessage = '';

	$system = Startup::getInstance();
	$orderItem = $system->db->order->getOrderItemById($_GET['orderItemId']);
	if(!is_null($orderItem))
	{
		if($orderItem->processingStagesId == ProcessingStage::STAGE_PENDING_CART_ORDER /*|| strrpos($_SERVER["HTTP_REFERER"], "shopify") !== FALSE*/)
		{
			if(isset($_GET['externalOrderId'])) $orderItem->externalOrderId = $_GET['externalOrderId'];
			if(isset($_GET['externalUserId'])) $orderItem->externalUserId = $_GET['externalUserId'];
			if(isset($_GET['externalOrderStatus'])) $orderItem->externalOrderStatus = $_GET['externalOrderStatus'];
			if(isset($_GET['externalOrderOptions'])) $orderItem->externalOrderOptions = $_GET['externalOrderOptions'];
			if(isset($_GET['confirm']) && ($_GET['confirm'] == 'true'))
			{
				$orderItem->processingStagesId = ProcessingStage::STAGE_PENDING_RENDERING;
			}

			if($system->db->order->updateOrderItem($orderItem))
			{
				$result->errorCode = 0;
				$system->db->order->insertExternalOrder($orderItem);
			}
			else $result->errorMessage = "Unable to update order item.";


			if (isset($_GET['shippingInfo']))
			{
				if (!$system->db->shipping->commitShippingInformationJSON($_GET['shippingInfo'], $orderItem->id))
				{
					$result->errorCode = 1;
					$result->errorMessage = "Unable to add shipping information.";
				}
			}

		}
		else $result->errorMessage = 'Invalid processing stage';

	}
	else $result->errorMessage = 'Invalid order item id';

	echo json_encode($result);
?>
