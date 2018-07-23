package com.instamp.workstation.processors.design;

import java.io.Serializable;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import com.instamp.workstation.Application;
import com.instamp.workstation.concurrency.JobManager;
import com.instamp.workstation.concurrency.JobManager.IJobRunnable;
import com.instamp.workstation.concurrency.JobManager.IObserverListener;
import com.instamp.workstation.concurrency.JobManager.Observer;
import com.instamp.workstation.data.GenesysDB;
import com.vaadin.server.VaadinSession;
import com.vaadin.ui.Component;

//http://stackoverflow.com/questions/11553576/asynchronously-update-vaadin-components

public abstract class DesignProcessor implements Serializable
{

	private final String _name;
	private final String _description;
	private Component _observerUI = null;
	private String _genesysURL = "";
	protected Integer _jobId = null;
	
	protected DesignProcessor(String name, String description)
	{
		_name = name;
		_description = description;
		
		Context context;
		try {
			context = new InitialContext();
			_genesysURL = (String) context.lookup(Application.GenesysURL);
		} catch (NamingException e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Load the configuration of the processor from a string returned by a previous call to saveConfig.  
	 * @param config A configuration for the processor encoded as a string or null. 
	 */
	public void loadConfig(String config) {}
	
	/**
	 * Returns the current configuration of the processor encoded as a string. 
	 * @return Configuration encoded as a string or null.
	 */
	public String saveConfig() { return null; }

	
	/**
	 * Returns the name of the collection processor that will be displayed to the user.
	 */	
	public final String getName() { return _name; }
	
	public String getGenesysURL() { return _genesysURL; }
	
	
	/**
	 * Returns the description of the collection processor that will be shown to the user.
	 */
	public final String getDescription() { return _description; }
	
	/**
	 * Returns a vaadin custom component that allows the user to configure the processor.
	 * @param designs The currently selected set of designs
	 * @return CustomComponent or null.
	 */
	public abstract Component getConfigUI(GenesysDB.DesignDetails[] designs);
	
	public Component getObserverUI() { 
		if (_observerUI == null) {
			_observerUI = new DefaultObserverUI(this);
		}
		return _observerUI;
	}
	
	/**
	 * Processes the specified designs.
	 * @param designs
	 */
	protected abstract void run(Observer observer, GenesysDB.DesignDetails[] designs);
	
	//Note: Done to avoid pulling pulling the current context into the JobProcessor context 
	private static final class DisconnectedRunnable implements IJobRunnable	
	{
		private final DesignProcessor _workerInstance;
		private final GenesysDB.DesignDetails[] _designs;
		
		public DisconnectedRunnable(DesignProcessor workerInstance, GenesysDB.DesignDetails[] designs)
		{
			_workerInstance = workerInstance;
			_designs = designs;
		}
		
		public void run(Observer observer)
		{
			_workerInstance.run(observer, _designs);
		}
	}
	
	protected final void cleanup() {
		if (_jobId != null) {
			JobManager jobManager = null;
			Context ctx;
			try {
				ctx = new InitialContext();
				jobManager = (JobManager)ctx.lookup(JobManager.INSTANCE_NAME);
				jobManager.removeJob(_jobId);
			} catch (NamingException e) {
				throw new RuntimeException(e);
			}
           
		}
	}
	
	public final int start(final GenesysDB.DesignDetails[] designs, final IObserverListener listener)
	{
        JobManager jobManager = null;
    	try
    	{
            final DesignProcessor workerInstance = this.getClass().newInstance();
            workerInstance.loadConfig(this.saveConfig());
    		Context ctx = new InitialContext();
            jobManager = (JobManager)ctx.lookup(JobManager.INSTANCE_NAME);
            _jobId = jobManager.startJob(
            		new DisconnectedRunnable(
            			workerInstance, 
            			designs
            		),
            		listener, 
            		VaadinSession.getCurrent()
            	);
            return _jobId;
    	}
    	catch(Exception e) { throw new RuntimeException(e); } 
	}

}
