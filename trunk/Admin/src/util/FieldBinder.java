package util;

import java.sql.Timestamp;
import java.util.List;

import model.Customer;
import model.DesignTemplateCategory;
import model.ProductsCategory;

import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.data.fieldgroup.DefaultFieldGroupFieldFactory;
import com.vaadin.data.fieldgroup.FieldGroup;
import com.vaadin.data.validator.BeanValidator;
import com.vaadin.ui.DateField;
import com.vaadin.ui.Field;

import components.CustomFieldCustomer;
import components.CustomFieldDesignCategory;
import components.CustomFieldProductCategory;

public class FieldBinder extends FieldGroup {
	
	private List<Customer> customers;
	
	private class EditFieldGroupFactory extends DefaultFieldGroupFieldFactory {
		
		@Override
		public <T extends Field> T createField(Class<?> type,
			Class<T> fieldType) {
			if (type.isAssignableFrom(ProductsCategory.class)) {
				return (T) new CustomFieldProductCategory();
			} else if (type.isAssignableFrom(DesignTemplateCategory.class)) {
				return (T) new CustomFieldDesignCategory(customers);
			} else if (type.isAssignableFrom(Customer.class)) {
				return (T) new CustomFieldCustomer();
			} else if (type.isAssignableFrom(Timestamp.class)) {
				DateField d = new DateField();
				d.setConverter(new util.DateToSqlTimestampConverter());
				return (T) d;
			}
			return super.createField(type, fieldType);
		}
		
	}
	
	public FieldBinder(EntityItem<?> item) {
		super(item);
		this.setFieldFactory(new EditFieldGroupFactory());
	}
	
	public FieldBinder(EntityItem<?> item, List<Customer> customers) {
		super(item);
		this.customers = customers;
		this.setFieldFactory(new EditFieldGroupFactory());
	}
	
	@Override
	protected void configureField(Field<?> field) {
		super.configureField(field);
		BeanValidator validator = new BeanValidator(((EntityItem)this.getItemDataSource()).getEntity().getClass(), getPropertyId(field).toString());
		field.addValidator(validator);
		if (field.getLocale() != null) {
			validator.setLocale(field.getLocale());
		}
	}
}
