<?php
	include_once "_common.php";
	require_once "./backend/design_elements.php";
	require_once './backend/email_service.php';
	
	//*
	if(isset($_GET['designId']))
	{
		$designId = 0;
		$type = "png";
		$dpi = Settings::HD_IMAGE_DPI;
		$name;
		$filter;
		$color;
		
		$designId = $_GET['designId'];
		
		if (isset($_GET['type'])) {
			$type = $_GET['type'];
		}
		
		if (isset($_GET['dpi'])) {
			$dpi = $_GET['dpi'];
		}
		
		if (isset($_GET['name'])) {
			$name = $_GET['name'];
		}
		
		if (isset($_GET['filter'])) {
			$filter = $_GET['filter'];
		}
		
		if (isset($_GET['color'])) {
			$color = $_GET['color'];
		}
		
		$design = Common::$designDB->getDesignById($designId);

		if (!isset($design) || $design == NULL) {
			header("HTTP/1.0 404 Not Found");
		} else {
			$designConfig = $design->getConfigItem();
			$product = Common::$orderDB->getProductById($designConfig->productId);
			
			if($product->frameWidth < $product->width) $product->frameWidth = $product->width;
			if($product->frameHeight < $product->height) $product->frameHeight = $product->height;
			
			$outputImageScale_trace = $dpi/25.4;
			
			$outputImageWidth_trace = round($product->width * $outputImageScale_trace);
			$outputImageHeight_trace = round($product->height * $outputImageScale_trace);	
			$outputImageFrameWidth_trace = round($product->frameWidth * $outputImageScale_trace);
			$outputImageFrameHeight_trace = round($product->frameHeight * $outputImageScale_trace);
			
			$url = Settings::SERVICE_RENDER_IMAGE . "?designJson=" . urlencode($design->designJSON) . "&imageURL=" . urlencode(SETTINGS::SERVICE_GET_IMAGE) . "&width=" . $outputImageWidth_trace . "&height=" . $outputImageHeight_trace . "&fillColor=%23ffffff&destId=" . $designId . "&type=" . $type . "&dpi=" . $dpi;
			if (isset($filter)) {
				$url = $url . "&filter=" . $filter;
			}
			if (isset($color)) {
				$url = $url . "&color=" . $color;
			}
			if (isset($_GET['debug']) && $_GET['debug'] == "true") {
				echo $url;
			} else {
				$im = @file_get_contents($url);
				if ($im === FALSE) {
					throw new Exception("Failed to open path " . $url);
				} else {
					header("content-type: image/" . $type);
					if (isset($name)) {
						header("Content-Disposition:  attachment; filename=\"". $name ."\";" );
					}
					//header("Content-Length: " . filesize($im));
					echo $im;
				}
			}
		}
	}
	else
	{
		header("HTTP/1.0 404 Not Found");
	}
?>