package views.tables;

import java.util.List;

import model.Barcode;
import model.Customer;
import model.User;

import org.tepi.filtertable.FilterGenerator;

import views.tables.editwindows.BarcodeEditWindow;
import views.tables.editwindows.BarcodeEditWindow.Action;

import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.BeanContainer;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.Not;
import com.vaadin.data.util.filter.Or;
import com.vaadin.ui.AbstractField;
import com.vaadin.ui.AbstractSelect.ItemCaptionMode;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.Field;
import com.vaadin.ui.VerticalLayout;

import components.CustomFieldButton;
import components.GenericTable;

public class BarcodeTable  extends CustomComponent  implements ClickListener {
		
	private class BarcodeFilterGenerator implements FilterGenerator {

		public BarcodeFilterGenerator() {
			super();
		}
		
		@Override
		public Filter generateFilter(Object propertyId, Object value) {
			if ("customer.description".equals(propertyId)) {
				if (value != null && value instanceof Integer) {
					try {
						return new Compare.Equal("customer.id", value);
					} catch (Exception e) {
						
					}
				}
			}
			return null;
		}

		@Override
		public AbstractField<?> getCustomFilterComponent(Object propertyId) {
			if ("customer.description".equals(propertyId)) {
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
			} else if ("Delete".equals(propertyId)) {
				CustomFieldButton addNew = new CustomFieldButton("Add New", new ClickListener() {

					@Override
					public void buttonClick(ClickEvent event) {
						EntityItem<Barcode> item = barcodes.createEntityItem(new Barcode());
						getUI().addWindow(new BarcodeEditWindow(item, table, Action.INSERT, currentUser));
					}
					
				});
				return addNew;
			} else if ("Edit".equals(propertyId)) {
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
	
	public static String PERMISSION_ACCESS = "admin_access";
	public static String PERMISSION_EDIT = "";
	public static String PERMISSION_DELETE = "";
	
	private JPAContainer<Barcode> barcodes = JPAContainerFactory.makeJndi(Barcode.class);
	private GenericTable table = new GenericTable(barcodes, this);
	private VerticalLayout vLayout = new VerticalLayout();
	private CurrentUser currentUser;
	
	public BarcodeTable(CurrentUser currentUser) {
		this.currentUser = currentUser;
		initObjects();
		initLayout();
	}

	private void initObjects() {
		barcodes.addNestedContainerProperty("customer.*");
		barcodes.addNestedContainerProperty("id.barcode");
		List<Customer> customers = currentUser.getUserCustomers();
		if (customers.size() > 0) {
			if (customers.size() > 1) {
				table.setVisibleColumns(new String[]{"customer.description","id.barcode","master", "configJson"});
			} else {
				table.setVisibleColumns(new String[]{"id.barcode","master", "configJson"}); 
			}
			Filter[] filters = new Filter[customers.size()];
			int i = 0;
			for (Customer c : customers) {
				filters[i++] = new Compare.Equal("customer.id", c.getId());
			}
			Filter f= new Or(filters);
			barcodes.addContainerFilter(f);
		} else {
			table.setVisibleColumns(new String[]{"customer.description","id.barcode","master", "configJson"});
		}
		barcodes.addContainerFilter(new Not(new Compare.Equal("customer.description", "")));
		table.setColumnHeader("customer.description","Customer");
		table.setColumnHeader("id.barcode","Barcode");
		table.setColumnHeader("master","Master");
		table.setColumnHeader("configJson","JSON");
		
		
		table.setFilterGenerator(new BarcodeFilterGenerator());
		table.init();
	}
	
	private void initLayout() {
		vLayout.addComponent(table);
		vLayout.setSizeFull();
		setCompositionRoot(vLayout);
		setSizeFull();
	}
	
	@Override
	public void buttonClick(ClickEvent event) {
		try {
			EntityItem<Barcode> item = barcodes.createEntityItem(barcodes.getItem(event.getButton().getData()).getEntity());
			getUI().addWindow(new BarcodeEditWindow(item, table, Action.UPDATE, currentUser));
			
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
}
