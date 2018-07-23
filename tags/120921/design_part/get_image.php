<?php
include_once "_common.php";

if($_user_id)
{
	if(!isset($_GET['id']))
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
	
	header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . " GMT");
	
	$image = $_image_db->getImageData($_GET['id'], $_user_id);
	if($image == NULL)
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}

	$image = imagecreatefromstring($image);

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

	if(isset($_GET['color']))
	{
		$color = $_GET['color'];
			
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

		imagefilter($image, IMG_FILTER_GRAYSCALE);
		imagefilter($image, IMG_FILTER_CONTRAST, -100);
				
		$foregroundColor = imagecolorallocate($image,$coloroptions[$color]['red'],$coloroptions[$color]['green'],$coloroptions[$color]['blue']);
		$backgroundColor = imagecolorallocate($image,255,255,255);
		imagecolortransparent($image, $backgroundColor);
		
		$threshold = 120*120*3;
		
		for($x = 0;$x<imagesx($image);$x++)
		{
			for($y = 0;$y<imagesy($image);$y++)
			{
				$rgb = imagecolorat($image, $x, $y);
				$colors = imagecolorsforindex($image, $rgb);
				$r = $colors['red'];
				$g = $colors['green'];
				$b = $colors['blue'];
				
				if($r * $r + $g * $g + $b * $b < $threshold)
				{
					imagesetpixel($image, $x, $y, $foregroundColor);
				}
				else
				{
					imagesetpixel($image, $x, $y, $backgroundColor);					
				}
			}
		}

	}
	
	header('Content-Type: image/png');
	imagepng($image);
	imagedestroy($image);
}
?>