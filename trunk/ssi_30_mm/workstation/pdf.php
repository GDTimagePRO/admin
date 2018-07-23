<?php
ini_set("max_execution_time",6000);
ini_set("default_socket_timeout",6000);
ini_set('memory_limit','512M');
ini_set("log_errors", 1);
ini_set("error_log", "output/error.log");
set_time_limit(6000);
global $layout,$width,$height,$marginx,$marginy,$rowMax,$current,$_order_db;
/*
 * NOTE: A lot of things in this file are hard-coded specifically for MasonRow. Eventually it would be needed to be read from the database.
 * NOTE: Using the userID as an id for the file may cause issues if more than one person is using it with the same log in at the same time.
 * 		 It would probably be better to use session_id when the log in stuff is finished and have the periodically deleted.
 * NOTE: There is likely a lot of room for efficiency improvements in this process. It was mostly just hacked together to get it working quickly. 
 */

define('FPDF_FONTPATH','./font');
require_once('fpdf.php');
require_once('fpdi.php');
require_once("_common.php");

/*
 * Add rotated text functionality to PDFs 
 * 
 *
 */
class PDF_Rotate extends FPDI
{
	var $angle=0;

	function Rotate($angle,$x=-1,$y=-1)
	{
		if($x==-1)
			$x=$this->x;
		if($y==-1)
			$y=$this->y;
		if($this->angle!=0)
			$this->_out('Q');
		$this->angle=$angle;
		if($angle!=0)
		{
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}

	function _endpage()
	{
		if($this->angle!=0)
		{
			$this->angle=0;
			$this->_out('Q');
		}
		parent::_endpage();
	}
}

class PDF extends PDF_Rotate
{
	function RotatedText($x,$y,$txt,$angle)
	{
		//Text rotated around its origin
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}

