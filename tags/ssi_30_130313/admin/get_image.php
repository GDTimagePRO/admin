<?php
include_once "_common.php";

if(!isset($_GET['id']))
{
	header("HTTP/1.0 404 Not Found");
	exit();
}

header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . " GMT");

$image = $_image_db->getImageById($_GET['id'], true);
if($image == NULL)
{
	header("HTTP/1.0 404 Not Found");
	exit();
}

$image = imagecreatefromstring($image->data);

if(isset($_GET['thumbnail']) && ($_GET['thumbnail']=="true"))
{
	$width = 0;
	$height = 0;
	if(imagesx($image) > imagesy($image))
	{
		$width = 125;
		$height = 125/imagesx($image)*imagesy($image);
	}
	else
	{
		$height = 125;
		$width = 125/imagesy($image)*imagesx($image);
	}
	$image2 = imagecreatetruecolor($width,$height);
	imagecopyresampled($image2,$image,0,0,0,0,$width,$height,imagesx($image),imagesy($image));
	

	//header('Content-Type: image/jpeg');
	//imagejpeg($image2);
	
	//header('Content-Type: image/png');
	//imagepng($image2);

	
	imagedestroy($image);
	$image = $image2;		
}


header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>