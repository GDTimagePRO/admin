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
	
	$xml = simplexml_load_file("Coreconfig_May_2012.xml");
	

	foreach( $xml->children() as $child )
	{
		foreach( $child->children() as $grandChild )
		{
			if ($grandChild->getName() == "ProductCategory")
			{
				if (array_key_exists($grandChild->attributes(), $array))
				{
					foreach($grandChild->children()->children() as $node)
					{
						$product = new Product();
						if ($node->attributes()->AllowPicture == "True")
							$product->allowGraphics = 1;
						else 
							$product->allowGraphics = 0;
						$product->categoryId = $array[$grandChild->attributes()];
						$product->code = $node->attributes()->Name;
						$product->width = $node->attributes()->Width;
						$product->height = $node->attributes()->Height;
						$product->frameHeight = 0;
						$product->frameWidth = 0;
						$product->shapeId = "";
						$product->productTypeId = 0;
						$product->longName = $node->attributes()->LongName;
						$_order_db->createProduct($product);
					}
				}
			}
		}
	}
?>
