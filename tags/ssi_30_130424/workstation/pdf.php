<?php
/*
 * NOTE: A lot of things in this file are hard-coded specifically for MasonRow. Eventually it would be needed to be read from the database.
 * NOTE: Using the userID as an id for the file may cause issues if more than one person is using it with the same log in at the same time.
 * 		 It would probably be better to use session_id when the log in stuff is finished and have the periodically deleted. 
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

/*
 * CREATE LAYOUT JSON
 */

global $layout,$width,$height,$marginx,$marginy,$rowMax,$current;

function interpretContent($content){
	global $current,$width,$height,$marginx,$marginy,$rowMax,$layout;
	foreach($content as $c){
		//echo $c->type."<br>";
		switch($c->type){
			case "text":
				addText(clone $c);
				break;
			case "repeaty":
				$repeat = $c->value->repeat;
				for($i=0;$i<$repeat;$i++){
					interpretContent($c->value->content);
					$current->y+= $rowMax;
					$current->x = $marginx;
					if($current->imageCount > $current->maxImages){
						break;
					}
				}

				break;
			case "repeatx":
				$repeat = $c->value->repeat;
				for($i=0;$i<$repeat;$i++){
					interpretContent($c->value->content);
					if($current->imageCount > $current->maxImages){
						break;
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
				addImage(clone $c);
				break;
			case "page":
				$layout[] = clone $c;
				break;
			case "box":
				$layout[] = clone $c;
				break;

		}
		if($current->imageCount > $current->maxImages){
			break;
		}
	}
}
/**
 *
 * * @param Object $text this contains the information for the text line
 *
 */
function addText($text){
	global $layout,$current;
	$c = clone $current;
	if(!isset($text->x)){
		$text->x = $c->x;
	}
	if(!isset($text->y)){
		$text->y = $c->y;
	}
	else if($text->y < 0){
		$text->y = $c->y+(-1)*$text->y;
	}
	$text->current = $c->imageCount+$text->offset;
	$layout[] = $text;
}

function addImage($image){
	global $current,$layout,$width,$height,$marginx,$marginy,$rowMax;


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
	//echo "Adding image $current->imageCount at ($current->x,$current->y)<br>";
	$current->x+= $rowMax*$scale;
	$current->imageCount++;
	$layout[] = $image;
}

function writeProgress($progress,$userID){
	$json = json_encode($progress);
	file_put_contents("output/".$userID."status.txt",$json);
}

function pdfInterpretObject($object,&$pdf,$ids){
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
	}
}

function pdfAddText($object,&$pdf,$ids){
	$x = $object->x;
	$y = $object->y;
	$orientation = $object->orientation;
	$text = $object->value;
	if(preg_match("/#oid/",$text)){
		$current = $object->current;
		$id = $ids[$current];
		$parts = explode(" ",$id);
		$text = preg_replace("/#oid/",$parts[1],$text);
	}
	if(preg_match("/#color/",$text)){
		$current = $object->current;
		$id = $ids[$current];
		$parts = explode(" ",$id);
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
	$pdf->RotatedText($x,$y,$text,$orientation);
}

function pdfAddImage($object,&$pdf,$ids){
	global $rowMax;
	$x = $object->x;
	$y = $object->y;
	$image = $object->value;
	//echo $image."<br>";
	$id = $ids[$image];
	//echo $id."<br>";
	if($id!=""){
		$parts = explode(" ",$id);
		$file = "http://www.in-stamp.com/masonrow/design_part/get_image.php?id=".$parts[0];
		if(isset($object->scale)){
			$scale = $object->scale;
		}
		else{
			$scale = 1;
		}
		$pwidth = $rowMax*$scale;
		$pheight = $rowMax*$scale;
		$pdf->Image($file,$x,$y,$pwidth,$pheight,"PNG");
		
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

$template = $_GET['template'];
switch($template){
	case 0:
		//include "pdftest.php";
		$temp = '{"orientation":"L","width":305,"height":229,"marginx":5,"marginy":6,"maxPages":1,"maxImages":30,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image"},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-41,"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}}]}';
		$rowMax = 41;
		
		break;
	case 1:
		$temp = '{"orientation":"P","width":108,"height":140,"marginx":5,"marginy":6,"maxPages":8,"maxImages":24,"content":[{"type":"repeaty","value":{"repeat":8,"content":[{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontSize":"15","fontAttributes":"","x":61.641,"y":9.708,"offset": 0},{"type":"text","value":"Color: #color","fontFamily":"Arial","fontSize":"12","fontAttributes":"","x":57,"y":20,"offset": 0},{"type":"image","x":7.422,"y":13.117},{"type":"text","value":"Color: #color","fontFamily":"Arial","fontSize":"12","fontAttributes":"","x":10,"y":75,"offset": 0},{"type":"image","x":54,"y":47.083},{"type":"text","value":"Color: #color","fontFamily":"Arial","fontSize":"12","fontAttributes":"","x":57,"y":115,"offset": 0},{"type":"image","x":7.422,"y":83.751},{"type":"page"}]}}]}';
		$rowMax = 46;
		break;
	case 2:
		$temp = '{"orientation":"P","width":54.45,"height":86.2,"marginx":5,"marginy":6,"maxPages":8,"maxImages":24,"content":[{"type":"repeaty","value":{"repeat":8,"content":[{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontSize":"6","fontAttributes":"","x":5,"y":4},{"type":"image","x":16.43,"y":8,"scale":0.46934782608696},{"type":"image","x":16.43,"y":32.59,"scale":0.46934782608696},{"type":"image","x":16.43,"y":57.18,"scale":0.46934782608696},{"type":"box","x":5,"y":5,"width":44.45,"height":76.2},{"type":"text","value":"1","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":10},{"type":"text","value":"2","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":40.6},{"type":"text","value":"3","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":71.2},{"type":"page"}]}}]}';
		$rowMax = 46;
		break;
	case 3:
		$temp = '{"orientation":"P","width":76.2,"height":76.2,"marginx":5,"marginy":6,"maxPages":30,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":30,"content":[{"type":"image","x":17.6,"y":17.6},{"type":"page"}]}}]}';
		$rowMax = 41;
		break;
	case 4:
		$temp = '{"orientation":"P","width":76.2,"height":76.2,"marginx":5,"marginy":6,"maxPages":30,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":30,"content":[{"type":"image","x":21.7,"y":21.7,"scale":0.8},{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontAttributes":"","fontSize":15,"x":22,"y":15, "offset": -1},{"type":"page"}]}}]}';
		$rowMax = 41;
		break;
	case 5:
		$temp = '{"orientation":"L","width":279.4,"height":215.9,"marginx":2,"marginy":2,"maxPages":1,"maxImages":30,"cutLines":true,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image"},{"type":"spacex","value":1}]}},{"type":"spacey","value":2}]}}]}';
		$rowMax = 41;
		break;
	case 6:
		$temp = '{"orientation":"L","width":279.4,"height":215.9,"marginx":2,"marginy":2,"maxPages":1,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image", "scale": 0.8},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":'.(-41*0.8).',"offset": -1},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Color: #color","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":'.(-41*0.8).',"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}}]}';
		$rowMax = 41;
}

$struct = json_decode($temp);

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
//$rowMax = 41;
$current->x = $marginx;
$current->y = $marginy;
$current->imageCount = 0;
$content = $struct->content;
$layout = array();

$ids_get = $_GET['id'];

$userID = $_GET['user'];
$ids = explode("|",$ids_get);
//$height = 229;
$current->maxImages = min($maxImages,sizeof($ids));
$t = $_GET['s'];
if($t==0){
	interpretContent($content);
	//var_dump($layout);
	$json2 = json_encode($layout);
	file_put_contents("output/".$userID."layout.txt",$json2);
	$layout = json_decode($json2);
	$pdf = new PDF($orientation,'mm',array($width,$height) );
	$pdf->AddPage();
	$x = $marginx;
	$y = $marginy;
	$pdf->Output("output/0output.pdf");
}
else{
	$json2 = file_get_contents("output/".$userID."layout.txt");
	$layout = json_decode($json2);
}

$pdf = new PDF($orientation,'mm',array($width,$height) );
$pdf->SetDrawColor(0,0,0);
$pdf->setLineWidth(0.2);
$pdf->addPage();
$pdf->cutLines = $cutLines;
$pdf->setSourceFile("output/0output.pdf");
$tplIdx = $pdf->importPage(1);
//echo $width."  ".$height;
$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);
$pageCount = 1;
for($i=0;$i<$t;$i++){
	if($layout[$i]->type=="page"){
		//echo 'Adding a page';
		$pdf->addPage();
		$tplIdx = $pdf->importPage(++$pageCount);
		$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);
	}
		
}

$progress = array();
$progress['total'] = sizeof($layout);
$progress['current'] = $t;
//$s = 0;
writeProgress($progress,$userID);
$i = 0;
for($i=$t;$i<$t+10&&$i<sizeof($layout);$i++){
	$object = $layout[$i];
	//echo $object->type."<br>";
	pdfInterpretObject($object,$pdf,$ids);
	$progress['current']++;
	writeProgress($progress,$userID);
}
$pdf->output("output/0output.pdf");
//$pdf->output();
if($i < sizeof($layout)){
	Header("Location: pdf.php?s=".$i."&id=".$ids_get."&user=".$userID."&template=".$template);
}
?>