<?php
	class ConfigItem
	{
		const TEMPLATE_CATEGORY_ID_WILDCARD = '*';

		public $productId = NULL;
		public $templateId = NULL;
		public $templateCategoryId = NULL;
		public $colors = NULL;
		public $misc = NULL;


		/**
		 * @return ConfigItem
		 */
		public static function merge($primary, $secondary)
		{
			if(is_null($primary)) $primary = new ConfigItem();
			if(is_null($secondary)) $secondary = new ConfigItem();

			$result = new ConfigItem();
			$result->productId = is_null($primary->productId) ?  $secondary->productId : $primary->productId;
			$result->templateId = is_null($primary->templateId) ?  $secondary->templateId : $primary->templateId;
			$result->templateCategoryId = is_null($primary->templateCategoryId) ?  $secondary->templateCategoryId : $primary->templateCategoryId;
			$result->colors = is_null($primary->colors) ?  $secondary->colors : $primary->colors;
			$result->misc = is_null($primary->misc) ?  $secondary->misc : $primary->misc;
			return $result;
		}

		public function toJSONObject()
		{
			$o = array();
			if(!is_null($this->productId)) $o['prod_id'] = $this->productId;
			if(!is_null($this->templateId)) $o['templ_id'] = $this->templateId;
			if(!is_null($this->templateCategoryId)) $o['tc_id'] = $this->templateCategoryId;
			if(!is_null($this->colors)) $o['colors'] = $this->colors;
			if(!is_null($this->misc)) $o['misc'] = $this->misc;
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
			if(isset($o->colors)) $result->colors = $o->colors;
			if(isset($o->misc)) $result->misc = $o->misc;
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
		const UI_MODE_NORMAL = NULL;
		const UI_MODE_SIMPLE = "simple";

		public $uiMode = NULL;
		public $items = NULL;
		public $theme = NULL;

		public function toJSON()
		{
			$result = array();
			if(!is_null($this->uiMode)) $result['ui_mode'] = $this->uiMode;

			if(!is_null($this->items))
			{
				$itemDataList = array();
				foreach($this->items as $item)
				{
					$itemDataList[] = $item->toJSONObject();
				}
				$result['items'] = $itemDataList;
				$result['theme'] = $this->theme;
			}

			return json_encode($result);
		}

		public static function fromJSON($json)
		{
			$result = new Config();

			if(($json == '') || is_null($json)) return $result;

			$o = json_decode($json);

			if(isset($o->ui_mode)) $result->uiMode = $o->ui_mode;

			if(isset($o->items))
			{
				$result->items = array();
				foreach($o->items as $itemData)
				{
					$result->items[] = ConfigItem::fromJSONObject($itemData);
				}
			}
			if(isset($o->theme)) $result->theme = $o->theme;

			return $result;
		}
	}

	class Barcode
	{
		public $barcode = NULL;
		public $customerId = NULL;
		public $dateCreated = NULL;
		public $configJSON = NULL;
		public $master = NULL;
		public $dateUsed = '0000-00-00 00:00:00';

		/**
		 * @return Config
		 */
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
			return (!$this->isMaster()) && ($this->dateUsed != 0);
		}
	}

	class CustomerConfig
	{
		public $theme;
		public $render_email; // svg | png
		public $vars; //{TITLE:"Theme Page Title}"
	}

	class Customer
	{
		const KEY_INTERNAL = '*';

		public $id = -1;
		public $idKey = NULL;
		public $domain = NULL;
		public $description = NULL;
		public $emailAddress = NULL;
		public $configJSON = NULL;

		/**
		 * @return CustomerConfig
		 */
		public function getConfigObj()
		{
			if(is_null($this->configJSON)) return new CustomerConfig();
			return json_decode($this->configJSON);
		}
	}

	class OrderItem
	{
		public $id = -1;
		public $customerId = NULL;
		public $barcode = NULL;
		public $processingStagesId = NULL;
		public $creationDate = NULL;
		public $configJSON = NULL;
		public $externalOrderId = NULL;
		public $externalOrderStatus = NULL;
		public $externalUserId = NULL;
		public $externalSystemName = NULL;


		public static function previewImageId($orderItemId)
		{
			return ResourceManager::GROUP_ORDER_ITEMS . '/' . $orderItemId . '_prev.png';
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
		const TYPE_ID_CIRCLE				= 1;
		const TYPE_ID_RECTANGLE				= 2;

		const COLOR_MODEL_1_BIT				= '1_BIT';
		const COLOR_MODEL_24_BIT			= '24_BIT';

		public $id = -1;
		public $code = NULL;
		public $width = NULL;
		public $height = NULL;
		public $longName = NULL;
		public $categoryId = NULL;
		public $allowGraphics = NULL;
		public $shapeId = NULL;
		public $frameWidth = NULL;
		public $frameHeight = NULL;
		public $productTypeId = NULL;
		public $colorModel = NULL;
		public $configJSON = NULL;
	}

	class OrderDB
	{
		const CUSTOMER_ID_NA		= 0;
		const CUSTOMER_ID_MASON_ROW = 1;
		const DEBUG = TRUE;

		private $connection = NULL;

		function __construct($connection)
		{
			$this->connection = $connection;
		}


		//==============================================================================
		// Barcode
		//==============================================================================

		const BARCODES_FIELDS = 'barcode, customer_id, UNIX_TIMESTAMP(date_created) AS date_created, config_json, master, UNIX_TIMESTAMP(date_used) AS date_used';

		function loadBarcode($row)
		{
			$result = new Barcode();

			$result->barcode = $row['barcode'];
			$result->customerId = $row['customer_id'];
			$result->dateCreated = $row['date_created'];
			$result->configJSON = $row['config_json'];
			$result->master = $row['master'];
			$result->dateUsed = $row['date_used'];

			return $result;
		}

		function createBarcode(Barcode $barcode)
		{
			if(!is_null($barcode->dateCreated))
			{
				$query = sprintf(
						"INSERT INTO barcodes(barcode, customer_id, date_created, config_json, master, date_used) VALUES ('%s', %d, FROM_UNIXTIME(%d), '%s', '%s', FROM_UNIXTIME(%d))",
						mysql_escape_string($barcode->barcode),
						$barcode->customerId,
						$barcode->dateCreated,
						mysql_escape_string($barcode->configJSON),
						mysql_escape_string($barcode->master),
						$barcode->dateUsed
					);
			}
			else
			{
				$query = sprintf(
						"INSERT INTO barcodes(barcode, customer_id, date_created, config_json, master, date_used) VALUES ('%s', %d, FROM_UNIXTIME(%d), '%s', '%s', FROM_UNIXTIME(%d))",
						mysql_escape_string($barcode->barcode),
						$barcode->customerId,
						time(),
						mysql_escape_string($barcode->configJSON),
						mysql_escape_string($barcode->master),
						$barcode->dateUsed
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
					"UPDATE barcodes SET config_json='%s', master='%s', date_used=FROM_UNIXTIME(%d) WHERE barcode = '%s' AND customer_id=%d",
					mysql_escape_string($barcode->configJSON),
					mysql_escape_string($barcode->master),
					$barcode->dateUsed,
					mysql_escape_string($barcode->barcode),
					$barcode->customerId
				);

			if(!mysql_query($query,$this->connection))
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
			return true;
		}

		function getBarcodeByBarcode($customerId, $barcode)
		{
			$query = sprintf("SELECT %s FROM barcodes WHERE barcode='%s' AND customer_id=%d",
					OrderDB::BARCODES_FIELDS,
					mysql_escape_string($barcode),
					$customerId
			);

			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;

			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;

			return $this->loadBarcode($row);
		}

		function getBarcode($barcode)
		{
			$query = sprintf("SELECT %s FROM barcodes WHERE barcode='%s'",
					OrderDB::BARCODES_FIELDS,
					mysql_escape_string($barcode),
					$customerId
			);

			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;

			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;

			return $this->loadBarcode($row);
		}

		function getBarcodeListByLastGenerated()
		{
			$query = "select " . OrderDB::BARCODES_FIELDS .  " from barcodes where date_created like (SELECT max(date_created) FROM barcodes)";

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
			$query = "SELECT " . OrderDB::BARCODES_FIELDS . " FROM barcodes ORDER BY date_created";

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

		function deleteBarcode($customerId, $barcode)
		{
			$query = sprintf("DELETE FROM barcodes WHERE barcode='%s' AND customer_id=%d",$barcode, $customerId);
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
		// Customer
		//==============================================================================
		function loadCustomer($row)
		{
			$result = new Customer();

			$result->id = $row['id'];
			$result->idKey = $row['id_key'];
			$result->domain = $row['domain'];
			$result->description = $row['description'];
			$result->emailAddress = $row['email_address'];
			$result->configJSON = $row['config_json'];

			return $result;
		}

		function getCustomerById($id)
		{
			$query = sprintf("SELECT * FROM customers WHERE id=%d", $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;

			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;

			return $this->loadCustomer($row);
		}

		function getCustomerByKey($idKey)
		{
			$query = sprintf("SELECT * FROM customers WHERE id_key='%s'", mysql_escape_string($idKey));
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;

			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;

			return $this->loadCustomer($row);
		}

		function getCustomerList()
		{
			$query = "SELECT * FROM Customers ORDER BY id";

			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return array();
			}

			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $this->loadCustomer($row);
			}
			return $return;
		}

		//==============================================================================
		// OrderItem
		//==============================================================================
		const ORDER_ITEMS_FIELDS = 'id, customer_id, barcode, processing_stages_id, UNIX_TIMESTAMP(date_created) as date_created, config_json, external_order_id, external_order_status, external_user_id, external_system_name';

		function loadOrderItem($row)
		{
			$result = new OrderItem();

			$result->id = $row['id'];
			$result->customerId = $row['customer_id'];
			$result->barcode = $row['barcode'];
			$result->processingStagesId = $row['processing_stages_id'];
			$result->creationDate = $row['date_created'];
			$result->configJSON = $row['config_json'];
			$result->externalOrderId = $row['external_order_id'];
			$result->externalOrderStatus = $row['external_order_status'];
			$result->externalUserId = $row['external_user_id'];
			$result->externalSystemName = $row['external_system_name'];

			return $result;
		}

		function createOrderItem(OrderItem $orderItem)
		{
			$query = "INSERT INTO order_items(customer_id, barcode, processing_stages_id, date_created, config_json, external_order_id, external_order_status, external_user_id, external_system_name) VALUES (";
			$query = $query.sprintf("%d, '%s', %d, FROM_UNIXTIME(%d), '%s', %d, %d, %d , '%s')",
					$orderItem->customerId,
					mysql_escape_string($orderItem->barcode),
					$orderItem->processingStagesId,
					time(),
					mysql_escape_string($orderItem->configJSON),
					$orderItem->externalOrderId,
					$orderItem->externalOrderStatus,
					$orderItem->externalUserId,
					mysql_escape_string($orderItem->externalSystemName)
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

			if(!is_null($orderItem->customerId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("customer_id=%d", $orderItem->customerId);
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

			if(!is_null($orderItem->externalOrderId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				//$query = $query.sprintf("external_order_id=%d", $orderItem->externalOrderId);
				$query .= "external_order_id=" .$orderItem->externalOrderId;
			}

			if(!is_null($orderItem->externalOrderStatus))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_order_status=%d", $orderItem->externalOrderStatus);
			}

			if(!is_null($orderItem->externalOrderStatus))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_order_options='%s'", $orderItem->externalOrderOptions);
			}

			if(!is_null($orderItem->externalUserId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_user_id=%d", $orderItem->externalUserId);
			}

			if(!is_null($orderItem->externalSystemName))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_system_name='%s'", mysql_escape_string($orderItem->externalSystemName));
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
			$query = sprintf("SELECT %s FROM order_items WHERE id=%d", OrderDB::ORDER_ITEMS_FIELDS, $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;

			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;

			return $this->loadOrderItem($row);
		}

		function getOrderItemByBarcode($barcode, $withData = false)
		{
			$query = sprintf("SELECT %s FROM order_items WHERE barcode='%s'", OrderDB::ORDER_ITEMS_FIELDS, mysql_escape_string($barcode));
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;

			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;

			return $this->loadOrderItem($row);
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
			$result->colorModel = $row['color_model'];
			$result->configJSON = $row['config_json'];
			$result->customer	= $row['customer'];

			return $result;
		}


		function createProduct(Product $product)
		{
			$query = sprintf(
					"INSERT INTO products( code, width, height, long_name, category_id, allow_graphics, shape_id, frame_width, frame_height, product_type_id, color_model, config_json ) ".
					"VALUES ( '%s', %f, %f, '%s', %d, %d, '%s', %f, %f, %d, '%s', '%s' )",
					mysql_escape_string($product->code),
					$product->width,
					$product->height,
					mysql_escape_string($product->longName),
					$product->categoryId,
					$product->allowGraphics,
					mysql_escape_string($product->shapeId),
					$product->frameWidth,
					$product->frameHeight,
					$product->productTypeId,
					mysql_escape_string($product->colorModel),
					mysql_escape_string($product->configJSON)
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

			if(!is_null($product->colorModel))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("color_model='%s'", mysql_real_escape_string($product->colorModel));
			}

			if(!is_null($product->configJSON))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("config_json='%s'", mysql_real_escape_string($product->configJSON));
			}

			$query = $query.sprintf(" WHERE id=%d", $product->id);

			if(!mysql_query($query,$this->connection))
			{
				if(OrderDB::DEBUG) echo mysql_error();
				return false;
			}
			return true;
		}
	}
?>
