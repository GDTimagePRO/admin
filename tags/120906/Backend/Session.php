<?php
/**
 * This class takes care of the session details.
 *   
 */

 class Session{
 	private $session_id = NULL;
	
	/**
	 * If the session has not been started, then start it.
	 */
	function __construct(){
		if(session_id() == ""){
			session_start();
			$this->session_id = session_id(); 
			
		}
		
	}
	
	
	/**
	 * Returns the user ID of the logged in user. 
	 * @return string returns the user id of the logged in user or "" if there is no one logged in.
	 */
	public function getUserId(){
		if(isset($_SESSION['userId'])){
			return $_SESSION['userId'];
		}
		else{
			return "";
		}
		
	}
	
	/**
	 * Sets the user ID for the logged in user.
	 * @param string $userId the user ID for the currently logged in user.
	 */
	public function setUserId($userId){
		$_SESSION['userId'] = $userId;
	}
	
	/**
	 * Set the current item that the user is working on.
	 * @param string $id the item id.
	 */
	public function setCurrentItem($id){
		$_SESSION['itemId'] = $id;
		
	}
	
	public function getCurrentItem(){
		if(isset($_SESSION['itemId'])){
			return $_SESSION['itemId'];
		}
		else{
			return "";
		}
	}
	
	/**
	 * Sets the current order
	 */
	public function setCurrentOrder($id){
		$_SESSION['orderId'] = $id;
	}
	
	/**
	 * Closes the session and logs out the user. Removes all references to all user information.
	 */
	public function close(){
		$_SESSION = array();

		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		session_destroy();
	}
	
	
 }

?>