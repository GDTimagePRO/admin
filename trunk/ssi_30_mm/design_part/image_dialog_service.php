<?php	
	require_once '../backend/resource_manager.php';
	require_once '../backend/settings.php';
	require_once '../backend/db_order.php';
	
	
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	
	$colorModel = NULL; 
	if(isset($_GET['color_model'])) $colorModel = $_GET['color_model'];
	if(($colorModel != Product::COLOR_MODEL_24_BIT) && ($colorModel != Product::COLOR_MODEL_1_BIT))
	{
		$colorModel = Product::COLOR_MODEL_24_BIT;
	}
		
	function createUploadPage()
	{
		echo '<div id="uploadPage">';
		echo '<label for="uploadGraphic">File:</label>';
		echo '<input type="file" name="file" onChange="TI.imageSelectDialog.selectFile(this.files[0]);"/>';
		echo '<button onClick="TI.imageSelectDialog.uploadFile()">Upload</button>  ';
		echo '<div id="dialog_select_image_filename"></div>';		
		//echo '<button onClick="TI.imageSelectDialog.removeImage()">Remove</button>';
		echo '<div id="dialog_select_image_upload_results"></div>';
		echo '<div>';
	}
	
	function writeImage($imageId)
	{
		$rid = ResourceId::fromId($imageId);
		$rid->type = ResourceManager::TYPE_THUMBNAIL;
		
		echo sprintf(
				'<td class="image_cell" imageId="%s" onclick="TI.imageSelectDialog.setSelected(\'%s\')">'.
				'<img src="%s"/><br/><p style="width: 142px;word-wrap: break-word;white-space: normal; margin: 3px 0px 3px">%s</p></td>',
				$imageId,
				$imageId,
				Settings::getImageUrl($rid->getId()),
				substr($rid->path, 0, strrpos($rid->path, "."))
			);
	}
		
	if(isset($_GET['tabRID']))
	{
			
		$list = ResourceId::fromId($_GET['tabRID'])->getFileList();			
		$rid = ResourceManager::parseId($_GET['tabRID']);
		$isUploadTab = $rid->group == ResourceManager::GROUP_SESSION;
		
		$params = NULL;
		if($isUploadTab && ($colorModel == Product::COLOR_MODEL_1_BIT))
		{
			$params = array(ResourceId::PARAM_MONOCHROME);
		}
		
		echo '<table cellpadding="0" cellspacing="2"><tr>';
		$col = 0;
		
		foreach($list as $item)
		{
			if($col == 5)
			{
				echo '</tr><tr>';
				$col = 0;
			}
		
			if(!is_null($params)) $item = ResourceId::setParams($item, $params);			
			writeImage($item);
			$col++;
		}
		for(; $col<5; $col++) echo '<td></td>';
		
		echo '</tr></table>';
		
		if($isUploadTab) createUploadPage();
		exit();
	}
?>