package views.tables.editwindows;

import java.util.List;

import javax.persistence.PersistenceException;

import org.vaadin.jouni.animator.Animator;
import org.vaadin.jouni.animator.client.Ease;
import org.vaadin.jouni.dom.client.Css;

import model.Customer;
import model.Product;
import util.FieldBinder;
import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.Or;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.Component;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Label;
import com.vaadin.ui.NativeSelect;
import com.vaadin.ui.Notification;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.themes.ValoTheme;

import components.CustomFieldCustomer;
import components.CustomFieldProductConfig;

public class ProductEditWindow extends Window {

	private EntityItem<Product> item;
	private String message = null;
	private CurrentUser currentUser;
	
	public ProductEditWindow(EntityItem<Product> item, CurrentUser currentUser) {
		this.currentUser = currentUser;
		this.item = item;
		init();
	}
	
	public ProductEditWindow(EntityItem<Product> item, CurrentUser currentUser, String message) {
		this.item = item;
		this.message = message;
		this.currentUser = currentUser;
		init();
	}
	
	public void init() {
		this.setModal(true);
		this.center();
		this.setSizeUndefined();
		this.setWidth("500px");
		this.setContent(createLayout());
	}
	
	protected Component createLayout() {
		VerticalLayout root = new VerticalLayout();
		FormLayout layout = new FormLayout();
		root.setMargin(true);
		root.setSizeUndefined();
		Label title = new Label("Product - " + item.getEntity().getId() + " - " + item.getEntity().getCode());
		title.setStyleName(ValoTheme.LABEL_H1);
		root.addComponent(title);
		
		final FieldBinder binder = new FieldBinder(item);
		
		binder.setBuffered(true);
		
		CustomFieldProductConfig configJson = new CustomFieldProductConfig();
		NativeSelect colorModel = new NativeSelect("Color Model");
		colorModel.setNullSelectionAllowed(false);
		colorModel.addItem("1_BIT");
		colorModel.addItem("24_BIT");
		
		layout.addComponent(binder.buildAndBind("code"));
		layout.addComponent(binder.buildAndBind("width"));
		layout.addComponent(binder.buildAndBind("height"));
		layout.addComponent(binder.buildAndBind("frameWidth"));
		layout.addComponent(binder.buildAndBind("frameHeight"));
		layout.addComponent(binder.buildAndBind("longName"));
		layout.addComponent(binder.buildAndBind("productsCategory"));
		final CustomFieldCustomer customer = new CustomFieldCustomer();
		layout.addComponent(customer);
		binder.bind(customer, "productCustomer");
		List<Customer> customers = currentUser.getUserCustomers();
		if (customers.size() > 0) {
			Filter[] filters = new Filter[customers.size()];
			int i = 0;
			for (Customer c : customers) {
				filters[i++] = new Compare.Equal("id", c.getId());
			}
			Filter f = new Or(filters);
			customer.setFilters(f);
		}
		
		layout.addComponent(colorModel);
		binder.bind(colorModel, "colorModel");
		layout.addComponent(configJson);
		binder.bind(configJson, "configJson");
		
		HorizontalLayout buttons = new HorizontalLayout();
		Button save = new Button("Save");
		buttons.addComponent(save);
		Button cancel = new Button("Close");
		buttons.addComponent(cancel);
		Button copy = new Button("Copy");
		buttons.addComponent(copy);
		final Label messageLabel = new Label();
		messageLabel.setStyleName("save-label");
		if (message != null) {
			messageLabel.setValue(message);
		}
		buttons.addComponent(messageLabel);
		layout.addComponent(buttons);
		
		copy.addClickListener(new ClickListener() {
			@Override
			public void buttonClick(ClickEvent event) {
				try {
					binder.commit();
					int id = (int) item.getContainer().addEntity(item.getEntity());
					Notification.show("Saved", Type.TRAY_NOTIFICATION);
					item = item.getContainer().createEntityItem(item.getContainer().getItem(id).getEntity());
					EntityItem<Product> newItem = item.getContainer().createEntityItem(new Product(item.getEntity()));
					getUI().addWindow(new ProductEditWindow(newItem, currentUser));
					ProductEditWindow.this.close();
				} catch (Exception e) {
					Notification.show("Failed to save item", Type.ERROR_MESSAGE);
					e.printStackTrace();
				}
			}
		});
		
		save.addClickListener(new ClickListener(){
			@Override
			public void buttonClick(ClickEvent event) {
				try {
					binder.commit();
					int id = (int) item.getContainer().addEntity(item.getEntity());
					item = item.getContainer().createEntityItem(item.getContainer().getItem(id).getEntity());
					Notification.show("Saved", Type.TRAY_NOTIFICATION);
					init();
				} catch (Exception e) {
					if (e.getMessage().contains("Duplicate entry")) {
						Notification.show("Product code is already in use", Type.ERROR_MESSAGE);
					}else {
						Notification.show("Failed to save item", Type.ERROR_MESSAGE);
						e.printStackTrace();
					}
				}
			}
		});
		
		cancel.addClickListener(new ClickListener(){
			@Override
			public void buttonClick(ClickEvent event) {
				binder.discard();
				ProductEditWindow.this.close();
			}
		});
		
		
		root.addComponent(layout);
		root.setExpandRatio(layout, 1.0f);
		return root;
	}

}
