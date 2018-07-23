<?php
	include_once "_common.php";	
	$_system->forceLogin();

	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	
	if(isset($_POST['removeImageId']) && !empty($_POST['removeImageId'])) 
	{
		$removeId = $_POST['removeImageId'];
		$_image_db->deleteImage($removeId);
	}

	function createUploadPage()
	{
		echo '<div id="uploadPage">';
		echo '<label for="uploadGraphic">File:</label>';
		echo '<input type="file" name="file" onChange="_imageSelectDialog.selectFile(this.files[0]);"/>';
		echo '<div id="dialog_select_image_filename"></div>';
		echo '<button onClick="_imageSelectDialog.uploadFile()">Upload</button>  ';
		//echo '<button onClick="_imageSelectDialog.removeImage()">Remove</button>';
		echo '<div id="dialog_select_image_upload_results"></div>';
		echo '<div>';
	}
	
	function writeImage($imageId)
	{
		echo sprintf(
				'<td class="image_cell" imageId="%s" onclick="_imageSelectDialog.setSelected(\'%s\')">'.
				'<img src="design_part/get_image.php?id=%s"/></td>',
				$imageId,
				$imageId,
				ImageDB::TYPE_THUMBNAIL.'.'.$imageId
		);
		
// 		$cssClass = "";
// 		if ($image->categoryId == ImageDB::CATEGORY_USER_UPLOADED)
// 		{
// 			echo sprintf(
// 					'<td class="%s" id="image_%d" onclick="_imageSelectDialog.setSelected(%d)">'.
// 					'<img src="design_part/get_image.php?thumbnail=true&id=%d&color=black"/>'.
// 					'<img id="image_garbage" src="images/delete_can_blue.png" onclick="_imageSelectDialog.removeImage(%d)"/></td>',
// 					$cssClass,
// 					$image->id,
// 					$image->id,
// 					$image->id,
// 					$image->id
// 			);
// 		}
// 		else
// 		{
// 			echo sprintf(
// 					'<td class="%s" id="image_%d" onclick="_imageSelectDialog.setSelected(%d)">'.
// 					'<img src="design_part/get_image.php?thumbnail=true&id=%d&color=black"/></td>',
// 					$cssClass,
// 					$image->id,
// 					$image->id,
// 					$image->id
// 			);		
// 		}
	}
		
	if(isset($_GET['tab']))
	{
		$list = $_image_db->getImageList($_GET['tab']);		
		
		echo '<table cellpadding="0" cellspacing="2"><tr>';
		$col = 0;
		
		foreach($list as $item)
		{
			if($col == 5)
			{
				echo '</tr><tr>';
				$col = 0;
			}
		
			writeImage($item);
			$col++;
		}
		for(; $col<5; $col++) echo '<td></td>';
		
		echo '</tr></table>';
		
		
// 		if ($_GET['tab'] == ImageDB::CATEGORY_USER_UPLOADED)
// 		{
// 			createUploadPage();
// 		}
		exit();
	}
?>