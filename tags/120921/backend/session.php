<?php
	include_once "db_user.php";
	//This class takes care of the session details.

	class Session
	{	
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
		
		function getActiveDesignId() { return $this->getSesstionVar('activeDesignId'); }
		function setActiveDesignId($id) { $_SESSION['activeDesignId'] = $id; }
		
		function getActiveDesignImageId() { return $this->getSesstionVar('activeDesignImageId'); }
		function setActiveDesignImageId($id) { $_SESSION['activeDesignImageId'] = $id; }
		
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