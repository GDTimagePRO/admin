<?php
	function createDesign($orderItemId, ConfigItem $barcodeConfigItem)
	{
		global $_design_db;
		global $_order_db;
		
		$product = $_order_db->getProductById($barcodeConfigItem->productId);
		if(is_null($product)) return NULL;
		
		$templateId = $barcodeConfigItem->templateId;
		if(is_null($templateId))
		{
			$templateId = $_design_db->getDefualtDesignTemplateId($product->productTypeId);
			if($templateId < 0) return NULL;
		}
			
		$template = $_design_db->getDesignTemplateById($templateId);
		if(is_null($template)) return NULL;
		
		$design = new Design();
		$design->productId = $product->id; 
		$design->orderItemId = $orderItemId;		
		$design->productTypeId = $product->productTypeId;
		$design->setConfigItem($barcodeConfigItem);
		$design->designJSON = $template->designJSON;
		$design->state = Design::STATE_PENDING_SCL_DATA;
		
		if(!$_design_db->createDesign($design)) return NULL;
		
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