	function RotatedImage($file,$x,$y,$w,$h,$angle)
	{
		//Image rotated around its upper-left corner
		$this->Rotate($angle,$x,$y);
		$this->Image($file,$x,$y,$w,$h);
		$this->Rotate(0);
	}
}

function initDebug(){
	file_put_contents("output/debug.txt","");
}

function debug($message){
	if(file_exists("output/debug.txt")){
	 $blah = file_get_contents("output/debug.txt");
	}
	else{
	$blah = "";
	}
	$blah .= $message."\n";
	file_put_contents("output/debug.txt",$blah);
}

/*
 * CREATE LAYOUT JSON
 */


function interpretContent($content,$ids){
	global $current,$width,$height,$marginx,$marginy,$rowMax,$layout,$_order_db;
	foreach($content as $c){
		//echo $c->type."<br>";
		switch($c->type){
			case "dynamic":
				//echo "Dynamic<br>";
				$x = $marginx;
				$y = $marginy;
				$imageCount = 0;
				//$current->dynamic = true;
				$spacey = $c->spacey;
				$rowMax = 0;
				$spacing = 0;
				foreach($c->content as $part){
					if($part->type=="spacex"){
						$spacing+=$part->value;
					}
					else if($part->type=="text"){
						//$spacing+=10;
					}
				}
				foreach($ids as $id){
					//echo $id."<br>";
					$parts = explode("^",$id);
					$pid = trim($parts[4]);
					$product = $_order_db->getProductById($pid);
					$pwidth = $product->width;
					$pheight = $product->height;
					if($pheight > $rowMax){
						$rowMax = $pheight;
					}
					
					if($x+$pwidth+$spacing >= $width){
						$y+=$rowMax+$spacey;
						$x = $marginx;
						$rowMax = 0;
					}
					if($y+$pheight >= $height){
						//$add_in = array();
						$blah->type = "page";
						//$add_in[] = $blah;
						$layout[] = $blah;
						$y = $marginy;
						$x = $marginx;
							
					}
					
					foreach($c->content as $part){
						if($part->type=="image"){
							//$cu = clone $current;
							$image = clone $part;
							$image->value = $imageCount;
							if(!isset($image->x)){
								$image->x = $x;
							}
							if(!isset($image->y)){
								$image->y = $y;
							}
							if(isset($image->scale)){
								$scale = $image->scale;
							}
							else{
								$scale = 1;
							}
							$current->scale = $scale;
							$image->width = $pwidth;
							$image->height = $pheight;
							$x+= $pwidth;
							$layout[] = $image;
							$current->imageCount++;
						}
						else if($part->type=="spacex"){
							$x+=$part->value;
						}
						else if($part->type=="text"){
							//$cu = clone $current;
							$text = clone $part;
							if(!isset($text->x)){
								$text->x = $x;
							}
							if(!isset($text->y)){
								$text->y = $y;
							}
							else if($text->y < 0){
								$text->y = $y+$pheight;
							}
							//echo $text->y."<br>";
							$text->current = $imageCount;
							/*if(isset($part->offset)){
								$part->current +=$part->offset;
							}*/
							$layout[] = $text;
							//$x+=10;
						}
					}
					$imageCount++;
				}
				return;
			case "text":
				addText(clone $c,$ids);
				break;
			case "repeaty":
				$repeat = $c->value->repeat;
				for($i=0;$i<$repeat;$i++){
					interpretContent($c->value->content,$ids);
					$current->y+= $rowMax;
					
					$current->x = $marginx;
					if($current->imageCount-$current->fileCount*$current->maxImages > $current->maxImages){
						return;
					}
				}

				break;
			case "repeatx":
				$repeat = $c->value->repeat;
				for($i=0;$i<$repeat;$i++){
					interpretContent($c->value->content,$ids);
					if($current->imageCount-$current->fileCount*$current->maxImages > $current->maxImages){
						return;
					}
				}
				break;
			case "spacex":
				$current->x+=$c->value;
				if($current->x >=$width){
					$current->x = $marginx;
				}
					
				break;
			case "spacey":
				$current->y+=$c->value;
					
				break;
			case "image":
				addImage(clone $c,$ids);
				break;
			case "page":
				$layout[] = clone $c;
				break;
			case "box":
				$layout[] = clone $c;
				break;

		}
		if($current->imageCount-$current->fileCount*$current->maxImages > $current->maxImages){
			return;
		}
	}
}
/**
 *
 * * @param Object $text this contains the information for the text line
 *
 */
function addText($text,$ids){
	global $layout,$current,$_order_db,$rowMax;
	$c = clone $current;
	/*$parts = explode(" ",$ids[$c->imageCount+$text->offset]);
	$id = trim($parts[4]);
	$product = $_order_db->getProductById($id);
	//echo $product->width.",".$product->height."->".$id."|".$product->longName."|<br>";
	$pwidth = $product->width*$current->scale;
	$pheight = $product->height*$current->scale;*/
	//echo $pheight."|<br>";
	if(!isset($text->x)){
		$text->x = $c->x;
	}
	if(!isset($text->y)){
		$text->y = $c->y;
	}
	else if($text->y ==-1){
		$text->y = $c->y+$rowMax-5;
	}
	else if($text->y < 0){
		$text->y = $c->y+$text->y;
	}
	//echo $text->y."<br>";
	$text->current = $c->imageCount;
	if(isset($text->offset)){
		$text->current +=$text->offset;
	}
	$layout[] = $text;
}

function addImage($image,$ids){
	global $current,$layout,$width,$height,$marginx,$marginy,$rowMax,$_order_db;

	
	$a = array();
	$c = clone $current;
	$image->value = $c->imageCount;
	if(!isset($image->x)){
		$image->x = $c->x;
	}
	if(!isset($image->y)){
		$image->y = $c->y;
	}
	if(isset($image->scale)){
		$scale = $image->scale;
	}
	else{
		$scale = 1;
	}
	$current->scale = $scale;
	/*$parts = explode(" ",$ids[$c->imageCount]);
	$id = trim($parts[4]);
	$product = $_order_db->getProductById($id);*/
	//echo $product->width.",".$product->height."->".$id."|".$product->longName."|<br>";
	
	//echo "Adding image $current->imageCount at ($current->x,$current->y)<br>";
	$id = $ids[$current->imageCount];
	$parts = explode("^",$id);
	$pid = trim($parts[4]);
	$product = $_order_db->getProductById($pid);
	if($rowMax!=0){
		$pwidth = $rowMax*$scale;//$product->width*$scale;
		$pheight = $rowMax*$scale;//$product->height*$scale;
	}
	else{
		$pwidth = $product->width*$scale;
		$pheight = $product->height*$scale;
	}
	if($product->frameWidth==0){
		$product->frameWidth = 47.5;
		$product->frameHeight = 47.5;
	}
	$fwidth= $product->frameWidth;
	$fheight = $product->frameHeight;
	$fx = $image->x - ($fwidth-$pwidth);
	$fy = $image->y - ($fheight-$pheight);
	//error_log("Dims: (".$pwidth.",".$pheight."), (".$fwidth.",".$fheight.")");
	$image->x-=0.5;
	$image->y-=0.5;
	$image->fx = $fx;
	$image->fy = $fy;
	$image->frameWidth = $fwidth;
	$image->frameHeight = $fheight;
	if($rowMax==0){
		$image->x = $fx+$fwidth/2-$pwidth/2;
		$image->y = $fy+$fheight/2-$pheight/2;
		
	}
	/*if(file_exists("output/debug.txt")){
		$blah = file_get_contents("output/debug.txt");
	}
	else{
		$blah = "";
	}
	$blah .= "Layout Frame Around Image #".($current->imageCount)." - (".$fx.",".$fy.",".$product->frameWidth.",".$product->frameHeight.")\n";
	file_put_contents("output/debug.txt",$blah);*/
	$image->width = $pwidth;
	$image->height = $pheight;
	/*if($pheight > $rowMax){
		$rowMax = $pheight;
	}*/
	$current->x+= $fwidth;
	/*if($current->x+$width/10 >= $width){
		$current->x = $marginx;
		$current->y+=$rowMax;
	}*/
	if($image->down){
		$current->y+=$fheight;
	}
	if($current->counting&&$image->count){
		$current->imageCount++;
	}
	else if(!$current->counting){
		$current->imageCount++;
	}
	$layout[] = $image;
}

function writeProgress($progress,$userID,$filename){
	$json = json_encode($progress);
	file_put_contents("output/".$filename."status.txt",$json);
}

function pdfInterpretObject($object,&$pdf,$ids){
	global $current;
	switch($object->type){
		case "text":
			pdfAddText($object,$pdf,$ids);
			break;
		case "image":
			pdfAddImage($object,$pdf,$ids);
			break;
		case "page":
			$pdf->AddPage();
			break;
		case "box":
			$pdf->Rect($object->x,$object->y,$object->width,$object->height);
			break;
		case "file":
			pdfNewFile($pdf,++$current->fileCount);
			break;
	}
}

function pdfAddText($object,&$pdf,$ids){
	$x = $object->x;
	$y = $object->y;
	$orientation = $object->orientation;
	$text = $object->value;
	$do = true;
	if(preg_match("/#oid/",$text)){
		$current = $object->current;
		$id = $ids[$current];
		$parts = explode("^",$id);
		if($parts[1]==""){
			$do=false;
		}
		$text = preg_replace("/#oid/",$parts[1],$text);
	}
	if(preg_match("/#color/",$text)){
		$current = $object->current;
		$id = $ids[$current];
		$parts = explode("^",$id);
		$text = preg_replace("/#color/",$parts[2],$text);
	}
	if(preg_match("/#inc/",$text)){
		$inc = file_get_contents("output/0inc.txt");
		$text = preg_replace("/#inc/",$inc,$text);
		$inc++;
		file_put_contents("output/0inc.txt",$inc);
	}
	//var_dump($object);
	//echo "|".$object->fontFamily."|<br>";
	$pdf->setFont($object->fontFamily,$object->fontAttributes,$object->fontSize);
	if($do){
		$pdf->RotatedText($x,$y,$text,$orientation);
	}
}

function pdfAddImage($object,&$pdf,$ids){
	global $rowMax,$current,$_workstation_db;
	$x = $object->x;
	$y = $object->y;
	//echo $x." ".$y."<br>";
	$image = $object->value;
	//echo $image."<br>";
	$id = $ids[$image];
	//echo $id."<br>";
	if($id!=""){
		$parts = explode("^",$id);
		//$file = "http://www.in-stamp.com.loucks51.arvixevps.com/masonrow/design_part/get_image.php?id=".$parts[0];
		require_once '../backend/settings.php';
		//$file = Settings::getImageUrl($parts[0], TRUE);
		$file = Settings::SERVICE_GET_IMAGE."?id=".preg_replace("/#id/",$parts[5],$object->link);
		
		if(isset($object->scale)){
			$scale = $object->scale;
		}
		else{
			$scale = 1;
		}
		//$pwidth = $rowMax*$scale;
		//$pheight = $rowMax*$scale;
		$pwidth = $object->width;
		$pheight = $object->height;
		//error_log("dims :(".$pwidth.",".$pheight.")");
		$fx = $object->fx;
		$fy = $object->fy;
		$fwidth = $object->frameWidth;
		$fheight = $object->frameHeight;
		//error_log("f-dims :(".$fwidth.",".$fheight.")");
		if($rowMax == 0&&$fwidth!=0){
			$x = $fx;
			$y = $fy;
			$pwidth = $fwidth;
			$pheight = $fheight;
		}
		try {
			$pdf->Image($file,$x,$y,$pwidth,$pheight,"PNG");
			//$pdf->Image($file,$x,$y,-Settings::HD_IMAGE_DPI,-Settings::HD_IMAGE_DPI,"PNG");
		} catch (Exception $e) {
			debug("Failed to add image ".$file);
		}
		if($pdf->statusChange){
			//echo "changing the status of ".$parts[3]."<br>";
			$_workstation_db->updateStatus($parts[3],ProcessingStage::STAGE_PRINTED);
		}
		if(!$pdf->counting){
			$current->imageCount++;
		}
		else if ($object->count){
			$current->imageCount++;
		}
		if($pdf->frame){
			$pdf->SetDrawColor(0,0,0);
			$pdf->setLineWidth(0.3);
			debug("Image: " . $x . " " . $y . " " . $pwidth . " " . $pheight);
			debug("Frame: " . $fx . " " . $fy . " " . $fwidth . " " . $fheight);
			$pdf->Rect($fx,$fy,$fwidth,$fheight);
		}
		if($pdf->cutLines){
			$cutLineBuffer = 0.5;
			$pdf->SetDrawColor(255,0,0);
			$pdf->setLineWidth(0.0762);
			$pdf->Line($x-$cutLineBuffer,$y-$cutLineBuffer,$x+$pwidth+$cutLineBuffer,$y-$cutLineBuffer);
			$pdf->Line($x-$cutLineBuffer,$y+$pheight+$cutLineBuffer,$x+$pwidth+$cutLineBuffer,$y+$pheight+$cutLineBuffer);
			$pdf->Line($x-$cutLineBuffer,$y+$cutLineBuffer,$x-$cutLineBuffer,$y+$pheight-$cutLineBuffer);
			$pdf->Line($x+$pwidth+$cutLineBuffer,$y+$cutLineBuffer,$x+$pwidth+$cutLineBuffer,$y+$pheight-$cutLineBuffer);
		}
	}
	
}

function pdfNewFile(&$pdf,$file){
	//echo "New File - ".$file."<br> ";
	$pdf->output("output/".$pdf->fileName."_".($file-1).".pdf");
	$orientation = $pdf->CurOrientation;
	$cutLines = $pdf->cutLines;
	$statusChange =$pdf->statusChange;
	$frame = $pdf->frame;
	$counting = $pdf->counting;
	$width = $pdf->w;
	$height = $pdf->h;
	$filename = $pdf->fileName;
	$pdf = new PDF($orientation,'mm',array($width,$height) );
	$pdf->SetDrawColor(0,0,0);
	$pdf->setLineWidth(0.2);
	$pdf->addPage();
	$pdf->cutLines = $cutLines;
	$pdf->fileName = $filename;
	$pdf->statusChange = $statusChange;
	$pdf->frame = $frame;
	$pdf->counting = $counting;
	/*$pdf->setSourceFile("output/0output_".$file.".pdf");
	$tplIdx = $pdf->importPage(1);
	//echo $width."  ".$height;
	$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);
	$pageCount = 1;
	$pdf->addPage();
	$tplIdx = $pdf->importPage(1);
	$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);*/
}

initDebug();
if(isset($_POST['template'])){
	$_GET['template'] = $_POST['template'];
}

$template = $_GET['template'];
switch($template){
	case 0: //polymer
		//include "pdftest.php";
		//$temp = '{"orientation":"L","width":305,"height":229,"marginx":5,"marginy":6,"maxPages":1,"maxImages":1,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"repeaty","value":{"repeat":1,"content":[{"type":"repeatx","value":{"repeat":1,"content":[{"type":"image"},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-41,"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}}]}';
		//$temp = '{"status": true,"filename": "polymer","orientation":"L","width":305,"height":229,"marginx":5,"marginy":6,"maxPages":1,"maxImages":30,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image"},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-41,"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}}]}';
		$temp = '{"background": "black",status": true,"filename": "polymer","orientation":"L","width":279.4,"height":215.9,"marginx":10,"marginy":10,"maxPages":1,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image","link": "inverted.designs%2F#id_hd.png"},{"type":"spacex","value":3}]}},{"type":"spacey","value":3}]}}]}';
		$rowMax = 39;		
		break;
	case 1: //trio
		$temp = '{"status": true,"frame": true,"filename": "trio","orientation":"P","width":108,"height":140,"marginx":10,"marginy":10,"maxPages":8,"maxImages":24,"content":[{"type":"repeaty","value":{"repeat":8,"content":[{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontSize":"15","fontAttributes":"","x":61.641,"y":9.708,"offset": 0},{"type":"text","value":"#color","fontFamily":"Arial","fontSize":"10","fontAttributes":"","x":57,"y":20,"offset": 0},{"type":"image","x":7.422,"y":13.117,"link": "original.designs%2F#id_hd.png"},{"type":"text","value":"#color","fontFamily":"Arial","fontSize":"10","fontAttributes":"","x":10,"y":75,"offset": 0},{"type":"image","x":56,"y":47.083,"link": "original.designs%2F#id_hd.png"},{"type":"text","value":"#color","fontFamily":"Arial","fontSize":"10","fontAttributes":"","x":57,"y":115,"offset": 0},{"type":"image","x":7.422,"y":83.751,"link": "original.designs%2F#id_hd.png"},{"type":"page"}]}}]}';
		$rowMax = 44;
		break;
	case 2: //trio index cards
		$temp = '{"filename": "trioIndex","orientation":"P","width":54.45,"height":86.2,"marginx":5,"marginy":6,"maxPages":8,"maxImages":24,"content":[{"type":"repeaty","value":{"repeat":8,"content":[{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontSize":"6","fontAttributes":"","x":5,"y":4},{"type":"image","x":16.43,"y":8,"scale":0.46934782608696,"link": "original.designs%2F#id_hd.png"},{"type":"image","x":16.43,"y":32.59,"scale":0.46934782608696,"link": "original.designs%2F#id_hd.png"},{"type":"image","x":16.43,"y":57.18,"scale":0.46934782608696,"link": "original.designs%2F#id_hd.png"},{"type":"box","x":5,"y":5,"width":44.45,"height":76.2},{"type":"text","value":"1","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":10},{"type":"text","value":"2","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":40.6},{"type":"text","value":"3","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":71.2},{"type":"page"}]}}]}';
		$rowMax = 46;
		break;
	case 3: //embossers
		//$temp = '{"status": true,"filename": "embosser","orientation":"P","width":76.2,"height":76.2,"marginx":5,"marginy":6,"maxPages":30,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":30,"content":[{"type":"image","x":17.6,"y":17.6},{"type":"page"}]}}]}'; 
		//embosser_m.designs%2F180_hd.png
		$temp = '{"status": true,"cutLines": true,"counting": true,"filename": "embosser","orientation":"L","width":279.4,"height":215.9,"marginx":10,"marginy":10,"maxPages":5,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":3,"content":[{"type":"image","link": "embosser_m.designs%2F#id_hd.png", "count": false},{"type":"spacex","value":1},{"type":"image","link": "embosser_f.designs%2F#id_hd.png","count": true},{"type":"spacex","value":1}]}},{"type":"spacey","value":2}]}}]}';
		$rowMax = 39;
		break;
	case 4: //embossers index cards
		//$temp = '{"filename": "embosserIndex","orientation":"P","width":76.2,"height":76.2,"marginx":5,"marginy":6,"maxPages":30,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":30,"content":[{"type":"image","x":21.7,"y":21.7,"scale":0.8,"link": "original.designs%2F#id_hd.png"},{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontAttributes":"","fontSize":15,"x":22,"y":15, "offset": -1},{"type":"page"}]}}]}';
		$temp = '{"filename": "embosserIndex","orientation":"L","width":279.4,"height":215.9,"marginx":2,"marginy":2,"maxPages":30,"maxImages":900,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":3,"content":[{"type":"image","link": "original.designs%2F#id_hd.png"},{"type": "spacex","value": 2},{"type":"text","orientation": 90,"value":"Order # #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-1, "offset": -1},{"type":"spacex","value":40}]}},{"type":"spacey","value":2}]}}]}';
		$rowMax = 39;
		break;
	case 5: //laser
		$temp = '{"status": true,"filename": "laser","orientation":"L","width":279.4,"height":215.9,"marginx":10,"marginy":10,"maxPages":1,"maxImages":30,"cutLines":true,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image","link": "original.designs%2F#id_hd.png"},{"type":"spacex","value":1}]}},{"type":"spacey","value":2}]}}]}';
		$rowMax = 39;
		break;
	case 6: //laser index cards
		$temp = '{"filename": "laserIndex","orientation":"L","width":279.4,"height":215.9,"marginx":2,"marginy":2,"maxPages":30,"maxImages":3000,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image", "scale": 0.8,"link": "original.designs%2F#id_hd.png"},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-1,"offset": -1},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Color: #color","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":'.(-39*0.8).',"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}},{"type":"page"}]}';
		$rowMax = 41;
		break;
	case 7: //dynamic polymer
		$temp = '{"status": true,"filename": "dynamicPolymer","orientation":"L","width":305,"height":229,"marginx":10,"marginy":10,"maxPages":1,"maxImages":30,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"dynamic","spacey": 0,"content":[{"type":"image","link": "inverted.designs%2F#id_hd.png"}]}]}';
		$rowMax = 0;
		break;
	case 8: //dynamic laser
		$temp = '{"status": true,"cutLines":true,"filename": "dynamicLaser","orientation":"L","width":279.4,"height":215.9,"marginx":10,"marginy":10,"maxPages":1,"maxImages":30,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"dynamic","spacey": 2,"content":[{"type":"image","link": "original.designs%2F#id_hd.png"},{"type":"spacex","value":1}]}]}';
		$rowMax = 0;
		break;
	case 9: //dynamic index
		$temp = '{"filename": "dynamicIndex","orientation":"L","width":279.4,"height":215.9,"marginx":10,"marginy":10,"maxPages":1,"maxImages":30,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"dynamic","spacey": 2,"content":[{"type":"image", "scale": 0.8,"link": "original.designs%2F#id_hd.png"},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-1,"offset": -1},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Color: #color","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":'.(-41*0.8).',"offset": -1},{"type":"spacex","value":1}]}]}';
		$rowMax = 0;
		break;
	case 10: //MR Canada trio
		$temp = '{"status": true,"frame": true,"filename": "mrcantrio","orientation":"P","width":215.9,"height":279.4,"marginx":19.05,"marginy":10,"maxPages":8,"maxImages":96,"content":[{"type":"repeaty","value":{"repeat":4,"content":[{"type": "spacey", "value": 19.225},{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontSize":"10","fontAttributes":"","offset": 0,"y": -5},{"type": "spacex", "value": 41.275},{"type": "repeatx", "value":{"repeat":3,"content":[{"type":"text","value":"#color","fontFamily":"Arial","fontSize":"10","fontAttributes":"","y":-5,"offset": 0},{"type":"image","link": "original.designs%2F#id_hd.png"}]}},{"type": "spacey", "value": 4}]}},{"type": "page"}]}';
		$rowMax = 44;
		break;
		
	case 11: //Dynamic Trio
		$temp = '{"status": true,"frame": true, "filename": "dynamicTrio", "orientation": "P","width": 108, "height": 140,"marginx": 20, "marginy": 10,"maxPages": 8, "maxImages": 24,"content": [{"type": "repeaty", "value":{"repeat": 3,"content": [{"type":"text","value": "Order # #oid","fontFamily":"Arial","fontSize":"10","fontAttributes":"","offset": 0},{"type": "spacex", "value": 30}, 
								{"type": "text","value":"#color","fontFamily":"Arial","fontSize":"10","fontAttributes":"","offset": 0},
								{"type": "spacey", "value": 10},{"type": "spacex", "value": -20},
								{"type": "image","link": "original.designs%2F#id_hd.png","down": true}]}},{"type": "page"}]}';
		$rowMax = 0;
		break;
		
}
//echo $temp;
//error_log($temp);
$struct = json_decode($temp);
$filename = $struct->filename;
//echo "<br>".$filename;
//error_log($filename);
$export = var_export($struct,TRUE);
//error_log($export);
//var_dump($struct);
if(isset($_POST['id'])){
	file_put_contents("output/".$filename."ids.txt",$_POST['id']);
	$_GET['user'] = $_POST['user'];
	$_GET['s'] = 0;
}
$width = $struct->width;
$height = $struct->height;

