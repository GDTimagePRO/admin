package com.instamp.workstation.ui.components;

import java.util.Collection;
import javax.naming.Context;
import javax.naming.InitialContext;

import com.instamp.workstation.concurrency.JobManager.IObserverListener;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.processors.design.DesignProcessor;
import com.instamp.workstation.processors.design.DesignProcessorFactory;
import com.instamp.workstation.ui.WorkstationUI;
import com.vaadin.ui.Button;
import com.vaadin.ui.Component;
import com.vaadin.ui.UI;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;

public final class DesignProcessorDialog extends Window 
{
	private static final long serialVersionUID = 5380423044707138211L;

	private final VerticalLayout _rootLayout = new VerticalLayout(); 
	private final DesignProcessor[] _processors;
	
	public DesignProcessorDialog()
	{
		setContent(_rootLayout);
		setModal(true);
		setWidth("400px");
		setWidth("300px");
		
		DesignProcessorFactory dpf = null;
		
		try
		{
			Context context = new InitialContext();
			dpf = (DesignProcessorFactory)context.lookup(DesignProcessorFactory.INSTANCE_NAME);
		}
		catch (Exception e) { throw new RuntimeException(e); } 

		_processors =  dpf.getProcessors();
	}
	
	public static void startProcessor(DesignProcessor processor, DesignDetails[] designs)
	{
		Component observerUi = processor.getObserverUI();
		Component configUI = processor.getConfigUI(designs);
		((WorkstationUI)UI.getCurrent()).addObserverUi(observerUi, true);
		if (configUI == null) {
			processor.start(designs, (IObserverListener)observerUi);
		}
	}
	
	public void show(final Collection<DesignDetails> designs)
	{
		show(designs.toArray(new DesignDetails[designs.size()]));
	}
	
	public void show(final DesignDetails[] designs)
	{
		_rootLayout.removeAllComponents();
		
		for(int i=0; i<_processors.length; i++)
		{
			final DesignProcessor selectedProcessor = _processors[i];
			
			_rootLayout.addComponent( new Button(					
					selectedProcessor.getName(), 
					new ClickListener() {
						
						private static final long serialVersionUID = 5102825549490502644L;

						@Override
						public void buttonClick(ClickEvent event)
						{
							DesignProcessorDialog.startProcessor(selectedProcessor, designs);
							close();
						}
					}
				));
		}
		
		
		addCloseListener( new CloseListener() {

			private static final long serialVersionUID = 8286158088971375063L;

			@Override
			public void windowClose(CloseEvent e)
			{
				UI.getCurrent().removeWindow(DesignProcessorDialog.this);
			}
		}); 
		
		UI.getCurrent().addWindow(this);
		center();
	}
}
