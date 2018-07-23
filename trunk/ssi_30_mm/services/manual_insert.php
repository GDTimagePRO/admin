<?php
	require_once '../backend/startup.php';
    require_once '../backend/db_order.php';
    require_once '../backend/db_design.php';

	header('Cache-Control: no-store, private, no-cache, must-revalidate');
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
	$design = new Design();
    $orderItem = new OrderItem();

    if(isset($_GET['externalOrderId'])) $orderItem->externalOrderId = $_GET['externalOrderId'];
	if(isset($_GET['customerId'])) $orderItem->customerId = $_GET['customerId'];
	if(isset($_GET['barcode'])) $orderItem->barcode = $_GET['barcode'];
	if(isset($_GET['externalSystem'])) $orderItem->externalSystemName = $_GET['externalSystem'];
    $orderItem->processingStagesId = 350;

    if($system->db->order->createOrderItem($orderItem)) {
        $design->orderItemId = $orderItem->id;
        $design->productTypeId = 2;
        $design->configJSON = $_GET['configJSON'];
        $design->designJSON = $_GET['designJSON'];
        $design->state = Design::STATE_PENDING_SCL_RENDERING;
        $design->productId = $_GET['productId'];
		$design->dateRendered = time();
		$design->externalDesignOptions = NULL;
        if(!$system->db->design->createDesign($design)) {
             $result->errorMessage = "Unable to create new design";
        } else {
            $result->errorCode = 0;
            if (isset($_GET['shippingInfo']))
            {
                if (!$system->db->shipping->commitShippingInformationJSON($_GET['shippingInfo'], $orderItem->id))
                {
                    $result->errorCode = 1;
                    $result->errorMessage = "Unable to add shipping information.";
                }
            }
        }
    } else {
         $result->errorMessage = "Unable to create new order";
    }

	echo json_encode($result);
?>
