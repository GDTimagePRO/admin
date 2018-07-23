package components;

import model.ProductsCategory;

import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomField;
import com.vaadin.ui.NativeSelect;

public class CustomFieldProductCategory extends CustomField<ProductsCategory> {

	private NativeSelect categoryId = new NativeSelect();
	
	@Override
	protected Component initContent() {
		setCaption("Product Category");
		final JPAContainer<ProductsCategory> container = JPAContainerFactory.makeJndi(ProductsCategory.class);
		categoryId.setContainerDataSource(container);
		categoryId.setItemCaptionPropertyId("name");
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
		if (this.getValue() != null) {
			categoryId.setValue(this.getValue().getId());
		}
		return categoryId;
	}

	@Override
	public Class<? extends ProductsCategory> getType() {
		return ProductsCategory.class;
	}

}
