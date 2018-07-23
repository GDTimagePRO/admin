package views.tables;

import java.lang.reflect.Constructor;
import java.math.BigDecimal;
import java.math.BigInteger;
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
import model.DesignsStateName;
import model.OrderItemsProcessingStageName;
import model.ProductsCategory;
import model.User;
import model.WorkstationTable;

import org.tepi.filtertable.FilterDecorator;
import org.tepi.filtertable.FilterGenerator;
import org.tepi.filtertable.FilterTable;
import org.tepi.filtertable.numberfilter.NumberFilterPopupConfig;
import org.tepi.filtertable.numberfilter.NumberInterval;

import workstation.processors.DesignProcessor;
import workstation.processors.DesignProcessorFactory;
import workstation.processors.OrderStatusProcessor;
import workstation.processors.SummaryProcessor;

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
import com.vaadin.data.util.filter.And;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.Or;
import com.vaadin.server.ExternalResource;
import com.vaadin.server.Resource;
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
import com.vaadin.ui.Field;
import com.vaadin.ui.Link;
import com.vaadin.ui.MenuBar;
import com.vaadin.ui.MenuBar.MenuItem;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.VerticalLayout;

import components.CustomFieldButton;
import components.CustomFieldCheckBox;
import components.WorkstationObservablesUI;
import concurrency.JobManager.FinishedEvent;
import concurrency.JobManager.IObserverListener;
import concurrency.JobManager.IOnFinishedListener;

public class WrkstTable extends CustomComponent {
	
	private class WorkstationFilterGenerator implements FilterGenerator {

		public WorkstationFilterGenerator() {
			super();
		}
		
		@Override
		public Filter generateFilter(Object propertyId, Object value) {
			if ("product.productCustomer.description".equals(propertyId)) {
				if (value != null && value instanceof Integer) {
					try {
						return new Compare.Equal("product.productCustomer.id", value);
					} catch (Exception e) {
						
					}
				}
			} else if ("orderItem.processingStageName.name".equals(propertyId)) {
				if (value != null && value instanceof Integer) {
					try {
						return new Compare.Equal("orderItem.processingStageName.value", value);
					} catch (Exception e) {
						
					}
				}
			} else if ("designsStateName.name".equals(propertyId)) {
				if (value != null && value instanceof Integer) {
					try {
						return new Compare.Equal("designsStateName.value", value);
					} catch (Exception e) {
						
					}
				}
			} else if ("product.productsCategory.name".equals(propertyId)) {
				if (value != null && value instanceof Integer) {
					try {
						return new Compare.Equal("product.productsCategory.id", value);
					} catch (Exception e) {
						
					}
				}
			} else if ("orderItem.externalOrderId".equals(propertyId)) {
				NumberInterval interval = (NumberInterval) value;

		        String ltValue = interval.getLessThanValue();
		        String gtValue = interval.getGreaterThanValue();
		        String eqValue = interval.getEqualsValue();
		        if (eqValue != null){
		        	gtValue = eqValue + "000";
		        	ltValue = eqValue + "999";
		        	eqValue = null;
		        } else {
		        	if (gtValue != null) gtValue += "000";
			        if (ltValue != null) ltValue += "999";
		        }
		        
		        if (ltValue != null && gtValue != null) {
		            return new And(new Compare.LessOrEqual(propertyId,  Long.valueOf(ltValue)), new Compare.GreaterOrEqual(propertyId, Long.valueOf(gtValue)));
		        } else if (ltValue != null) {
		            return new Compare.LessOrEqual(propertyId, Long.valueOf(ltValue));
		        } else if (gtValue != null) {
		            return new Compare.GreaterOrEqual(propertyId, Long.valueOf(gtValue));
		        }
		        return null;
			} else if (value instanceof NumberInterval) {
				NumberInterval interval = (NumberInterval) value;
	
		        String ltValue = interval.getLessThanValue();
		        String gtValue = interval.getGreaterThanValue();
		        String eqValue = interval.getEqualsValue();
		        Class<?> typeClass = workstation.getType(propertyId);;
		        
		        if (eqValue != null) {
		            return new Compare.Equal(propertyId, parseNumberValue(typeClass, eqValue));
		        } else if (ltValue != null && gtValue != null) {
		            return new And(new Compare.LessOrEqual(propertyId, parseNumberValue(typeClass,
		                    ltValue)), new Compare.GreaterOrEqual(propertyId, parseNumberValue(
		                    typeClass, gtValue)));
		        } else if (ltValue != null) {
		            return new Compare.LessOrEqual(propertyId, parseNumberValue(typeClass, ltValue));
		        } else if (gtValue != null) {
		            return new Compare.GreaterOrEqual(propertyId, parseNumberValue(typeClass,
		                    gtValue));
		        }
		        return null;
			}
			return null;
		}

