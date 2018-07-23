<?php
require_once '../backend/startup.php';
require_once '../backend/db_order.php';

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

    $product = $system->db->order->getProductByCode($_GET['productCode']);

    if($product == NULL) {
        $result->errorMessage = "Unable to find product";
        echo json_encode($result);
    } else {
		$result->errorCode = 0;
        $result->product = $product;
        echo json_encode($product);
    }

?>
