<?php
	require_once 'backend/startup.php';
	$system = Startup::getInstance();
	
	if(isset($_GET['code']))
	{
		$query = "SELECT * FROM used_codes_ex WHERE rcid= ".Settings::CUSTOMER_ID . " AND rc='" . mysql_real_escape_string($_GET['code']) . "'";
		
		$result = mysql_query($query,$system->db->connection);		
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			exit();
		}
		
		$row = mysql_fetch_assoc($result);
		
		$shippingDetails = json_decode($row['shipping_details']);
		$orderDetails = json_decode($row['order_details']);
		
		date_default_timezone_set("UTC");
		
		$params = new stdClass();
		$params->id = $row['order_item_id'];
		$params->createdOnUtc = date("Y-m-d H:i:s");
		$params->paymentMethod = '';
		$params->shippingMethod = '';
		$params->discountCoupon = $row['rc'];
		
		$address = new stdClass();
		$address->company = '';
		$address->firstName = $shippingDetails->firstName;
		$address->lastName = $shippingDetails->lastName;
		$address->phoneNumber = $shippingDetails->phone;
		$address->address1 = $shippingDetails->unit . ' ' . $shippingDetails->street;
		$address->city = $shippingDetails->city;
		$address->stateProvince = $shippingDetails->region;
		$address->country = $shippingDetails->country;
		$address->zipPostalCode = $shippingDetails->zip;
		
		$params->billingAddress = $address;
		$params->shippingAddress = $address;
		
		$product = new stdClass();
		$product->name = $row['product_name'];
		$product->imageUrl = Settings::getImageUrl($orderDetails->imageId_S);
		$product->categoryName = $row['barcode'];
		$product->ManufacturerPartNumber = $row['product_code'];
		$product->quantity = "1";
		$product->quantityshipped = "0";
		
		$params->products = array($product);
		
		
		//echo json_encode(array($params));
		Header("location: http://genesys.in-stamp.com:8080/PackingSlipService/services/packing-slips/pdf?data=".urlencode(json_encode(array($params))));
		exit();
	}
	
	$query = "SELECT rc_group_id, rc_group_description, rc, date_used, order_item_id, processing_stage_id,design_id,design_state, shipping_email FROM used_codes_ex WHERE rcid=".Settings::CUSTOMER_ID;
	
	$result = mysql_query($query,$system->db->connection);
	if(!$result)
	{
		if(DesignDB::DEBUG) echo mysql_error();
		exit();
	}
	
?>
<!DOCTYPE html>
<html>
<head>
<style>	
	table{ border-collapse: collapse; }
	tr:nth-child(odd)		{ background-color:#ffffff; }
	tr:nth-child(even)		{ background-color:#eeeeff; }
	
	th {
		border-style:solid;
		border-width:1px;
		margin:0;
		padding-left:10px;
		padding-right:10px;
		padding-top:3px;
		padding-bottom:3px;
	}
	
	td {
		border-style:solid;
		border-width:1px;
		margin:0;
		padding-left:10px;
		padding-right:10px;
		padding-top:3px;
		padding-bottom:3px;
	}
</style>
</head>
<body>
<table>
<?php
	$isFirst = true;
	$prevId = '';
	
	while($row = mysql_fetch_assoc($result))
	{
		if($isFirst)
		{
			echo "<tr>";
			foreach($row as $key => $value)
			{
				echo '<th>';
				echo htmlspecialchars($key);
				echo '</th>';
			}
			echo '<th>slip</th>';
			echo "</tr>";
			
			
			$isFirst = false;
		}
				
		echo "<tr>";
		foreach($row as $key => $value)
		{
			echo '<td>';
			echo htmlspecialchars($value);
			echo '</td>';
		}
		echo '<td>';
		if($prevId != $row['rc'])
		{
			echo '<a href="list_codes.php?code='. $row['rc'] .'" target="_blank">[DL]</a>';
			$prevId = $row['rc'];
		}
		echo '</td>';		
		echo "</tr>";
	}	
?>	
</table>
</body>
</html>
