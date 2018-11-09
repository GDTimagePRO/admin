package views.tables;


import java.io.File;
import java.io.FileWriter;
import java.lang.reflect.Constructor;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Set;

import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.annotations.Theme;
import com.vaadin.data.util.BeanItemContainer;
import com.vaadin.data.util.filter.Between;
import com.vaadin.data.util.filter.SimpleStringFilter;
import com.vaadin.event.LayoutEvents.LayoutClickEvent;
import com.vaadin.event.LayoutEvents.LayoutClickListener;
import com.vaadin.server.FileDownloader;
import com.vaadin.server.FileResource;
import com.vaadin.server.Resource;
import com.vaadin.server.ThemeResource;
import com.vaadin.server.VaadinService;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.DateField;
import com.vaadin.ui.Grid;
import com.vaadin.ui.Grid.Column;
import com.vaadin.ui.Grid.FooterCell;
import com.vaadin.ui.Grid.FooterRow;
import com.vaadin.ui.Grid.MultiSelectionModel;
import com.vaadin.ui.Grid.SelectionMode;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Label;
import com.vaadin.ui.Link;
import com.vaadin.ui.MenuBar;
import com.vaadin.ui.Notification;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.themes.ValoTheme;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.MenuBar.MenuItem;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.Table;
import com.vaadin.ui.TextField;
import components.WorkstationObservablesUI;
import concurrency.JobManager.FinishedEvent;
import concurrency.JobManager.IObserverListener;
import concurrency.JobManager.IOnFinishedListener;
import concurrency.JobManager.Observer;
import model.Customer;
import model.Design;
import redemption.RedemptionCode;
import redemption.RedemptionCodeGroup;
import workstation.processors.DesignProcessor;
import workstation.processors.DesignProcessorFactory;
import workstation.processors.DesignProcessorFactoryForRedemption;
import workstation.processors.OrderStatusProcessor;
import workstation.processors.SummaryProcessor;

@Theme("admin")
public class Redemption extends CustomComponent implements ClickListener {
	private JPAContainer<Design> workstation = JPAContainerFactory.makeJndi(Design.class);
	
	public static String PERMISSION_ACCESS = "redemption_access";
	private List<Customer> customerList;
	private List<Integer> redemptionCustomerIdList;
	
	private CurrentUser currentUser;
	private Table table;
	private Grid grid;
	private TextField filterField;
	private ComboBox groupIdComboBox;
	private ComboBox usageComboBox;
	private ComboBox date_usedComboBox;
	
	private VerticalLayout vLayout = new VerticalLayout();
	private MenuBar menu = new MenuBar();
	private int iterator;
	private List<RedemptionCode> redemptionCodeList;
	private RedemptionCode currentRedemptionCode;
	private BeanItemContainer<RedemptionCode> redemptionCodeContainer;
	private static final String DATABASE = "jdbc:mysql://localhost:3306/redemption_db?zeroDateTimeBehavior=convertToNull";
	private static final String DATABASE2 = "jdbc:mysql://localhost:3306/genesys_core?zeroDateTimeBehavior=convertToNull";
	private static final String USERNAME = "root";
	//private static final String PASSWORD = "Loucks74";
	private static final String PASSWORD = "D@n13lD@ng28";
	
	Connection conn = null;
	Connection conn2 = null;
	java.sql.Statement stmt = null;
	ResultSet rs = null;
	
	private List<Integer> groupIdList;
	
	private List<RedemptionCodeGroup> redemptionCodeGroupList;
	private RedemptionCodeGroup currentRedemptionCodeGroup;
	private BeanItemContainer<RedemptionCodeGroup> redemptionCodeGroupContainer;
	
	private Boolean currentMode;
	private List<RedemptionCode> selectedRedemptionCodes;
	
	private WorkstationObservablesUI observableWindow;
	
	private DesignProcessorFactoryForRedemption dpf = new DesignProcessorFactoryForRedemption();
	
