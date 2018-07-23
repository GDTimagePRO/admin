<?php
	include_once "_common.php";
	
	$path = "C:\\_src\\eclipse_php\\ssi_30\\Library Images";
	$scan = scandir($path);
	
	foreach ($scan as $category) {
		if ($category === '.' or $category === '..') continue;
	
		if (is_dir($path . '\\' . $category)) {
			$image = new ImageCategory();
			$image->name = $category;
			$image->type = ImageCategory::TYPE_PUBLIC;
				
			if (!$_image_db->getImageCategoryByName($image->name))
				$_image_db->createImageCategory($image);
			
			$newPath = $path . '\\' . $category;
			$scanImages = scandir($newPath);

			foreach ($scanImages as $files) {
				if ($files === '.' or $files === '..') continue;

				if (!is_dir($newPath . '\\' . $files)) {
					$size = getimagesize($newPath . '\\' . $files);
					if ($size) {
						$filename = $newPath . '\\' . $files;
						
						// insert image!!!!!!!!
						
						$result = $_image_db->createImageInline(
								$image->id,
								ImageDB::IMAGE_PUBLIC_USER_ID,
								$files,
								file_get_contents($filename)
						);

						if($result == -1)
						{
							echo 'db error<br>';
						}
					}
				}
			}
		}
	}
?>
