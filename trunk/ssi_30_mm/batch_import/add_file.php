<?php 
	require_once '../backend/startup.php';
	require_once 'db_batch_import.php';
	
	class FieldData
	{
		public $page;
		public $name;
		public $value;
	}
	
	function csv_to_array($filename='', $delimiter=',')
	{
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;
	
		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
			{
				if(!$header)
					$header = $row;
				else
					$data[] = $row;//array_combine($header, $row);
			}
			fclose($handle);
		}
		return $data;
	}
	
	/**
	 * @return FieldData
	 */
	function getFieldData($rowData, $iField)
	{
		$i = $iField * 2  + 20;		
		
		if(count($rowData) <= $i + 1) return null;
		if(strlen($rowData[$i]) == 0) return null;
		
		$result = new FieldData();
		$result->value = $rowData[$i + 1];		
		$a = explode(':', $rowData[$i]);
		
		if(count($a) == 1)
		{
			$result->page = 0;
			$result->name = $a[0]; 
		}
		else 
		{
			$sn = trim($a[1]);
			
			if($a[0] == 'Stamp One') $result->page = 0;
			else if($a[0] == 'Stamp Two') $result->page = 1;
			else if($a[0] == 'Stamp Three') $result->page = 2;
			else {
				$result->page = 0;
				$sn = $rowData[$i];
				
				if(strpos($sn, "From: To: Message:") === 0)
				{
					$sn = "From: To: Message:";
					$result->value = substr($sn, 18);					
				}
			}
			$result->name = $sn;
		}
		return $result;
	}
	
	/**
	 * @param BIQueueItem $queueItem
	 * @return boolean
	 */
	function do_custom_stuff($queueItem, $designData)
	{
//  		if(		($queueItem->barcode == 'RD-1000') ||
//  				($queueItem->barcode == 'RD-1001') ||
//  				($queueItem->barcode == 'RD-1002') ||
//  				($queueItem->barcode == 'RD-1003') ||
//  				($queueItem->barcode == 'RD-1004') ||
//  				($queueItem->barcode == 'RD-1005') ||
//  				($queueItem->barcode == 'RD-1006') ||
//  				($queueItem->barcode == 'RD-1007') ||
//  				($queueItem->barcode == 'RD-1008') ||
 					
//  				($queueItem->barcode == 'TR-1020') ||
//  				($queueItem->barcode == 'TR-1016') ||
//  				($queueItem->barcode == 'TR-1010') )
//  		{
//  			return true;
//  		}
	
		return true;
	}
	
	function cancelExisting($barcode)
	{
		$query = "
				UPDATE order_items SET processing_stages_id = 100 
				WHERE id IN (SELECT order_item_id FROM batch_import_queue WHERE barcode = '" . $barcode . "')
			";
				
		if(!mysql_query($query,Startup::getInstance()->db->connection))
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		return true;
	}
	
	function importFile($fileName, $customerId, $externalSystemName)
	{
		//cancelExisting('RD-1000');
		//cancelExisting('RD-1001');
		//cancelExisting('RD-1002');
		//cancelExisting('RD-1003');
		//cancelExisting('RD-1004');
		//cancelExisting('RD-1005');
		//cancelExisting('RD-1006');
		//cancelExisting('RD-1007');
		//cancelExisting('RD-1008');
		
		
		$startup = Startup::getInstance();
		$batchImportDB = new  BatchImportDB($startup->db->connection);
		$fileData = csv_to_array($fileName);
		
		
		foreach($fileData as $rowData)
		{
			$queueItem = new BIQueueItem();
			$queueItem->customerId = $customerId;
			$queueItem->externalSystemName = $externalSystemName;
			$queueItem->orderItemId = BatchImportDB::INVALID_ORDER_ITEM_ID;

			$queueItem->externalOrderId = $rowData[1];
			$queueItem->barcode			= $rowData[3];
			
			$designData = array(array(), array(), array());
			
			for($i=0; ;$i++)
			{
				$fieldData = getFieldData($rowData, $i);
				if(is_null($fieldData)) break;
				$designData[$fieldData->page][$fieldData->name] = $fieldData->value;
			}
			
			if( do_custom_stuff($queueItem, $designData))
			{
				$queueItem->data = serialize($designData);				
				if(!$batchImportDB->createBIQueueItem($queueItem))
				{
					throw new Exception("Failed to insert order id : " . $queueItem->externalOrderId);
				}
			}
		}
	}
	
	importFile('140207.csv', 5, "Import");
	//importFile('20131215_b.csv', 5, "Import");
	echo 'done';
	#echo substr("From: To: Message:Happy FIRST Anniversary to a special couple! Wishing you love and happiness forever ", 18)
?>