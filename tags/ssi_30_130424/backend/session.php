<?php
	include_once "db_user.php";
	//This class takes care of the session details.

	class Session
	{	
		const DESIGN_MODE_FULL = "full";
		const DESIGN_MODE_SIMPLE = "simple";
		
		private $session_id = NULL;
		
		function getSesstionVar($id, $default="")
		{
			if(!isset($_SESSION[$id])) return $default;
			return $_SESSION[$id];
		}
		
		function getActiveUserId() { return $this->getSesstionVar('activeUserId'); }
		function setActiveUserId($id) { $_SESSION['activeUserId'] = $id; }
		
		function getActiveOrderId() { return $this->getSesstionVar('activeOrderId'); }
		function setActiveOrderId($id) { $_SESSION['activeOrderId'] = $id; }
		
		function getActiveOrderItemId() { return $this->getSesstionVar('activeOrderItemId'); }
		function setActiveOrderItemId($id) { $_SESSION['activeOrderItemId'] = $id; }

		//function getActiveThemeName() { return "humanity"; }
		function getActiveThemeName() { return "cupertino"; }
		
		function getActiveDesignId() { return $this->getSesstionVar('activeDesignId'); }
		function setActiveDesignId($id) { $_SESSION['activeDesignId'] = $id; }
				
		function getSelectedTemplateId() { return $this->getSesstionVar('selectedTemplateId'); }
		function setSelectedTemplateId($id) { $_SESSION['selectedTemplateId'] = $id; }
		
		function getDesignMode() { return $this->getSesstionVar('designMode'); }
		function setDesignMode($designMode) { $_SESSION['designMode'] = $designMode; }		
		
		function getEnableTemplateBrowser() { return $this->getSesstionVar('enableTemplateBrowser'); }
		function setEnableTemplateBrowser($enableTemplateBrowser) { $_SESSION['enableTemplateBrowser'] = $enableTemplateBrowser; }		
		
		function getReturnUrl() { return $this->getSesstionVar('returnUrl'); }
		function setReturnUrl($returnUrl) { $_SESSION['returnUrl'] = $returnUrl; }
		
		function getUserId() { return $this->getSesstionVar('userId'); }
		function setUserId($userId) { $_SESSION['userId'] = $userId; }
		
		function __construct()
		{
			if(session_id() == "") session_start();
			$this->session_id = session_id(); 			
		}
	
		public function close()
		{
			$_SESSION = array();

			if (ini_get("session.use_cookies"))
			{
		    	$params = session_get_cookie_params();
				setcookie(
						session_name(), 
						'', 
						time() - 42000,
						$params["path"],
						$params["domain"],
						$params["secure"],
						$params["httponly"]
		    		);
			}
			session_destroy();
		}
	}
?>