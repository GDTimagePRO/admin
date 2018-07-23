<?php
	require_once "_common.php";	
	require_once "batch_import/db_batch_import.php";
	
	
	function renameField($page, $oldName, $newName, &$data)
	{
		if(!isset($data[$page][$oldName]))
		{
			return;	
		} 
		
		if(isset($data[$page][$newName]))
		{
			echo '[' . $page . ']["'. $newName .'"] is already in use.';
			exit;
		}
		$data[$page][$newName] = $data[$page][$oldName];
		unset($data[$page][$oldName]);
	}
	
	function monogramFix($page, $oldName, $newName1, $newName2, $newName3, &$data)
	{
		if(!isset($data[$page][$oldName]))
		{
			return;
		}
	
		if(isset($data[$page][$newName1]))
		{
			echo '[' . $page . ']["'. $newName1 .'"] is already in use.';
			exit;
		}
		
		if(isset($data[$page][$newName2]))
		{
			echo '[' . $page . ']["'. $newName2 .'"] is already in use.';
			exit;
		}
		
		if(isset($data[$page][$newName3]))
		{
			echo '[' . $page . ']["'. $newName3 .'"] is already in use.';
			exit;
		}
		
		$val = trim(str_replace(' ', '', $data[$page][$oldName]));
		unset($data[$page][$oldName]);
		$data[$page][$newName1] = substr($val, 0, 1);
		$data[$page][$newName2] = substr($val, 1, 1);
		$data[$page][$newName3] = substr($val, 2, 1);
	}
	
	
	function fixData($barcode, &$data)
	{
		if($barcode == 'SP-1006')
		{
			renameField(0, 'Last Name', 'surname', $data);
			if(!isset($data[0]['message 3'])) $data[0]['message 3'] = ' ';
			if(!isset($data[0]['message 2'])) $data[0]['message 2'] = ' ';
		}
		else if($barcode == 'BR-1007')
		{
			renameField(0, 'Name One', 'first name(s)', $data);
			renameField(0, 'Name Two', 'surname', $data);
		}
		else if($barcode == 'AD-1002')
		{
			renameField(0, 'City, State', 'city,state', $data);
		}		
 		else if($barcode == 'AD-1013')
 		{
 			renameField(0, 'Message One', 'message 1', $data);
 			renameField(0, 'Street Number', 'street #', $data);
 			unset($data[0]['From: To: Message:']);
 		}
	 	else if($barcode == 'AD-1014')
 		{
 			renameField(0, 'Street Number', 'street #', $data);
 		}
		else if($barcode == 'AD-1016')
 		{
 			renameField(0, 'State, Zip Code', 'state,zip', $data);
 		}
		else if($barcode == 'AD-1017')
 		{
 			renameField(0, 'Street Number', 'street #', $data);
 		}
		else if($barcode == 'AD-1018')
 		{
 			renameField(0, 'Surname Initial', 'surname init.', $data);
 		}
 		else if($barcode == 'AD-1024')
 		{
 			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
 		}
 		else if($barcode == 'AD-1025')
 		{
 			renameField(0, 'Initial', 'surname init.', $data);
 			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
 		}
 		else if($barcode == 'AD-1026')
 		{
 			renameField(0, 'Surname Initial', 'surname init.', $data);
 			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
 		}
 		else if($barcode == 'AD-1027')
 		{
 			renameField(0, 'Name', 'first name(s)', $data);
 			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
 		}
 		else if($barcode == 'AD-1028')
 		{
 			renameField(0, 'Surname Initial', 'surname init.', $data);
 			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
 		} 		
 		else if($barcode == 'AD-1032')
 		{
 			renameField(0, 'Line One', 'first name(s)', $data);
 			renameField(0, 'Line Two', 'surname', $data);
 			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
 		}
 		else if($barcode == 'SO-2008')
 		{
 			renameField(0, 'Message One', 'message 1', $data);
 			renameField(0, 'Message Two', 'message 2', $data);
 			renameField(0, 'Message Three', 'name', $data);
 		}
 		else if($barcode == 'MO-2006')
 		{
 			renameField(0, 'First Initial', '1st initial', $data);
 			renameField(0, 'Surname Initial', 'surname init.', $data);
 			renameField(0, 'Second Initial', '2nd initial', $data);
 		}
	 	else if($barcode == 'MO-2012')
 		{
 			renameField(0, 'First Initial', '1st initial', $data);
 			renameField(0, 'Surname Initial', 'surname init.', $data);
 			renameField(0, 'Second Initial', '2nd initial', $data);
 		}
 		else if($barcode == 'AD-2013')
 		{
 			renameField(0, 'Message One', 'message 1', $data);
 			renameField(0, 'Street Number', 'street #', $data);
 		}
 		//Misc:
		//	Template id 448 needs it's input field renamed to "Name"
 		//	Template id 449 needs it's input field renamed to "Name"
 		//	Template id 450 needs it's input field renamed to "Name"
 		//	Template id 451 needs it's input field renamed to "Name"
		//	Template id 452 needs it's input field renamed to "Name"
		//	Template id 453 needs it's input field renamed to "Name"
 			
 		
// 		if($barcode == 'AD-3013')
// 		{
// 			renameField(0, 'Street Number', 'street #', $data);
// 		}
// 		if($barcode == 'AD-3014')
// 		{
// 			renameField(0, 'Street Number', 'street #', $data);
// 		}
// 		else if($barcode == 'AD-1016')
// 		{
// 			renameField(0, 'State, Zip Code', 'state,zip', $data);
// 		}		
// 		else if($barcode == 'MO-3004')
// 		{
// 			renameField(0, 'First Initial', '1st initial', $data);
// 		}				
// 		else if($barcode == 'AD-1002')
// 		{
// 			renameField(0, 'City, State', 'city,state', $data);
// 		}
// 		else if($barcode == 'MO-2012')
// 		{
// 			monogramFix(
// 					0, 
// 					'Monogram Initials', 
// 					'1st initial', 
// 					'surname init.', 
// 					'2nd intial', 
// 					$data
// 				);
// 		}		
// 		else if($barcode == 'MO-2006')
// 		{
// 			monogramFix(
// 					0, 
// 					'Monogram Initials', 
// 					'1st initial', 
// 					'surname init.', 
// 					'2nd inItial', 
// 					$data
// 				);
// 		}		
//  		else if($barcode == 'RD-1000')
//  		{
//  			renameField(0, 'Line One', 'first name(s)', $data);
//  			renameField(0, 'Line Two', 'surname', $data);
//  			renameField(0, 'Street Number', 'street #', $data);
			
//  			renameField(1, 'Line One', 'name', $data);
//  		}
// 	 	else if($barcode == 'RD-1001')
//  		{
//  			renameField(1, 'Last Name (Plural)', 'name', $data);
//  			renameField(1, 'City, State, Zip Code', 'city,state,zip', $data);
//  		}
//  		else if($barcode == 'RD-1002')
//  		{
//  			renameField(0, 'Initial', 'surname init.', $data);
//  			renameField(0, 'Text', 'first name(s)', $data);
//  			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
 			
//  			renameField(1, 'Initial', 'surname init.', $data); 			
//  		}
//  		else if($barcode == 'RD-1003')
//  		{
//  			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);

//  			$s = str_replace(',', '',str_replace(' ', '',trim($data[1]['Monogram Initials'])));
//  			unset($data[1]['Monogram Initials']);
//  			$data[1]['1st initial'] = substr( $s, 0, 1);	
// 			$data[1]['surname init.'] = substr( $s, 1, 1);
// 			$data[1]['2nd initial'] = substr( $s, 2, 1);	
//  		}
//  		else if($barcode == 'RD-1004')
//  		{
//  			renameField(0, 'Line One', 'message 1', $data);
//  			renameField(0, 'Line Two', 'surname', $data);
//  			renameField(0, 'Street Number', 'street #', $data);
 			
//  			renameField(1, 'Line One', 'message 1', $data);
//  			renameField(1, 'Line Two', 'name', $data);
 			
//  		}
//  		else if($barcode == 'RD-1005')
//  		{
//  			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
//  		}
//  		else if($barcode == 'RD-1006')
//  		{
//  			renameField(0, 'Line One', 'message 1', $data);
//  			renameField(0, 'Line Two', 'name', $data);

//  			renameField(1, 'Line One', 'message 1', $data);
//  			renameField(1, 'Line Two', 'message 2', $data);
//  			renameField(1, 'Line Three', 'name', $data);
//  		}
// 		else if($barcode == 'RD-1007')
// 		{
// 			renameField(0, 'Line One', 'message 1', $data);
// 			renameField(0, 'Line Two', 'name', $data);
// 			renameField(0, 'Line Three', 'message 3', $data);
			
// 			$data[0]['message 2'] = "??????? MISSING ????????";
// 			$data[0]['message 4'] = "??????? MISSING ????????";
			
// 			renameField(1, 'Line Four', 'message 1', $data);
// 			renameField(1, 'Line Five', 'message 2', $data);
				
// 			$data[1]['name'] = "??????? MISSING ????????";
				
// 		}
//  		else if($barcode == 'RD-1008')
//  		{
//  			renameField(1, 'Line One', 'message 1', $data);
//  			renameField(1, 'Line Two', 'name', $data);
//  		}
//   		else if($barcode == 'TR-1009')
//   		{
//   			renameField(0, 'Initial', 'surname init.', $data);
//   			renameField(0, 'Text', 'surname', $data);
//   			renameField(0, 'City, State, Zip Code', 'city,state,zip', $data);
			
//  			renameField(1, 'Last Name', 'surname', $data);
//  			renameField(1, 'Street Number', 'street #', $data);
			
//  			renameField(2, 'Text', 'name', $data);
//  		}
//  		else if($barcode == 'TR-1010')
//  		{
//  			renameField(1, 'state, zip', 'state,zip', $data);
//  			if(!isset($data[2]['message 1'])) $data[2]['message 1'] = 'the';
//  		}
// 	 	else if($barcode == 'TR-1016')
//  		{
//  			$data[0]['city,state,zip'] = trim($data[0]['City, State']) . ' ' . trim($data[0]['Zip Code']);
//  			unset($data[0]['City, State']); 
//  			unset($data[0]['Zip Code']);
 			
//  			if(!isset($data[1]['message 1'])) $data[1]['message 1'] = 'the';
//  		}
// 	 	else if($barcode == 'TR-1020')
//  		{
//  			renameField(0, 'city,state', 'city, state', $data);
 			
//  			$s = str_replace(',', '',str_replace(' ', '',trim($data[2]['Monogram Initials'])));
//  			unset($data[2]['Monogram Initials']);
//  			$data[2]['1st initial'] = substr( $s, 0, 1);
//  			$data[2]['surname init.'] = substr( $s, 1, 1);
//  			$data[2]['2nd intial'] = substr( $s, 2, 1);
 			
//  		}
	}
	
	$batchImportDB = new  BatchImportDB(Common::$system->db->connection);
	if(!is_null(Common::$session) && isset($_GET['orderDetails']))
	{
		$orderDetails = json_decode($_GET['orderDetails']);
		
		$oldQueueItem = $batchImportDB->getBIQueueItemById(Common::$session->designEnvironment->batchImportQueueItemId);
		$oldQueueItem->orderItemId = $orderDetails->orderItemId; 
		
		$orderItem = new  OrderItem();
		$orderItem->id = $oldQueueItem->orderItemId;
		$orderItem->externalOrderId = $oldQueueItem->externalOrderId * 1000 + 1;
		$orderItem->externalSystemName = $oldQueueItem->externalSystemName;
		$orderItem->processingStagesId = ProcessingStage::STAGE_PENDING_RENDERING;
		
		if(!Common::$orderDB->updateOrderItem($orderItem))
		{
			echo "Failed to update order item : " . $orderItem->id;
			exit();
		}
		
		if(!$batchImportDB->updateBIQueueItem($oldQueueItem))
		{
			echo "Failed to update previous queue item : " . $oldQueueItem->id;
			exit();
		}
		
	}
	
	
	
	
	$queueItem = $batchImportDB->getBIQueueItemFirstPending();
	if(is_null($queueItem))
	{
		echo "All done ^_^";
		exit();
	} 
	
	$errorHTML = "";
		
	$customer = Common::$orderDB->getCustomerById($queueItem->customerId);
	$barcode = Common::$orderDB->getBarcodeByBarcode( $customer->id, $queueItem->barcode);
	
	if(is_null($barcode))
	{
		echo 'Barcode "' . $queueItem->barcode . '" for queue item ' . $queueItem->id . ' seems to be missing.';
		exit;
	}
	
	Common::$session = Session::create($customer);
 	Common::$session->designEnvironment = DesignEnvironment::createFromBarcode($barcode, Common::$session->sessionId);
 	
 	if(is_null(Common::$session->designEnvironment))
 	{
		echo 'Failed to initialize design environment for queue item ' . $queueItem->id;
 		exit;
 	}
 	 	
 	Common::$session->designEnvironment->batchImportQueueItemId = $queueItem->id;
 	
 	$activeDesigns = Common::$session->designEnvironment->activeDesigns;
 	$defaultValues = unserialize($queueItem->data);
 	
 	fixData($queueItem->barcode, $defaultValues);
 	
 	if(count($activeDesigns) > count($defaultValues))
 	{
		echo 'Barcode "' . $queueItem->barcode . '" generated more designs than queue item ' . $queueItem->id . ' has pages.';
 		exit;
 	}
 	
 	for( $i = 0; $i < count($activeDesigns); $i++ )
 	{
 		$activeDesigns[$i]->defaultValues = $defaultValues[$i];
 	}
 	
 	Common::$session->urlHome = "import_robot.php";
 	Common::$session->urlSubmit = "import_robot.php";
 	Common::$session->urlReturn = "import_robot.php";
 	
 	Common::$session->config->theme = "default"; 	
 				
	Common::$session->save();
	
 	Header("location: http://". Settings::HOME_URL . "design_customize.php?" . Common::queryVars());
 	exit();
?>






