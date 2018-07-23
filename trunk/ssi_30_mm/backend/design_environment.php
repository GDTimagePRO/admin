<?php
	require_once "settings.php";
	require_once "db_order.php";
	require_once "db_design.php";

	class PaletteColor
	{
		const COLOR_INK		= 'ink';

		public $name;
		public $value;

		public function __construct($name, $value)
		{
			$this->name = $name;
			$this->value = $value;
		}
	}

	class ActiveDesign
	{
		const EDITOR_SIMPLE		= 0;
		const EDITOR_ADVANCED	= 1;

		/**
		 * @var Design
		 */
		public $design = NULL;
		public $product = NULL;
		public $previewImageId = NULL;
		public $colorPalettes = NULL;
		public $defaultValues = NULL;


		/**
		 * @param ConfigItem $configItem
		 * @return ActiveDesign
		 */
		public static function createFromBarcodeConfigItem(ConfigItem $configItem, $externalDesignOptions)
		{
			$activeDesign = new ActiveDesign();

			$system = Startup::getInstance();
			$designDB = $system->db->design;
			$orderDB = $system->db->order;

			$product = $orderDB->getProductById($configItem->productId);
			if(is_null($product)) return NULL;

			$activeDesign->product = $product;

			$templateId = $configItem->templateId;
			if(is_null($templateId))
			{
				$templateId = $designDB->getDefualtDesignTemplateId($product->productTypeId);
				if($templateId < 0) return NULL;
			}

			$template = $designDB->getDesignTemplateById($templateId);
			if(is_null($template)) return NULL;

			$design = $activeDesign->design = new Design();
			$design->setConfigItem(ConfigItem::merge($configItem, $template->getConfigItem()));
			$design->orderItemId = -1;
			$design->productId = $product->id;
			$design->productTypeId = $product->productTypeId;
			$design->designJSON = $template->designJSON;
			$design->externalDesignOptions = $externalDesignOptions;
			$design->state = Design::STATE_PENDING_SCL_RENDERING;

			return $activeDesign;
		}

		public static function load($orderId)
		{
		}

		public function save()
		{
			$orderDB = Startup::getInstance()->db->order;
			$designDB = Startup::getInstance()->db->design;
			if($this->design->id == -1)
			{
				if(!$designDB->createDesign($this->design)) return false;

				$response = Settings::resourceOpCopy(
						$this->previewImageId,
						$this->design->getPreviewImageId()
					);
				return $response->errorCode == ResourceOpResult::CODE_OK;
			}
			else
			{
				if(!$designDB->updateDesign($this->design)) return false;
			}

			return true;
		}

	}


	class DesignEnvironment
	{
		const MODE_NEW_ORDER = 1;
		const MODE_EDIT_TEMPLATE = 20;

		public $mode = DesignEnvironment::MODE_NEW_ORDER;
		public $theme = NULL;

		public $batchImportQueueItemId = NULL;

		/**
		 * @var OrderItem
		 */
		public $orderItem = NULL;
		public $activeDesigns = array();

		/**
		 * @return DesignEnvironment
		 */
		public static function createFromTemplate($templateId, $sessionId, $productId)
		{
			$designEnvironment = new DesignEnvironment();
			$designEnvironment->mode = DesignEnvironment::MODE_EDIT_TEMPLATE;

			$orderItem = $designEnvironment->orderItem = new OrderItem();
			$config = new Config();
			$config->uiMode = Config::UI_MODE_NORMAL;
			$orderItem->setConfig($config);

			$configItem = new ConfigItem();
			$configItem->productId = $productId;
			$configItem->templateId = $templateId;
			$configItem->templateCategoryId = ConfigItem::TEMPLATE_CATEGORY_ID_WILDCARD;
			$activeDesign = ActiveDesign::createFromBarcodeConfigItem($configItem, null);

			$product = $activeDesign->product;
			$colors = json_decode($product->configJSON);
			$activeDesign->colorPalettes = array();

			if ($colors != null && isset($colors->colors)) {
				$activeDesign->colorPalettes[PaletteColor::COLOR_INK] = array();
				foreach ($colors->colors as $color) {
					array_push($activeDesign->colorPalettes[PaletteColor::COLOR_INK], new PaletteColor($color->name, $color->value));
				}
			} else {
				$activeDesign->colorPalettes[PaletteColor::COLOR_INK] =  array(
	// 					new PaletteColor('Fire Brick',		'B22222'),
	// 					new PaletteColor('Royal Blue',		'4169E1'),
	// 					new PaletteColor('Crimson',			'DC143C'),
	// 					new PaletteColor('Pale Violet Red',	'DB7093'),
	// 					new PaletteColor('Lime Green',		'32CD32'),
	// 					new PaletteColor('Dodger Blue',		'1E90FF'),
	// 					new PaletteColor('Sienna',			'A0522D'),
	// 					new PaletteColor('Slate Blue',		'6A5ACD'),
	// 					new PaletteColor('Black',			'000000')
	//					new PaletteColor('Blissful-Burgundy',		'A60F42'),
						new PaletteColor('Blueberry',				'235DA7'),
						new PaletteColor('Candy-Apple-Red',			'E61938'),
						new PaletteColor('Electrifyingly-Pink',		'ED628B'),
						new PaletteColor('Go Green',				'1DA038'),
						new PaletteColor('Mediterranean-Blue',		'2DA5BD'),
						new PaletteColor('Mocha-Brown',				'52240A'),
						new PaletteColor('Purple-Rain',				'5A3F82'),
						new PaletteColor('Midnight-Black',			'000000')
					);
			}


			$activeDesign->previewImageId = ResourceManager::getId(
					ResourceManager::GROUP_SESSION,
					$sessionId . '_item0_prev.png'
				);

			$designEnvironment->activeDesigns[] = $activeDesign;

			return $designEnvironment;
		}


		/**
		 * @param Barcode $barcode
		 * @return DesignEnvironment
		 */
		public static function createFromBarcode(Barcode $barcode, $sessionId, $externalDesignOptions )
		{
			$designEnvironment = new DesignEnvironment();
			$designEnvironment->mode = DesignEnvironment::MODE_NEW_ORDER;

			$orderItem = $designEnvironment->orderItem = new OrderItem();
			$orderItem->externalUserId = -1;
			$orderItem->externalOrderId = -1;
			$orderItem->externalOrderStatus = 0;
			$orderItem->externalSystemName = '';
			$orderItem->barcode = $barcode->barcode;
			$orderItem->customerId = $barcode->customerId;
			$orderItem->processingStagesId = ProcessingStage::STAGE_PENDING_CART_ORDER;

			$config = $barcode->getConfig();
			$configItems = $config->items;
			$designEnvironment->theme = $config->theme;

			if(is_null($configItems)) return NULL;

			$config->items = null;
			$orderItem->setConfig($config);

			$colorPalettes = array();
			if(count($configItems) > 1)
			{

				$colorPalettes[PaletteColor::COLOR_INK] = array(
//					new PaletteColor('Blissful-Burgundy',		'A60F42'),
					new PaletteColor('Blueberry',				'235DA7'),
					new PaletteColor('Candy-Apple-Red',			'E61938'),
					new PaletteColor('Electrifyingly-Pink',		'ED628B'),
					new PaletteColor('Go Green',				'1DA038'),
					new PaletteColor('Mediterranean-Blue',		'2DA5BD'),
					new PaletteColor('Mocha-Brown',				'52240A'),
					new PaletteColor('Purple-Rain',				'5A3F82'),
					new PaletteColor('Midnight-Black',			'000000')
				);
			}
			else
			{
				$colorPalettes[PaletteColor::COLOR_INK] = array(
						new PaletteColor('Midnight-Black',		'000000')
					);
			}

			$itemCount = 0;
			foreach($configItems as $item)
			{
				/* @var $item ConfigItem */
				$activeDesign = ActiveDesign::createFromBarcodeConfigItem($item, $externalDesignOptions);
				if(is_null($activeDesign)) return NULL;

				$product = $activeDesign->product;
				$colors = json_decode($product->configJSON);

				if ($colors != null && isset($colors->colors)) {
					$activeDesign->colorPalettes = array();
					$activeDesign->colorPalettes[PaletteColor::COLOR_INK] = array();
					foreach ($colors->colors as $color) {
						array_push($activeDesign->colorPalettes[PaletteColor::COLOR_INK], new PaletteColor($color->name, $color->value));
					}
				} else {
					$activeDesign->colorPalettes = $colorPalettes;
				}

				if(is_null($activeDesign)) return NULL;
				$activeDesign->previewImageId = ResourceManager::getId(
						ResourceManager::GROUP_SESSION,
						$sessionId . '_item' . ($itemCount++) . '_prev.png'
					);
				$designEnvironment->activeDesigns[] = $activeDesign;
			}

			return $designEnvironment;
		}

		/**
		 * @return DesignEnvironment
		 */
		public static function load($orderId)
		{
		}


		public function save()
		{
			if($this->mode == DesignEnvironment::MODE_NEW_ORDER)
			{
				return $this->saveAsOrder();
			}
			else if($this->mode == DesignEnvironment::MODE_EDIT_TEMPLATE)
			{
				return $this->saveAsTemplate();
			}

			return false;
		}

		public function saveAsTemplate()
		{
			$designDB = Startup::getInstance()->db->design;

			/* @var $activeDesign ActiveDesign */
			$activeDesign = $this->activeDesigns[0];

			$templateId = $activeDesign->design->getConfigItem()->templateId;
			$template = $designDB->getDesignTemplateById($templateId);
			if(is_null($template)) return false;

			$template->designJSON = $activeDesign->design->designJSON;
			if(!$designDB->updateDesignTemplate($template)) return false;


			$response = Settings::resourceOpCopy(
					$activeDesign->previewImageId,
					$template->getPreviewImageId()
			);
			return $response->errorCode == ResourceOpResult::CODE_OK;
		}

		public function saveAsOrder()
		{
			$orderDB = Startup::getInstance()->db->order;
			if($this->orderItem->id == -1)
			{
				if(!$orderDB->createOrderItem($this->orderItem)) return false;

				foreach($this->activeDesigns as $activeDesign)
				{
					/* @var $activeDesign ActiveDesign */
					$activeDesign->design->orderItemId = $this->orderItem->id;
				}
			}
			else
			{
				if(!$orderDB->updateOrderItem($this->orderItem)) return false;
			}



			if(count($this->activeDesigns) < 3)
			{
				$srcIds = null;
				foreach($this->activeDesigns as $activeDesign)
				{
					/* @var $activeDesign ActiveDesign */
					if(!$activeDesign->save()) return false;

					if(is_null($srcIds))
					{
						$srcIds = $activeDesign->design->getPreviewImageId();
					}
					else
					{
						$srcIds .= ',' . $activeDesign->design->getPreviewImageId();
					}
				}
				$size = 250;
			}
			else
			{
				$srcIds = null;
				foreach($this->activeDesigns as $activeDesign)
				{
					/* @var $activeDesign ActiveDesign */
					if(!$activeDesign->save()) return false;

					$rid = ResourceId::fromId($activeDesign->design->getPreviewImageId());
					$rid->type = ResourceManager::TYPE_THUMBNAIL;

					if(is_null($srcIds))
					{
						$srcIds = $rid->getId();
					}
					else
					{
						$srcIds .= ',' . $rid->getId();
					}
				}
				$size = 250;
			}

			$result = file_get_contents(
					Settings::SERVICE_CREATE_COLLAGE.
					'?srcIds=' . urlencode($srcIds) .
					'&destId=' . urlencode($this->orderItem->getPreviewImageId()) .
					'&width=' . $size . '&height=' . $size
			);

			return $result == 'true';
		}
	}
?>
