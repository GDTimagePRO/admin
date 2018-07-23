<?php
require_once("_common.php");
$id = $_GET['user'];
//$pageNum = file("page.txt");
$ids_get = $_GET['id'];
//echo $ids."<br>";
$userID = $_GET['user'];
$ids = explode("|",$ids_get);
switch ($_GET['status']){
		case 0:
			$status = ProcessingStage::STAGE_READY;
			break;
		case 1:
			$status = ProcessingStage::STAGE_ARCHIVED;
			break;
	}
for($i=0;$i<sizeof($ids);$i++){
	$parts = explode(" ",$ids[$i]);
	$order_id = $parts[1];
	$_workstation_db->updateStatus($order_id,$status);
}