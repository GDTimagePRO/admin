<?php
	
	class Barcode
	{
		public $barcode;
		public $productId;
		public $dateCreate = null;
		public $master;
		public $dateUsed = '0000-00-00 00:00:00';
		public $templateCategoryId = -1;
		public $templateId = -1;
		
		function isMaster()
		{
			return $this->master == "Y";
		}
		
		function isUsed()
		{
			return (!$this->isMaster()) && ($this->dateUsed != '0000-00-00 00:00:00');
		}
	}
	
	class OrderItem
	{
		public $id = -1;
		public $orderId = null;
		public $processingStagesId = null;
		public $barcode = null;
		public $shapeId = null;
		public $color = null;
		public $plasticCategoryId = null;
		public $creationDate = null;
		public $designId = null;
	}
	
	class Order
	{
		public $id = -1;
		public $userId;
		public $processingStagesId;
		public $startDate;
		public $submitDate = 0;
	}
	
	class PlasticCategory
	{
		public $id = -1;
		public $material;
	}
	
	class ProcessingStage
	{
		public $id = -1;
		public $keyName;
		public $name;
		public $shortName;
	}
	
	class ProductCategory
	{
		public $id = -1;
		public $name;
	}
	
	class Product
	{
		public $id = -1;
		public $code;
		public $width;
		public $height;
		public $longName;
		public $categoryId;
		public $allowGraphics;
		public $shapeId;
		public $frameWidth;
		public $frameHeight;
		public $productTypeId;		
	}
	
	
	
	class OrderDB
	{
		const DEBUG = TRUE;
		
		private $connection = NULL;		
	
		function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		
		//==============================================================================
		// Barcode
		//==============================================================================
	
		
		function loadBarcode($row)
		{
			$result = new Barcode();
			
			$result->barcode = $row['barcode'];
			$result->productId = $row['product_id'];
			$result->dateCreate = strtotime($row['date_created']);
			$result->master = $row['master'];
			$result->dateUsed = $row['date_used'];
			$result->templateCategoryId = $row['template_category_id'];
			$result->templateId = $row['template_id'];
				
			return $result;
		}
		
		function createBarcode(Barcode $barcode)
		{
			if(!is_null($barcode->dateCreate))
			{
				$query = sprintf(
						"INSERT INTO barcodes(barcode, product_id, date_created, master, date_used, template_category_id, template_id) VALUES ('%s', %d, '%s', '%s', '%s', %d, %d)",
						mysql_escape_string($barcode->barcode),
						$barcode->productId,
						date("Y-m-d H:i:s", $barcode->dateCreate),
						mysql_escape_string($barcode->master),
						mysql_escape_string($barcode->dateUsed),
						$barcode->templateCategoryId,
						$barcode->templateId
				);	
			}
			else
			{
				$query = sprintf(
						"INSERT INTO barcodes(barcode, product_id, date_created, master, date_used, template_category_id, template_id) VALUES ('%s', %d, NOW(), '%s', '%s', %d, %d)",
						mysql_escape_string($barcode->barcode),
						$barcode->productId,
						mysql_escape_string($barcode->master),
						mysql_escape_string($barcode->dateUsed),
						$barcode->templateCategoryId,
						$barcode->templateId
				);
			}
	
			if(!mysql_query($query,$this->connection))
			{
				//if(OrderDB::DEBUG) echo mysql_error();
				return false; 		
			}
			return true;
		}
		
		function updateBarcode(Barcode $barcode)
		{
			$query = sprintf(
					"UPDATE barcodes SET product_id=%d, master='%s', date_used='%s', template_category_id=%d, template_id=%d WHERE barcode = '%s'",
					$barcode->productId,
					mysql_escape_string($barcode->master),
					mysql_escape_string($barcode->dateUsed),
					$barcode->templateCategoryId,
					$barcode->templateId,
					mysql_escape_string($barcode->barcode)
				);
	
			if(!mysql_query($query,$this->connection))
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return false; 		
			}
			return true;
		}
		
		function getBarcodeByBarcode($barcode)
		{
			$query = sprintf("SELECT * FROM barcodes WHERE barcode='%s'", mysql_escape_string($barcode));
	
	
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
			
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
					
			return $this->loadBarcode($row);
		}
		
		function getBarcodeListByLastGenerated()
		{
			$query = "select * from barcodes where date_created like (SELECT max(date_created) FROM barcodes)";
		
			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(OrderDB::DEBUG) echo mysql_error();;
				return array();
			}
			
			$barcodes = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$barcodes[] = $this->loadBarcode($row);
			}
			
			return $barcodes;
		}
		
		
		
		function getBarcodeList() 
		{
			$query = "SELECT * FROM barcodes ORDER BY date_created";
		
			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(OrderDB::DEBUG) echo mysql_error();;
				return array();
			}
		
			$barcodes = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$barcodes[] = $this->loadBarcode($row);
			}
		
			return $barcodes;
		}
		
		function deleteBarcode($barcode)
		{
			$query = sprintf("DELETE FROM barcodes WHERE barcode='%s'",$barcode);
			$result = mysql_query($query,$this->connection);
		
			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
	
		//==============================================================================
		// OrderItem
		//==============================================================================
		
		
		function loadOrderItem($row)
		{
			$result = new OrderItem();
			
			$result->id = $row['id'];
			$result->orderId = $row['order_id'];
			$result->processingStagesId = $row['processing_stages_id'];
			$result->barcode = $row['barcode'];
			$result->shapeId = $row['shape_id'];
			$result->color = $row['color'];
			$result->plasticCategoryId = $row['plastic_category_id'];
			$result->creationDate = strtotime($row['date_created']);
			$result->designId = $row['design_id'];
				
			return $result;
		}
		
		function createOrderItem(OrderItem $orderItem)
		{
			$query = "INSERT INTO order_items(order_id, processing_stages_id, barcode, shape_id, color, plastic_category_id, date_created, design_id) VALUES (";
			$query = $query.sprintf("%d, %d, '%s', %d, '%s', %d, NOW(), %d )",
					$orderItem->orderId,
					$orderItem->processingStagesId,
					mysql_escape_string($orderItem->barcode),
					$orderItem->shapeId,
					mysql_escape_string($orderItem->color),
					$orderItem->plasticCategoryId,
					$orderItem->designId
			);
	
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$orderItem->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$orderItem->id = -1;
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function updateOrderItem(OrderItem $orderItem)
		{			
			$query = "UPDATE order_items SET ";
			$first = true;
			
			if(!is_null($orderItem->orderId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("order_id=%d", $orderItem->orderId); 
			}
				
			if(!is_null($orderItem->processingStagesId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("processing_stages_id=%d", $orderItem->processingStagesId);				
			}

			if(!is_null($orderItem->barcode))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("barcode='%s'", mysql_escape_string($orderItem->barcode));
			}
			
			if(!is_null($orderItem->shapeId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("shape_id=%d", $orderItem->shapeId);
			}
			
			if(!is_null($orderItem->color))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("color='%s'", mysql_escape_string($orderItem->color));
			}
				
			if(!is_null($orderItem->plasticCategoryId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("plastic_category_id=%d", $orderItem->plasticCategoryId);
			}

			if(!is_null($orderItem->designId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("design_id=%d", $orderItem->designId);
			}

			$query = $query.sprintf(" WHERE id=%d", $orderItem->id);
			
			if(!mysql_query($query,$this->connection))
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
			return true;
		}
		
		
		function getOrderItemById($id)
		{
			$query = sprintf("SELECT * FROM order_items WHERE id=%d", $id);			
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
			
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
					
			return $this->loadOrderItem($row);
		}
		
		function getOrderItemByBarcode($barcode, $withData = false)
		{
			$query = sprintf("SELECT * FROM order_items WHERE barcode='%s'", mysql_escape_string($barcode));
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadOrderItem($row);
		}	
	
		
		function getOrderItemsByOrderId($orderId, $processingStageId = NULL, $barcodeStr = NULL)
		{
			$query = sprintf("SELECT * FROM order_items WHERE order_id=%d", $orderId);			
			if(!is_null($processingStageId))
			{
				$query = $query.sprintf(" AND processing_stages_id=%d",$processingStageId);
			}
			
			if(!is_null($barcodeStr))
			{
				$query = $query.sprintf(" AND barcode='%s'",mysql_escape_string($barcodeStr));
			}
				
			$query = $query." ORDER BY id";
				
			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return array();
			}
				
			$orders = array();
			while($row = mysql_fetch_assoc($result))
			{
				$orders[] = $this->loadOrderItem($row);;
			}
			return $orders;
		}
		
		function deleteOrderItemsByOrderId($orderId, $processingStageId = NULL, $barcodeStr = NULL)
		{
			$query = sprintf("DELETE FROM order_items WHERE order_id=%d", $orderId);
			if(!is_null($processingStageId))
			{
				$query = $query.sprintf(" AND processing_stages_id=%d",$processingStageId);
			}
				
			if(!is_null($barcodeStr))
			{
				$query = $query.sprintf(" AND barcode='%s'",mysql_escape_string($barcodeStr));
			}
		
			if(mysql_query($query,$this->connection))
			{
				return true;
			}
			return false;
		}
		
		//==============================================================================
		// Order
		//==============================================================================
	
		
		function loadOrder($row)
		{
			$result = new Order();
	
			$result->id = $row['id'];
			$result->userId = $row['user_id'];
			$result->processingStagesId = $row['processing_stages_id'];
			$result->startDate = strtotime($row['start_date']);
			$result->submitDate = strtotime($row['submit_date']);
		
			return $result;
		}
		
		
		function createOrder(Order $order)
		{		
			$query = sprintf(
					"INSERT INTO orders(user_id, processing_stages_id, start_date, submit_date) VALUES (%d, %d, '%s', '%s')",
					$order->userId,
					$order->processingStagesId,
					date("Y-m-d H:i:s", $order->startDate),
					date("Y-m-d H:i:s", $order->submitDate)
				);
		
		
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$order->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$order->id = -1;
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function getOrderById($id)
		{
			$query = sprintf("SELECT * FROM orders WHERE id=%d", $id);	
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadOrder($row);
		}
				
		function getOrdersByUserId($userId, $processingStageId = NULL)
		{
			$query = sprintf("SELECT * FROM orders WHERE user_id=%d", $userId);
			
			if(!is_null($processingStageId))
			{
				$query = $query.sprintf(" AND processing_stages_id=%d",$processingStageId); 
			}

			$query = $query." ORDER BY id";
			
			$result = mysql_query($query,$this->connection);			
			if(!$result)
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return array();
			}
			
			$orders = array();
			while($row = mysql_fetch_assoc($result))
			{
				$orders[] = $this->loadOrder($row);;
			}
			return $orders;
		}
		
		
		//==============================================================================
		// PlasticCategory
		//==============================================================================
	
		
		function loadPlasticCategory($row)
		{
			$result = new PlasticCategory();
		
			$result->id = $row['id'];
			$result->material = $row['material'];
		
			return $result;
		}
		
		
		function createPlasticCategory(PlasticCategory $plasticCategory)
		{
			$query = sprintf(
					"INSERT INTO plastic_categories(material) VALUES ('%s')",
					mysql_escape_string($plasticCategory->material)
				);
		
		
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$plasticCategory->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$plasticCategory->id = -1;
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function getPlasticCategoryById($id)
		{
			$query = sprintf("SELECT * FROM plastic_categories WHERE id=%d", $id);
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadPlasticCategory($row);
		}
		
	
		//==============================================================================
		// ProcessingStage
		//==============================================================================
		
		
		function loadProcessingStage($row)
		{
			$result = new ProcessingStage();
		
			$result->id = $row['id'];
			$result->keyName = $row['key_name'];
			$result->name = $row['name'];
			$result->shortName = $row['short_name'];
		
			return $result;
		}
		
		
		function createProcessingStage(ProcessingStage $processingStage)
		{
			$query = sprintf(
					"INSERT INTO processing_stages(key_name, name, short_name) VALUES ('%s','%s','%s')",
					mysql_escape_string($processingStage->keyName),
					mysql_escape_string($processingStage->name),
					mysql_escape_string($processingStage->shortName)
			);
		
		
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$processingStage->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$processingStage->id = -1;
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function getProcessingStageById($id)
		{
			$query = sprintf("SELECT * FROM processing_stages WHERE id=%d", $id);
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadProcessingStage($row);
		}
	
	
		//==============================================================================
		// ProductCategory
		//==============================================================================
		
		
		function loadProductCategory($row)
		{
			$result = new ProductCategory();
		
			$result->id = $row['id'];
			$result->name = $row['name'];
		
			return $result;
		}
		
		
		function createProductCategory(ProductCategory $productCategory)
		{
			$query = sprintf(
					"INSERT INTO products_category( name ) VALUES ( '%s' )",
					mysql_escape_string($productCategory->name)
			);
		
		
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$productCategory->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$productCategory->id = -1;
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		
		public function getProductCategoryList()
		{
			$query = "SELECT id, name FROM products_category";
			$result = mysql_query($query,$this->connection);
		
			if(!$result)
			{
				if(DesignDB::DEBUG) echo mysql_error();
				return NULL;
			}
		
			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $row;
			}
			return $return;
		}
		
		function getProductCategoryById($id)
		{
			$query = sprintf("SELECT * FROM products_category WHERE id=%d", $id);
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadProductCategory($row);
		}
	
		function getProductCategoryByName($name)
		{
			$query = sprintf("SELECT * FROM products_category WHERE id=%d", mysql_escape_string($name));
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadProductCategory($row);
		}
		
		
		//==============================================================================
		// Product
		//==============================================================================
		
		
		function loadProduct($row)
		{
			$result = new Product();
		
			$result->id = $row['id'];
			$result->code = $row['code'];
			$result->width = $row['width'];
			$result->height = $row['height'];
			$result->longName = $row['long_name'];
			$result->categoryId = $row['category_id'];
			$result->allowGraphics = $row['allow_graphics'];
			$result->shapeId = $row['shape_id'];
			$result->frameWidth = $row['frame_width'];
			$result->frameHeight = $row['frame_height'];
			$result->productTypeId = $row['product_type_id'];
				
			return $result;
		}
		
		
		function createProduct(Product $product)
		{
			$query = sprintf(
					"INSERT INTO products( code, width, height, long_name, category_id, allow_graphics, shape_id, frame_width, frame_height, product_type_id ) ".
					"VALUES ( '%s', %f, %f, '%s', %d, %d, '%s', %f, %f, %d )",
					mysql_escape_string($product->code),
					$product->width,
					$product->height,
					mysql_escape_string($product->longName),
					$product->categoryId,
					$product->allowGraphics,
					mysql_escape_string($product->shapeId),
					$product->frameWidth,
					$product->frameHeight,
					$product->productTypeId						
				);
		
		
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$product->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$product->id = -1;
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function getProductById($id)
		{
			$query = sprintf("SELECT * FROM products WHERE id=%d", $id);
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadProduct($row);
		}
		
		function getProductByCode($code)
		{
			$query = sprintf("SELECT * FROM products WHERE code='%s'", mysql_escape_string($code));
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadProduct($row);
		}
		
		function getProductByOrderItemId($orderItemId)
		{			
			$query = sprintf(
					"SELECT products.* FROM products, barcodes, order_items  WHERE ".
					"(order_items.barcode = barcodes.barcode) AND (barcodes.product_id = products.id) AND (order_items.id = %d)", 
					$orderItemId
				);
		
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadProduct($row);
		}
		
		function getProducts()
		{
			$query = "SELECT * FROM products ORDER BY id";
			
			$result = mysql_query($query,$this->connection);
			if(!$result) 
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return array();
			}
			
			$product = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$product[] = $this->loadProduct($row);
			}
			
			return $product;
		}
		
		function getProductList()
		{
			$query = "SELECT * FROM products ORDER BY id";
				
			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return array();
			}
				
			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $row;
			}
			return $return;
		}
		
		function deleteProduct($id)
		{
			$query = sprintf("DELETE FROM products WHERE id=%d",$id);
			$result = mysql_query($query,$this->connection);
		
			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		
		function updateProduct(Product $product)
		{
			$query = "UPDATE products SET ";
			$first = true;
		
			if(!is_null($product->code))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("code='%s'", mysql_real_escape_string($product->code));
			}
			
			if(!is_null($product->width))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("width='%s'", $product->width);
			}
			
			if(!is_null($product->height))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("height='%s'", $product->height);
			}
			
			if(!is_null($product->longName))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("long_name='%s'", mysql_real_escape_string($product->longName));
			}
			
			if(!is_null($product->categoryId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("category_id='%s'", $product->categoryId);
			}
			
			if(!is_null($product->allowGraphics))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("allow_graphics='%s'", $product->allowGraphics);
			}
			
			if(!is_null($product->shapeId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("shape_id='%s'", mysql_real_escape_string($product->shapeId));
			}
			
			if(!is_null($product->frameWidth))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("frame_width='%s'", $product->frameWidth);
			}
			
			if(!is_null($product->frameHeight))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("frame_height='%s'", $product->frameHeight);
			}
			
			if(!is_null($product->productTypeId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("product_type_id='%s'", $product->productTypeId);
			}
					
			$query = $query.sprintf(" WHERE id=%d", $product->id);
			
			if(!mysql_query($query,$this->connection))
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
			return true;
		}
		
	
	
	
	
	
	
	
	
	

	
	
	
	
	
	
	
	
	
	
	
