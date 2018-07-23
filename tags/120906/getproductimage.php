<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$id = $_GET['id'];
$data = $db->getOrderData($id);
$data = explode(",",$data);
//echo $data;
//$data = preg_replace('/\s+/', '', $data[1]);
$data = str_replace(' ','+',$data[1]);
//echo $data;
$image = imagecreatefromstring(base64_decode($data));
//echo 'Blah Blah';
if(isset($_GET['color'])){
	
	$color = $_GET['color'];
	//echo $color;
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
	$newcolor = imagecolorallocate($image,$coloroptions[$color]['red'],$coloroptions[$color]['green'],$coloroptions[$color]['blue']);
	
	for($x = 0;$x<imagesx($image);$x++){
		for($y = 0;$y<imagesy($image);$y++){
			$rgb = imagecolorat($image, $x, $y);
			$colors = imagecolorsforindex($image, $rgb);
			//echo $colors['red']." ".$colors['green']." ".$colors['blue']."<br>";
			if($colors['red']!=255||$colors['green']!=255||$colors['blue']!=255){
				//echo "Setting to new color <br />";
				imagesetpixel($image, $x, $y, $newcolor);
			}
		}
	}
}
header('Content-Type: image/jpeg');
imagejpeg($image);

?>

