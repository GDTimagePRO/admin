package views.tables.editwindows;

import java.security.MessageDigest;
import java.util.ArrayList;
import java.util.List;
import java.util.Set;

import javax.xml.bind.DatatypeConverter;

import org.vaadin.jouni.animator.Animator;
import org.vaadin.jouni.animator.client.Ease;
import org.vaadin.jouni.dom.client.Css;

import model.Customer;
import model.Permission;
import model.User;
import util.FieldBinder;

import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.CheckBox;
import com.vaadin.ui.Component;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.JavaScript;
import com.vaadin.ui.JavaScriptFunction;
import com.vaadin.ui.Label;
import com.vaadin.ui.Link;
import com.vaadin.ui.Notification;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.OptionGroup;
import com.vaadin.ui.PasswordField;
import com.vaadin.ui.TextField;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.themes.ValoTheme;

import elemental.json.JsonArray;

public class UserEditWindow extends Window {

	private EntityItem<User> item;
	private String message = null;
	
	public UserEditWindow(EntityItem<User> item) {
		this.item = item;
		init();
	}
	
	public UserEditWindow(EntityItem<User> item, String message) {
		this.item = item;
		this.message = message;
		init();
	}
	
	public void init() {
		this.setModal(true);
		this.center();
		this.setSizeUndefined();
		this.
		setContent(createLayout());
	}
	
	protected Component createLayout() {
		VerticalLayout root = new VerticalLayout();
		FormLayout layout = new FormLayout();
		root.setMargin(true);
		root.setSizeUndefined();
		Label title = new Label("User - " + item.getEntity().getUsername());
		title.setStyleName(ValoTheme.LABEL_H1);
		root.addComponent(title);
		
		final FieldBinder binder = new FieldBinder(item);
		
		binder.setBuffered(true);
		
		TextField username = new TextField("Username");
		final CheckBox isAdmin = new CheckBox("Is System Admin");
		final PasswordField password = new PasswordField("Password");
		password.setValue(item.getEntity().getPassword());
		final OptionGroup permissions = new OptionGroup("Permissions");
		final OptionGroup customers = new OptionGroup("Customers");
		permissions.setMultiSelect(true);
		customers.setMultiSelect(true);
		final JPAContainer<Permission> permissionsContainer = JPAContainerFactory.makeJndi(Permission.class);
		final JPAContainer<Customer> customersContainer = JPAContainerFactory.makeJndi(Customer.class);
		permissions.setContainerDataSource(permissionsContainer);
		permissions.setItemCaptionPropertyId("name");
		customers.setContainerDataSource(customersContainer);
		customers.setItemCaptionPropertyId("description");
		layout.addComponent(username);
		layout.addComponent(password);
		layout.addComponent(isAdmin);
		layout.addComponent(customers);
		layout.addComponent(permissions);
		binder.bind(username, "username");
		
		ArrayList<Integer> selectedPermissions = new ArrayList<Integer>();
		for (Permission p : item.getEntity().getAllPermissions()) {
			selectedPermissions.add(p.getId());
		}
		
		permissions.setValue(selectedPermissions);
		
		ArrayList<Integer> selectedCustomers = new ArrayList<Integer>();
		if (item.getEntity().getCustomers() != null) {
			for (Customer c : item.getEntity().getCustomers()) {
				selectedCustomers.add(c.getId());
			}
		}
		
		customers.setValue(selectedCustomers);
		
		if (item.getEntity().getUserGroup() != null && item.getEntity().getUserGroup().getGroupname().equals("admin")) {
			isAdmin.setValue(true);
		}
		
		if (!item.getEntity().getUsername().isEmpty()) {
			username.setEnabled(false);
		}
		
		HorizontalLayout buttons = new HorizontalLayout();
		Button save = new Button("Save");
		buttons.addComponent(save);
		Button cancel = new Button("Close");
		buttons.addComponent(cancel);
		final Label messageLabel = new Label();
		messageLabel.setStyleName("save-label");
		if (message != null) {
			messageLabel.setValue(message);
		}
		buttons.addComponent(messageLabel);
		layout.addComponent(buttons);
		
		save.addClickListener(new ClickListener(){
			@Override
			public void buttonClick(ClickEvent event) {
				try {
					binder.commit();
					List<Permission> ps = new ArrayList<Permission>();
					for (Integer p : ((Set<Integer>)permissions.getValue())) {
						ps.add(permissionsContainer.getItem(p).getEntity());				
					}
					item.getEntity().setPermissions(ps);
					
					List<Customer> cs = new ArrayList<Customer>();
					for (Integer p : ((Set<Integer>)customers.getValue())) {
						cs.add(customersContainer.getItem(p).getEntity());				
					}
					item.getEntity().setCustomers(cs);
					
					String pwd = password.getValue();
					if (!pwd.equalsIgnoreCase(item.getEntity().getPassword())) {
						pwd = DatatypeConverter.printHexBinary(MessageDigest.getInstance("SHA-256").digest(pwd.getBytes("UTF-8")));
						item.getEntity().setPassword(pwd);
					}
					item.getEntity().getUserGroup().setUsername(item.getEntity().getUsername());
					if (isAdmin.getValue()) {
						item.getEntity().getUserGroup().setGroupname("admin");
					} else {
						item.getEntity().getUserGroup().setGroupname("user");
					}
					
					int id = (int) item.getContainer().addEntity(item.getEntity());
					item = item.getContainer().createEntityItem(item.getContainer().getItem(id).getEntity());
					Notification.show("Saved", Type.TRAY_NOTIFICATION);
				} catch (Exception e) {
					Notification.show("Failed to save item", Type.ERROR_MESSAGE);
					e.printStackTrace();
				}
			}
		});
		
		cancel.addClickListener(new ClickListener(){
			@Override
			public void buttonClick(ClickEvent event) {
				binder.discard();
				UserEditWindow.this.close();
			}
		});
		
		
		root.addComponent(layout);
		return root;
	}
}
