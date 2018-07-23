<?php
	include_once "_common.php";

	if(!isset($_GET['id']))
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
		
	$imageId = $_GET['id'];	
	$timeChanged = $_image_db->getDateChanged($imageId);

	if($timeChanged == 0)
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
	
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
		
	$parts = ImageDB::parseImageId($imageId);
	$fileType = strtolower(substr($parts['path'], -4));
	if($fileType == '.png')
	{
		header('Content-Type: image/png');
	}
	else if($fileType == '.jpg')
	{
		header('Content-Type: image/jpeg');
	}
	
	try
	{
		
		$imageData = $_image_db->getImageData($imageId, true);
		if(is_null($imageData))
		{
			header("HTTP/1.0 404 Not Found");
			exit();
		}
		else
		{
			echo $imageData; 
		}
	}
	catch (Exception $e)
	{
		header('HTTP/1.1 408 Request Timeout', true, 408);		
	}	
?>