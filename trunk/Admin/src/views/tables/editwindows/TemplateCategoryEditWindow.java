package views.tables.editwindows;

import java.util.List;

import org.vaadin.jouni.animator.Animator;
import org.vaadin.jouni.animator.client.Ease;
import org.vaadin.jouni.dom.client.Css;

import model.Customer;
import model.DesignTemplateCategory;
import model.User;
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
import com.vaadin.ui.Notification;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.themes.ValoTheme;

import components.CustomFieldCustomer;

public class TemplateCategoryEditWindow extends Window {

	private EntityItem<DesignTemplateCategory> item;
	private String message = null;
	private CurrentUser currentUser;
	
	public TemplateCategoryEditWindow(EntityItem<DesignTemplateCategory> item, CurrentUser currentUser) {
		this.item = item;
		this.currentUser = currentUser;
		init();
	}
	
	public TemplateCategoryEditWindow(EntityItem<DesignTemplateCategory> item, CurrentUser currentUser, String message) {
		this.item = item;
		this.message = message;
		this.currentUser = currentUser;
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
		Label title = new Label("Template Category - " + item.getEntity().getId() + " - " + item.getEntity().getName());
		title.setStyleName(ValoTheme.LABEL_H1);
		root.addComponent(title);
		
		final FieldBinder binder = new FieldBinder(item);
		
		binder.setBuffered(true);
		
		layout.addComponent(binder.buildAndBind("name"));
		final CustomFieldCustomer customer = new CustomFieldCustomer();
		layout.addComponent(customer);
		binder.bind(customer, "customer");
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
				TemplateCategoryEditWindow.this.close();
			}
		});
		
		
		root.addComponent(layout);
		return root;
	}

}