// 	/**
// 	 * Create a new order for a certain user.
// 	 * @param int $userId the user to create the order for.
// 	 * @return int the id of the order just created
// 	 */
// 	public function newOrder($userId){
// 		//echo "USERID: ".$userId;
// 		$startup = Startup::getInstance("../");
// 		$query = sprintf("INSERT INTO orders(user_id,processingstages_id,startdate) VALUES ('%d','%d',NOW())",$userId,
// 					$startup->processingstages[$startup->settings['default order processing stage']]);
// 		if(!$result = mysql_query($query,$this->connection)){
// 			echo "ERROR: ".mysql_error();
// 		}
// 		$order_id = mysql_insert_id($this->connection);
// 		$startup->session->setCurrentOrder($order_id);
// 		return $order_id;
// 	}
	
	
// 	/**
// 	 * Creates a new order if there isn't an order currently open. Then adds new item based on barcode
// 	 * to the current order and returns the item number.
// 	 * @param string $code the barcode of the item being added to the order
// 	 * @return int the item number from the database
// 	 */
// 	public function newOrderItem($code){
// 		$startup = Startup::getInstance("../");	
// 		$s = $startup->session;	
// 		$userId = $s->getUserId();
// 		/*
// 		 * Check to see if the user has an open order. If not then make one, otherwise get the order id
// 		 */
// 		$query = sprintf("SELECT id FROM orders WHERE user_id = '%d' and processingstages_id='%d'",$userId,
// 					$startup->processingstages[$startup->settings['default order processing stage']]);
// 		$result = mysql_query($query,$this->connection);
// 		$order_id = 0;
		
