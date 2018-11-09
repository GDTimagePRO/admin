package views.tables;

import java.lang.reflect.Constructor;
import java.math.BigDecimal;
import java.math.BigInteger;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Set;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.persistence.EntityManager;
import javax.persistence.TypedQuery;

import model.Customer;
import model.Design;
import model.Design2;
import model.DesignsStateName;
import model.OrderItemsProcessingStageName;
import model.ProductsCategory;
import model.User;
import model.WorkstationItem;
import model.WorkstationTable;
import redemption.RedemptionCode;
import redemption.RedemptionCodeGroup;
import views.tables.WrkstTable.TableFilterDecorator;
import views.tables.WrkstTable.WorkstationFilterGenerator;

import org.tepi.filtertable.FilterDecorator;
import org.tepi.filtertable.FilterGenerator;
import org.tepi.filtertable.FilterTable;
import org.tepi.filtertable.numberfilter.NumberFilterPopupConfig;
import org.tepi.filtertable.numberfilter.NumberInterval;

import workstation.processors.BatchInputProcessor;
import workstation.processors.BatchInputProcessor2;
import workstation.processors.DesignProcessor;
import workstation.processors.DesignProcessorFactory;
import workstation.processors.FedexShippingProcessor;
import workstation.processors.JaneBatchInputProcessor;
import workstation.processors.JaneBatchInputProcessor2;
import workstation.processors.OrderStatusProcessor;
import workstation.processors.ShippingLabelProcessor;
import workstation.processors.SummaryProcessor;
import workstation.processors.UPSShippingProcessor;
import workstation.processors.ZulilyXSLProcessor;
import workstation.processors.ZulilyXSLProcessor2;

import com.admin.ui.AdminSerlvetListener;
import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.addon.jpacontainer.provider.jndijta.JndiAddresses;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.Property;
import com.vaadin.data.Property.ValueChangeEvent;
import com.vaadin.data.Property.ValueChangeListener;
import com.vaadin.data.util.BeanContainer;
import com.vaadin.data.util.BeanItemContainer;
import com.vaadin.data.util.filter.And;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.Or;
import com.vaadin.server.ExternalResource;
import com.vaadin.server.FileResource;
import com.vaadin.server.Resource;
import com.vaadin.server.ThemeResource;
import com.vaadin.server.VaadinService;
import com.vaadin.shared.ui.MultiSelectMode;
import com.vaadin.shared.ui.datefield.Resolution;
import com.vaadin.ui.AbstractField;
import com.vaadin.ui.AbstractSelect.ItemCaptionMode;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.CheckBox;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.CustomTable;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Notification;
import com.vaadin.ui.CustomTable.ColumnGenerator;
import com.vaadin.ui.Grid.Column;
import com.vaadin.ui.Field;
import com.vaadin.ui.Grid;
import com.vaadin.ui.Link;
import com.vaadin.ui.MenuBar;
import com.vaadin.ui.MenuBar.MenuItem;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.Table;
import com.vaadin.ui.VerticalLayout;

import components.CustomFieldButton;
import components.CustomFieldCheckBox;
import components.WorkstationObservablesUI;
import concurrency.JobManager.FinishedEvent;
import concurrency.JobManager.IObserverListener;
import concurrency.JobManager.IOnFinishedListener;

public class WrkstTable2 extends CustomComponent {
	
	//private Grid grid;
	public static String PERMISSION_ACCESS = "test_access";
	private FilterTable table;
	private List<WorkstationItem> itemList;
	private static final String DATABASE = "jdbc:mysql://localhost:3306/gdt_core?zeroDateTimeBehavior=convertToNull";
	private static final String USERNAME = "root";
	//private static final String PASSWORD = "Loucks74";
	private static final String PASSWORD = "D@n13lD@ng28";
	
	Connection conn = null;
	Connection conn2 = null;
	java.sql.Statement stmt = null;
	ResultSet rs = null;
	private VerticalLayout vLayout = new VerticalLayout();
	
	private WorkstationItem currentItem;
	private BeanItemContainer<WorkstationItem> workstationItemContainer;
	private CurrentUser currentUser;
	private Object lastSelectionValue;
	private Map<Object, CheckBox> itemIdToCheckbox = new HashMap<Object, CheckBox>();
	private boolean ignorePropertyChangeEventInCheckBoxListener = false;
	private MenuBar menu = new MenuBar();
	private WorkstationObservablesUI observableWindow = new WorkstationObservablesUI();
	private DesignProcessorFactory dpf = new DesignProcessorFactory();
	
