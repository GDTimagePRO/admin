<?php 
	class WorkstationItem{
		public $submit_time;			//order_items.date_created
		public $order_id;				//order_items.id?
		public $design_id;				//designs.id
		public $image_id;				//designs.image_id
		public $product_name;			//products.long_name
		public $material;				//products_category.name
		public $manufacturer_id;
		public $product_id;
		public $color;
		public $frame_width;
		public $extras;
	}
	
	class WorkstationDB{
	
		const DEBUG = TRUE;
		
		private $connection = NULL;
		
		
		function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		/**
		 * This function returns a list of WorkstationItems to list for a manufacturer in the workstation.
		 * @param int $customerId the customerId of the logged in manufacturer
		 * @param int $status the processing stages id for the products you want a list of
		 * @param string $material the product_category name
		 * @return array:WorkstationItem
		 */
		function getItemsByMaterial($customerId, $status, $material = NULL){
			
			//Note: The status assigned to order items when each of their designs has
			//	been rendered is ProcessingStage::STAGE_READY (db_order.php) 
			
			//Note2: If you need the ink color for a specific design you can get it via
			//	Design::getInkColorFromJSON($json) (db_design.php)
			//	Also, ink color is now per design.
			
			//Note3: The processing stage is now shared between one or more designs and
			//	it is possible for this method to not return all desings connected to an 
			//	order because of the LIMIT clause
			
			$items = array();
			//TODO need to add in a where clause for user_id
			$query = sprintf(
					"SELECT
						oi.date_created AS date_created,
						oi.id AS order_id,
						d.id AS design_id,
						p.long_name AS long_name,
						pc.name AS material,
						d.design_json AS json,
						oi.customer_id AS customer_id,
						oi.external_order_id AS external_order_id,
						p.id as product_id 
					FROM 
						order_items oi, 
						designs d, 
						products p, 
						products_category pc  
					WHERE 
						d.order_item_id = oi.id and  
						p.id = d.product_id and 
						pc.id = p.category_id and  
						oi.processing_stages_id = %d and 
						oi.customer_id = %d  
					" ,
					$status,
					$customerId					
				);
			
			if($material != NULL){
				$query .= sprintf(" and pc.name = '%s'",$material);
			}
			$query .=" ORDER BY oi.customer_id,oi.external_order_id,oi.id, oi.date_created DESC";
			
				
			//echo $query;
			//$query = "SELECT count(*) FROM order_items WHERE processing_stages_id = 0";
			if(!$result = mysql_query($query,$this->connection)){
				if(WorkstationDB::DEBUG) echo mysql_error();
				return array();
			}
				
			while($row = mysql_fetch_assoc($result)){
				//$items[] = $row;
				$item = new WorkstationItem();
				$item->submit_time = $row['date_created'];
				//$item->manufacturing_time = $row['submit_date'];
				$item->order_id = $row['order_id'];
				$item->design_id = $row['design_id'];
				$item->image_id = Design::highDefImageId($row['design_id']);
				$item->product_name = $row['long_name'];
				$item->material = $row['material'];
				$item->manufacturer_id = $row['external_order_id'];
				$item->color = Design::colorsFromJSON($row['json'])->ink->name;
				$item->product_id = $row['product_id'];
				$items[] = $item;
			}
				
			return $items;
		}
		
		function getItemById($item_id){
			$query = sprintf(
					"SELECT
						oi.date_created AS date_created,
						oi.id AS order_id,
						d.id AS design_id,
						p.long_name AS long_name,
						pc.name AS material,
						d.design_json AS json,
						oi.customer_id AS customer_id,
						oi.external_order_id AS external_order_id,
						p.id as product_id  
					FROM
						order_items oi,
						designs d,
						products p,
						products_category pc
					WHERE
						d.order_item_id = oi.id and
						p.id = d.product_id and
						pc.id = p.category_id and
						oi.id = %d
					" ,
					$item_id
			);
		
		
				
			//echo $query;
			//$query = "SELECT count(*) FROM order_items WHERE processing_stages_id = 0";
			if(!$result = mysql_query($query,$this->connection)){
				if(WorkstationDB::DEBUG) echo mysql_error();
				return array();
			}
				
			$row = mysql_fetch_assoc($result);
			//$items[] = $row;
			$item = new WorkstationItem();
				$item->submit_time = $row['date_created'];
				//$item->manufacturing_time = $row['submit_date'];
				$item->order_id = $row['order_id'];
				$item->design_id = $row['design_id'];
				$item->image_id = Design::highDefImageId($row['design_id']);
				$item->product_name = $row['long_name'];
				$item->material = $row['material'];
				$item->manufacturer_id = $row['external_order_id'];
				$item->color = Design::colorsFromJSON($row['json'])->ink->name;
				$item->product_id = $row['product_id'];
			return $item;
		}
		
		/**
		 * This function changes the processing stage status for an order item 
		 * @param int $item_id The order_item id to change the status of
		 * @param int $status the processing_stages_id to change the status to
		 */
		function updateStatus($item_id,$status)
		{
			global $_order_db;
			
			$orderItem = new OrderItem();
			$orderItem->id = $item_id;
			$orderItem->processingStagesId = $status;
			 
			$_order_db->updateOrderItem($orderItem);
		}
		
		
		/**
		 * This function changes the manufacturer order id for an order item
		 * @param int $item_id The order_item id to change the status of
		 * @param int $status the processing_stages_id to change the status to
		 */
		function updateManufacturerOrderId($item_id,$order_id){
			global $_order_db;
			$orderItem = new OrderItem();
			$orderItem->id = $item_id;
			$orderItem->manufacturerOrderId = $order_id;
			
			$_order_db->updateOrderItem($orderItem);
		}
		
		/**
		 * Returns the json string for the currently logged in user
		 * @param int $user_id The id of the currently logged in user
		 * @return string the json settings string
		 */
		function getSettings($user_id){

		}
		
		/**
		 * Returns all of the templates associated with the currently logged in user
		 * @param int $user_id the id of the currently logged in user
		 * @return array the results of the query (id, name, template json)
		 */
		function getTemplates($user_id){
			
		}
		
		/**
		 * Creates a new template associated with the currently logged in user
		 * @param int $user_id the id of the currently logged in user
		 * @param string $name the name of the template
		 * @param string $template the json string of the template
		 */
		function newTemplate($user_id,$name,$template){
			
		}
		
		/**
		 * Edits the template
		 * @param int $template_id
		 * @param string $name
		 * @param string $template
		 */
		function editTemplate($template_id, $name, $template){
			
		}
		
		/**
		 * 
		 * @param int $template_id
		 * @return string returns the json string for the template
		 */
		function getTemplate($template_id){
			
		}
	}

?>