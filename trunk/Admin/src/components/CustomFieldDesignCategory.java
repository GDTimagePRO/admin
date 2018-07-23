package components;


import java.util.List;

import javax.ejb.Stateless;
import javax.inject.Inject;

import model.Customer;
import model.DesignTemplateCategory;

import com.admin.ui.AdminUI;
import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.IsNull;
import com.vaadin.data.util.filter.Or;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomField;
import com.vaadin.ui.NativeSelect;
import com.vaadin.ui.UI;


public class CustomFieldDesignCategory extends CustomField<DesignTemplateCategory> {
	
	private ComboBox categoryId = new ComboBox();
	private List<Customer> customers;
	
	public CustomFieldDesignCategory( List<Customer> customers ) {
		super();
		this.customers = customers;
	}
	
	@Override
	protected Component initContent() {
		setCaption("Template Category");
		final JPAContainer<DesignTemplateCategory> container = JPAContainerFactory.makeJndi(DesignTemplateCategory.class);
		container.addNestedContainerProperty("customer.*");
		/*if (!(AdminUI.getCurrent()).getCurrentUser().isUserInRole("admin")) {
			container.addContainerFilter(new Compare.Equal("customer.id", ((AdminUI)UI.getCurrent()).getCurrentUser().getUserCustomers()));
		}*/
		
		if ( customers.size() > 0 ) {
        	Filter[] filters = new Filter[customers.size() + 1];
    		int i = 0;
    		for (Customer c : customers) {
    			filters[i++] = new Compare.Equal("customer.id", c.getId());
    		}
    		filters[i++] = new IsNull("customer");
    		Filter f= new Or(filters);
    		container.addContainerFilter(f);
        }
		
		categoryId.setContainerDataSource(container);
		categoryId.setItemCaptionPropertyId("name");
		categoryId.setNullSelectionAllowed(false);
		categoryId.setWidth("245px");
		categoryId.addValueChangeListener(new ValueChangeListener() {

			@Override
			public void valueChange(
					com.vaadin.data.Property.ValueChangeEvent event) {
				if (categoryId.getValue() == null) {
					setValue(null);
				} else {
					setValue(container.getItem(categoryId.getValue()).getEntity());
				}
			}
			
		});
		if (this.getValue() != null) {
			categoryId.setValue(this.getValue().getId());
		}
		return categoryId;
	}

	@Override
	public Class<? extends DesignTemplateCategory> getType() {
		return DesignTemplateCategory.class;
	}

}
