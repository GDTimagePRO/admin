<?php
	include_once "_common.php";

	//http://in-stamp.com.loucks51.arvixevps.com/masonrow/SetUp.php?emailUrl=cs@stampsignsbadges.com&code=HO-1009&emailUs=cs@stampsignsbadges.com&sName=Mason%20Row&url=http://www.masonrow.com/holiday-greeting

	class InitSessionResponse
	{
		public $error;
		public $sid;
		public $url;
	}

	function initSession()
	{
		$barcodeStr = $_GET['code'];
		$siteName = $_GET['sName'];
		$submitUrl = $_GET['url'];

		$response = new InitSessionResponse();

		if(!isset($submitUrl))
		{
			$response->error = "Missing submit URL.";
			return $response;
		}

		if(!isset($barcodeStr))
		{
			$response->error = "Missing barcode.";
			return $response;
		}

		if(!isset($siteName))
		{
			$response->error = "Missing Site Name.";
			return $response;
		}

		$customer = Common::$orderDB->getCustomerByKey($siteName);
		if(is_null($customer))
		{
			$response->error = 'Failed to load customer with key : "' . $siteName . '"';
			return $response;
		}

		$barcode = Common::$orderDB->getBarcodeByBarcode( $customer->id, $barcodeStr);
	 	if(!is_null($barcode))
	 	{
	 		if(!$barcode->isUsed())
	 		{
	 			Common::$session = Session::create($customer);
				if(isset($_GET['externalDesignOptions'])) {
					Common::$session->designEnvironment = DesignEnvironment::createFromBarcode($barcode, Common::$session->sessionId, $_GET['externalDesignOptions']);
				} else {
					Common::$session->designEnvironment = DesignEnvironment::createFromBarcode($barcode, Common::$session->sessionId, null);
				}

	 			if(isset($_GET['system_name']))
	 			{
	 				Common::$session->designEnvironment->orderItem->externalSystemName = $_GET['system_name'];
	 			}

	 			if(isset($_GET['user_id']))
	 			{
	 				Common::$session->designEnvironment->orderItem->externalUserId = $_GET['user_id'];
	 			}

	 			if(isset($_GET['order_id']))
	 			{
	 				Common::$session->designEnvironment->orderItem->externalOrderId = $_GET['order_id'];
	 			}

	 			if(isset($_GET['order_status']))
	 			{
	 				Common::$session->designEnvironment->orderItem->externalOrderStatus = $_GET['order_status'];
	 			}

	 			if(isset($_GET['return_url']))
	 			{
	 				Common::$session->urlHome = $_GET['return_url'];
	 				Common::$session->urlReturn = $_GET['return_url'];
	 			}
	 			if(isset($_SERVER['HTTP_REFERER']))
	 			{
	 				Common::$session->urlHome = $_SERVER['HTTP_REFERER'];
	 				Common::$session->urlReturn = $_SERVER['HTTP_REFERER'];
	 			}
	 			Common::$session->urlSubmit = $submitUrl;

	 			if(isset($_GET['attachment'])) Common::$session->attachment = $_GET['attachment'];

	 			if(!is_null(Common::$session->designEnvironment))
	 			{
	 				Common::$session->save();
	 				$response->sid = Common::$session->sessionId;
	 				$response->url = "http://". Settings::HOME_URL . "design_customize.php?" . Common::queryVars();
	 				if(!isset($_GET['redirect']) || ($_GET['redirect']=='true')) Header("location: " . $response->url);
	 			}
	 			else
	 			{
	 				$response->error = "Error: Could not create design environment.";
	 			}
	 		}
	 		else
	 		{
	 			$response->error = "That code has already been used.";
	 		}
	 	}
	 	else
	 	{
	 		$response->error = "That code was not recognised.";
	 	}

	 	return $response;
	}

	echo json_encode(initSession());
?>
