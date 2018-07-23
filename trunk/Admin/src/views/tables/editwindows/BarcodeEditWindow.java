package views.tables.editwindows;

import java.util.List;

import javax.naming.InitialContext;
import javax.persistence.EntityManager;
import javax.persistence.Query;
import javax.transaction.UserTransaction;

import org.vaadin.jouni.animator.Animator;
import org.vaadin.jouni.animator.client.Ease;
import org.vaadin.jouni.dom.client.Css;

import model.Barcode;
import model.BarcodePK;
import model.Customer;
import model.Product;
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
import com.vaadin.ui.CustomTable;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Label;
import com.vaadin.ui.NativeSelect;
import com.vaadin.ui.Notification;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.themes.ValoTheme;

import components.CustomFieldBarcodeConfig;
import components.CustomFieldCustomer;

public class BarcodeEditWindow   extends Window {

	public enum Action {
		UPDATE,
		INSERT
	}
	
	private EntityItem<Barcode> item;
	private CustomTable table;
	private Action action;
	private String message = null;
	private CurrentUser currentUser;
	
	public BarcodeEditWindow(EntityItem<Barcode> item, CustomTable table, Action action, CurrentUser currentUser) {
		this.item = item;
		this.table = table;
		this.action = action;
		this.currentUser = currentUser;
		init();
	}
	
	public BarcodeEditWindow(EntityItem<Barcode> item, CustomTable table, Action action, CurrentUser currentUser, String message) {
		this.item = item;
		this.table = table;
		this.action = action;
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
		Label title = new Label("Barcode - " + item.getEntity().getBarcode().getBarcode());
		title.setStyleName(ValoTheme.LABEL_H1);
		root.addComponent(title);
		
		final FieldBinder binder = new FieldBinder(item);
		
		binder.setBuffered(true);
		
		//TextArea configJson = new TextArea("Config JSON");
		CustomFieldBarcodeConfig configJson = new CustomFieldBarcodeConfig(currentUser);
		final CustomFieldCustomer customer = new CustomFieldCustomer();
		NativeSelect master = new NativeSelect("Master");
		master.addItem("Y");
		master.addItem("N");
		layout.addComponent(binder.buildAndBind("id.barcode"));
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
		
		layout.addComponent(master);
		binder.bind(master, "master");
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
					EntityItem<Barcode> newItem = item.getContainer().createEntityItem(new Barcode(item.getEntity()));
					getUI().addWindow(new BarcodeEditWindow(newItem, table, Action.INSERT, currentUser));
					BarcodeEditWindow.this.close();
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
					if (action == Action.UPDATE) {
						Barcode b = item.getEntity();
						String barcode = b.getBarcode().getBarcode();
						int customer_id = b.getBarcode().getCustomerId();
						binder.commit();
						item.getEntity().getBarcode().setCustomerId(customer.getValue().getId());
			            UserTransaction transaction = (UserTransaction)new InitialContext().lookup("java:comp/UserTransaction");
			            transaction.begin();
			            EntityManager lookup = item.getContainer().getEntityProvider().getEntityManager();
			            Query query = lookup.createQuery("UPDATE Barcode b set b.id.barcode=:barcode, b.id.customerId=:customer_id, b.configJson=:config_json, b.master=:master WHERE b.id.barcode=:old_barcode AND b.id.customerId=:old_customer_id");
			            query.setParameter("barcode", b.getBarcode().getBarcode());
			            query.setParameter("customer_id", customer.getValue().getId());
			            query.setParameter("config_json", b.getConfigJson());
			            query.setParameter("master", b.getMaster());
			            query.setParameter("old_barcode", barcode);
			            query.setParameter("old_customer_id", customer_id);
			            query.executeUpdate();
			            transaction.commit();
			            table.refreshRowCache();
			            Notification.show("Saved", Type.TRAY_NOTIFICATION);
					} else {
						binder.commit();
						item.getEntity().getBarcode().setCustomerId(customer.getValue().getId());
						BarcodePK id = (BarcodePK) item.getContainer().addEntity(item.getEntity());
						item = item.getContainer().createEntityItem(item.getContainer().getItem(id).getEntity());
						action = Action.UPDATE;
						Notification.show("Saved", Type.TRAY_NOTIFICATION);
					}
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
				BarcodeEditWindow.this.close();
			}
		});
		
		
		root.addComponent(layout);
		return root;
	}

}