		@Override
		public AbstractField<?> getCustomFilterComponent(Object propertyId) {
			if ("product.productCustomer.description".equals(propertyId)) {
				ComboBox cmb = new ComboBox();
				List<Customer> customers = currentUser.getUserCustomers();
				if (customers.size() > 0) {
					BeanContainer<Integer, Customer> container = new BeanContainer<Integer, Customer>(Customer.class);
					container.setBeanIdProperty("id");
					container.addAll(customers);
					cmb.setContainerDataSource(container);
				} else {
					cmb.setContainerDataSource(JPAContainerFactory.makeJndi(Customer.class));
				}
				cmb.setItemCaptionPropertyId("description");
				cmb.setItemCaptionMode(ItemCaptionMode.PROPERTY);
				cmb.setNullSelectionAllowed(true);
				cmb.setSizeFull();
				return cmb;
			} else if ("orderItem.processingStageName.name".equals(propertyId)) {
				ComboBox cmb = new ComboBox();
				JPAContainer<OrderItemsProcessingStageName> container = JPAContainerFactory.makeJndi(OrderItemsProcessingStageName.class);
				cmb.setContainerDataSource(container);
				
				cmb.setItemCaptionPropertyId("name");
				cmb.setItemCaptionMode(ItemCaptionMode.PROPERTY);
				cmb.setNullSelectionAllowed(true);
				cmb.setSizeFull();
				return cmb;
			} else if ("designsStateName.name".equals(propertyId)) {
				ComboBox cmb = new ComboBox();
				JPAContainer<DesignsStateName> container = JPAContainerFactory.makeJndi(DesignsStateName.class);
				cmb.setContainerDataSource(container);
				
				cmb.setItemCaptionPropertyId("name");
				cmb.setItemCaptionMode(ItemCaptionMode.PROPERTY);
				cmb.setNullSelectionAllowed(true);
				cmb.setSizeFull();
				return cmb;
			} else if ("product.productsCategory.name".equals(propertyId)) {
				ComboBox cmb = new ComboBox();
				JPAContainer<ProductsCategory> container = JPAContainerFactory.makeJndi(ProductsCategory.class);
				cmb.setContainerDataSource(container);
				
				cmb.setItemCaptionPropertyId("name");
				cmb.setItemCaptionMode(ItemCaptionMode.PROPERTY);
				cmb.setNullSelectionAllowed(true);
				cmb.setSizeFull();
				return cmb;
			} else if ("View Image".equals(propertyId)) {
				CustomFieldButton filter = new CustomFieldButton("Filter", new ClickListener() {

					@Override
					public void buttonClick(ClickEvent event) {
						Button filter = event.getButton();
						if (filter.getData() == null || filter.getData().equals("filter")) {
							filter.setData("clear");
							filter.setCaption("Clear");
						} else {
							table.clearFilters();
							filter.setData("filter");
							filter.setCaption("Filter");
						}
					}
					
				});
				return filter;
			} else if ("CHECKBOX".equals(propertyId)) {
				CustomFieldCheckBox filter = new CustomFieldCheckBox(null, new ValueChangeListener() {

					@Override
					public void valueChange(ValueChangeEvent event) {
						if ((boolean) event.getProperty().getValue()) {
							Filter f = new And(workstation.getFilters().toArray(new Filter[0]));
							List<Object> items = workstation.getEntityProvider().getAllEntityIdentifiers(workstation, f, null);
							table.setValue(items);
						} else {
							table.setValue(new HashSet<WorkstationTable>());
						}
					}
				
				});
				return filter;
			}
			return null;
		}

