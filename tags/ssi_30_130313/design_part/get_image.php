<?php
	include_once "_common.php";

	if(!$_user_id) $_user_id = -9999999;	

	if(!isset($_GET['id']))
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
	
	
	$imageId = $_GET['id'];
	$image = $_image_db->getImageById($imageId);
	if(is_null($image))
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
	
	$timeChanged = $image->dateChanged;
	
	if(isset($_GET['nocache']) && ($_GET['nocache']=="true"))
	{
		header('Cache-Control: no-store, private, no-cache, must-revalidate');                  // HTTP/1.1
		header('Cache-Control: pre-check=0, post-check=0, max-age=0, max-stale = 0', false);    // HTTP/1.1
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');                                       // Date in the past
		header('Expires: 0', false);
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		header('Pragma: no-cache');
	}
	else 
	{		
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		// Set to expire in 2 days
		header("Expires: " . date(DATE_RFC822, strtotime(" 2 day")));
		
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $timeChanged))
		{
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $timeChanged).' GMT', true, 304);
			exit();
		}
		else
		{
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", $timeChanged) . " GMT");
		}
	}
	
	
	header('Content-Type: image/png');
	
	$maxSize = isset($_GET['max_size']) ? intval($_GET['max_size']) : 125;	
	
	if(isset($_GET['thumbnail']) && ($_GET['thumbnail']=="true"))
	{
		$thumbFileName = '../thumbs/'.$imageId.(isset($_GET['color']) ? $_GET['color'] : '').$maxSize.'.png';
	}
	else
	{
		$thumbFileName = '../image_cache/'.$imageId.(isset($_GET['color']) ? $_GET['color'] : '').'.png';
	}
	
	if (file_exists($thumbFileName))
	{
		if(filemtime($thumbFileName) == $timeChanged)
		{
			echo file_get_contents($thumbFileName);
			exit;				
		}
	}

	try
	{
		
		$image = null;
		$imageData = $_image_db->getImageData($imageId, $_user_id);
		if(is_null($imageData))
		{
			header("HTTP/1.0 404 Not Found");
			exit();
		}
		
		
// 		if(isset($_GET['color']))
// 		{
// 			$color = $_GET['color'];				
// 			$coloroptions = array();
// 			$coloroptions['black']['red'] = 0;
// 			$coloroptions['black']['green'] = 0;
// 			$coloroptions['black']['blue'] = 0;
// 			$coloroptions['red']['red'] = 255;
// 			$coloroptions['red']['green'] = 0;
// 			$coloroptions['red']['blue'] = 0;
// 			$coloroptions['green']['red'] = 0;
// 			$coloroptions['green']['green'] = 128;
// 			$coloroptions['green']['blue'] = 0;
// 			$coloroptions['blue']['red'] = 0;
// 			$coloroptions['blue']['green'] = 0;
// 			$coloroptions['blue']['blue'] = 255;
// 			$coloroptions['yellow']['red'] = 255;
// 			$coloroptions['yellow']['green'] = 255;
// 			$coloroptions['yellow']['blue'] = 0;
// 			$coloroptions['grey']['red'] = 128;
// 			$coloroptions['grey']['green'] = 128;
// 			$coloroptions['grey']['blue'] = 128;
// 			$coloroptions['silver']['red'] = 192;
// 			$coloroptions['silver']['green'] = 192;
// 			$coloroptions['silver']['blue'] = 192;
// 			$coloroptions['violet']['red'] = 238;
// 			$coloroptions['violet']['green'] = 130;
// 			$coloroptions['violet']['blue'] = 238;
// 			$coloroptions['purple']['red'] = 98;
// 			$coloroptions['purple']['green'] = 45;
// 			$coloroptions['purple']['blue'] = 101;
	
// 			imagefilter($image, IMG_FILTER_GRAYSCALE);
// 			imagefilter($image, IMG_FILTER_CONTRAST, -100);
					
// 			$foregroundColor = imagecolorallocate($image,$coloroptions[$color]['red'],$coloroptions[$color]['green'],$coloroptions[$color]['blue']);
// 			$backgroundColor = imagecolorallocate($image,255,255,255);
// 			imagecolortransparent($image, $backgroundColor);
			
// 			$threshold = 120*120*3;
			
// 			for($x = 0;$x<imagesx($image);$x++)
// 			{
// 				for($y = 0;$y<imagesy($image);$y++)
// 				{
// 					$rgb = imagecolorat($image, $x, $y);
// 					$colors = imagecolorsforindex($image, $rgb);
// 					$r = $colors['red'];
// 					$g = $colors['green'];
// 					$b = $colors['blue'];
					
// 					if($r * $r + $g * $g + $b * $b < $threshold)
// 					{
// 						imagesetpixel($image, $x, $y, $foregroundColor);
// 					}
// 					else
// 					{
// 						imagesetpixel($image, $x, $y, $backgroundColor);					
// 					}
// 				}
// 			}	
// 		}
		
		
		if(isset($_GET['thumbnail']) && ($_GET['thumbnail']=="true"))
		{
			$image = imagecreatefromstring($imageData);	
			
			$width = 0;
			$height = 0;
			
			if(imagesx($image) > imagesy($image))
			{
				$width = $maxSize;
				$height = $maxSize/imagesx($image)*imagesy($image);
			}
			else
			{
				$height = $maxSize;
				$width = $maxSize/imagesy($image)*imagesx($image);
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
	
		if(isset($thumbFileName)) ob_start();
		
		if($image != null)
		{
			$result = imagepng($image);
			if(!$result)
			{		
				header('HTTP/1.1 408 Request Timeout',true,408);		
			}
			imagedestroy($image);
		}
		else
		{
			echo $imageData;
			$result = true;
		}
		
		
		if(isset($thumbFileName))
		{
			if($result)
			{
				file_put_contents($thumbFileName, ob_get_contents());
				touch($thumbFileName, $timeChanged);
			}
			ob_end_flush();
		}
	}
	catch (Exception $e)
	{
			header('HTTP/1.1 408 Request Timeout',true,408);		
	}	
?>