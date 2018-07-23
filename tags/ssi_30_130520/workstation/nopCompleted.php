<?php

include "_common.php";

//$json = $_POST['']
//var_dump($_POST);

$orderId = $_GET['orderId'];
$designs = $_GET['designs'];

$s = "Order Id: ".$orderId."<br>";
$d = explode("|",$designs);
$count = 1;
foreach($d as $designId){
	if($designId!=""){
		$b = explode("/",$designId);
		$b2 = explode("_",$b[1]);
		$designId = $b2[0];
		//$designId = preg_replace(preg_quote("thumbs.order_items/"),"",$designId);
		//$designId = preg_replace(preg_quote("_prev.png"),"",$designId);
		$s.= $designId."<br>";
		$_workstation_db->updateManufacturerOrderId($designId,$orderId*1000+$count);
		$count++;
		$_workstation_db->updateStatus($designId,ProcessingStage::STAGE_PENDING_RENDERING);
	}
}

echo $s;
//mail("cmcguinn@uoguelph.ca","NOPCompleted",$s);
//file_put_contents("nopCompleted.txt",$s);

?>