	public WrkstTable2(CurrentUser currentUser) throws InstantiationException, IllegalAccessException, ClassNotFoundException, SQLException {
		this.currentUser = currentUser;
		if (currentUser.hasPermission(PERMISSION_ACCESS)) {
			initObjects();
			initLayout();
		}
	}
	
	private void setCheckBoxes(Object itemIdOrIds, boolean value) {
		if (itemIdOrIds instanceof Collection) {
			Collection ids = (Collection) itemIdOrIds;
			for (Object id : ids) {
				setCheckBox(id, value);
			}
		} else {
			setCheckBox(itemIdOrIds, value);
		}
	}
	
	private void setCheckBox(Object id, boolean value) {
		CheckBox checkBox = itemIdToCheckbox.get(id);
		if (checkBox != null) {
			checkBox.setValue(value);
		}
	}
	
	public void initObjects() {
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			stmt = conn.createStatement();
			rs = stmt.executeQuery("SELECT description, external_order_id, order_item_number, order_item_id, design_id, stage_name, state_name, date_changed,\r\n" + 
					"date_rendered, system_name, product_category_name\r\n" + 
					"FROM order_items\r\n" + 
					"JOIN orders ON order_items.external_order = orders.external_order_id\r\n" + 
					"JOIN processing_stages ON order_items.processing_stage = processing_stages.stage\r\n" + 
					"LEFT OUTER JOIN barcodes ON order_items.barcode = barcodes.barcode\r\n" + 
					"LEFT OUTER JOIN designs ON order_items.order_item_id = designs.order_item\r\n" + 
					"JOIN external_systems ON orders.external_system = external_systems.system_id\r\n" + 
					"LEFT OUTER JOIN design_states ON designs.design_state = design_states.state\r\n" + 
					"LEFT OUTER JOIN products ON designs.product = products.product_id\r\n" + 
					"LEFT OUTER JOIN product_categories ON products.category = product_categories.product_category_id\r\n" + 
					"JOIN customers ON barcodes.customer = customers.customer_id");
			itemList = new ArrayList<WorkstationItem>();
			while(rs.next()){
				currentItem = new WorkstationItem(
						rs.getString(1), 
						rs.getInt(2),
						rs.getInt(3),
						rs.getInt(4),
						rs.getInt(5),
						rs.getString(6),
						rs.getString(7), 
						rs.getDate(8), 
						rs.getDate(9), 
						rs.getString(10),
						rs.getString(11));
				itemList.add(currentItem);
	    	}
			workstationItemContainer = new BeanItemContainer<WorkstationItem>(WorkstationItem.class, itemList);
			rs.close();
			stmt.close();
			conn.close();
		} catch (SQLException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		}
		
		table = new FilterTable();
		table.setContainerDataSource(workstationItemContainer);
		//table.setFilterBarVisible(true);
		table.setSelectable(true);
		table.setMultiSelect(true);
		table.setMultiSelectMode(MultiSelectMode.SIMPLE);
		table.setSizeFull();
		table.setImmediate(true);
		lastSelectionValue = table.getValue();
		
		table.addGeneratedColumn("CHECKBOX", new ColumnGenerator() {
			@Override
			public Object generateCell(CustomTable source, final Object itemId,
					Object columnId) {
				Set<WorkstationTable> values = (Set<WorkstationTable>) table.getValue();
				boolean checked = values.contains(itemId);
	            final CheckBox checkbox = new CheckBox(null, checked);
	            checkbox.setImmediate(true);
	            itemIdToCheckbox.put(itemId, checkbox);
	            checkbox.addValueChangeListener(new ValueChangeListener() {

	                @Override
	                public void valueChange(ValueChangeEvent event) {
	                	if (!ignorePropertyChangeEventInCheckBoxListener) {
		                	if ((boolean) event.getProperty().getValue()) {
		                		table.select(itemId);
		                	} else {
			                	table.unselect(itemId);
		                	}
	                	}
	                }
	            });
	            return checkbox;
			}
		});
		