	public Redemption(CurrentUser currentUser) throws InstantiationException, IllegalAccessException, ClassNotFoundException, SQLException {
		this.currentUser = currentUser;
		customerList = currentUser.getUserCustomers();
		
		if(!currentUser.getUserName().equals("admin"))
		{
			try {
				conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			} catch (SQLException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			
			redemptionCustomerIdList = new ArrayList<Integer>();
			stmt = conn.createStatement();
			for(Customer customer : customerList)
			{
				rs = stmt.executeQuery("SELECT * FROM customers WHERE genesys_id = " + customer.getId());
				if(rs.next())
				{
					redemptionCustomerIdList.add(rs.getInt("id"));
				}
			}
			rs.close();
			stmt.close();
			conn.close();
		}
		
		initObjects();
		initLayout();
	}
	
	public void initObjects() {
	}

	private void initLayout() throws InstantiationException, IllegalAccessException, ClassNotFoundException, SQLException {
		String basepath = VaadinService.getCurrent().getBaseDirectory().getAbsolutePath();
		final FileResource resource;
		
		menu.addItem("Report", new MenuBar.Command() {
			
			@Override
			public void menuSelected(MenuItem selectedItem) {
				printReport();
			}
		});
		
		menu.addItem("Print Packing Slip", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				printPackingSlip();
			}
		});
		