		@Override
		public Filter generateFilter(Object propertyId,
				Field<?> originatingField) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public void filterRemoved(Object propertyId) {
			// TODO Auto-generated method stub
			
		}

		@Override
		public void filterAdded(Object propertyId,
				Class<? extends Filter> filterType, Object value) {
			// TODO Auto-generated method stub
			
		}

		@Override
		public Filter filterGeneratorFailed(Exception reason,
				Object propertyId, Object value) {
			// TODO Auto-generated method stub
			return null;
		}
	}
	
	public class TableFilterDecorator implements FilterDecorator {

	    @Override
	    public String getEnumFilterDisplayName(Object propertyId, Object value) {
	        // returning null will output default value
	        return null;
	    }

		@Override
		public Resource getEnumFilterIcon(Object propertyId, Object value) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getBooleanFilterDisplayName(Object propertyId,
				boolean value) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public Resource getBooleanFilterIcon(Object propertyId, boolean value) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public boolean isTextFilterImmediate(Object propertyId) {
			// TODO Auto-generated method stub
			return false;
		}

		@Override
		public int getTextChangeTimeout(Object propertyId) {
			// TODO Auto-generated method stub
			return 0;
		}

		@Override
		public String getFromCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getToCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getSetCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getClearCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public Resolution getDateFieldResolution(Object propertyId) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getDateFormatPattern(Object propertyId) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public Locale getLocale() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getAllItemsVisibleString() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public NumberFilterPopupConfig getNumberFilterPopupConfig() {
			// TODO Auto-generated method stub
			NumberFilterPopupConfig config = new NumberFilterPopupConfig();
			config.setGtPrompt("Greater than or equal to");
			config.setLtPrompt("Less than or equal to");
			return config;
		}

		@Override
		public boolean usePopupForNumericProperty(Object propertyId) {
			// TODO Auto-generated method stub
			return true;
		}
	}
	
	public static String PERMISSION_ACCESS = "workstation_access";
	
	private JPAContainer<Design> workstation = JPAContainerFactory.makeJndi(Design.class);
	private FilterTable table = new FilterTable(){
	    @Override
	    protected String formatPropertyValue(Object rowId,
	            Object colId, Property property) {
	    	// Format by property type
	        if (property.getType() == Long.class && colId == "orderItem.externalOrderId") {
	        	String system = workstation.getItem(rowId).getEntity().getOrderItem().getExternalSystemName();
	        	String num = String.format("%d", property.getValue());
	        	if (system.toLowerCase().equals("redemption")) {
	        		num += "-000";
	        	} else {
		            if (num.length() >= 4) {
		            	num = num.substring(0, num.length() - 3) + "-" + num.substring(num.length() - 3, num.length());
		            }
	        	}
	            return num;
	        }

	        return super.formatPropertyValue(rowId, colId, property);
	    }
	};

	
	private VerticalLayout vLayout = new VerticalLayout();
	private MenuBar menu = new MenuBar();
	private DesignProcessorFactory dpf = new DesignProcessorFactory();
	private WorkstationObservablesUI observableWindow = new WorkstationObservablesUI();
	private CurrentUser currentUser;
	private Map<Object, CheckBox> itemIdToCheckbox = new HashMap<Object, CheckBox>();
	private boolean ignorePropertyChangeEventInCheckBoxListener = false;
	private Object lastSelectionValue;
	
