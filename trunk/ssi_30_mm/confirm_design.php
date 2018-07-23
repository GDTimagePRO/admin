<?php
	include_once "_common.php";
	require_once "./backend/theme_interface.php";
	
	$designEnvironment =  is_null(Common::$session) ? null : Common::$session->designEnvironment;
	if(is_null($designEnvironment))
	{
		Common::$system->errorRedirect(Startup::URL_REDIRECT_NO_DESIGN_ENVIRONMENT);
	}
	
	$errorHTML = "";
	
	class ConfirmDesign
	{
		public $designImageIds = array();
		public $designImageIds_S = array();
		public $designImageIds_L = array();
		public $designProductNames = array();
		public $designColors= array();	
		public $designEnvironment;
		
		public function writeHead()
		{
			global $designEnvironment;
			echo '<script src="js/confirm_design_TI.js?v=' . Common::$version . '"></script>' . "\n";
			echo '<script type="text/javascript">';
			echo 'TI.returnURL = "design_customize.php?' . Common::queryVars() . '&page=' . (count($designEnvironment->activeDesigns) -1) . '"';	
			echo '</script>';
		}
		
		public function writeErrors()
		{
			global $errorHTML;
			if($errorHTML != "")
			{
				echo '<div id="error">'.$errorHTML.'</div>';
				echo '<div id="blank">&nbsp;</div>';
			}				
		}
		
		
		public function writeBodyHeader()
		{
			
		}
		
		public function writeBodyFooter()
		{
			?>
			<div id="termsAccepted" title="Terms not accepted" class="hidden">
			<p>
			<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 50px 0;"></span>
			You must accept the terms and conditions before you can continue.
			</p>
			</div>
			<form id="TI_submit" method="post"><input type="hidden" name="finishButton" value="Finish"></form>			
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
	
	
	$container = new ConfirmDesign();
	$container->designEnvironment = $designEnvironment;
	
	require_once 'themes/' . strtolower($ti->themeName) . '/confirm_design.php';
	
	
	for($i=0; $i<count($designEnvironment->activeDesigns); $i++)
	{
		/* @var $activeDesign ActiveDesign */
		$activeDesign = $designEnvironment->activeDesigns[$i];
		
		$container->designImageIds_L[] = $activeDesign->previewImageId; 
		
		$rid = ResourceId::fromId($activeDesign->previewImageId);
		$colors = $activeDesign->design->getColorsFromJSON();
		
		$rid->type = ResourceManager::TYPE_WEB;
		$container->designColors[] = $colors->ink->name;
		$container->designImageIds[] = $rid->getId();
		
		$designConfig = $activeDesign->design->getConfigItem();
		$designProductId = $designConfig->productId;
		
		$product = Common::$orderDB->getProductById($designProductId);
		$container->designProductNames[] = $product->longName;
	}
	
	$designImageIds_S = $container->designImageIds; 
	
	if(isset($_POST['finishButton']) || !is_null($activeDesign->defaultValues))
	{
		if(function_exists("themeOnConfirm"))
		{
			themeOnConfirm($ti, $container, $designEnvironment);
		}
		
		$imageId = '';
		if($designEnvironment->save())
		{
			$rid = ResourceId::fromId($designEnvironment->orderItem->getPreviewImageId());
			$rid->type = ResourceManager::TYPE_THUMBNAIL;
				
			$url = Common::$session->urlSubmit;
			$url .= strrpos($url, '?') ? '&' : '?';
			$url .= 'OrderId=' . urlencode($rid->getId());
			$url .= '&' . Common::queryVars();
				
			if($designEnvironment->mode == DesignEnvironment::MODE_NEW_ORDER)
			{
				require_once "./backend/genesys_interface.php";
				
				$orderDetails = new GenesysOrderDetails();
				$orderDetails->orderItemId = $designEnvironment->orderItem->id;
				$orderDetails->imageId_L = $designEnvironment->orderItem->getPreviewImageId();
				
				$smallImageRID = ResourceId::fromId($orderDetails->imageId_L);
				$smallImageRID->type = ResourceManager::TYPE_THUMBNAIL;
				$orderDetails->imageId_S = $smallImageRID->getId();
				
				$orderDetails->attachment = Common::$session->attachment;
				$orderDetails->designs = array();
	
				foreach($designEnvironment->activeDesigns as $activeDesign)
				{
					/* @var $activeDesign ActiveDesign */
					$designDetails = new GenesysDesignDetails();
					$designDetails->designId = $activeDesign->design->id;
						
					$designDetails->imageId_XL =  $activeDesign->design->getHighDefImageId();
					$designDetails->imageId_L =  $activeDesign->design->getPreviewImageId();
						
					$smallImageRID = ResourceId::fromId($designDetails->imageId_L);
					$smallImageRID->type = ResourceManager::TYPE_THUMBNAIL;
					$designDetails->imageId_S =  $smallImageRID->getId();
						
					$designDetails->colors = $activeDesign->design->getColorsFromJSON();
						
					$orderDetails->designs[] = $designDetails;
				}
	
				$url .= '&orderDetails=' . urlencode(json_encode($orderDetails));
			}
			if (isset(Common::$session->urlSubmit)) {
				Header("location: " . $url);
				exit();
			} else {
				echo  "<script type='text/javascript'>";
				echo "window.close();";
				echo "</script>";
			}
		}
		else
		{
			$errorHTML = "Save failed.";
		}
	
		exit();
	}
	else
	{
		themeMain($ti, $container);
	}	
?>

	

