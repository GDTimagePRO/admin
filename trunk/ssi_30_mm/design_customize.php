<?php
	require_once "_common.php";
	require_once "./backend/design_elements.php";
	require_once "./backend/theme_interface.php";

	$designEnvironment =  is_null(Common::$session) ? null : Common::$session->designEnvironment;
	if(is_null($designEnvironment))
	{
		Common::$system->errorRedirect(Startup::URL_REDIRECT_NO_DESIGN_ENVIRONMENT);
	}

	//The user has clicked next or previous
	if(isset($_POST['stateJSON']))
	{
		/* @var $activeDesign ActiveDesign */
		$activeDesign = $designEnvironment->activeDesigns[$_POST['activeDesignIndex']];
		$activeDesign->design->designJSON = $_POST['stateJSON'];
		Common::$session->designEnvironment = $designEnvironment;
		Common::$session->save();
		header('Location: ' . $_POST['destURL']);
		exit();
	}

	//The template dialog is requesting the JSON for some template
	if(isset($_GET['templateId']))
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

		$template = Common::$designDB->getDesignTemplateById($_GET['templateId']);
		echo $template->designJSON;
		exit();
	}

	//The template dialog is requesting a tab
	if(isset($_GET['templateGroupId']))
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

		exit();
	}

	$designEnvironmentConfig = $designEnvironment->orderItem->getConfig();

	$selectedIndex = isset($_GET['page']) ? $_GET['page'] : 0;
	/* @var $activeDesign ActiveDesign */
	$activeDesign = $designEnvironment->activeDesigns[$selectedIndex];
	$activeDesignConfig = $activeDesign->design->getConfigItem();

	$showColorSelector = count($designEnvironment->activeDesigns) != 1;
	$simple_mode = $designEnvironmentConfig->uiMode == Config::UI_MODE_SIMPLE;

	$product = Common::$orderDB->getProductById($activeDesignConfig->productId);

	if($product->frameWidth < $product->width) $product->frameWidth = $product->width;
	if($product->frameHeight < $product->height) $product->frameHeight = $product->height;

	class DesignCustomize
	{
		public $scriptUiPanelBasic = 'ui_panel_basic.js';

		public $simpleMode = true;
		public $allowTemplateSelect = true;

		/**
		 * @var 	Product
		 */
		public $product = NULL;

		/**
		 * @var 	DesignEnvironment
		 */
		public $designEnvironment = NULL;


		/**
		 * @var 	ActiveDesign
		 */
		public $activeDesign = NULL;


		public function writeColorSelector()
		{
			global $activeDesign;
			$inkColors = $activeDesign->colorPalettes[PaletteColor::COLOR_INK];
			if(count($inkColors) > 1)
			{
				echo '<div class="color_selector_container" id="ink_color_palette">';
				echo '<div class="color_selector_label">Ink Colour</div>';

				foreach ($inkColors as $inkColor)
				{
					$icolor = $inkColor->value;
					if (strpos($icolor, "#") === 0) {
						$icolor = substr($icolor, 1, strlen($icolor));
					}
					/* @var $inkColor PaletteColor */
					echo sprintf(
							'<div class="color_selector_box" id="%s" style="background-color:#%s" onclick="_system.changeInkColour(\'%s\',\'%s\')"></div>',
							$icolor,
							$icolor,
							$inkColor->name,
							$icolor
					);
				}
				echo '</div>';
			}
		}

		public function writeHead()
		{
			global $product;
			global $activeDesign;
			global $selectedIndex;
			global $designEnvironment;
			global $designEnvironmentConfig;

			echo '<script src="js/browser_check.js"></script>' . "\n";

			echo '<script src="js/lib/jquery.json-2.3.min.js"></script>' . "\n";
			echo '<script src="js/lib/qtip/jquery.qtip-1.0.0-rc3.js"></script>' . "\n";
			echo '<script src="js/lib/file_upload/jquery.fileupload.js"></script>' . "\n";
			echo '<script src="js/lib/file_upload/main.js"></script>' . "\n";

			echo '<script src="js/design/system.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/maps.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/patterns.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/drawables.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/widgets.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/scene.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/script_container.js?v=' . Common::$version . '"></script>' . "\n";



			if($this->simpleMode)
			{
				echo '<script src="js/design/' . $this->scriptUiPanelBasic . '?v=' . Common::$version . '"></script>' . "\n";
			}
			else
			{
				echo '<script src="js/design/ui_panel_v2.js?v=' . Common::$version . '"></script>' . "\n";
			}

			echo '<script src="js/design/elements/prototype_element.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/elements/border_element.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/elements/image_element.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/elements/line_element.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script src="js/design/elements/text_element.js?v=' . Common::$version . '"></script>' . "\n";

			echo '<script src="js/design_customize_TI.js?v=' . Common::$version . '"></script>' . "\n";


			echo '<script type="text/javascript">' . "\n";

			echo '	System.COMMON_URL_VARS			= "' . Common::queryVars() . '";' . "\n";
			echo '	System.ACTIVE_DESIGN_INDEX		= "' . $selectedIndex . '";' . "\n";
			echo '	System.ASPECT_RATIO				= ' . ($product->height / $product->width) . ';' . "\n";


			if($product->productTypeId == Product::TYPE_ID_CIRCLE)
			{
				echo '	System.ASPECT_PAGE_TYPE		= System.PAGE_TYPE_CIRCLE;' . "\n";
			}
			else
			{
				echo '	System.ASPECT_PAGE_TYPE		= System.PAGE_TYPE_BOX;' . "\n";
			}

			echo '	System.IMAGE_SERVICE			= "' . Settings::SERVICE_GET_IMAGE . '";' . "\n";

			echo '	Scene.RENDER_SERVICE_URL		= "' . Settings::SERVICE_RENDER_SCENE . '";' . "\n";
			echo '	System.FONT_SERVICE_URL		= "' . Settings::SERVICE_GET_FONT . '";' . "\n";

			if($selectedIndex == 0)
			{
				$nav_prev = Common::$session->urlReturn;
			}
			else
			{
				$nav_prev = 'design_customize.php?page='.($selectedIndex - 1) . '&' . Common::queryVars();
			}

			if($selectedIndex == count($designEnvironment->activeDesigns) - 1)
			{
				$nav_next = 'confirm_design.php?' . Common::queryVars();
			}
			else
			{
				$nav_next = 'design_customize.php?page='.($selectedIndex + 1) . '&' . Common::queryVars();
			}


			$outputImageWidth_preview = 250;
			$outputImageHeight_preview = 250;

			if($product->width > $product->height)
			{
				$outputImageHeight_preview = round($outputImageHeight_preview * $product->height / $product->width);
			}
			else
			{
				$outputImageWidth_preview = round($outputImageWidth_preview * $product->width / $product->height);
			}

			$outputImageScale_trace = 90/25.4;

			$initStateJSON = str_replace("\\", "\\\\", $activeDesign->design->designJSON);
			$initStateJSON = str_replace('"', '\"', $initStateJSON);

			$productConfigJSON = str_replace("\\", "\\\\", $product->configJSON);
			$productConfigJSON = str_replace('"', '\"', $productConfigJSON);

			echo '	TI.nav_prev						= "' . $nav_prev . '";' . "\n";
			echo '	TI.nav_next						= "' . $nav_next . '";' . "\n";
			echo '	TI.designImageId				= "' . $activeDesign->previewImageId . '";' . "\n";
			echo '	TI.outputImageWidth_preview		= "' . $outputImageWidth_preview . '";' . "\n";
			echo '	TI.outputImageHeight_preview	= "' . $outputImageHeight_preview . '";' . "\n";
			echo '	TI.initStateJSON				= "' . $initStateJSON . '";' . "\n";
			echo '	TI.productWidth					= "' . $product->width . '";' . "\n";
			echo '	TI.productHeight				= "' . $product->height . '";' . "\n";
			echo "	TI.simpleMode 					= " . ($designEnvironmentConfig->uiMode == Config::UI_MODE_SIMPLE ? 'true' : 'false') . ";\n";
			echo "	TI.colorModel 					= '" . $product->colorModel . "';\n";
			echo "	TI.productConfigJSON			= '" . $productConfigJSON . "';\n";
			echo "	TI.serviceResourceOp			= '" . Settings::SERVICE_RESOURCE_OP . "';\n";

			if(!is_null($activeDesign->defaultValues))
			{
				$defaultValueJSON = str_replace("\\", "\\\\", json_encode($activeDesign->defaultValues));
				$defaultValueJSON = str_replace('"', '\"', $defaultValueJSON);
				echo '	TI.defaultValueJSON				= "' . $defaultValueJSON . '";' . "\n";
			}

			DesignElements::writeJS(Common::$session->customerId);

			echo '</script>' . "\n";
		}

		//maybe combine with other function
		private function writeImageDialogArtLibrary()
		{
			$list = array();

			if(Common::$session->customerId != '' )
			{
				$list[] = array(
					'id' => ResourceManager::getid(
						ResourceManager::GROUP_CUSTOMER,
						Common::$session->customerId . '/' . ResourceManager::DIR_CUSTOMER_ART
					),
					'name' => 'Art'
				);

			}
			foreach($list as $item)
	 		{
	 			echo sprintf("<li><a href='design_part/image_dialog_service.php?tabRID=%s&color_model=%s'>%s</a></li>",
		 				urlencode($item['id']),
	 					urlencode(Product::COLOR_MODEL_24_BIT),
		 				htmlspecialchars($item['name'])
		 			);
	 		}
		}

		//probably integrate in other function
		private function writeImageDialogCategories()
		{
			global $product;
	 		$list = array();

			/*if(!$this->simpleMode)
			{
		 		$list[] = array(
						'id' => ResourceManager::getId(ResourceManager::GROUP_LEGACY_IMAGES),
						'name' => 'Legacy'
		 			);
			}*/

			if(Common::$session->customerId != '')
			{
				if($this->simpleMode )
				{
					$list[] = array(
							'id' => Common::$session->getUploadDirId(),
							'name' => 'Uploaded'
					);
				}
				else
				{
					$list[] = array(
						'id' => ResourceManager::getId(
								ResourceManager::GROUP_CUSTOMER,
								Common::$session->customerId . '/' . ResourceManager::DIR_CUSTOMER_IMAGES
							),
						'name' => 'Customer'
					);

					$art_list = array(
						'id' => ResourceManager::getid(
							ResourceManager::GROUP_CUSTOMER,
							Common::$session->customerId . '/' . ResourceManager::DIR_CUSTOMER_ART
						),
						'name' => 'Art'
					);
					if(sizeof($art_list) > 2) {
						$list[] = $art_list;
					}
				}
			}

	 		foreach($list as $item)
	 		{
	 			echo sprintf("<li><a href='design_part/image_dialog_service.php?tabRID=%s&color_model=%s'>%s</a></li>",
		 				urlencode($item['id']),
	 					urlencode($product->colorModel),
		 				htmlspecialchars($item['name'])
		 			);
	 		}
		}

		private function writeTemplateDialogCategories()
		{
			global $activeDesignConfig;
	 		if($activeDesignConfig->templateCategoryId == ConfigItem::TEMPLATE_CATEGORY_ID_WILDCARD)
	 		{
	 			if(is_null(Common::$session->customerId) || (Common::$session->customerId == '')) return;
	 			$list = Common::$designDB->getDesignTemplateCategories(Common::$session->customerId);
	 		}
	 		else if(!is_null($activeDesignConfig->templateCategoryId))
	 		{
	 			$list = array();
	 			$cids = explode(',', $activeDesignConfig->templateCategoryId);
	 			foreach($cids as $cid)
	 			{
	 				$list[] = Common::$designDB->getDesignTemplateCategoryById($cid);
	 			}
	 		}
	 		else return;

	 		foreach($list as $item)
	 		{
	 			/* @var $item DesignTemplateCategory */
	 			echo sprintf("<li><a href='design_part/template_dialog_service.php?tabTemplateCategoryId=%s'>%s</a></li>",
	 					urlencode($item->id),
	 					htmlspecialchars($item->name)
	 			);
	 		}
		}

		public function writeBodyHeader()
		{
		}

		public function writeBodyFooter()
		{
			?>
			<div id="fade"></div>

			<div id="dialog_add_element" title="Add element" class="hidden">
				<table style="margin-left:auto; margin-right:auto; margin-top:15px;">
				<tr>
					<td><button class="add_element_button" id="addTextLine">Text<br>Line</button></td>
					<td><button class="add_element_button" id="addTextCircle">Text<br>Circle</button></td>
					<td><button class="add_element_button" id="addTextEllipse">Text<br>Ellipse</button></td>
				</tr>
				<tr>
					<td><button class="add_element_button" id="addBorderRectangle">Pattern<br>Box</button></td>
					<td><button class="add_element_button" id="addBorderCircle">Pattern<br>Circle</button></td>
					<td><button class="add_element_button" id="addBorderEllipse">Pattern<br>Ellipse</button></td>
				</tr>
				<tr>
					<td><button class="add_element_button" id="addImageElement">Image or<br>Symbol</button></td>
					<td><button class="add_element_button" id="addLineElement">Line</button></td>
				<td></td>
				</tr>
				</table>
			</div>

				<div id="dialog_upload_image" title="Upload Image" class="hidden">
					For best results please select an image that is larger than 950 by 950 pixels.
					<input type="file" id="file" name="file" style="margin-top:15px;margin-left:auto;margin-right:auto;display: block;"/>
				</div>
				<div id="dialog_user_select_image" title="Select Image" class="hidden">
					<div id="dialog_select_image_tabs" style="width:100%;">
						<ul>
							<?php $this->writeImageDialogArtLibrary(); ?>
						</ul>
					</div>
				</div>
				<div id="dialog_select_image" title="Select Image" class="hidden">
					<div id="dialog_select_image_tabs" style="width:100%;">
						<ul>
							<?php $this->writeImageDialogCategories(); ?>
						</ul>
					</div>
				</div>

			<div id="dialog_select_template" title="Select New Design" class="hidden">
				<div id="dialog_select_template_tabs" style="width:100%;">
					<ul>
						<?php $this->writeTemplateDialogCategories(); ?>
					</ul>
				</div>
			</div>

			<div id="dialog_confirm_previous" title="Abandon Design" class="hidden">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>If you return to the previous page all changes your design will be lost.<br><br> Are you sure you wish to continue ?</p>
			</div>

			<div id="dialog_delete_element" title="Remove element" class="hidden">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to remove the "<span id="dialog_delete_element_name"></span>" element from your design.<br><br> Are you sure you wish to continue ?</p>
			</div>

			<div id="overlay" style="visibility:hidden;width: 100%; height: 100%; display: block; position: absolute; left: 0; top: 0; z-index: 99999;">
				<div class="ui-widget-overlay"></div>
				<div style="width: 250px; margin: 0 auto;  position:relative; top: 30%; border-radius: 10px; border: 6px solid; border-color: #000; color: #000000; background: #FFFFFF;text-align: center;">
					<h2 id="overlay_text"></h2>
					<div id="save_progress_bar"></div>
				</div>
			</div>

			<div id="uiPanelSlider" class="class_box_shadow" style="position:absolute; height:100px;"></div>

			<form id="navForm" method="POST">
				<input type="hidden" id="stateJSON" name="stateJSON">
				<input type="hidden" id="destURL" name="destURL">
				<input type="hidden" id="activeDesignIndex" name="activeDesignIndex">
			</form>


			<?php
		}
	}

	$ti = new ThemeInterface();
	$ti->customerId = Common::$session->customerId;

	if(isset(Common::$session->designEnvironment->theme) && (Common::$session->designEnvironment->theme != ''))
	{
		$ti->themeName = Common::$session->designEnvironment->theme;
	}
	else $ti->themeName = Common::$session->config->theme;

	$ti->HOME_URL = 'themes/' . $ti->themeName;
	$ti->sessionId = Common::$session->sessionId;
	$ti->config = Common::$session->config;
	$ti->vars['PRODUCT_NAME'] = $product->longName;

	if(!is_null($designEnvironment->batchImportQueueItemId))
	{
		$ti->vars['PRODUCT_NAME'] .= ' ( ' . $designEnvironment->orderItem->barcode . ' : ' . $selectedIndex . ' )';
	}



	//public $simpleMode = true;
	//public $allowTemplateSelect = true;

	$container = new DesignCustomize();
	$container->simpleMode = $designEnvironmentConfig->uiMode == Config::UI_MODE_SIMPLE;
	$container->allowTemplateSelect = ($activeDesignConfig->templateCategoryId != NULL) || !$container->simpleMode;
	$container->product = $product;
	$container->designEnvironment = $designEnvironment;
	$container->activeDesign = $activeDesign;


	require_once 'themes/' . strtolower($ti->themeName) . '/design_customize.php';

	themeMain($ti, $container);
?>