// 		if(mysql_affected_rows($this->connection)==0){
// 			$order_id = $this->newOrder($userId);
// 		}
// 		else{
// 			$row = mysql_fetch_assoc($result);
// 			$order_id = $row['id'];
// 		}	
// 		/*
// 		 * Set the barcode to be used if it's not a master code
// 		 */ 
// 		 if(!$this->isMasterCode($code)){
// 		 	$query = sprintf("UPDATE barcodes SET date=NOW() WHERE barcode='%s'",$code);
// 			 mysql_query($query,$this->connection);
// 		 }
// 		/*
// 		 * Add a new order item and get the order item id and return it.
// 		 */
// 		$query = sprintf("INSERT INTO orderitems (order_id,processingstages_id,barcode) VALUES ('%d','%d','%s')", $order_id,
// 					$startup->processingstages[$startup->settings['default order item processing stage']],$code);
// 		$result = mysql_query($query,$this->connection);
// 		$orderitem_id = mysql_insert_id($this->connection);
// 		return $orderitem_id;
// 	}
	
	
// 	public function updateOrderData($orderitem_id,$data){
// 		$query = sprintf("UPDATE orderitems SET data='%s' WHERE id=%d",$data,$orderitem_id);
// 		if(!$result = mysql_query($query,$this->connection)){
// 			return "ERROR: "+mysql_error($this->connection);
// 		}
// 		else return "Data update ok";
// 	}
	
// 	public function getOrderData($orderitem_id){
// 		$query = sprintf("SELECT data fROM orderitems WHERE id=%d",$orderitem_id);
// 		if(!$result = mysql_query($query,$this->connection)){
// 			echo mysql_error($this->connection);
// 		}
// 		$row = mysql_fetch_row($result);
// 		return $row[0];
// 	}
}
?>