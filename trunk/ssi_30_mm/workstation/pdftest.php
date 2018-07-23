<?php
set_time_limit(6000);
ini_set("max_execution_time","6000");
function writeProgress($progress,$userID){
	$json = json_encode($progress);
	file_put_contents("output/".$userID."status.txt",$json);
}
$id = $_GET['user'];

$ids_get = $_GET['id'];

$userID = $_GET['user'];
$ids = explode("|",$ids_get);


$x = 5;
$y = 6;
$pdf = new PDF('L','mm',array($width,$height) );
$pdf->addPage();
$pdf->setSourceFile("output/0output.pdf");
$tplIdx = $pdf->importPage(1);
// use the imported page and place it at point 10,10 with a width of 100 mm
$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);

$t = $_GET['s'];
$progress = array();
$progress['total'] = min(sizeof($ids),29);
$progress['current'] = $t;
//$s = 0;
writeProgress($progress,$userID);
for($i=0;$i<5&&$s < sizeof($ids)-1;$i++){
	$s = $t+$i;
	$parts = explode(" ",$ids[$s]);
	$file = "http://www.in-stamp.com/masonrow/design_part/get_image.php?id=".$parts[0];
	$pwidth = 41;
	$pheight = 41;
	//$file_parts = explode("-",$file);
	$order_id = ltrim($parts[1],"0");
	$ix = $x+($pwidth+5)*($s%6);
	$iy = $y+($pheight+3)*(floor($s/6));
	$pdf->Image($file,$ix,$iy,$pwidth,$pheight,"PNG");
	$pdf->setFont("Arial","",8);
	$pdf->RotatedText($ix+$pwidth+3,$iy+$pheight,"Order # ".$order_id,90);
	$progress['current']++; 
	$_workstation_db->updateStatus($order_id,ProcessingStage::STAGE_ARCHIVED);
	writeProgress($progress,$userID);
}
$pdf->Output("output/0output.pdf");
if($s < 29&&$s < sizeof($ids)){
	Header("Location: pdf.php?s=".($s+1)."&id=".$ids_get."&user=".$userID);
}
?>
