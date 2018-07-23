package services;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.script.ScriptException;
import javax.servlet.ServletContextEvent;
import javax.servlet.ServletContextListener;
import javax.servlet.annotation.WebListener;

import data.ResourceManager;

@WebListener
public class ContextListener implements ServletContextListener {

	public static final String BaseFolder = "BaseFolder";
	
	@Override
	public void contextDestroyed(ServletContextEvent arg0) {

		try
		{	
			Context context = new InitialContext();
			context.unbind(BaseFolder);
		} catch (NamingException e) {
			e.printStackTrace();
		}
	}

	@Override
	public void contextInitialized(ServletContextEvent arg0) {
		try {
			Context context = new InitialContext();
			context.rebind(BaseFolder, arg0.getServletContext().getInitParameter(BaseFolder));

			ResourceManager.init();

		} catch (NamingException e) {
			e.printStackTrace();
		}
	}

}