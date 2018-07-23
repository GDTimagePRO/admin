<?php

	class ShippingInformation
	{

		public $id = -1;
		public $first_name = NULL;
        public $last_name = NULL;
        public $address_1  = NULL;
        public $address_2 = NULL;
        public $city = NULL;
        public $state_province = NULL;
        public $zip_postal_code  = NULL;
        public $country  = NULL;
		public $email = NULL;
		public $company = NULL;
		public $ship_qty = 1;

		public function toJSONObject()
		{
			$o = array();
			if(!is_null($this->id)) $o['id'] = $this->id;
			if(!is_null($this->first_name)) $o['first_name'] = $this->first_name;
			if(!is_null($this->last_name)) $o['last_name'] = $this->last_name;
			if(!is_null($this->address_1)) $o['address_1'] = $this->address_1;
			if(!is_null($this->address_2)) $o['address_2'] = $this->address_2;
			if(!is_null($this->city)) $o['city'] = $this->city;
			if(!is_null($this->state_province)) $o['state_province'] = $this->state_province;
			if(!is_null($this->zip_postal_code)) $o['zip_postal_code'] = $this->zip_postal_code;
			if(!is_null($this->country)) $o['country'] = $this->country;
			if(!is_null($this->email)) $o['email'] = $this->email;
			if(!is_null($this->company)) $o['company'] = $this->company;
			if(!is_null($this->ship_qty)) $o['ship_qty'] = $this->ship_qty;
			return $o;
		}

		public function toJSON()
		{
			return json_encode($this->toJSONObject());
		}

		public static function fromJSONObject($o)
		{
			$result = new ShippingInformation();
			if(isset($o->first_name)) $result->first_name = $o->first_name;
			if(isset($o->last_name)) $result->last_name = $o->last_name;
			if(isset($o->address_1)) $result->address_1 = $o->address_1;
			if(isset($o->address_2)) $result->address_2 = $o->address_2;
			if(isset($o->city)) $result->city = $o->city;
			if(isset($o->state_province)) $result->state_province = $o->state_province;
			if(isset($o->zip_postal_code)) $result->zip_postal_code = $o->zip_postal_code;
			if(isset($o->country)) $result->country = $o->country;
			if(isset($o->id)) $result->id = $o->id;
			if(isset($o->email)) $result->email = $o->email;
			if(isset($o->company)) $result->company = $o->company;
			if(isset($o->ship_qty)) $result->ship_qty = $o->ship_qty;
			return $result;
		}

		public static function fromJSON($json)
		{
			if(($json == '') || is_null($json)) return new ShippingInformation();
			return ShippingInformation::fromJSONObject(json_decode($json));
		}

	}

	class ShippingDB
	{
		const DEBUG = TRUE;

		private $connection = NULL;

		function __construct($connection)
		{
			$this->connection = $connection;
		}

		function commitShippingInformation($shippingInfo, $orderId) {
			$query = sprintf(
						"INSERT INTO shipping_information(order_id, first_name, last_name, address_1, address_2, city, state_province, zip_postal_code, country, email, company, ship_qty) ".
						"VALUES('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')",
						$orderId,
						mysql_real_escape_string($shippingInfo->first_name),
						mysql_real_escape_string($shippingInfo->last_name),
						mysql_real_escape_string($shippingInfo->address_1),
						mysql_real_escape_string($shippingInfo->address_2),
						mysql_real_escape_string($shippingInfo->city),
						mysql_real_escape_string($shippingInfo->state_province),
						mysql_real_escape_string($shippingInfo->zip_postal_code),
						mysql_real_escape_string($shippingInfo->country),
						mysql_real_escape_string($shippingInfo->email),
						mysql_real_escape_string($shippingInfo->company),
						mysql_real_escape_string($shippingInfo->ship_qty)
					);
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$shippingInfo->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$shippingInfo->id = -1;
				if(ShippingDB::DEBUG) echo mysql_error();
				return false;
			}
		}

		function commitShippingInformationJSON($shippingJson, $orderId) {
			$shippingInfo = ShippingInformation::fromJSON($shippingJson);
			return $this->commitShippingInformation($shippingInfo, $orderId);
		}
	}

?>