		table.addValueChangeListener(new ValueChangeListener() {
		      @Override
		      public void valueChange(Property.ValueChangeEvent event) {
		    	  ignorePropertyChangeEventInCheckBoxListener = true;
		          Object newSelectionValue = event.getProperty().getValue();
		          setCheckBoxes(lastSelectionValue, false);
		          setCheckBoxes(newSelectionValue, true);

		          lastSelectionValue = newSelectionValue;
		          ignorePropertyChangeEventInCheckBoxListener = false;
		      }
		});

		List<Customer> customers = currentUser.getUserCustomers();
		if (customers.size() > 0) {
			if (customers.size() > 1) {
				table.setVisibleColumns(new String[]{"CHECKBOX","orderItem.customer.description","orderItem.externalOrderId","orderItem.id","id","orderItem.processingStageName.name","designsStateName.name","orderItem.dateCreated","dateRendered","orderItem.externalSystemName","product.productsCategory.name"}); 
			} else {
				table.setVisibleColumns(new String[]{"CHECKBOX","orderItem.externalOrderId","orderItem.id","id","orderItem.processingStageName.name","designsStateName.name","orderItem.dateCreated","dateRendered","orderItem.externalSystemName","product.productsCategory.name"}); 
			}
			Filter[] filters = new Filter[customers.size()];
			int i = 0;
			for (Customer c : customers) {
				filters[i++] = new Compare.Equal("product.productCustomer.id", c.getId());
			}
			//Filter f= new Or(filters);
			//workstation.addContainerFilter(f);
		} else {
			//table.setVisibleColumns(new String[]{"CHECKBOX","product.productCustomer.description","orderItem.externalOrderId","orderItem.id","id","orderItem.processingStageName.name","designsStateName.name","orderItem.dateCreated","dateRendered","orderItem.externalSystemName","product.productsCategory.name"}); 
		}
		
		table.setVisibleColumns( new Object[] {"CHECKBOX", "customer", "website_order_id", "genesys_id", "design_id", "order_state",
				"design_state", "last_updated", "date_rendered", "external_system_name", "type"} );

		table.setColumnHeader("CHECKBOX", "");
		table.setColumnHeader("customer","Customer");
		table.setColumnHeader("website_order_id","Website Order ID");
		table.setColumnHeader("genesys_id","Genesys ID");
		table.setColumnHeader("design_id","Design ID");
		table.setColumnHeader("order_state","Order State");
		table.setColumnHeader("design_state","Design State");
		table.setColumnHeader("last_updated","Last Updated");
		table.setColumnHeader("date_rendered","Date Rendered");
		table.setColumnHeader("external_system_name","External System Name");
		table.setColumnHeader("type","Type");
		
		table.setColumnWidth("CHECKBOX", 40);

		
		table.addGeneratedColumn("View Image", new ColumnGenerator() {
			@Override
			public Object generateCell(CustomTable source, Object itemId,
					Object columnId) {
				String genesys_url = "http://genesys.in-stamp.com:8080/ARTServer";
				try {
					Context context = new InitialContext();
					genesys_url = (String) context.lookup(AdminSerlvetListener.GenesysURL);
				} catch (NamingException e) {
					e.printStackTrace();
				}
				final Link link = new Link("Get Image", new ExternalResource(genesys_url + "/GetImage?id=designs/" + source.getItem(itemId).getItemProperty("design_id").getValue() + "_hd.png"));
				link.setTargetName("_blank");
				return link;
			}
		});
		