$maxImages = $struct->maxImages;
if(isset($struct->marginx)){
	$marginx = $struct->marginx;
}
else{
	$marginx = 0;
}
if(isset($struct->marginy)){
	$marginy = $struct->marginy;
}
else{
	$marginy = 0;
}
if(isset($struct->orientation)){
	$orientation = $struct->orientation;
}
else{
	$orientation = "P";
}
if(isset($struct->cutLines)){
	$cutLines = $struct->cutLines;
}
else{
	$cutLines = false;
}
if(isset($struct->frame)){
	$frame = true;
}
else{
	$frame = false;
}
//$rowMax = 41;
if(isset($struct->status)){
	$statusChange = true;
}
else{
	$statusChange = false;
}
if(isset($struct->counting)){
	$counting = true;
}
else{
	$counting = false;
}

if (isset($_POST['width']) && isset($_POST['height'])) {
	$width = $_POST['width'];
	$height = $_POST['height'];
	if ($width > $height) {
		$orientation = "L";
	} else {
		$orientation = "P";
	}
	debug("Custom size: " . $width . " " . $height);
}
//$statusChange = $struct->status;
$current->x = $marginx;
$current->y = $marginy;
$current->imageCount = 0;
$current->counting = $counting;
$content = $struct->content;
$layout = array();

