package views.tables;

import java.util.List;

import model.Customer;
import model.Product;
import model.ProductsCategory;

import org.tepi.filtertable.FilterGenerator;

import views.tables.editwindows.ProductEditWindow;

import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.BeanContainer;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.IsNull;
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

public class ProductTable extends CustomComponent implements ClickListener {
	
	private class ProductFilterGenerator implements FilterGenerator {

		public ProductFilterGenerator() {
			super();
		}
		
		@Override
		public Filter generateFilter(Object propertyId, Object value) {
			if ("productsCategory.name".equals(propertyId)) {
				if (value != null && value instanceof Integer) {
					try {
						return new Compare.Equal("productsCategory.id", value);
					} catch (Exception e) {
						
					}
				}
			} else if ("productCustomer.description".equals(propertyId)) {
				if (value != null && value instanceof Integer) {
					try {
						return new Compare.Equal("productCustomer.id", value);
					} catch (Exception e) {
						
					}
				}
			}
			return null;
		}

		@Override
		public AbstractField<?> getCustomFilterComponent(Object propertyId) {
			if ("productsCategory.name".equals(propertyId)) {
				ComboBox cmb = new ComboBox();
				cmb.setContainerDataSource(JPAContainerFactory.makeJndi(ProductsCategory.class));
				cmb.setItemCaptionPropertyId("name");
				cmb.setItemCaptionMode(ItemCaptionMode.PROPERTY);
				cmb.setNullSelectionAllowed(true);
				cmb.setSizeFull();
				return cmb;
			} else if ("productCustomer.description".equals(propertyId)) {
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
						EntityItem<Product> item = products.createEntityItem(new Product());
						getUI().addWindow(new ProductEditWindow(item, currentUser));
					}

				});
				return addNew;
			} else if ("Edit".equals(propertyId)) {
				CustomFieldButton filter = new CustomFieldButton("Filter", new ClickListener() {

					@Override
					public void buttonClick(ClickEvent event) {
						Button filter = event.getButton();
						if (filter.getData() == null || filter.getData().equals("filter")) {
							//table.runFilters();
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
	
	private JPAContainer<Product> products = JPAContainerFactory.makeJndi(Product.class);
	private GenericTable table = new GenericTable(products, this);
	private VerticalLayout vLayout = new VerticalLayout();
	private CurrentUser currentUser;
	
	public ProductTable(CurrentUser currentUser) {
		this.currentUser = currentUser;
		initObjects();
		initLayout();
	}
	
	private void initObjects() {
		products.addNestedContainerProperty("productsCategory.*");
		products.addNestedContainerProperty("productCustomer.*");
		
		
		List<Customer> customers = currentUser.getUserCustomers();
		if (customers.size() > 0) {
			if (customers.size() > 1) {
				table.setVisibleColumns(new String[]{"id","code","productCustomer.description", "width","frameWidth","height","frameHeight","longName","productsCategory.name"});
			} else {
				table.setVisibleColumns(new String[]{"id","code","width","frameWidth","height","frameHeight","longName","productsCategory.name"});
			}
			Filter[] filters = new Filter[customers.size() + 1];
			int i = 0;
			for (Customer c : customers) {
				filters[i++] = new Compare.Equal("productCustomer.id", c.getId());
			}
			filters[i++] = new IsNull("productCustomer");
			Filter f= new Or(filters);
			products.addContainerFilter(f);
		} else {
			table.setVisibleColumns(new String[]{"id","code","productCustomer.description", "width","frameWidth","height","frameHeight","longName","productsCategory.name"});
		}
		
		table.setColumnHeader("id","Id");
		table.setColumnHeader("code","Code");
		table.setColumnHeader("width","Width");
		table.setColumnHeader("frameWidth","Frame Width");
		table.setColumnHeader("height","Height");
		table.setColumnHeader("frameHeight","Frame Height");
		table.setColumnHeader("longName","Name");
		table.setColumnHeader("productsCategory.name","Category");
		table.setColumnHeader("productCustomer.description","Customer");
		table.setFilterGenerator(new ProductFilterGenerator());
		table.init();
	}
	
	private void initLayout() {
		vLayout.addComponent(table);
		vLayout.setExpandRatio(table, 1f);
		vLayout.setSizeFull();
		setCompositionRoot(vLayout);
		setSizeFull();
	}
	

	@Override
	public void buttonClick(ClickEvent event) {
		try {
			EntityItem<Product> item = products.createEntityItem(products.getItem(event.getButton().getData()).getEntity());
			getUI().addWindow(new ProductEditWindow(item, currentUser));
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

}
