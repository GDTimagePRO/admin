<?php
/*

delimiter $$

CREATE TABLE `batch_import_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_item_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `barcode` varchar(45) NOT NULL,
  `external_order_id` int(11) NOT NULL,
  `external_system_name` varchar(45) NOT NULL,
  `data` blob NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=810 DEFAULT CHARSET=latin1$$
 
*/	
	class BIQueueItem
	{
		public $id = NULL;
		public $orderItemId = NULL;
		public $customerId = NULL; 
		public $barcode = NULL;
		public $data = NULL;
		public $externalOrderId = NULL;
		public $externalSystemName = NULL;
		public $dateCreated = NULL;
	}

	class BatchImportDB
	{
		const INVALID_ORDER_ITEM_ID	= -1; 
		
		private $connection = NULL;		
		
		function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		function loadBIQueueItem($row)
		{
			$result = new BIQueueItem();
			$result->id = $row['id'];
			$result->orderItemId = $row['order_item_id'];
			$result->customerId = $row['customer_id'];
			$result->data = $row['data'];
			$result->externalOrderId = $row['external_order_id'];
			$result->externalSystemName = $row['external_system_name'];
			$result->barcode = $row['barcode'];
			$result->dateCreated = $row['date_created'];
			
			return $result;
		}
		
		/**
		 * @param BIQueueItem $item
		 */
		function createBIQueueItem($item)
		{
			$query = sprintf(
					"INSERT INTO batch_import_queue(order_item_id, external_order_id, customer_id, barcode, data, external_system_name) ".
					"VALUES( %d, %d, %d, '%s', '%s', '%s' )",
					$item->orderItemId,
					$item->externalOrderId,
					$item->customerId,
					mysql_real_escape_string($item->barcode),
					mysql_real_escape_string($item->data),
					mysql_real_escape_string($item->externalSystemName)
			);
			
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$templateCategory->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$templateCategory->id = -1;
				echo mysql_error();
				return false;
			}
		}
		
		/**
		 * @param BIQueueItem $item
		 */
		function updateBIQueueItem($item)
		{
			$query = "UPDATE batch_import_queue SET ";
			$first = true;

			
			if(!is_null($item->customerId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("customer_id=%d", $item->customerId);
			}
				
			if(!is_null($item->orderItemId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("order_item_id=%d", $item->orderItemId);
			}

			if(!is_null($item->externalOrderId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_order_id=%d", $item->externalOrderId);
			}
			
			if(!is_null($item->externalSystemName))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_system_name='%s'", mysql_real_escape_string($item->externalSystemName));
			}
			
			if(!is_null($item->barcode))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("barcode='%s'", mysql_real_escape_string($item->barcode));
			}

			if(!is_null($item->data))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("data='%s'", mysql_real_escape_string($item->data));
			}
				
			$query = $query.sprintf(" WHERE id=%d", $item->id);
		
			if(!mysql_query($query,$this->connection))
			{
				echo mysql_error();
				return false;
			}
			return true;
		}
		
		/**
		 * @return BIQueueItem
		 */
		function getBIQueueItemById($id)
		{
			$query = sprintf("SELECT * FROM batch_import_queue WHERE id=%d", $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadBIQueueItem($row);
		}
		
		/**
		 * @return BIQueueItem
		 */
		function getBIQueueItemFirstPending()
		{
			$query = sprintf("SELECT * FROM batch_import_queue WHERE order_item_id=%d LIMIT 0, 1", BatchImportDB::INVALID_ORDER_ITEM_ID);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadBIQueueItem($row);
		}
	}
?>