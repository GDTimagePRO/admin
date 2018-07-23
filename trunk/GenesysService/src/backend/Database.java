package backend;

import java.sql.Connection;
import java.sql.SQLException;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.ConnectionPoolDataSource;
import javax.sql.DataSource;
import javax.sql.PooledConnection;

public class Database
{
	
	private static Database db = null;
	private Connection connection; //this stores the connection to the database		
	
	/**
	 * @var DesignDB
	 */
	public DesignDB design;

	/**
	 * @var OrderDB
	 */
	public OrderDB order;

	private Database() throws NamingException, SQLException
	{
		if(connection == null)
		{
			InitialContext ctx = new InitialContext();
			ConnectionPoolDataSource  ds = (ConnectionPoolDataSource) ctx.lookup("genesys_core");
			PooledConnection pc = ds.getPooledConnection();
            connection = pc.getConnection();
		}
			
		design = new DesignDB(connection);
		order = new OrderDB(connection);
	}
	
	public static Database getDatabase() throws NamingException, SQLException {
		if (db == null) {
			db = new Database();
		}
		return db;
	}
	
	public void close() throws SQLException {
		connection.close();
	}
}