<?php
include_once "_common.php";

if($_user_id)
{
 	echo json_encode($_design_db->setDesignJSON($_POST['id'], $_POST['json']));
 	//$orderItem = $_order_db->getOrderItemById($_session->getActiveOrderItemId());
 	//$config = $orderItem->getConfig(); 
 	//$config->inkColor = $_POST['inkColor'];
 	//$orderItem->setConfig($config);
 	//$_order_db->updateOrderItem($orderItem);
}
?>