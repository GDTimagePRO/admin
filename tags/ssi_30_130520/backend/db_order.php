<?php
	class ConfigItem
	{
		public $productId = null;
		public $templateId = null;
		public $templateCategoryId = null;
		
		public function toJSONObject()
		{
			$o = array();
			if(!is_null($this->productId)) $o['prod_id'] = $this->productId;
			if(!is_null($this->templateId)) $o['templ_id'] = $this->templateId;
			if(!is_null($this->templateCategoryId)) $o['tc_id'] = $this->templateCategoryId;
			return $o;
		}
		
		public function toJSON()
		{
			return json_encode($this->toJSONObject());
		}
		
		public static function fromJSONObject($o)
		{
			$result = new ConfigItem();
			if(isset($o->prod_id)) $result->productId = $o->prod_id;
			if(isset($o->templ_id)) $result->templateId = $o->templ_id;
			if(isset($o->tc_id)) $result->templateCategoryId = $o->tc_id;
			return $result;
		}
		
		public static function fromJSON($json)
		{
			if(($json == '') || is_null($json)) return new ConfigItem();
			return ConfigItem::fromJSONObject(json_decode($json));
		}
	}	
	
	class Config
	{
		const UI_MODE_NORMAL = null;
		const UI_MODE_SIMPLE = "simple";
		
		public $uiMode = null;
		public $inkColor = null;
		public $items = null;
		
		public function toJSON()
		{
			$result = array();
			if(!is_null($this->uiMode)) $result['ui_mode'] = $this->uiMode;  
			if(!is_null($this->inkColor)) $result['inkColor'] = $this->inkColor;
				
			if(!is_null($this->items))
			{
				$itemDataList = array(); 
				foreach($this->items as $item)
				{
					$itemDataList[] = $item->toJSONObject();
				}
				$result['items'] = $itemDataList;
			}
									
			return json_encode($result);
		}
		
		public static function fromJSON($json)
		{
			$result = new Config();
			 
			if(($json == '') || is_null($json)) return $result;
				
			$o = json_decode($json);
				
			if(isset($o->ui_mode)) $result->uiMode = $o->ui_mode;
			if(isset($o->inkColor)) $result->inkColor = $o->inkColor;
			
			if(isset($o->items))
			{
				$result->items = array();
				foreach($o->items as $itemData)
				{
					$result->items[] = ConfigItem::fromJSONObject($itemData);
				}
			}
			
			return $result;
		}	
	}
	
	class Barcode
	{
		public $barcode = null;
		public $dateCreate = null;
		public $master = null;
		public $dateUsed = '0000-00-00 00:00:00';
		public $configJSON = null;

		function getConfig()
		{
			return Config::fromJSON($this->configJSON);
		}		
		
		function setConfig(Config $value)
		{
			$this->configJSON = $value->toJSON();
		}		
		
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
		public $creationDate = null;
		public $configJSON = null;
		public $manufacturerId = null;
		public $manufacturerOrderId = null;
		public $manufacturerOrderStatus = null;
		public $workstationPrintTag = null;
		
		
		
		public static function previewImageId($orderItemId)
		{
			return ImageDB::GROUP_ORDER_ITEMS . '/' . $orderItemId . '_prev.png';
		}
		
		public function getPreviewImageId() { return OrderItem::previewImageId($this->id); }
		
		
		function getConfig()
		{
			return Config::fromJSON($this->configJSON);
		}
				
		function setConfig(Config $value)
		{
			$this->configJSON = $value->toJSON();
		}
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
		const STAGE_PENDING_CANCELED		= 100;
		const STAGE_PENDING_CONFIRMATION	= 200;
		const STAGE_PENDING_CART_ORDER		= 300;
		const STAGE_PENDING_RENDERING		= 350;
		const STAGE_READY					= 400;
		const STAGE_PRINTED					= 425;
		const STAGE_SHIPPED					= 450;
		const STAGE_ARCHIVED				= 500;
		
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
		const MANUFACTURER_ID_NA		= 0;
		const MANUFACTURER_ID_MASON_ROW = 1000;
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
			$result->dateCreate = strtotime($row['date_created']);
			$result->configJSON = $row['config_json'];
			$result->master = $row['master'];				
			$result->dateUsed = $row['date_used'];
				
			return $result;
		}
		
		function createBarcode(Barcode $barcode)
		{
			if(!is_null($barcode->dateCreate))
			{
				$query = sprintf(
						"INSERT INTO barcodes(barcode, date_created, config_json, master, date_used) VALUES ('%s', '%s', '%s', '%s', '%s')",
						mysql_escape_string($barcode->barcode),
						date("Y-m-d H:i:s", $barcode->dateCreate),
						mysql_escape_string($barcode->configJSON),
						mysql_escape_string($barcode->master),
						mysql_escape_string($barcode->dateUsed)
					);	
			}
			else
			{
				$query = sprintf(
						"INSERT INTO barcodes(barcode, date_created, config_json, master, date_used) VALUES ('%s', NOW(), '%s', '%s', '%s')",
						mysql_escape_string($barcode->barcode),
						mysql_escape_string($barcode->configJSON),
						mysql_escape_string($barcode->master),
						mysql_escape_string($barcode->dateUsed)
					);	
			}
	
			if(!mysql_query($query,$this->connection))
			{
				//if(OrderDB::DEBUG) echo mysql_error();
				echo mysql_error();
				return false; 		
			}
			return true;
		}
		
		function updateBarcode(Barcode $barcode)
		{
			$query = sprintf(
					"UPDATE barcodes SET config_json='%s', master='%s', date_used='%s' WHERE barcode = '%s'",
					mysql_escape_string($barcode->configJSON),
					mysql_escape_string($barcode->master),
					mysql_escape_string($barcode->dateUsed),
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
			$result->creationDate = strtotime($row['date_created']);
			$result->configJSON = $row['config_json'];
			$result->manufacturerId = $row['manufacturer_id'];
			$result->manufacturerOrderId = $row['manufacturer_order_id'];
			$result->manufacturerOrderStatus = $row['manufacturer_order_status'];
			$result->workstationPrintTag = $row['workstation_print_tag'];
			return $result;
		}
		
		function createOrderItem(OrderItem $orderItem)
		{
			$query = "INSERT INTO order_items(order_id, processing_stages_id, barcode, date_created, config_json, manufacturer_id, manufacturer_order_id, manufacturer_order_status, workstation_print_tag) VALUES (";
			$query = $query.sprintf("%d, %d, '%s', NOW(), '%s', %d, %d, %d, '%s')",
					$orderItem->orderId,
					$orderItem->processingStagesId,
					mysql_escape_string($orderItem->barcode),
					mysql_escape_string($orderItem->configJSON),
					$orderItem->manufacturerId,
					$orderItem->manufacturerOrderId,
					$orderItem->manufacturerOrderStatus,
					mysql_escape_string($orderItem->workstationPrintTag)
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
			
			if(!is_null($orderItem->configJSON))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("config_json='%s'", mysql_escape_string($orderItem->configJSON));
			}			

			if(!is_null($orderItem->manufacturerId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("manufacturer_id=%d", $orderItem->manufacturerId);
			}			
				
			if(!is_null($orderItem->manufacturerOrderId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("manufacturer_order_id=%d", $orderItem->manufacturerOrderId);
			}
							
			if(!is_null($orderItem->manufacturerOrderStatus))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("manufacturer_order_status=%d", $orderItem->manufacturerOrderStatus);
			}

			if(!is_null($orderItem->workstationPrintTag))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("workstation_print_tag='%s'", mysql_escape_string($orderItem->workstationPrintTag));
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
		
		function cancelActiveOrderItemsByOrderId($orderId, $barcodeStr = NULL)
		{
			$query = sprintf(
					"UPDATE order_items SET processing_stages_id=%d WHERE processing_stages_id=%d AND order_id=%d", 
					ProcessingStage::STAGE_PENDING_CANCELED,
					ProcessingStage::STAGE_PENDING_CONFIRMATION,
					$orderId);
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


		//==============================================================================
		// Product
		//==============================================================================
		
		function getManufacturerIdForURL($url)
		{
			return is_null($url) ? OrderDB::MANUFACTURER_ID_NA : OrderDB::MANUFACTURER_ID_MASON_ROW; 
		}
		
	}
?>