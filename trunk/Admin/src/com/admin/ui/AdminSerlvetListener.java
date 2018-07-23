package com.admin.ui;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.servlet.ServletContextEvent;
import javax.servlet.ServletContextListener;
import javax.servlet.annotation.WebListener;

import workstation.processors.DesignProcessorFactory;
import concurrency.JobManager;

@WebListener
public class AdminSerlvetListener  implements ServletContextListener
{
	
	public static final String GenesysURL = "Admin_GenesysURL";
	public static final String PropertiesFile = "Admin_PropertiesFile";
	public static final String APIURL= "Admin_APIURL";
	
	@Override
	public void contextInitialized(ServletContextEvent sce)	
	{
		try
		{
			
			System.out.println("Workstation Init");		

			Context context = new InitialContext();			
		
			context.rebind(GenesysURL, sce.getServletContext().getInitParameter(GenesysURL));
			context.rebind(APIURL, sce.getServletContext().getInitParameter(APIURL));
			context.rebind(PropertiesFile, sce.getServletContext().getInitParameter(PropertiesFile));
			
			System.out.println("  Starting : " + JobManager.INSTANCE_NAME);
			JobManager jobManager = new JobManager();			
			jobManager.startup();
			context.rebind(JobManager.INSTANCE_NAME, jobManager);
			
			System.out.println("  Starting : " + DesignProcessorFactory.INSTANCE_NAME);
			DesignProcessorFactory designProcessorFactory = new DesignProcessorFactory();
			context.rebind(DesignProcessorFactory.INSTANCE_NAME, designProcessorFactory);			
		}
		catch(NamingException e)
		{
			e.printStackTrace();
		}
	}

	@Override
	public void contextDestroyed(ServletContextEvent sce)
	{
		try
		{
			System.out.println("Workstation Destroy");			

			Context context = new InitialContext();			
			
			context.unbind(GenesysURL);
			context.unbind(APIURL);
			
			System.out.println("  Shutting down : " + JobManager.INSTANCE_NAME);
			JobManager jobManager = (JobManager)context.lookup(JobManager.INSTANCE_NAME);
			context.unbind(JobManager.INSTANCE_NAME);
			jobManager.shutdown();
			System.out.println("  Shutting down : " + DesignProcessorFactory.INSTANCE_NAME);
			context.unbind(DesignProcessorFactory.INSTANCE_NAME);
		}
		catch(NamingException e)
		{
			e.printStackTrace();
		}
	}
}