		table.addGeneratedColumn("SVG", new ColumnGenerator() {
			@Override
			public Object generateCell(CustomTable source, Object itemId,
					Object columnId) {
				String genesys_url = "http://genesys.in-stamp.com:8080/ARTServer";
				try {
					Context context = new InitialContext();
					genesys_url = (String) context.lookup(AdminSerlvetListener.GenesysURL);
				} catch (NamingException e) {
					e.printStackTrace();
				}
				String genesys_api = "http://genesys.in-stamp.com/";
				try {
					Context context = new InitialContext();
					genesys_api = (String) context.lookup(AdminSerlvetListener.APIURL);
				} catch (NamingException e) {
					e.printStackTrace();
				}
				HorizontalLayout layout = new HorizontalLayout();
				layout.setSpacing(true);
				final Link link = new Link("R", new ExternalResource(genesys_api + "render_image.php?designId=" + source.getItem(itemId).getItemProperty("design_id").getValue() + "&type=svg&dpi=300&name=" + source.getItem(itemId).getItemProperty("design_id").getValue() + ".svg"));
				final Link linkm = new Link("M", new ExternalResource(genesys_url + "/GetImage?id=embosser_m.designs/" + source.getItem(itemId).getItemProperty("design_id").getValue() + "_hd-dpi300.svg&saveas=" + source.getItem(itemId).getItemProperty("design_id").getValue() + "_m.svg"));
				final Link linkf = new Link("F", new ExternalResource(genesys_url + "/GetImage?id=embosser_f.designs/" + source.getItem(itemId).getItemProperty("design_id").getValue() + "_hd-dpi300.svg&saveas=" + source.getItem(itemId).getItemProperty("design_id").getValue() + "_f.svg"));
				link.setTargetName("_blank");
				linkm.setTargetName("_blank");
				linkf.setTargetName("_blank");
				layout.addComponent(link);
				layout.addComponent(linkf);
				layout.addComponent(linkm);
				return layout;
			}
		});

		//table.setFilterGenerator(new WorkstationFilterGenerator());
		//table.setFilterDecorator(new TableFilterDecorator());
		//table.setFilterFieldVisible("SVG", false);
		//table.setFilterFieldValue("orderItem.processingStageName.name", 400);
		
