package workstation.processors;


import java.io.Serializable;
import java.util.List;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;

import model.Design;

import com.admin.ui.AdminSerlvetListener;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.ui.Component;
import com.vaadin.ui.UI;

import concurrency.JobManager;
import concurrency.JobManager.IJobRunnable;
import concurrency.JobManager.IObserverListener;
import concurrency.JobManager.Observer;

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
			_genesysURL = (String) context.lookup(AdminSerlvetListener.GenesysURL);
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
	public abstract Component getConfigUI(List<EntityItem<Design>> designs);
	
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
	protected abstract void run(Observer observer, List<EntityItem<Design>> designs);
	
	//Note: Done to avoid pulling pulling the current context into the JobProcessor context 
	private static final class DisconnectedRunnable implements IJobRunnable	
	{
		private final DesignProcessor _workerInstance;
		private final List<EntityItem<Design>> _designs;
		
		public DisconnectedRunnable(DesignProcessor workerInstance, List<EntityItem<Design>> designs)
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
	
	public final int start(final List<EntityItem<Design>> designs, final IObserverListener listener)
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
            		UI.getCurrent()
            	);
            return _jobId;
    	}
    	catch(Exception e) { throw new RuntimeException(e); } 
	}
	
	public static void startProcessor(DesignProcessor processor, List<EntityItem<Design>> designs)
	{
		Component observerUi = processor.getObserverUI();
		Component configUI = processor.getConfigUI(designs);
		if (configUI == null) {
			processor.start(designs, (IObserverListener)observerUi);
		}
	}

}
