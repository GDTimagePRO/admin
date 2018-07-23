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
		$templateId = $_GET['templateId'];	
		$productId = $_GET['productId'];	
		$siteName = $_GET['sName'];
		
		$response = new InitSessionResponse();
			
		if(!isset($templateId))
		{
			$response->error = "Missing template id.";
			return $response;
		}
		
		if(!isset($productId))
		{
			$response->error = "Missing product id.";
			return $response;
		}
		
		if(!isset($siteName))
		{
			$response->error = "Missing Site Name.";
			return $response;
		}
		
		$customer = Common::$orderDB->getCustomerByKey(Customer::KEY_INTERNAL);
		$session = Session::create($customer);
		$session->customerId = $customer->id;
		$session->designEnvironment = DesignEnvironment::createFromTemplate($templateId, $session->sessionId, $productId);
		$template = COMMON::$designDB->getDesignTemplateById($templateId);
		$templateCategory = COMMON::$designDB->getDesignTemplateCategoryById($template->categoryId);
		$session->customerId = $templateCategory->customerId;
		
		if(isset($_GET['return_url']))
		{
			$session->urlHome = $_GET['return_url'];
			$session->urlReturn = $_GET['return_url'];
		}
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$session->urlHome = $_SERVER['HTTP_REFERER'];
			$session->urlReturn = $_SERVER['HTTP_REFERER'];
		}
		$session->urlSubmit = $submitUrl;
		
		if(isset($_GET['attachment'])) $session->attachment = $_GET['attachment'];  	 			
					
		if(!is_null($session->designEnvironment))
		{
			$session->save();
			$response->sid = $session->sessionId;
			$response->url = "http://". Settings::HOME_URL . "design_customize.php?sid=" . $session->sessionId;
			if(!isset($_GET['redirect']) || ($_GET['redirect']=='true')) Header("location: " . $response->url);
		}
		else
		{
			$response->error = "Error: Could not create design environment.";
		}
	 	
	 	return $response;
	}
	
	echo json_encode(initSession());
?>