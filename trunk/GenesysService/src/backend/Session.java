package backend;

import backend.Resource_Manager.ResourceManager;


public class Session {
	
	//This class takes care of the session details.

			
		public String sessionId;
		
		public String config;
		/**
		 * @var DesignEnvironment
		 */
		public String designEnvironment;
		public String urlHome;
		public String urlSubmit;
		public String urlReturn;
		public String customerId;
		public String attachment;
		
		public static String uploadDirPath(String sid)
		{
			return ResourceManager.getPath(ResourceManager.GROUP_SESSION, sid);
		}
		
		public String uploadDirId(String sid)
		{
			return ResourceManager.getId(ResourceManager.GROUP_SESSION, sid);
		}
		
		public String getUploadDirId()
		{
			return uploadDirId(sessionId);
		}
		
		
		/**
		 * @return NULL|Session
		 */
		public static String load(String sessionId)
		{
			result = mysql_query(
					"SELECT data FROM sessions WHERE id = '" . mysql_real_escape_string($sessionId) . "'",
					Startup.getInstance()->db->connection
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
		public static Session create(Customer customer)// customer defaulted to null
		{
			Session session = new Session();
			while(true)
			{
				session.sessionId = hash("ripemd160", uniqid('', true));
				
				$query = sprintf(
						"INSERT INTO sessions(id, data, date_modified) VALUES('%s', '%s', FROM_UNIXTIME(%d))",
						mysql_real_escape_string(session.sessionId),
						mysql_real_escape_string(serialize($session)), 
						time()
				);
				
				if(mysql_query($query, Startup::getInstance()->db->connection)) 
				{
					break;
				}				
			}
			
			if(customer == null)
			{
				session.customerId = customer.id;
				try
				{
					session.config = json_decode(customer.configJSON);
				}
				catch(Exception $e)
				{
					session.config = new stdClass();
				}
				
				if(!isset($session->config->theme))
				{
					session.config.theme = "default";
				}
			
				if(!isset(session.config.vars))
				{
					session.config.vars = new stdClass();
					$session->config->vars->TITLE = $customer->description; 
				}
			}
			
			return session;
		}
	
}
