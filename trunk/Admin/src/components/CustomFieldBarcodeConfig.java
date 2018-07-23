package components;

import java.util.ArrayList;
import java.util.List;

import model.Barcode;
import model.BarcodeConfig;
import model.BarcodeConfig.Items;
import model.Customer;
import model.DesignTemplate;
import model.DesignTemplateCategory;
import model.Product;

import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.BeanItemContainer;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.IsNull;
import com.vaadin.data.util.filter.Or;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomField;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.ListSelect;
import com.vaadin.ui.NativeSelect;
import com.vaadin.ui.TextField;
import com.vaadin.ui.VerticalLayout;

public class CustomFieldBarcodeConfig  extends CustomField<String> {

	private NativeSelect productSelect = new NativeSelect("Product");
	private NativeSelect templateSelect = new NativeSelect("Template");
	private NativeSelect templateCategorySelect = new NativeSelect("Category");
	private NativeSelect uiMode = new NativeSelect("GUI Type");
	private ListSelect items = new ListSelect("Items");
	private Button addItem = new Button("Add");
	private Button removeItem = new Button("Remove");
	private TextField theme = new TextField("Theme");
	final JPAContainer<Product> productContainer = JPAContainerFactory.makeJndi(Product.class);
	final JPAContainer<DesignTemplate> templateContainer = JPAContainerFactory.makeJndi(DesignTemplate.class);
	final JPAContainer<DesignTemplateCategory> templateCategoryContainer = JPAContainerFactory.makeJndi(DesignTemplateCategory.class);
	final private BeanItemContainer<Items> container;
	BarcodeConfig config;
	
	public CustomFieldBarcodeConfig(CurrentUser currentUser) {
		setCaption("Config");
		List<Customer> customers = currentUser.getUserCustomers();
		
		productSelect.setContainerDataSource(productContainer);
		productSelect.setItemCaptionPropertyId("fullname");
		productSelect.setNullSelectionAllowed(false);
		productContainer.addNestedContainerProperty("productCustomer.*");
		if (customers.size() > 0) {
			Filter[] filters = new Filter[customers.size() + 1];
			int i = 0;
			for (Customer c : customers) {
				filters[i++] = new Compare.Equal("productCustomer.id", c.getId());
			}
			filters[i++] = new IsNull("productCustomer");
			Filter f = new Or(filters);
			productContainer.addContainerFilter(f);
		}
		
		templateSelect.setContainerDataSource(templateContainer);
		templateSelect.setItemCaptionPropertyId("fullname");
		templateSelect.setNullSelectionAllowed(true);
		templateContainer.addNestedContainerProperty("designTemplateCategory.customer.*");
		if (customers.size() > 0) {
			Filter[] filters = new Filter[customers.size()];
			int i = 0;
			for (Customer c : customers) {
				filters[i++] = new Compare.Equal("designTemplateCategory.customer.id", c.getId());
			}
			Filter f = new Or(filters);
			templateContainer.addContainerFilter(f);
		}
		
		templateCategorySelect.setContainerDataSource(templateCategoryContainer);
		templateCategorySelect.setItemCaptionPropertyId("name");
		templateCategorySelect.setNullSelectionAllowed(false);
		templateCategoryContainer.addNestedContainerProperty("customer.*");
		if (customers.size() > 0) {
			Filter[] filters = new Filter[customers.size()];
			int i = 0;
			for (Customer c : customers) {
				filters[i++] = new Compare.Equal("customer.id", c.getId());
			}
			Filter f = new Or(filters);
			templateCategoryContainer.addContainerFilter(f);
		}
		
		container = new BeanItemContainer<Items>(Items.class);
		items.setContainerDataSource(container);
		items.setItemCaptionPropertyId("caption");
		items.setNullSelectionAllowed(false);
		items.setImmediate(true);
		items.addValueChangeListener(new ValueChangeListener() {
			@Override
			public void valueChange(com.vaadin.data.Property.ValueChangeEvent event) {
				Items item = (Items)items.getValue();
				if (item.tc_id != null && !item.tc_id.isEmpty() && !item.tc_id.equals("*")) {
					try {
						templateCategorySelect.setValue(Integer.parseInt(item.tc_id));
					} catch (Exception e) {
						
					}
				} else if (item.tc_id.equals("*")) {
					templateCategorySelect.setValue(null);
				}
				templateSelect.setValue(item.templ_id);
				productSelect.setValue(item.prod_id);
			}
		});
		
		theme.setNullRepresentation("");
		
		addItem.addClickListener(new ClickListener() {
			@Override
			public void buttonClick(ClickEvent event) {
				Items item = new Items();
				if (templateCategorySelect.getValue() == null) {
					item.tc_id = "*";
				} else {
					item.tc_id = String.valueOf(templateCategoryContainer.getItem(templateCategorySelect.getValue()).getEntity().getId());
				}
				if (templateContainer.getItem(templateSelect.getValue()) != null) {
					item.templ_id = templateContainer.getItem(templateSelect.getValue()).getEntity().getId();
				} else {
					item.templ_id = null;
				}
				item.prod_id = productContainer.getItem(productSelect.getValue()).getEntity().getId();
				container.addItem(item);
			}
		});
		
		removeItem.addClickListener(new ClickListener() {
			@Override
			public void buttonClick(ClickEvent event) {
				container.removeItem(items.getValue());
			}
		});
		
		uiMode.addItem("simple");
		uiMode.addItem("normal");
		uiMode.setNullSelectionAllowed(false);
	}
	
	@Override
	protected Component initContent() {
		VerticalLayout layout = new VerticalLayout();
		try {
			config = Barcode.getBarcodeConfigFromString(this.getValue());
			container.addAll(config.getItems());
			uiMode.setValue(config.getUIMode());
			theme.setValue(config.getTheme());
		} catch (Exception e) {
			
		}
		layout.addComponent(uiMode);
		layout.addComponent(theme);
		layout.addComponent(items);
		layout.addComponent(productSelect);
		layout.addComponent(templateSelect);
		layout.addComponent(templateCategorySelect);
		HorizontalLayout buttons = new HorizontalLayout();
		buttons.addComponent(addItem);
		buttons.addComponent(removeItem);
		layout.addComponent(buttons);
		return layout;
	}
	
	public void commit() {
		config.setItems(new ArrayList<Items>(container.getItemIds()));
		if (uiMode.getValue() != null) {
			config.setUIMode(uiMode.getValue().toString());
		}
		config.setTheme(theme.getValue());
		this.setInternalValue(config.toString());
		super.commit();
	}
	
	@Override
	public Class<? extends String> getType() {
		return String.class;
	}

}