<?php
	class CustomerConfigGenesis
	{
		public $key;
	}
	
	class CustomerConfig
	{
		/**
		 * @var CustomerConfigGenesis
		 */
		public $genesis;
	}

	class Customer
	{
		public $id = -1;
		public $name = NULL;
		public $key = NULL;
		public $configJSON = NULL;
		
		/**
		 * @return CustomerConfig
		 */
		public function getConfigObj()
		{
			return json_decode($this->configJSON);
		}
	}
	
	
	class RedemptionCodeGroupConfigGenesis
	{
		public $code;
	}
	
	class RedemptionCodeGroupConfig
	{
		/**
		 * @var RedemptionCodeGroupConfigGenesis
		 */
		public $genesis;
	}
	
	class RedemptionCodeGroup
	{
		public $id = -1;
		public $customerId = NULL;
		public $dateCreated = NULL;
		public $description = NULL;		
		public $configJSON = NULL;
		
		/**
		 * @return RedemptionCodeGroupConfig
		 */
		public function getConfigObj()
		{
			return json_decode($this->configJSON);
		}
	}
	
	class RedemptionCode
	{
		const CODE_FORMAT		= "###-###-####";
		const CODE_LENGTH		= 10;
		public static $CODE_ALPHABET		= array('3','4','5','6','7','8','9','W','E','T','Y','P','A','S','F','H','J','K','X','V','N','M');
		
		public $id = -1;
		public $customerId = NULL;
		public $code = NULL;
		public $groupId = NULL;
		public $dateUsed = NULL;
		public $externalOrderId = NULL;
		public $externalOrderDetails = NULL;
		public $shippingEmail = NULL;
		public $shippingDetails = NULL;
		
		public static function formatCode($str)
		{
			$str = strtoupper(trim($str));
			
			$str = str_replace(
					array( '/', ' ', '_', '-', '.', ',', '\\'), 
					array(  '',  '',  '',  '',  '',  '',   ''), 
					$str
				);
			
			if(strlen($str) >= 10)
			{
				$str =	substr($str, 0, 3) . '-' .
						substr($str, 3, 3) . '-' .
						substr($str, 6);
			}
			
			return $str; 
		}
	}
	
	class RedemptionDB
	{
		private $connection = NULL;		
		
		function __construct($connection)
		{
			$this->connection = $connection;
		}
	
		
		//==============================================================================
		// Customer
		//==============================================================================
		/**
		 * @return Customer
		 */
		function loadCustomer($row)
		{
			$result = new Customer();
			$result->id = $row['id'];
			$result->name = $row['name'];
			$result->key = $row['key'];
			$result->configJSON = $row['config_json'];
			
			return $result;
		}
		
		/**
		 * @param unknown_type $id
		 * @return NULL|Customer
		 */
		function getCustomerById($id)
		{
			$query = sprintf("SELECT * FROM customers WHERE id=%d", $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
				
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
				
			return $this->loadCustomer($row);
		}
				
		//==============================================================================
		// RedemptionCodeGroup
		//==============================================================================
		
		const REDEMPTION_CODE_GROUPS_FIELDS = 'id, customer_id, UNIX_TIMESTAMP(date_created) as date_created, description, config_json';
		
		/**
		 * @return RedemptionCodeGroup
		 */
		function loadRedemptionCodeGroup($row)
		{
			$result = new RedemptionCodeGroup();
			$result->id = $row['id'];
			$result->customerId = $row['customer_id'];
			$result->dateCreated = $row['date_created'];
			$result->description = $row['description'];
			$result->configJSON = $row['config_json'];
							
			return $result;
		}
		
		/**
		 * @return NULL|RedemptionCodeGroup
		 */
		function getRedemptionCodeGroupById($id)
		{
			$query = sprintf("SELECT %s FROM redemption_code_groups WHERE id=%d", RedemptionDB::REDEMPTION_CODE_GROUPS_FIELDS, $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
				
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
				
			return $this->loadRedemptionCodeGroup($row);
		}
		
		
		//==============================================================================
		// RedemptionCode
		//==============================================================================
		
		const REDEMPTION_CODES_FIELDS = 'id, customer_id, code, group_id, UNIX_TIMESTAMP(date_used) as date_used, external_order_id, external_order_details, shipping_email, shipping_details';
		
		/**
		 * @return RedemptionCode
		 */
		function loadRedemptionCode($row)
		{
			$result = new RedemptionCode();
			$result->id = $row['id'];
			$result->code = $row['code'];
			$result->customerId = $row['customer_id'];
			$result->groupId = $row['group_id'];
			$result->dateUsed = $row['date_used'];
			$result->externalOrderId = $row['external_order_id'];;
			$result->externalOrderDetails = $row['external_order_details'];
			$result->shippingEmail = $row['shipping_email'];
			$result->shippingDetails = $row['shipping_details'];
							
			return $result;
		}
		
		/**
		 * @param RedemptionCode $code
		 * @return boolean
		 */
		function createRedemptionCode($code)			
		{
			$query = sprintf(
					"INSERT INTO redemption_codes(customer_id, code, group_id, date_used, external_order_id, external_order_details, shipping_email, shipping_details) ".
					"VALUES( %d, '%s', %d, FROM_UNIXTIME(%d), %s , %s , %s, %s )",
					$code->customerId,
					mysql_real_escape_string($code->code),
					$code->groupId,
					is_null($code->dateUsed) ? 0 : $code->dateUsed,
					is_null($code->externalOrderId) ? 'NULL' : $code->externalOrderId,
					is_null($code->externalOrderDetails) ? 'NULL' : "'" . mysql_real_escape_string($code->externalOrderDetails) . "'", 
					is_null($code->shippingEmail) ? 'NULL' : "'" . mysql_real_escape_string($code->shippingEmail) . "'",
					is_null($code->shippingDetails) ? 'NULL' : "'" . mysql_real_escape_string($code->shippingDetails) . "'"
				);
			
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$code->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$code->id = -1;
				if(DesignDB::DEBUG) echo mysql_error();
				return false;
			}
		}
				
		function getRedemptionCodeById($id)
		{
			$query = sprintf("SELECT %s FROM redemption_codes WHERE id=%d", RedemptionDB::REDEMPTION_CODES_FIELDS, $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadRedemptionCode($row);
		}
		
		function getRedemptionCodeByCode($customerId, $code)
		{
			$query = sprintf(
					"SELECT %s FROM redemption_codes WHERE customer_id=%d AND code='%s'", 
					RedemptionDB::REDEMPTION_CODES_FIELDS,
					$customerId, 
					mysql_real_escape_string($code)
				);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadRedemptionCode($row);
		}
		
		function updateRedemptionCode(RedemptionCode $redemptionCode)
		{
			$query = "UPDATE redemption_codes SET ";
			$first = true;
		
			if(!is_null($redemptionCode->code))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("code='%s'", mysql_real_escape_string($redemptionCode->code));
			}
			
			if(!is_null($redemptionCode->customerId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("customer_id=%d", $redemptionCode->customerId);
			}
				
			if(!is_null($redemptionCode->groupId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("group_id=%d", $redemptionCode->groupId);
			}

			if(!is_null($redemptionCode->dateUsed))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf(
						'date_used=FROM_UNIXTIME(%d)', 
						$redemptionCode->dateUsed
					);
			}

			if(!is_null($redemptionCode->externalOrderId))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_order_id=%d", $redemptionCode->externalOrderId);
			}

			if(!is_null($redemptionCode->externalOrderDetails))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("external_order_details='%s'", mysql_real_escape_string($redemptionCode->externalOrderDetails));
			}

			if(!is_null($redemptionCode->shippingEmail))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("shipping_email='%s'", mysql_real_escape_string($redemptionCode->shippingEmail));
			}

			if(!is_null($redemptionCode->shippingDetails))
			{
				if($first) { $first = false; } else { $query = $query.", "; }
				$query = $query.sprintf("shipping_details='%s'", mysql_real_escape_string($redemptionCode->shippingDetails));
			}
				
			$query = $query.sprintf(" WHERE id=%d", $redemptionCode->id);
		
			if(!mysql_query($query,$this->connection))
			{
				echo mysql_error();
				return false;
			}
			return true;
		}
	}
?>