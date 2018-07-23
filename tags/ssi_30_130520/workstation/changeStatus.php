<?php
require_once("_common.php");
//$pageNum = file("page.txt");
$ids_get = $_POST['id'];
//echo $ids."<br>";
$userID = $_POST['user'];
$ids = explode("|",$ids_get);
$status = $_POST['status'];
for($i=0;$i<sizeof($ids);$i++){
	$parts = explode(" ",$ids[$i]);
	$order_id = $parts[3];
	$_workstation_db->updateStatus($order_id,$status);
}