		menu.addItem("Redemption Codes", new MenuBar.Command() {

			@Override
			public void menuSelected(MenuItem selectedItem) {
				selectedItem.setText("");
				selectedItem.setIcon(new ThemeResource("redemptionCodes_underline.PNG"));
				menu.getItems().get(3).setText("Redemption Code Groups");
				menu.getItems().get(3).setIcon(null);
				showRedemptionCodes(0);
			}
		});
		menu.addItem("Code Groups", new MenuBar.Command() {
			
			@Override
			public void menuSelected(MenuItem selectedItem) {
				selectedItem.setText("");
				selectedItem.setIcon(new ThemeResource("redemptionCodeGroups_underline.PNG"));
				menu.getItems().get(2).setText("Redemption Codes");
				menu.getItems().get(2).setIcon(null);
				showRedemptionCodeGroups(0);
			}
		});
		menu.getItems().get(2).setText("");
		menu.getItems().get(2).setIcon(new ThemeResource("redemptionCodes_underline.PNG"));
		vLayout.addComponent(menu);
		showRedemptionCodes(0);
		vLayout.setSizeFull();
		setCompositionRoot(vLayout);
		setSizeFull();
	}
	
	private void showRedemptionCodes(int selected_group_id) {
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		currentMode = false;
		if(grid != null)
		{
			vLayout.removeComponent(grid);
		}
		
		try {
		    
		    stmt = conn.createStatement();
		    if(selected_group_id != -1 && selected_group_id != 0)
		    {
		    	rs = stmt.executeQuery("SELECT * FROM redemption_codes WHERE group_id = " + selected_group_id);
		    } else if(currentUser.getUserName().equals("admin"))
		    {
		    	rs = stmt.executeQuery("SELECT * FROM redemption_codes");
		    } else {
		    	String query = "SELECT * FROM redemption_codes WHERE ";
		    	for(int i = 0; i < redemptionCustomerIdList.size(); i++)
		    	{
		    		if(i != redemptionCustomerIdList.size() - 1)
		    		{
		    			query += "customer_id = " + redemptionCustomerIdList.get(i) + "|| ";
		    		} else {
		    			query += "customer_id = " + redemptionCustomerIdList.get(i);
		    		}
		    	}
		    	rs = stmt.executeQuery(query);
		    }
			
			redemptionCodeList = new ArrayList<RedemptionCode>();
			if(groupIdList == null)
				groupIdList = new ArrayList<Integer>();
			
			iterator = 1;
			while(rs.next()){
				currentRedemptionCode = new RedemptionCode(
						rs.getInt("ID"), 
						rs.getInt("customer_id"),
						rs.getString("code"),
						rs.getInt("group_id"),
						rs.getTimestamp("date_used"),
						rs.getInt("external_order_id"), 
						rs.getString("external_order_details"), 
						rs.getString("shipping_email"), 
						rs.getString("shipping_details"));
				redemptionCodeList.add(currentRedemptionCode);
				if(selected_group_id != -1 && !groupIdList.contains(currentRedemptionCode.getGroup_id()))
				{
					groupIdList.add(currentRedemptionCode.getGroup_id());
				}
	    	}
			redemptionCodeContainer = new BeanItemContainer<RedemptionCode>(RedemptionCode.class, redemptionCodeList);
			rs.close();
			stmt.close();
			conn.close();
			
			grid = new Grid();
			grid.setContainerDataSource(redemptionCodeContainer);
			grid.setSizeFull();
			
			Column id = grid.getColumn("id");
			grid.removeColumn(id.getPropertyId());
			Column customer_id = grid.getColumn("customer_id");
			grid.removeColumn(customer_id.getPropertyId());
			Column external_order_details = grid.getColumn("external_order_details");
			grid.removeColumn(external_order_details.getPropertyId());
			Column shipping_details = grid.getColumn("shipping_details");
			grid.removeColumn(shipping_details.getPropertyId());
			
			grid.setColumnOrder(new String[] {"group_id", "external_order_id",
					"code", "usage", "date_used", "shipping_email"});
			
			Column group_id = grid.getColumn("group_id");
			group_id.setHeaderCaption("Group");
			Column external_order_id = grid.getColumn("external_order_id");
			external_order_id.setHeaderCaption("Order #");
			Column code = grid.getColumn("code");
			code.setHeaderCaption("Code");
			Column date_used = grid.getColumn("date_used");
			date_used.setHeaderCaption("Date Used");
			Column shipping_email = grid.getColumn("shipping_email");
			shipping_email.setHeaderCaption("Email Address");
			FooterRow filterRow = grid.appendFooterRow(); 
			
			for (Object pid: grid.getContainerDataSource().getContainerPropertyIds())
			{ 
				FooterCell cell = filterRow.getCell(pid);
				if(cell != null)
				{
					if(pid.equals("group_id"))
					{
						
						groupIdComboBox = new ComboBox();
						groupIdComboBox.setNullSelectionAllowed(false);
						groupIdComboBox.addItems("ALL");
						groupIdComboBox.addItems(groupIdList);
						if(selected_group_id == -1 || selected_group_id == 0)
							groupIdComboBox.setValue("ALL");
						else
							groupIdComboBox.setValue(selected_group_id);
						groupIdComboBox.addValueChangeListener(change -> { 
							int gid = -1;
							if(!groupIdComboBox.getValue().toString().equals("ALL"))
								gid = (int) groupIdComboBox.getValue();
								
							showRedemptionCodes(gid);
						});
						cell.setComponent(groupIdComboBox);
						
					} else if(pid.equals("usage"))
					{
						usageComboBox = new ComboBox();
						usageComboBox.setNullSelectionAllowed(false);
						usageComboBox.addItem("Used");
						usageComboBox.addItem("Unused");
						usageComboBox.addItem("ALL");
						usageComboBox.addValueChangeListener(change -> { 
							redemptionCodeContainer.removeContainerFilters(pid);
							
							if(!usageComboBox.getValue().equals("ALL"))
							{
								redemptionCodeContainer.addContainerFilter( new  SimpleStringFilter(pid, usageComboBox.getValue().toString(), true, true));
							}
							
						});
						usageComboBox.setValue("Used");
						cell.setComponent(usageComboBox);
					} else if(pid.equals("date_used"))
					{
						date_usedComboBox = new ComboBox();
						HorizontalLayout datefieldLayout = new HorizontalLayout();
						Label startDateLabel = new Label("From:");
						startDateLabel.setWidth("50px");
				        DateField startDateField = new DateField();
				        startDateField.addStyleName(ValoTheme.DATEFIELD_TINY);
				        startDateField.setWidth("100px");
				        DateField endDateField = new DateField();
				        endDateField.addStyleName(ValoTheme.DATEFIELD_TINY);
				        endDateField.setWidth("100px");
				        Label endDateLabel = new Label("-     To:");
				        endDateLabel.setWidth("50px");
				        HorizontalLayout linkLayout = new HorizontalLayout();
				        Link resetButton = new Link("Reset", null);
				        resetButton.setCaptionAsHtml(true);
				        linkLayout.addComponent(resetButton);
				        
				        datefieldLayout.addComponent(startDateLabel);
				        datefieldLayout.addComponent(startDateField);
				        datefieldLayout.addComponent(endDateLabel);
				        datefieldLayout.addComponent(endDateField);
				        datefieldLayout.addComponent(linkLayout);
				        startDateField.addValueChangeListener(change -> { 
							redemptionCodeContainer.removeContainerFilters(pid);
							redemptionCodeContainer.addContainerFilter(new Between(pid,
									startDateField.getValue(), endDateField.getValue()));
							
						});
				        endDateField.addValueChangeListener(change -> { 
							redemptionCodeContainer.removeContainerFilters(pid);
							redemptionCodeContainer.addContainerFilter(new Between(pid,
									startDateField.getValue(), endDateField.getValue()));
							
						});
				        linkLayout.addLayoutClickListener(new LayoutClickListener() {
							
							@Override
							public void layoutClick(LayoutClickEvent event) {
								startDateField.setValue(null);
								endDateField.setValue(null);
								redemptionCodeContainer.removeContainerFilters(pid);
							}
						});
						cell.setComponent(datefieldLayout);
					} else {
						filterField = new TextField();
						filterField.setColumns((int) grid.getColumn(pid).getMinimumWidth());
						filterField.addTextChangeListener(change -> { 
							redemptionCodeContainer.removeContainerFilters(pid);
							
							if (! change.getText().isEmpty()) 
								redemptionCodeContainer.addContainerFilter( new SimpleStringFilter(pid, change.getText(), true, false));

						});
						cell.setComponent(filterField);
					}
				}
			}
			grid.setSelectionModel(new MultiSelectionModel());
			
			vLayout.addComponent(grid);
			vLayout.setExpandRatio(grid, 1.0f);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	private void showRedemptionCodeGroups(int selected_group_id)
	{
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		currentMode = true;
		if(grid != null)
		{
			vLayout.removeComponent(grid);
		}
		
		try {
			stmt = conn.createStatement();
			if(selected_group_id != -1 && selected_group_id != 0)
			{
				rs = stmt.executeQuery("SELECT * FROM redemption_code_groups WHERE id =" + selected_group_id);
			} else if(currentUser.getUserName().equals("admin"))
		    {
		    	rs = stmt.executeQuery("SELECT * FROM redemption_code_groups");
		    } else {
		    	String query = "SELECT * FROM redemption_code_groups WHERE ";
		    	for(int i = 0; i < redemptionCustomerIdList.size(); i++)
		    	{
		    		if(i != redemptionCustomerIdList.size() - 1)
		    		{
		    			query += "customer_id = " + redemptionCustomerIdList.get(i) + "|| ";
		    		} else {
		    			query += "customer_id = " + redemptionCustomerIdList.get(i);
		    		}
		    	}
		    	rs = stmt.executeQuery(query);
		    }
			redemptionCodeGroupList = new ArrayList<RedemptionCodeGroup>();
			
			if(groupIdList == null)
				groupIdList = new ArrayList<Integer>();
			
			iterator = 1;
			while(rs.next()){
				currentRedemptionCodeGroup = new RedemptionCodeGroup(
						rs.getInt("id"), 
						rs.getInt("customer_id"),
						rs.getTimestamp("date_created"),
						rs.getString("description"), 
						rs.getString("config_json"));
				redemptionCodeGroupList.add(currentRedemptionCodeGroup);
				if(selected_group_id != -1 && !groupIdList.contains(currentRedemptionCodeGroup.getId()))
				{
					groupIdList.add(currentRedemptionCodeGroup.getId());
				}
	    	}
			redemptionCodeGroupContainer = new BeanItemContainer<RedemptionCodeGroup>(RedemptionCodeGroup.class, redemptionCodeGroupList);
			rs.close();
			stmt.close();
			conn.close();
			
			grid = new Grid();
			grid.setContainerDataSource(redemptionCodeGroupContainer);
			grid.setSizeFull();
			
			Column customer_id = grid.getColumn("customer_id");
			grid.removeColumn(customer_id.getPropertyId());
			Column config_json = grid.getColumn("config_json");
			grid.removeColumn(config_json.getPropertyId());
			
			grid.setColumnOrder(new String[] {"id", "description",
					"barCode", "date_created"});
			
			Column id = grid.getColumn("id");
			id.setHeaderCaption("Group #");
			Column description = grid.getColumn("description");
			description.setHeaderCaption("Description");
			Column barCode = grid.getColumn("barCode");
			barCode.setHeaderCaption("BarCode");
			Column date_created = grid.getColumn("date_created");
			date_created.setHeaderCaption("Date Issued");
			
			FooterRow filterRow = grid.appendFooterRow(); 
			for (Object pid: grid.getContainerDataSource().getContainerPropertyIds())
			{ 
				FooterCell cell = filterRow.getCell(pid);
				if(cell != null)
				{
					if(pid.equals("id"))
					{
						
						groupIdComboBox = new ComboBox();
						groupIdComboBox.setNullSelectionAllowed(false);
						groupIdComboBox.addItem("ALL");
						groupIdComboBox.addItems(groupIdList);
						if(selected_group_id == -1 || selected_group_id == 0)
							groupIdComboBox.setValue("ALL");
						else
							groupIdComboBox.setValue(selected_group_id);
						groupIdComboBox.addValueChangeListener(change -> { 
							int gid = -1;
							if(!groupIdComboBox.getValue().toString().equals("ALL"))
								gid = (int) groupIdComboBox.getValue();
								
							showRedemptionCodeGroups(gid);
						});
						cell.setComponent(groupIdComboBox);
					} else if(pid.equals("usage"))
					{
						usageComboBox = new ComboBox();
						groupIdComboBox.setNullSelectionAllowed(false);
						usageComboBox.addItem("Used");
						usageComboBox.addItem("Unused");
						usageComboBox.addItem("ALL");
						usageComboBox.setValue("ALL");
						usageComboBox.addValueChangeListener(change -> { 
							redemptionCodeContainer.removeContainerFilters(pid);
							
							if(!usageComboBox.getValue().equals("ALL"))
							{
								redemptionCodeContainer.addContainerFilter( new SimpleStringFilter(pid, usageComboBox.getValue().toString(), true, false));
							}
							
						});
						cell.setComponent(usageComboBox);
					} else {
						filterField = new TextField();
						filterField.setColumns((int) grid.getColumn(pid).getMinimumWidth());
						filterField.addTextChangeListener(change -> { 
							redemptionCodeGroupContainer.removeContainerFilters(pid);
							
							if (! change.getText().isEmpty()) 
								redemptionCodeGroupContainer.addContainerFilter( new SimpleStringFilter(pid, change.getText(), true, false));

						});
						cell.setComponent(filterField);
					}
				}
			
			
			}
			grid.setSelectionMode(SelectionMode.MULTI);
			vLayout.addComponent(grid);
			vLayout.setExpandRatio(grid, 1.0f);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	public void printReport()
	{
		observableWindow = new WorkstationObservablesUI();
		observableWindow.show();
		Button dl = new Button();
		dl.setCaption("Download CSV");
		observableWindow.addObservable(dl);
		
		StringBuilder sb = new StringBuilder();
		int selectedCount = 0;
		if(currentMode == false)
		{
			observableWindow.setCaption("Redemption Code Exporter");
			try {
				
				File f = new File("redemptionCodes.csv");
				FileWriter fileWriter = new FileWriter(f);
				fileWriter.write("Group,Order #,Code,Usage,Date Used,Email Address,\n");
				MultiSelectionModel selections = (MultiSelectionModel) grid.getSelectionModel();
				for(Object selectedItem : selections.getSelectedRows())
				{
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("group_id").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("external_order_id").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("code").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("usage").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("date_used").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("shipping_email").getValue().toString() + ",");
					fileWriter.write("\n");
					selectedCount++;
				}
				fileWriter.write("Total number of codes: " + selectedCount);
				Resource res = new FileResource(f);
				FileDownloader fd = new FileDownloader(res);
				fd.extend(dl);
				
				fileWriter.flush();
				fileWriter.close();
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			} 

		} else 
		{
			observableWindow.setCaption("Redemption Code Group Exporter");
			try {
				
				File f = new File("redemptionCodeGroup.csv");
				FileWriter fileWriter = new FileWriter(f);
				fileWriter.write("Group #,Description,BarCode,Date Issued,\n");
				MultiSelectionModel selections = (MultiSelectionModel) grid.getSelectionModel();
				for(Object selectedItem : selections.getSelectedRows())
				{
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("id").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("description").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("barCode").getValue().toString() + ",");
					fileWriter.write(grid.getContainerDataSource().getItem(selectedItem).getItemProperty("date_created").getValue().toString() + ",");
					fileWriter.write("\n");
					selectedCount++;
				}
				fileWriter.write("Total number of code groups: " + selectedCount);
				Resource res = new FileResource(f);
				FileDownloader fd = new FileDownloader(res);
				fd.extend(dl);
				
				fileWriter.flush();
				fileWriter.close();
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			} 
		}
		
	}
	
	public void printPackingSlip()
	{
		observableWindow = new WorkstationObservablesUI();
		List<EntityItem<Design>> designs = getSelectedDesigns();
		if (designs.size() > 0) {
			DesignProcessor p;
			Constructor<? extends DesignProcessor> ctor;
			try {
				ctor = dpf.getProcessors()[0].getClass().getDeclaredConstructor();
				ctor.setAccessible(true);
				p = ctor.newInstance();
			} catch (Exception e) {
				throw new RuntimeException(e);
			}
			observableWindow.show();
			DesignProcessor.startProcessor(p, designs);
			((IObserverListener)p.getObserverUI()).addOnFinishedListener(new IOnFinishedListener() {
				@Override
				public void finished(final FinishedEvent e) {
					//table.refreshRowCache();
				}
			});
			observableWindow.addObservable(p.getObserverUI());
		} else {
			Notification.show("No Designs For Selected Code", Type.WARNING_MESSAGE);
		}
		/*Design[] designsArray = new Design[designs.size()];
		int i = 0;
		for (EntityItem<Design> d : designs) {
			designsArray[i] = d.getEntity();
			i++;
		}
		try {
			processor.print(new Observer(), designsArray);
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		*/
	}
	
	private List<EntityItem<Design>> getSelectedDesigns(){
		List<Integer> genesys_ids = new ArrayList<Integer>();
		MultiSelectionModel selections = (MultiSelectionModel) grid.getSelectionModel();
		for(Object selectedItem : selections.getSelectedRows())
		{
			genesys_ids.add((int) grid.getContainerDataSource().getItem(selectedItem).getItemProperty("external_order_id").getValue());
		}
		
		List<Integer> ids = new ArrayList<Integer>();
		for(int id : genesys_ids)
		{
			try {
				conn2 = DriverManager.getConnection(DATABASE2, USERNAME,PASSWORD);
				stmt = conn2.createStatement();
				rs = stmt.executeQuery("SELECT id FROM designs WHERE order_item_id = " + id);
				if(rs.next())
				{
					ids.add(rs.getInt("id"));
				}
			} catch (SQLException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}
		
		List<EntityItem<Design>> designs = new ArrayList<EntityItem<Design>>();
		for(int id : ids)
		{
			EntityItem<Design> design = workstation.getItem(id);
			designs.add(design);
		}
		return designs;
	}
	
	@Override
	public void buttonClick(ClickEvent event) {
	}

}
