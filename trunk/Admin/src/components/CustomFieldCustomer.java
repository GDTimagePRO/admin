package components;

import model.Customer;

import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomField;
import com.vaadin.ui.NativeSelect;

public class CustomFieldCustomer  extends CustomField<Customer> {

	private NativeSelect categoryId = new NativeSelect("");
	final JPAContainer<Customer> container = JPAContainerFactory.makeJndi(Customer.class);
	
	public CustomFieldCustomer() {
		setCaption("Customer");
		categoryId.setContainerDataSource(container);
		categoryId.setItemCaptionPropertyId("description");
		categoryId.setNullSelectionAllowed(false);
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
		
	}
	
	public void setFilters(Filter f) {
		container.addContainerFilter(f);
	}
	
	@Override
	protected Component initContent() {
		if (this.getValue() != null) {
			categoryId.setValue(this.getValue().getId());
		}
		return categoryId;
	}
	
	public void setCustomerById(int id) {
		categoryId.setValue(id);
		if (categoryId.getValue() != null) {
			setValue(container.getItem(categoryId.getValue()).getEntity());
		}
	}

	@Override
	public Class<? extends Customer> getType() {
		return Customer.class;
	}

}