	public WrkstTable(CurrentUser currentUser) {
		this.currentUser = currentUser;
		initObjects();
		initLayout();
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
	
	private void initObjects() {
		table.setContainerDataSource(workstation);
		table.setFilterBarVisible(true);
		table.setSelectable(true);
		table.setMultiSelect(true);
		table.setMultiSelectMode(MultiSelectMode.SIMPLE);
		table.setSizeFull();
		table.setImmediate(true);
		lastSelectionValue = table.getValue();
		
		workstation.addNestedContainerProperty("orderItem.*");
		workstation.addNestedContainerProperty("orderItem.processingStageName.*");
		workstation.addNestedContainerProperty("orderItem.customer.*");
		workstation.addNestedContainerProperty("designsStateName.*");
		workstation.addNestedContainerProperty("product.productsCategory.*");
		workstation.addNestedContainerProperty("product.productCustomer.*");
		
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
			Filter f= new Or(filters);
			workstation.addContainerFilter(f);
		} else {
			table.setVisibleColumns(new String[]{"CHECKBOX","product.productCustomer.description","orderItem.externalOrderId","orderItem.id","id","orderItem.processingStageName.name","designsStateName.name","orderItem.dateCreated","dateRendered","orderItem.externalSystemName","product.productsCategory.name"}); 
		}

		table.setColumnHeader("product.productCustomer.description","Customer");
		table.setColumnHeader("orderItem.externalOrderId","Website Order ID");
		table.setColumnHeader("orderItem.id","Genesys ID");
		table.setColumnHeader("id","Design ID");
		table.setColumnHeader("orderItem.processingStageName.name","Order State");
		table.setColumnHeader("designsStateName.name","Design State");
		table.setColumnHeader("orderItem.dateCreated","Last Updated");
		table.setColumnHeader("dateRendered","Date Rendered");
		table.setColumnHeader("orderItem.externalSystemName","External System Name");
		table.setColumnHeader("product.productsCategory.name","Type");
		table.setColumnHeader("CHECKBOX", "");
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
				final Link link = new Link("Get Image", new ExternalResource(genesys_url + "/GetImage?id=designs/" + itemId + "_hd.png"));
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
				final Link link = new Link("R", new ExternalResource(genesys_api + "render_image.php?designId=" + itemId + "&type=svg&dpi=300&name=" + itemId + ".svg"));
				final Link linkm = new Link("M", new ExternalResource(genesys_url + "/GetImage?id=embosser_m.designs/" + itemId + "_hd-dpi300.svg&saveas=" + itemId + "_m.svg"));
				final Link linkf = new Link("F", new ExternalResource(genesys_url + "/GetImage?id=embosser_f.designs/" + itemId + "_hd-dpi300.svg&saveas=" + itemId + "_f.svg"));
				link.setTargetName("_blank");
				linkm.setTargetName("_blank");
				linkf.setTargetName("_blank");
				layout.addComponent(link);
				layout.addComponent(linkf);
				layout.addComponent(linkm);
				return layout;
			}
		});

		table.setFilterGenerator(new WorkstationFilterGenerator());
		table.setFilterDecorator(new TableFilterDecorator());
		table.setFilterFieldVisible("SVG", false);
		table.setFilterFieldValue("orderItem.processingStageName.name", 400);
		
		final MenuBar.MenuItem printMenuItem = menu.addItem("Print", null, null);
		final MenuBar.MenuItem batchMenuItem = menu.addItem("Batch Design Input", null, null);
		final MenuBar.MenuItem summaryMenuItem = menu.addItem("Create Summary Report", new MenuBar.Command() {
			@Override
			public void menuSelected(MenuItem selectedItem) {
				List<EntityItem<Design>> designs = getSelectedDesigns();
				if (designs.size() > 0) {
					DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.Summary);
					observableWindow.show();
					DesignProcessor.startProcessor(p, designs);
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
				List<EntityItem<Design>> designs = getSelectedDesigns();
				if (designs.size() > 0) {
					DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.ChangeStatus);
					observableWindow.show();
					DesignProcessor.startProcessor(p, designs);
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
			if (selectedProcessor.getClass() != SummaryProcessor.class && selectedProcessor.getClass() != OrderStatusProcessor.class) {
				printMenuItem.addItem(selectedProcessor.getName(), new MenuBar.Command() {
					@Override
					public void menuSelected(MenuItem selectedItem) {
						List<EntityItem<Design>> designs = getSelectedDesigns();
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
							DesignProcessor.startProcessor(p, designs);
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
				DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.BatchInput);
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
				DesignProcessor p = dpf.getProcessor(DesignProcessorFactory.DesignProcessorType.JaneBatchInput);
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
	
	private List<EntityItem<Design>> getSelectedDesigns(){
		Set<Integer> ids = (Set<Integer>) table.getValue();
		List<EntityItem<Design>> designs = new ArrayList<EntityItem<Design>>();
		if (ids.size() > 0) {
			for (int id : ids) {
				designs.add(workstation.getItem(id));
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