		final MenuBar.MenuItem printMenuItem = menu.addItem("Print", null, null);
		final MenuBar.MenuItem batchMenuItem = menu.addItem("Batch Design Input", null, null);
		final MenuBar.MenuItem summaryMenuItem = menu.addItem("Create Summary Report", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				List<Design2> designs = getSelectedDesigns();
				if (designs.size() > 0) {
					DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.Summary);
					observableWindow.show();
					DesignProcessor.startProcessor2(p, designs);
					table.refreshRowCache();
					observableWindow.addObservable(p.getObserverUI());
				} else {
					Notification.show("No Designs Selected", Type.WARNING_MESSAGE);
				}
			}
		});
		
		final MenuBar.MenuItem statusMenuItem = menu.addItem("Change Order Status", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				List<Design2> designs = getSelectedDesigns();
				if (designs.size() > 0) {
					DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.ChangeStatus);
					observableWindow.show();
					DesignProcessor.startProcessor2(p, designs);
					((IObserverListener)p.getObserverUI()).addOnFinishedListener(new IOnFinishedListener() {
						@Override
						public void finished(final FinishedEvent e) {
							//table.refreshRowCache();
						}
					});
					table.refreshRowCache();
					observableWindow.addObservable(p.getObserverUI());
				} else {
					Notification.show("No Designs Selected", Type.WARNING_MESSAGE);
				}
			}
		});
		
		final MenuBar.MenuItem jobsMenuItem = menu.addItem("Show Jobs", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				observableWindow.show();
			}
		});
		
		DesignProcessor[] _processors =  dpf.getProcessors();
		
		for(int i=0; i<_processors.length; i++)
		{
			final DesignProcessor selectedProcessor = _processors[i];
			if (selectedProcessor.getClass() != SummaryProcessor.class && selectedProcessor.getClass() != OrderStatusProcessor.class
					&& selectedProcessor.getClass() != FedexShippingProcessor.class && selectedProcessor.getClass() != BatchInputProcessor.class
					&& selectedProcessor.getClass() != BatchInputProcessor2.class && selectedProcessor.getClass() != JaneBatchInputProcessor.class
					&& selectedProcessor.getClass() != ZulilyXSLProcessor.class && selectedProcessor.getClass() != ShippingLabelProcessor.class
					&& selectedProcessor.getClass() != UPSShippingProcessor.class && selectedProcessor.getClass() != JaneBatchInputProcessor2.class
					&& selectedProcessor.getClass() != ZulilyXSLProcessor2.class) {
				printMenuItem.addItem(selectedProcessor.getName(), new MenuBar.Command() {
					@Override
					public void menuSelected(MenuItem selectedItem) {
						List<Design2> designs = getSelectedDesigns();
						if (designs.size() > 0) {
							DesignProcessor p;
							Constructor<? extends DesignProcessor> ctor;
							try {
								ctor = selectedProcessor.getClass().getDeclaredConstructor();
								ctor.setAccessible(true);
								p = ctor.newInstance();
							} catch (Exception e) {
								throw new RuntimeException(e);
							}
							observableWindow.show();
							DesignProcessor.startProcessor2(p, designs);
							((IObserverListener)p.getObserverUI()).addOnFinishedListener(new IOnFinishedListener() {
								@Override
								public void finished(final FinishedEvent e) {
									//table.refreshRowCache();
								}
							});
							table.refreshRowCache();
							observableWindow.addObservable(p.getObserverUI());
						} else {
							Notification.show("No Designs Selected", Type.WARNING_MESSAGE);
						}
					}
				});
			}
		}
		batchMenuItem.addItem("GDT CSV", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.BatchInput2);
				observableWindow.show();
				DesignProcessor.startProcessor(p, null);
				((IObserverListener)p.getObserverUI()).addOnFinishedListener(new IOnFinishedListener() {
					@Override
					public void finished(final FinishedEvent e) {
						//table.refreshRowCache();
					}
				});
				table.refreshRowCache();
				observableWindow.addObservable(p.getObserverUI());
			}
		});
		batchMenuItem.addItem("Jane.com CSV", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.JaneBatchInput2);
				observableWindow.show();
				DesignProcessor.startProcessor(p, null);
				((IObserverListener)p.getObserverUI()).addOnFinishedListener(new IOnFinishedListener() {
					@Override
					public void finished(final FinishedEvent e) {
						//table.refreshRowCache();
					}
				});
				table.refreshRowCache();
				observableWindow.addObservable(p.getObserverUI());
			}
		});
		batchMenuItem.addItem("Zulily XLS", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.ZulilyXSL2);
				observableWindow.show();
				DesignProcessor.startProcessor(p, null);
				((IObserverListener)p.getObserverUI()).addOnFinishedListener(new IOnFinishedListener() {
					@Override
					public void finished(final FinishedEvent e) {
						//table.refreshRowCache();
					}
				});
				table.refreshRowCache();
				observableWindow.addObservable(p.getObserverUI());
			}
		});
	}
	
	private List<Design2> getSelectedDesigns(){
		Set<WorkstationItem> ids = (Set<WorkstationItem>) table.getValue();
		List<Design2> designs = new ArrayList<Design2>();
		if (ids.size() > 0) {
			for (WorkstationItem id : ids) {
				try {
					conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
					stmt = conn.createStatement();
					rs = stmt.executeQuery("SELECT * FROM designs;");
					while(rs.next()){
						Design2 design = new Design2(
								rs.getInt(1), 
								rs.getString(2),
								rs.getString(3),
								rs.getDate(4),
								rs.getDate(5),
								rs.getInt(6), 
								rs.getInt(7), 
								rs.getInt(8));
						designs.add(design);
			    	}
					rs.close();
					stmt.close();
					conn.close();
				} catch (SQLException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				}
			}
		}
		return designs;
	}
	
	private void initLayout() {
		vLayout.addComponent(menu);
		vLayout.addComponent(table);
		vLayout.setExpandRatio(table, 1.0f);
		vLayout.setSizeFull();
		setCompositionRoot(vLayout);
		setSizeFull();
	}
	
	private static Object parseNumberValue(Class<?> typeClass, String value) {
    	if (typeClass == BigDecimal.class)
    		return new BigDecimal(value);
    	if (typeClass == BigInteger.class)
    		return new BigInteger(value);
    	if (typeClass == byte.class || typeClass == Byte.class)
    		return Byte.valueOf(value);
    	if (typeClass == short.class || typeClass == Short.class)
    		return Short.valueOf(value);
    	if (typeClass == int.class || typeClass == Integer.class)
    		return Integer.valueOf(value);
    	if (typeClass == long.class || typeClass == Long.class)
    		return Long.valueOf(value);
    	if (typeClass == float.class || typeClass == Float.class)
    		return Float.valueOf(value);
    	if (typeClass == double.class || typeClass == Double.class)
    		return Double.valueOf(value);
    	
    	throw new UnsupportedOperationException("Unsupported number type; " + typeClass.getName());
    }
}
