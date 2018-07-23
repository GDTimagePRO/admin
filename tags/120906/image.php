<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$id = $_GET['id'];
$color = $_GET['color'];
$imagedata = $db->getImage($id);
$coloroptions = array();
$coloroptions['black']['red'] = 0;
$coloroptions['black']['green'] = 0;
$coloroptions['black']['blue'] = 0;
$coloroptions['red']['red'] = 255;
$coloroptions['red']['green'] = 0;
$coloroptions['red']['blue'] = 0;
$coloroptions['blue']['red'] = 0;
$coloroptions['blue']['green'] = 0;
$coloroptions['blue']['blue'] = 255;
$coloroptions['green']['red'] = 0;
$coloroptions['green']['green'] = 255;
$coloroptions['green']['blue'] = 0;
$coloroptions['purple']['red'] = 98;
$coloroptions['purple']['green'] = 45;
$coloroptions['purple']['blue'] = 101;

//$length = strlen($imagedata);
//header('Last-Modified: '.date('r'));
header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . " GMT");
//header('Accept-Ranges: bytes');
//header('Content-Length: '.$length);
header('Content-Type: image/jpeg');
$image = imagecreatefromstring($imagedata);
imagefilter($image, IMG_FILTER_GRAYSCALE);
imagefilter($image, IMG_FILTER_CONTRAST, -100);
$newcolor = imagecolorallocate($image,$coloroptions[$color]['red'],$coloroptions[$color]['green'],$coloroptions[$color]['blue']);
for($x = 0;$x<imagesx($image);$x++){
	for($y = 0;$y<imagesy($image);$y++){
		$rgb = imagecolorat($image, $x, $y);
		$colors = imagecolorsforindex($image, $rgb);
		if($colors['red']!=255||$colors['green']!=255||$colors['blue']!=255){
			//echo "Setting to new color <br />";
			imagesetpixel($image, $x, $y, $newcolor);
		}
	}
}
//echo $index;
//imagecolorset($image,$index,0,0,255);
$width = 0;$height = 0;
if(imagesx($image) > imagesy($image)){
	$width = 125;
	$height = 125/imagesx($image)*imagesy($image);
}
else{
	$height = 125;
	$width = 125/imagesy($image)*imagesx($image);
}
$image2 = imagecreatetruecolor($width,$height);
imagecopyresampled($image2,$image,0,0,0,0,$width,$height,imagesx($image),imagesy($image));
imagejpeg($image2);
?>