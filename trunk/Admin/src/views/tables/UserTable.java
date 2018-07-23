package views.tables;

import model.User;

import org.tepi.filtertable.FilterGenerator;

import views.tables.editwindows.UserEditWindow;

import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.ui.AbstractField;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.Field;
import com.vaadin.ui.VerticalLayout;

import components.CustomFieldButton;
import components.GenericTable;

public class UserTable extends CustomComponent implements ClickListener {
	
	private class UserFilterGenerator implements FilterGenerator {

		public UserFilterGenerator() {
			super();
		}
		
		@Override
		public Filter generateFilter(Object propertyId, Object value) {
			return null;
		}

		@Override
		public AbstractField<?> getCustomFilterComponent(Object propertyId) {
			if ("Delete".equals(propertyId)) {
				CustomFieldButton addNew = new CustomFieldButton("Add New", new ClickListener() {

					@Override
					public void buttonClick(ClickEvent event) {
						EntityItem<User> item = users.createEntityItem(new User());
						getUI().addWindow(new UserEditWindow(item));
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
	
	public static String PERMISSION_ACCESS = "sysadmin_only";
	public static String PERMISSION_EDIT = "";
	public static String PERMISSION_DELETE = "";
	
	private JPAContainer<User> users = JPAContainerFactory.makeJndi(User.class);
	private GenericTable table = new GenericTable(users, this);
	private VerticalLayout vLayout = new VerticalLayout();
	
	public UserTable() { 
		initObjects();
		initLayout();
	}
	
	private void initObjects() {
		users.addNestedContainerProperty("customers.*");
		users.addNestedContainerProperty("userGroup.*");

		table.setVisibleColumns(new String[]{"id","username","userGroup.groupname"}); 

		table.setColumnHeader("id","Id");
		table.setColumnHeader("username","Username");
		table.setColumnHeader("userGroup.groupname","Group");
		table.setFilterGenerator(new UserFilterGenerator());
		
		
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
			EntityItem<User> item = users.createEntityItem(users.getItem(event.getButton().getData()).getEntity());
			getUI().addWindow(new UserEditWindow(item));
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
}
