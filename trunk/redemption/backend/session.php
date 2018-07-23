<?php
require_once 'genesys_interface.php';
class Session
{
	public $sessionId;
	public $customerId;
	public $code;
	
	
	/**
	 * @var GenesysOrderDetails
	 */
	public $orderDetails;
	
	/**
	 * @return Session
	 */
	public static function load($sid)
	{
		session_start();
		if(!isset($_SESSION[$sid])) return NULL;
		return unserialize($_SESSION[$sid]);
	}
	
	/**
	 * @return Session
	 */
	public static function create($sid)
	{
		$session = new Session();
		$session->sessionId = $sid;
		return $session;
	}

	public function save()
	{
		session_start();
		$_SESSION[$this->sessionId] = serialize($this);
	}
}

?>