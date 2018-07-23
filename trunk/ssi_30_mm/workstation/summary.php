<?php

require_once 'Spreadsheet/Excel/Writer.php';
require_once "_common.php";
// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
$workbook->send('summary-'.date('Y-m-d').'.xls');
$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$ids = $_POST['id'];
$ids = explode("|",$ids);
$items = array();
foreach($ids as $id){
	if($id!=""){
		//echo 'Id: '.$id.'<br>';
		$ida = explode("^",$id);
		$order_id = $ida[3];
		$item = $_workstation_db->getItemById($order_id);
		//echo 'Order Id: '.$order_id.'<br>';
		//echo 'Material: '.$item->material.'<br>';
		$items[] = $item;
	}
}
$categories = array();
$per_category = array();
for($i=0;$i<sizeof($items);$i++){
	$material = $items[$i]->material;
	if(!in_array($material,$categories)){
		$categories[] = $material;
		//echo $material."<br>";
	}
}
$worksheets = array();
$currentRow = array();
foreach($categories as $category){
	$worksheets[$category] = &$workbook->addWorksheet($category);
	$currentRow[$category] = 1;
	$worksheets[$category]->write(0,0,'Submit Time',$format_bold);
	$worksheets[$category]->write(0,1,'Order ID',$format_bold);
	$worksheets[$category]->write(0,2,'Design ID',$format_bold);
	$worksheets[$category]->write(0,3,'Product',$format_bold);
	$worksheets[$category]->write(0,4,'Color',$format_bold);
}

foreach($items as $item){
	$material = $item->material;
	$current = $currentRow[$material];
	if($item->manufacturer_id != 0)
	{
		$orderId = substr($item->manufacturer_id, 0, -3) . '-' . substr($item->manufacturer_id, -3);
	}
	else
	{
		$orderId = 'i:'.$item->order_id;
	}
	$worksheets[$material]->write($current,0,$item->submit_time);
	$worksheets[$material]->write($current,1,$orderId);
	$worksheets[$material]->write($current,2,$item->design_id);
	$worksheets[$material]->write($current,3,$item->product_name);
	$worksheets[$material]->write($current,4,$item->color);
	$currentRow[$material]++;
}

$workbook->close();
?>