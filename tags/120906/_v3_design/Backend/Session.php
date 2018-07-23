<?php
class Session
{
	private $session_id = NULL;

	function __construct()
	{
		if(session_id() == "")
		{
			session_start();
			$this->session_id = session_id();
		}
	}
	
	public function getUserId()
	{
		return 1000;
	}

	public function getDesignId()
	{
		return 2;
	}
}
?>