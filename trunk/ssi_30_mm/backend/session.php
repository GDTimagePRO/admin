<?php
	require_once "design_environment.php";
	require_once "startup.php";
	require_once 'resource_manager.php';
	//This class takes care of the session details.

	class Session
	{			
		public $sessionId = NULL;
		
		public $config = NULL;
		/**
		 * @var DesignEnvironment
		 */
		public $designEnvironment = NULL;
		public $urlHome = NULL;
		public $urlSubmit = NULL;
		public $urlReturn = NULL;
		public $customerId = NULL;
		public $attachment = NULL;
		
		public static function uploadDirPath($sid)
		{
			return ResourceManager::getPath(ResourceManager::GROUP_SESSION, $sid);
		}
		
		public static function uploadDirId($sid)
		{
			return ResourceManager::getId(ResourceManager::GROUP_SESSION, $sid);
		}
		
		public function getUploadDirId()
		{
			return Session::uploadDirId($this->sessionId);
		}
		
		
		/**
		 * @return NULL|Session
		 */
		public static function load($sessionId)
		{
			$result = mysql_query(
					"SELECT data FROM sessions WHERE id = '" . mysql_real_escape_string($sessionId) . "'",
					Startup::getInstance()->db->connection
				);
						
			if(!$result) return NULL;
			
			$row = mysql_fetch_row($result);
			
			if(!$row) return NULL;
			
			return unserialize($row[0]);
		}
		
		public function save()
		{
			$query = sprintf(
					"UPDATE sessions SET data = '%s', date_modified=FROM_UNIXTIME(%d) WHERE id = '%s'",
					mysql_real_escape_string(serialize($this)),
					time(),
					mysql_real_escape_string($this->sessionId)
				);
					
			return mysql_query($query, Startup::getInstance()->db->connection);
		}

		/**
		 * @return Session
		 */
		public static function create(Customer $customer = NULL)
		{
			$session = new Session();
			while(true)
			{
				$session->sessionId = hash('ripemd160', uniqid('', true));
				
				$query = sprintf(
						"INSERT INTO sessions(id, data, date_modified) VALUES('%s', '%s', FROM_UNIXTIME(%d))",
						mysql_real_escape_string($session->sessionId),
						mysql_real_escape_string(serialize($session)), 
						time()
				);
				
				if(mysql_query($query, Startup::getInstance()->db->connection)) 
				{
					break;
				}				
			}
			
			if(!is_null($customer))
			{
				$session->customerId = $customer->id;
				try
				{
					$session->config = json_decode($customer->configJSON);
				}
				catch(Exception $e)
				{
					$session->config = new stdClass();
				}
				
				if(!isset($session->config->theme))
				{
					$session->config->theme = "default";
				}
			
				if(!isset($session->config->vars))
				{
					$session->config->vars = new stdClass();
					$session->config->vars->TITLE = $customer->description; 
				}
			}
			
			return $session;
		}
	}
?>