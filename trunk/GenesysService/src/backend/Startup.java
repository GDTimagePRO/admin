package backend;

import java.sql.SQLException;

import javax.naming.NamingException;

public class Startup {
		public static final String URL_REDIRECT_NO_DESIGN_ENVIRONMENT	= "missing_design_environment.php";
		public static final String URL_REDIRECT_NO_CUSTOMER				= "missing_customer.php";
		
		/**
		 * @var Startup
		 */
		private static Startup instance;

		/**
		 * @var Database
		 */
		public Database db;

		private Startup() throws NamingException, SQLException
		{		
			db = Database.getDatabase();
		}
				
		/*void errorRedirect(String to)
		{
			String Header = new String();
			if(isset($_COOKIE["redirect"]) && ($_COOKIE["redirect"] != ""))
			{
				Header("location: " . $_COOKIE["redirect"]);				
			}
			else
			{
				Header("location: http://" + Settings.HOME_URL + to);
			}			
		}*/
		
		/**
		 * @return Startup
		 * @throws SQLException 
		 * @throws NamingException 
		 */
		public static Startup getInstance() throws NamingException, SQLException
		{
			if(instance == null)
			{
				instance = new Startup(); 
			}
			return instance;
	  	}
	
}
