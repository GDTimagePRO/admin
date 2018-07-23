package com.instamp.workstation.concurrency;

import java.util.LinkedList;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.ThreadFactory;

import com.vaadin.server.StreamResource;
import com.vaadin.server.VaadinSession;

public final class JobManager
{
	public static String INSTANCE_NAME = "JobManager"; 
	
	private ExecutorService executor;

	public interface IObserverListener
	{
		void onChange(Observer observer);
	}
	
	public interface IJobRunnable
	{
		public void run(Observer observer);
	}
	
	 private static class DaemonThreadFactory implements ThreadFactory {

         private final ThreadFactory factory;

         public DaemonThreadFactory() {
                 this(Executors.defaultThreadFactory());
         }

         public DaemonThreadFactory(ThreadFactory factory) {
                 if (factory == null)
                         throw new NullPointerException("factory cannot be null");
                 this.factory = factory;
         }

         public Thread newThread(Runnable r) {
                 final Thread t = factory.newThread(r);
                 t.setDaemon(true);
                 return t;
         }
 }
	
	public final class Observer
	{
		private boolean _isObserverDirty = false;
		
		private float _progress = 0;
		private String _stateMessage = "";
		private StreamResource _result = null;
		private boolean _errorState = false;
		
		private void setDirty()
		{
			synchronized(_observerDaemonLock)
			{
				_isObserverDirty = true;
				_isJobManagerDirty = true;
				_observerDaemonLock.notify();
			}
		}
		
		public synchronized float getProgress() { return _progress; }
		
		public synchronized StreamResource getResult() { return _result; }
		
		public synchronized boolean getErrorState() { return _errorState; }

		public synchronized void setProgress(float progress)
		{
			if(progress < 0) progress = 0;
			if(progress > 1) progress = 1;

			_progress = progress;
			
			setDirty();
		}

		public synchronized void setProgress(float progress, String state)
		{
			if(state == null) state = "";
			if(progress < 0) progress = 0;
			if(progress > 1) progress = 1;

			_progress = progress;
			_stateMessage = state;
			
			setDirty();
		}
		
		public synchronized void logState(String state)
		{
			if(state == null) state = "";
			_stateMessage = state; 
			setDirty();
		}
		
		public synchronized void logError(String state)
		{
			_errorState = true;
			logState(state);
			setDirty();
		}
		
		public synchronized String getLastStateMessage() { return _stateMessage; }
		
		public synchronized void logError(Exception e)
		{
			logError("Error finishing job: " + e.getMessage());
			setDirty();
		}
		
		public synchronized void submitResult(StreamResource result)
		{
			_result = result;
			setDirty();
		}
		
		public synchronized void done()
		{
			setDirty();
		}
	}
	
	private final class Job extends Thread
	{
		final int _id;		
		final Observer _observer;
		final VaadinSession _vaadinSession;
		final IObserverListener _listener;
		final IJobRunnable _runnable;
		
		public Job(int id, VaadinSession vaadinSession, IObserverListener listener, IJobRunnable runnable)
		{
			_id = id;
			_observer = new Observer();
			_vaadinSession = vaadinSession;
			_listener = listener;
			_runnable = runnable;
			setDaemon(true);
			setPriority(Thread.MIN_PRIORITY);
		}

		@Override
		public void run()
		{
			try
			{
				_runnable.run(_observer);
			}
			catch(Exception e)
			{
				_observer.logError(e);
			}
		}
	}

	private final class ObserverDaemon extends Thread
	{
		boolean _timeToDie = false;
		
		public ObserverDaemon()
		{
			setDaemon(true);			
		}

		@Override
		public void run()
		{			
			_timeToDie = false;
			_isJobManagerDirty = true;
			
			while(true)
			{
				synchronized(_observerDaemonLock)
				{
					if(!_isJobManagerDirty) 
					{
						try { _observerDaemonLock.wait( 200 ); } catch ( Exception e ) {};
					}
					_isJobManagerDirty = false;
				}
				
				if(_timeToDie) return;				
				try
				{
					LinkedList<Job> todo = new LinkedList<>();
					synchronized (JobManager.this)
					{
						java.util.Iterator<Job> itr = _jobs.iterator();
						while(itr.hasNext())
						{
							Job job = itr.next();
							if(job._observer._isObserverDirty)
							{						
								if(job._listener != null)
								{
									if(job._observer._isObserverDirty);
									todo.add(job);
									job._observer._isObserverDirty = false;
								}
							}
						}				
					}
					
					java.util.Iterator<Job> itr = todo.iterator();
					while(itr.hasNext())
					{
						final Job job = itr.next();
						if(job._vaadinSession != null)
						{
							job._vaadinSession.access(new Runnable() {
								public void run() {
									job._listener.onChange(job._observer);
								}
							});
						}
						else
						{
							job._listener.onChange(job._observer);
						}
					}
				}
				catch(Exception e) { e.printStackTrace(); }
			}
		}
	}

	private Object _observerDaemonLock = new Object(); 
	private boolean _isJobManagerDirty = true;	
	
	private int _idCount = 1;
	private final LinkedList<Job> _jobs = new LinkedList<>(); 
	private ObserverDaemon _observerDaemon = null;
	
	public synchronized int startJob(IJobRunnable runnable, IObserverListener listener, VaadinSession vaadinSession)
	{
		Job job = new Job( _idCount++, vaadinSession, listener, runnable );
		_jobs.add(job);
		executor.execute(job);
		return job._id;
	}
	
	public synchronized void removeJob(int jobId) {
		for (Job j : _jobs) {
			if (j._id == jobId) {
				_jobs.remove(j);
			}
		}
	}
	
	public void startup()
	{
		executor = Executors.newFixedThreadPool(15, new DaemonThreadFactory());
		_observerDaemon = new ObserverDaemon();
		_observerDaemon.start();
	}

	public synchronized void shutdown()
	{
		synchronized(_observerDaemonLock)
		{
			_isJobManagerDirty = true;
			_observerDaemon._timeToDie = true;
			_observerDaemonLock.notify();
		}
		executor.shutdownNow();
		try { _observerDaemon.join(500); } catch(Exception e) {};
	}
	
}
