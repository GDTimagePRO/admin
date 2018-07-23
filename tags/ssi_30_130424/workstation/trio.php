<?php
define('FPDF_FONTPATH','./font');
require_once('fpdf.php');

$width = 108;
$height = 140;
$s = $_GET['s'];
$folder = "toRun/";
$files = scandir($folder);
$numPages = 8;
for($page = $s;$page < sizeof($files)/($numPages*3);$page++){
	$start = $page*$numPages*3;
	$pdf = new FPDF('P','mm',array($width,$height) );
	$pdf->setTopMargin(6);
	$pdf->setLeftMargin(18);
	$pdf->AddPage();
	$x = 5;
	$y = 6;
	$imageheight = 0;
	$pdf->SetDrawColor(0,0,0);
	$pdf->setLineWidth(0.5);
	$pdf->setFont("Arial","",15);
	
	//print_r($files);
	//foreach($files as $file){
	
	  
	for($i=2+$start;$i<$start+$numPages*3&&$i<sizeof($files);$i=$i+3){
		for($j=0;$j<3;$j++){
			if($i+$j < sizeof($files)){
				$file = $files[$i+$j];
				switch($j){
					case 0:
						$x = 7.422;
						$y = $height-80.883-46;
						$tx = 57;
						$ty = 20;
						break;
					case 1:
						$x = 54;
						$y = $height-46.917-46;
						$tx = 15;
						$ty = 65;
						break;
					case 2:
						$x = 7.422;
						$y = $height-10.249-46;
						$tx = 57;
						$ty = 115;
				}
				if(!is_dir($file)){	
					$pwidth = 46;
					$pheight = 46;
					$fwidth = 0;
					$fheight = 0;
					$twidth = $pwidth;
					$theight = $pheight;
					$dwidth = 0;
					$dheight = 0;
					if($fwidth > 0){
						$twidth = $fwidth;
						$theight = $fheight;
						$dwidth = ($fwidth - $pwidth)/2;
						$dheight = ($fheight - $pheight)/2;
						
					}
					$file_parts = explode("-",$file);
					$order_id = ltrim($file_parts[0],"0");
					$design_id = ltrim($file_parts[1],"0");
					if($fwidth > 0){
						
						//$pdf->Rect($x,$y,$fwidth,$fheight);
					}
					//$pdf->Image("http://www.cameronmcguinness.com/ssi/getproductimage.php?id=".$id,$x+$dwidth,$y+$dheight,$pwidth,$pheight,"JPEG");
					$pdf->Image($folder."/".$file,$x+$dwidth,$y+$dheight,$pwidth,$pheight,"PNG");
					//$pdf->setFont("Arial","",15);
					/*$pdf->Text($tx,$ty,"Forever");
					$pdf->Text($tx,$ty+6,"Black Ink");
					$pdf->setFont("Arial","",8);
					$pdf->Text($tx,$ty+10,"Template: ".$file_parts[3]);
					$pdf->Text($tx,$ty+14,"Order #: ".$order_id);
					$pdf->Text($tx,$ty+18,"Design #: ".$design_id);*/
					
					/*$text = "Color: Blue";//.$item['color'];
					$textX = $x+$fwidth - $pdf->GetStringWidth($text);
					$pdf->Text($textX,$y+$fheight+3,$text);*/
					$x+=$twidth+5;
					if($theight > $imageheight){
						$imageheight=$theight+20;
					}
					if($j==2){
						$pdf->setFont("Arial","",15);
						$pdf->Text(61.641,$height-130.292,"Order #: ".$order_id);
					}
					if($j==2&&$i+$j<sizeof($files)&&$i+$j<$start+$numPages*3){
						
						
						$pdf->addPage();	
					}
				}
			}
		}	
	}
	
	
	$pdf->Output("output/trio".$page.".pdf");
}

echo "Complete";
?>