$ids_get = file_get_contents("output/".$filename."ids.txt");

$userID = $_GET['user'];
$ids = explode("|",$ids_get);
//$height = 229;
//echo sizeof($ids);
$current->maxImages = min($maxImages,sizeof($ids));
$current->fileCount = 0;
$t = $_GET['s'];
$progress = array();
$progress['total'] = sizeof($layout);
$progress['current'] = $t;
//$s = 0;
writeProgress($progress,$userID,$filename);
$startTime = microtime(true);
if($t==0&&($template<7||$template>=10)){
	while($current->imageCount < sizeof($ids)){
		$current->x = $marginx;
		$current->y = $marginy;
		interpretContent($content,$ids);
		//echo $current->imageCount." ".$maxImages."<br>";
		if($current->imageCount%$maxImages==0){
			$page ->type = 'file';
			$layout[] = clone $page;
			$current->fileCount++;
		}
	}
	//var_dump($layout);
	$json2 = json_encode($layout);
	file_put_contents("output/".$filename."layout.txt",$json2);
	$layout = json_decode($json2);
	$pdf = new PDF($orientation,'mm',array($width,$height) );
	$pdf->AddPage();
	$x = $marginx;
	$y = $marginy;
	$pdf->Output("output/".$filename."_0.pdf");
	$totalFiles = 0;
	for($i=0;$i<sizeof($layout)-1;$i++){
		if($layout[$i]->type=="file"){
			$totalFiles++;
			$pdf->Output("output/".$filename."_".$totalFiles.".pdf");
		}
	}
	
}
else if($template >= 7){
	$pdf = new PDF($orientation, 'mm', array($width,$height));
	$pdf->AddPage();
	$x = $marginx;
	$y = $marginy;
	$maxpheight = 0;
	
	$progress = array();
	$progress['total'] = count($ids) + 1;
	$progress['current'] = 0;
	$progress['message'] = "Printing...";
	$progress['date'] = date('Y-m-d');
	writeProgress($progress,$userID,$filename);
	
	require_once '../backend/settings.php';
	foreach ($ids as $id) {
		$parts = explode("^",$id);
		$pid = trim($parts[4]);
		$product = $_order_db->getProductById($pid);
		$pwidth = $product->width;
		$pheight = $product->height;
		$fwidth = $product->frameWidth;
		$fheight = $product->frameHeight;
		$fx = ($fwidth - $pwidth) / 2;
		$fy = ($fheight - $pheight) / 2;
		if ($x + $fwidth >= $width) {
			$x = $marginx;
			$y = $y + $maxpheight + 0.0762;
			$maxpheight = 0;
		}
		if ($y + $pheight > $height) {
			$pdf->AddPage();
			$x = $marginx;
			$y = $marginy;
			$maxpheight = 0;
		}
		if ($fheight > $maxpheight) {
			$maxpheight = $fheight;
		}
		$file = Settings::SERVICE_GET_IMAGE."?id=".preg_replace("/#id/",$parts[5],"original.designs%2F#id_hd.png");
		
		$pdf->Image($file,$x + $fx,$y + $fy,$pwidth,$pheight,"PNG");
		
		$pdf->SetDrawColor(255,0,0);
		$pdf->setLineWidth(0.0762);
		
		if ($template == 8) {
			$pdf->Line($x,$y+0.25,$x,$y+$fheight-0.25);
			$pdf->Line($x+0.25,$y+$fheight,$x+$fwidth-0.25,$y+$fheight);
			$pdf->Line($x+0.25,$y,$x+$fwidth-0.25,$y);
			$pdf->Line($x+$fwidth,$y+0.25,$x+$fwidth,$y+$fheight-0.25);
		}
		if ($template == 9) {
			$pdf->setFont("Arial","",8);
			$pdf->RotatedText($x+$fwidth+2,$y+$fheight-1,"Order # " . $parts[1],90);
			$pdf->RotatedText($x+$fwidth+5,$y+$fheight-1,$parts[2],90);
			$x=$x + 5;
		}
		if ($template != 9)  {
			$_workstation_db->updateStatus($parts[3],ProcessingStage::STAGE_PRINTED);
		}
		
		$x = $x + $fwidth;
		$progress['current']++;
		writeProgress($progress,$userID,$filename);
	}
	//$progress['current']++;
	$pdf->output("output/".$filename."_1.pdf");
	writeProgress($progress,$userID,$filename);
}
else{
	$json2 = file_get_contents("output/".$filename."layout.txt");
	$layout = json_decode($json2);
}
if($template<7||$template>=10){
//var_dump($layout);
//find out which file I'm on.
$currentFile = 0;
$newFileLocation = 0;
for($i=0;$i<$t;$i++){
	if($layout[$i]->type=="file"){
		$currentFile++;
		$newFileLocation = $i;
	}
}
$current->fileCount = $currentFile;
$pdf = new PDF($orientation,'mm',array($width,$height) );
$pdf->SetDrawColor(0,0,0);
$pdf->setLineWidth(0.2);
$pdf->addPage();
$pdf->cutLines = $cutLines;
$pdf->fileName = $filename;
$pdf->statusChange = $statusChange;
$pdf->frame = $frame;
$pdf->counting = $counting;
//echo "StatusChange: ".$pdf->statusChange."<br>";
$pdf->setSourceFile("output/".$filename."_".$currentFile.".pdf");
$tplIdx = $pdf->importPage(1);
//echo $width."  ".$height;
$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);
$pageCount = 1;
for($i=$newFileLocation;$i<$t;$i++){
	if($layout[$i]->type=="page"){
		//echo 'Adding a page';
		$pdf->addPage();
		$tplIdx = $pdf->importPage(++$pageCount);
		$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);
	}
		
}
$totalImages = $current->imageCount;
$current->imageCount = 0;
$progress = array();
$progress['total'] = sizeof($layout)+1;
$progress['current'] = $t;
$progress['message'] = "";
$progress['date'] = date('Y-m-d');
//$s = 0;

