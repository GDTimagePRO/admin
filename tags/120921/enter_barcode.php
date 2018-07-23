<?php
	include_once "_common.php";	

	$_system->forceLogin();
	
	$errorHTML = "";	
	
	if(isset($_POST['code']))
	{		
		$barcode = $_order_db->getBarcodeByBarcode($_POST['code']);
		
		if(!is_null($barcode))
		{
			if(!$barcode->isUsed())
			{
				$order = NULL;
				$orderArray = $_order_db->getOrdersByUserId(
						$_user_id, 
						$_settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE]
					);
				if(count($orderArray) != 0) $order = $orderArray[0];
				
				
				if(is_null($order))
				{
					$order = new Order();
					$order->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_PROCESSING_STAGE];
					$order->startDate = time();
					$order->submitDate = 0;
					$order->userId = $_user_id;
					if(!$_order_db->createOrder($order)) $order = NULL; 
				}
				
				if(!is_null($order))
				{
					$design = new Design();
					$design->productTypeId = -1;
					
					
					$defaultTemplate = null;
					$defaultTemplateImage = null;
					
					$product = $_order_db->getProductById($barcode->productId);
					if(!is_null($product))
					{
						$design->productTypeId = $product->productTypeId;
						 
						$defaultTemplate = $_design_db->getDesignTemplateById(
								$_design_db->getDefualtDesignTemplateId($design->productTypeId)
							);
						
						if(!is_null($defaultTemplate))
						{
							$design->json = $defaultTemplate->json;
							$defaultTemplateImage = $_image_db->getImageById($defaultTemplate->previewImageId); 
						}
					}
					
					$design->imageId = $_image_db->createImageInline(
							ImageDB::CATEGORY_DESIGN_IMAGE,
							$_user_id,
							"orderid_".$order->id,
							is_null($defaultTemplateImage) ? "" : $defaultTemplateImage->data 
						);
		
					if($design->imageId >= 0)
					{
						if($_design_db->createDesign($design))
						{
							$orderItem = new OrderItem();
							$orderItem->orderId = $order->id;
							$orderItem->designId = $design->id;
							$orderItem->barcode = $barcode->barcode;
							$orderItem->processingStagesId = $_settings[Startup::SETTING_DEFAULT_ORDER_ITEM_PROCESSING_STAGE];
	
							//TODO:
							$orderItem->plasticCategoryId = 99999;
							$orderItem->color = "black";
							$orderItem->shapeId = "O_o";
							
							if($_order_db->createOrderItem($orderItem))
							{
								if(!$barcode->isMaster())
								{
									$barcode->dateUsed = time();
									$_order_db->updateBarcode($barcode);								
								}
								
								$_session->setActiveOrderId($order->id);
								$_session->setActiveOrderItemId($orderItem->id);
								$_session->setActiveDesignId($design->id);
								$_session->setActiveDesignImageId($design->imageId);
								
								Header("Location: http://".$_url."design.php");
								exit();
							}
							else
							{
								$errorHTML = "Internal processing error 1. Please try again.";
							}
						}
						else
						{
							$errorHTML = "Internal processing error 2. Please try again.";
						}
					}
					else
					{
						$errorHTML = "Internal processing error 3. Please try again.";
					}						
				}
				else
				{
					$errorHTML = "Internal processing error 4. Please try again.";
				}
			}
			else
			{
				$errorHTML = "That code has already been used.";
			}		
		}
		else
		{
			$errorHTML = "That code was not recognised.";
		}
	}

	
	
	
	
	
	
	
	
	
	// 	}
	
	// 		//check to see if the code is valid.
	// 		$returncode = $db->checkCode($_POST['code']);
	// 		if($returncode == Database::CODE_OK){
	// 			$id = $db->newOrderItem($_POST['code']);
	// 			$s->setCurrentItem($id);
	// 			Header("Location: http://".$startup->settings['url']."design.php");
	// 		}
	// 		elseif($returncode == Database::CODE_UNKNOWN){
	// 			$error = "That code was not recognised.";
	
	// 		}
	// 		elseif($returncode == Database::CODE_USED){
	// 			$error = "That code has already been used.";
	
	// 		}
	// 		include "preamble.php";
	// 		echo '<div id="error">'.$error.'</div>';
	// 		echo '<div id="blank">&nbsp;</div>';
	// 		printForm();
	// 		include "postamble.php";
	
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
	
	
	include "preamble.php";
	if($errorHTML != "")
	{
		echo '<div id="error">'.$errorHTML.'</div>';
		echo '<div id="blank">&nbsp;</div>';
	}
	
?>

	<div id="code_box">
		<form method="post" action="">
			<div id="interior_code">
				Code: &nbsp;&nbsp;
				<input type="text" name="code" placeholder="Product Code" required="required" value="<?php echo htmlspecialchars(getPost('code')); ?>" />
				<input type="submit" name="submit" value="Next" class="code_submit_button"/>
			</div>
		</form>
	</div>

	
<?php include "postamble.php"; ?>










