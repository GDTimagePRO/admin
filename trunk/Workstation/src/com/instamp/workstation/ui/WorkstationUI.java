package com.instamp.workstation.ui;

import java.lang.reflect.Constructor;
import java.sql.SQLException;
import java.util.Collection;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.servlet.annotation.WebServlet;

import com.instamp.workstation.data.GenesysDB;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.processors.design.DesignProcessor;
import com.instamp.workstation.processors.design.DesignProcessorFactory;
import com.instamp.workstation.processors.design.OrderStatusProcessor;
import com.instamp.workstation.processors.design.SummaryProcessor;
import com.instamp.workstation.ui.components.DesignListView;
import com.instamp.workstation.ui.components.DesignProcessorDialog;
import com.vaadin.annotations.Push;
import com.vaadin.annotations.Theme;
import com.vaadin.annotations.VaadinServletConfiguration;
import com.vaadin.data.util.sqlcontainer.connection.JDBCConnectionPool;
import com.vaadin.server.VaadinRequest;
import com.vaadin.server.VaadinServlet;
import com.vaadin.shared.communication.PushMode;
import com.vaadin.ui.Component;
import com.vaadin.ui.MenuBar;
import com.vaadin.ui.TabSheet;
import com.vaadin.ui.UI;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.MenuBar.MenuItem;

@Push(PushMode.AUTOMATIC)
@Theme("workstation")
public class WorkstationUI extends UI
{
	private static final long serialVersionUID = 7640878814254954101L;
	
	private static JDBCConnectionPool connectionPool;

	@WebServlet(value = "/WorkstationUI/*", asyncSupported = true)
	@VaadinServletConfiguration(productionMode = true, ui = WorkstationUI.class)
	public static class Servlet extends VaadinServlet
	{
		private static final long serialVersionUID = 256702570235481534L;
	}

	private final VerticalLayout _rootLayout = new VerticalLayout();
	private final VerticalLayout _observerListLayout = new VerticalLayout();
	private final MenuBar _menu = new MenuBar();		
	private final TabSheet _bodyTabs = new TabSheet();
	
	public void addObserverUi(Component component, boolean showToUser)
	{
		_observerListLayout.addComponent(component);
		if(showToUser) _bodyTabs.setSelectedTab(_observerListLayout);
	}
	
	public void removeObserverUi() {
		_observerListLayout.removeAllComponents();
	}
	
	@Override
	public void detach() {
		connectionPool.destroy();
		super.detach();
	}
	
	@Override
	protected void init(VaadinRequest request)
	{
		_rootLayout.setMargin(false);
		_rootLayout.setSpacing(false);
		_rootLayout.addComponent(_menu); 
		_rootLayout.addComponent(_bodyTabs);
		_rootLayout.setExpandRatio(_bodyTabs, 1F);
		_rootLayout.setSizeFull();
		
		final MenuBar.MenuItem toolsMenuItem = _menu.addItem("Tools", null, null);
		final MenuBar.MenuItem settingsMenuItem = _menu.addItem("Settings", null, null);
		final MenuBar.MenuItem helpMenuItem = _menu.addItem("Help", null, null);
		
		final MenuBar.MenuItem printMenuItem = toolsMenuItem.addItem("Print", null);
		/*printMenuItem.addItem("Polymer", null);
		printMenuItem.addItem("Trio", null);
		printMenuItem.addItem("Trio Index Cards", null);
		printMenuItem.addItem("Embossers Index Cards", null);
		printMenuItem.addItem("Laser", null);
		printMenuItem.addItem("Laser Layout Sheet", null);*/
		
		
		_bodyTabs.setSizeFull();
		
		setContent(_rootLayout);
		final DesignListView designListView;
		final DesignProcessorFactory dpf;
		
		try
		{
			Context context = new InitialContext();
			dpf = (DesignProcessorFactory)context.lookup(DesignProcessorFactory.INSTANCE_NAME);
			
		}
		catch (Exception e) { throw new RuntimeException(e); } 
		
		
		
		try
		{
			
			designListView = new DesignListView(GenesysDB.getConnectionPool(), null);
			designListView.setSizeFull();
			
			_bodyTabs.addTab(designListView, "Home");
		}
		catch(SQLException | NamingException e) { throw new RuntimeException(e); }
		

		DesignProcessor[] _processors =  dpf.getProcessors();
		
		for(int i=0; i<_processors.length; i++)
		{
			final DesignProcessor selectedProcessor = _processors[i];
			if (selectedProcessor.getClass() != SummaryProcessor.class && selectedProcessor.getClass() != OrderStatusProcessor.class) {
				printMenuItem.addItem(selectedProcessor.getName(), new MenuBar.Command() {
					@Override
					public void menuSelected(MenuItem selectedItem) {
						DesignProcessor sp;
						Constructor<? extends DesignProcessor> ctor;
						try {
							ctor = selectedProcessor.getClass().getDeclaredConstructor();
							ctor.setAccessible(true);
							sp = ctor.newInstance();
						} catch (Exception e) {
							throw new RuntimeException(e);
						}
						Collection<DesignDetails> designDetails = designListView.getSelectedDesigns();
						DesignDetails[] designs = designDetails.toArray(new DesignDetails[designDetails.size()]);
						DesignProcessorDialog.startProcessor(sp, designs);
						designListView.clearSelectedDesigns();
					}
				});
			}
		}
		
		
		toolsMenuItem.addItem("Create Summary Report", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				Collection<DesignDetails> designDetails = designListView.getSelectedDesigns();
				DesignDetails[] designs = designDetails.toArray(new DesignDetails[designDetails.size()]);
				DesignProcessorDialog.startProcessor(dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.Summary), designs);
				designListView.clearSelectedDesigns();
			}
		});
		
		toolsMenuItem.addItem("Change Order Status", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				Collection<DesignDetails> designDetails = designListView.getSelectedDesigns();
				DesignDetails[] designs = designDetails.toArray(new DesignDetails[designDetails.size()]);
				DesignProcessorDialog.startProcessor(dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.ChangeStatus), designs);
				designListView.clearSelectedDesigns();
			}
		});

		_bodyTabs.addTab(_observerListLayout, "Observers");
		
	}

}