writeProgress($progress,$userID,$filename);
$i = 0;
for($i=$t;$i<sizeof($layout);$i++){
	$object = $layout[$i];
	//echo $object->type."<br>";
	pdfInterpretObject($object,$pdf,$ids);
	$progress['current']++;
	$currentTime = microtime(true);
	$seconds = ($currentTime - $startTime);
	$hours = $seconds / 3600; //make hours
	$rem_seconds = $seconds % 3600; //get the remainder
	$minutes = $rem_seconds / 60;  //make minutes
	$rem_seconds = $rem_seconds % 60; //remainder is seconds
	$fnow = sprintf("%dh%dm%02ds",$hours,$minutes,$rem_seconds);
	
	$timePerObject = $seconds / $progress['current'];
	
	$seconds = ($progress['total'] - $progress['current'])*$timePerObject;
	
	$hours = $seconds / 3600; //make hours
	$rem_seconds = $seconds % 3600; //get the remainder
	$minutes = $rem_seconds / 60;  //make minutes
	$rem_seconds = $rem_seconds % 60; //remainder is seconds
	$fexp = sprintf("%dh%dm%02ds",$hours,$minutes,$rem_seconds);
	echo "Current File: ".$current->fileCount."<br>";
	$progress['message'] = "Elapsed Time: ". $fnow."<br>Estimated Time Remaining: ".$fexp."<br>"."Number of Images Processed: ".$current->imageCount."/".$totalImages;
	echo $progress['message'].'<br>';
	writeProgress($progress,$userID,$filename);
	
}
	debug("Outputting pdf "."output/".$filename."_".$current->fileCount.".pdf");
	$pdf->output("output/".$filename."_".$current->fileCount.".pdf");

}

//echo $current->fileCount."<br>";
//writeProgress($progress,$userID,$filename);
//$pdf->output();
	$files = scandir("output/");
	 $zip = new ZipArchive();
	$zipName = "output/".$filename."_".date('Y-m-d').".zip";
	if(file_exists($zipName)){
		unlink($zipName);
	}
	debug("Creating zip file ".$zipName);
	$zip->open($zipName,ZipArchive::CREATE);
	//echo sizeof($files)."<br>";
	foreach($files as $file){
		if(!is_dir($file)&&pathinfo($file, PATHINFO_EXTENSION)=="pdf"){
		//echo "Adding ".$file."<br/>";
		if(file_exists("output/".$file)){
			$zip->addFile("output/".$file,$file);
		}
		else echo "NON_EXIST";
		}
	}
	$zip->close();
	//echo '<a href="'.$zipName.'">Download zip file here</a>';
	foreach($files as $file){
		if(!is_dir($file)&&pathinfo($file, PATHINFO_EXTENSION)=="pdf"){
			unlink("output/".$file);
		}
	}
	$progress['current']++;
	writeProgress($progress,$userID,$filename);
//}

?>