<?php
ini_set("max_execution_time",6000);
ini_set("default_socket_timeout",6000);
ini_set('memory_limit','512M');
set_time_limit(6000);

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
					if($current->imageCount-$current->fileCount*$current->maxImages > $current->maxImages){
						return;
					}
				}

				break;
			case "repeatx":
				$repeat = $c->value->repeat;
				for($i=0;$i<$repeat;$i++){
					interpretContent($c->value->content);
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
				addImage(clone $c);
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
	$text->current = $c->imageCount;
	if(isset($text->offset)){
		$text->current +=$text->offset;
	}
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
	global $rowMax,$current,$_workstation_db;
	$x = $object->x;
	$y = $object->y;
	$image = $object->value;
	//echo $image."<br>";
	$id = $ids[$image];
	//echo $id."<br>";
	if($id!=""){
		$parts = explode(" ",$id);
		$file = "http://in-stamp.com.loucks51.arvixevps.com/masonrow/design_part/get_image.php?id=".$parts[0];
		if(isset($object->scale)){
			$scale = $object->scale;
		}
		else{
			$scale = 1;
		}
		$pwidth = $rowMax*$scale;
		$pheight = $rowMax*$scale;
		try {
			$pdf->Image($file,$x,$y,$pwidth,$pheight,"PNG");
		} catch (Exception $e) {
		}
		if($pdf->statusChange){
			//echo "changing the status of ".$parts[3]."<br>";
			$_workstation_db->updateStatus($parts[3],ProcessingStage::STAGE_PRINTED);
		}
		$current->imageCount++;
		
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
	$pdf->output("output/".$pdf->fileName."_".($file-1)."_".date("dmy").".pdf");
	$orientation = $pdf->CurOrientation;
	$cutLines = $pdf->cutLines;
	$statusChange =$pdf->statusChange;
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
	/*$pdf->setSourceFile("output/0output_".$file.".pdf");
	$tplIdx = $pdf->importPage(1);
	//echo $width."  ".$height;
	$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);
	$pageCount = 1;
	$pdf->addPage();
	$tplIdx = $pdf->importPage(1);
	$pdf->useTemplate($tplIdx, 0, 0, $width,$height,true);*/
}
if(isset($_POST['template'])){
	$_GET['template'] = $_POST['template'];
}

$template = $_GET['template'];
switch($template){
	case 0: //polymer
		//include "pdftest.php";
		//$temp = '{"orientation":"L","width":305,"height":229,"marginx":5,"marginy":6,"maxPages":1,"maxImages":1,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"repeaty","value":{"repeat":1,"content":[{"type":"repeatx","value":{"repeat":1,"content":[{"type":"image"},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-41,"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}}]}';
		$temp = '{"status": true,"filename": "polymer","orientation":"L","width":305,"height":229,"marginx":5,"marginy":6,"maxPages":1,"maxImages":30,"content":[{"type":"text","value":"Form # #inc","x":295,"y":219,"fontFamily":"Arial","fontAttributes":"B","fontSize":20,"orientation":90},{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image"},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":-41,"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}}]}';
		$rowMax = 41;
		
		break;
	case 1: //trio
		$temp = '{"status": true,"filename": "trio","orientation":"P","width":108,"height":140,"marginx":5,"marginy":6,"maxPages":8,"maxImages":24,"content":[{"type":"repeaty","value":{"repeat":8,"content":[{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontSize":"15","fontAttributes":"","x":61.641,"y":9.708,"offset": 0},{"type":"text","value":"Color: #color","fontFamily":"Arial","fontSize":"12","fontAttributes":"","x":57,"y":20,"offset": 0},{"type":"image","x":7.422,"y":13.117},{"type":"text","value":"Color: #color","fontFamily":"Arial","fontSize":"12","fontAttributes":"","x":10,"y":75,"offset": 0},{"type":"image","x":54,"y":47.083},{"type":"text","value":"Color: #color","fontFamily":"Arial","fontSize":"12","fontAttributes":"","x":57,"y":115,"offset": 0},{"type":"image","x":7.422,"y":83.751},{"type":"page"}]}}]}';
		$rowMax = 46;
		break;
	case 2: //trio index cards
		$temp = '{"filename": "trioIndex","orientation":"P","width":54.45,"height":86.2,"marginx":5,"marginy":6,"maxPages":8,"maxImages":24,"content":[{"type":"repeaty","value":{"repeat":8,"content":[{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontSize":"6","fontAttributes":"","x":5,"y":4},{"type":"image","x":16.43,"y":8,"scale":0.46934782608696},{"type":"image","x":16.43,"y":32.59,"scale":0.46934782608696},{"type":"image","x":16.43,"y":57.18,"scale":0.46934782608696},{"type":"box","x":5,"y":5,"width":44.45,"height":76.2},{"type":"text","value":"1","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":10},{"type":"text","value":"2","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":40.6},{"type":"text","value":"3","fontFamily":"Arial","fontSize":"8","fontAttributes":"","x":7,"y":71.2},{"type":"page"}]}}]}';
		$rowMax = 46;
		break;
	case 3: //embossers
		$temp = '{"status": true,"filename": "embosser","orientation":"P","width":76.2,"height":76.2,"marginx":5,"marginy":6,"maxPages":30,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":30,"content":[{"type":"image","x":17.6,"y":17.6},{"type":"page"}]}}]}';
		$rowMax = 41;
		break;
	case 4: //embossers index cards
		$temp = '{"filename": "embosserIndex","orientation":"P","width":76.2,"height":76.2,"marginx":5,"marginy":6,"maxPages":30,"maxImages":30,"content":[{"type":"repeaty","value":{"repeat":30,"content":[{"type":"image","x":21.7,"y":21.7,"scale":0.8},{"type":"text","value":"Order # #oid","fontFamily":"Arial","fontAttributes":"","fontSize":15,"x":22,"y":15, "offset": -1},{"type":"page"}]}}]}';
		$rowMax = 41;
		break;
	case 5: //laser
		$temp = '{"status": true,"filename": "laser","orientation":"L","width":279.4,"height":215.9,"marginx":2,"marginy":2,"maxPages":1,"maxImages":30,"cutLines":true,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image"},{"type":"spacex","value":1}]}},{"type":"spacey","value":2}]}}]}';
		$rowMax = 41;
		break;
	case 6: //laser index cards
		$temp = '{"filename": "laserIndex","orientation":"L","width":279.4,"height":215.9,"marginx":2,"marginy":2,"maxPages":30,"maxImages":3000,"content":[{"type":"repeaty","value":{"repeat":5,"content":[{"type":"repeatx","value":{"repeat":6,"content":[{"type":"image", "scale": 0.8},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Order #: #oid","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":'.(-41*0.8).',"offset": -1},{"type":"spacex","value":3},{"orientation":90,"type":"text","value":"Color: #color","fontFamily":"Arial","fontAttributes":"","fontSize":8,"y":'.(-41*0.8).',"offset": -1},{"type":"spacex","value":2}]}},{"type":"spacey","value":3}]}},{"type":"page"}]}';
		$rowMax = 41;
}

$struct = json_decode($temp);
$filename = $struct->filename;
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
//$rowMax = 41;
if(isset($struct->status)){
	$statusChange = true;
}
else{
	$statusChange = false;
}
//$statusChange = $struct->status;
$current->x = $marginx;
$current->y = $marginy;
$current->imageCount = 0;
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
$progress['date'] = date("dmy");
//$s = 0;
writeProgress($progress,$userID,$filename);
$startTime = microtime(true);
if($t==0){
	while($current->imageCount < sizeof($ids)){
		$current->x = $marginx;
		$current->y = $marginy;
		interpretContent($content);
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
			$pdf->Output("output/".$filename."_".$totalFiles."_".date("dmy").".pdf");
		}
	}
	
}
else{
	$json2 = file_get_contents("output/".$userID."layout.txt");
	$layout = json_decode($json2);
}
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
$totalImages = $current->imageCount-1;
$current->imageCount = 0;
$progress = array();
$progress['total'] = sizeof($layout)+1;
$progress['current'] = $t;
$progress['message'] = "";
$progress['date'] = date("dmy");
//$s = 0;
writeProgress($progress,$userID,$filename);
$i = 0;
for($i=$t;$i</*$t+10&&$i<*/sizeof($layout);$i++){
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
$pdf->output("output/".$filename."_".$current->fileCount."_".date("dmy").".pdf");
//echo $current->fileCount."<br>";

//$pdf->output();
/*if($i < sizeof($layout)){
	Header("Location: pdf.php?s=".$i."&user=".$userID."&template=".$template);
}
else{*/
	$files = scandir("output/");
	 $zip = new ZipArchive();
	$zipName = "output/".$filename."_".date("dmy").".zip";
	if(file_exists($zipName)){
		unlink($zipName);
	}
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