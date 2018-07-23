<?php
	function createDesign($templateId, $userId)
	{
		global $_design_db;
		global $_image_db;
		
		$template = $_design_db->getDesignTemplateById($templateId);
		if(is_null($template)) return NULL;
		
		$design = new Design();
		$design->productTypeId = $template->productTypeId;
		$design->json = $template->json;		
		$design->imageId = $_image_db->createImageInline(
				ImageDB::CATEGORY_DESIGN_IMAGE,
				//$userId,
				ImageDB::IMAGE_PUBLIC_USER_ID,
				"design_image",
				""
			);
		
		if(!$_design_db->createDesign($design))
		{
			if($design->imageId >= 0) $_image_db->deleteImage($design->imageId);
			return NULL;
		}
		
		return $design;
	}
	
	function deleteDesign($designId)
	{
		global $_image_db;
		global $_design_db;
		
		$design = $_design_db->getDesignById($designId);
		if(is_null($design)) return false;
		
		if($design->imageId >= 0) $_image_db->deleteImage($design->imageId);
		return $_design_db->deleteDesign($design->id);
	}
?>