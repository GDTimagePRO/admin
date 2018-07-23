<?php
	include_once "_common.php";
	$array = array(
			"Rubber" => 1,
			"Stamp" => 3,
			"Flash" => 4,
			"Dater" => 5,
			"Badge" => 6,
			"Sign" => 7,
			"Tag" => 8,
			"Polymer" => 9
			);
	
	$xml = simplexml_load_file("core_config.xml");
	
	$MM_IN_INCH = 25.4;
	

	foreach( $xml->children() as $child )
	{
		if($child->getName() != "ProductCategories") continue;		
			
		foreach( $child->children() as $grandChild )
		{
			if ($grandChild->getName() == "ProductCategory")
			{
				$category = $_order_db->getProductCategoryByName($grandChild->attributes()->Name);
				if($category == null)
				{
					$category =  new ProductCategory();
					$category->name = (string)$grandChild->attributes()->Name;
					$_order_db->createProductCategory($category);
				}
				
				foreach($grandChild->children() as $productNodeRoot)
				{
					if($productNodeRoot->getName() != "Products") continue;
					
					foreach($grandChild->children()->children() as $productNode)
					{						
						$product = $_order_db->getProductByCode((string)$productNode->attributes()->Name);
						if(is_null($product)) continue;
						
						
						//$product = new Product();
						$product->categoryId = (int)$category->id; 
						if ($productNode->attributes()->AllowPicture == "True")
							$product->allowGraphics = 1;
						else 
							$product->allowGraphics = 0;
						$product->code = (string)$productNode->attributes()->Name;
						$product->frameWidth = $product->width = (double)$productNode->attributes()->Width * $MM_IN_INCH;
						$product->frameHeight = $product->height = (double)$productNode->attributes()->Height * $MM_IN_INCH;
						
						if(isset($productNode->attributes()->ContentAreaHeight))
						{
							$product->height = (double)$productNode->attributes()->ContentAreaHeight * $MM_IN_INCH;
						}
						
						if(isset($productNode->attributes()->ContentAreaWidth))
						{
							$product->width = (double)$productNode->attributes()->ContentAreaWidth * $MM_IN_INCH;
						}
						
						if($product->width == $product->height)
						{
							$product->productTypeId = 1;
						}
						else
						{
							$product->productTypeId = 2;
						}
												
						$product->shapeId = "";
						$product->longName = $productNode->attributes()->LongName;
						//$_order_db->createProduct($product);
						$_order_db->updateProduct($product);
						
					}
				}
			}
		}
	}
?>
