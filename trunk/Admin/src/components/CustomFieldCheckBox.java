package components;

import com.vaadin.ui.CheckBox;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomField;
import com.vaadin.ui.VerticalLayout;

public class CustomFieldCheckBox extends CustomField<String> {

	private final ValueChangeListener listener;
	private final String caption;
	
	public CustomFieldCheckBox(String caption, ValueChangeListener listener) {
		this.caption = caption;
		this.listener = listener;
	}
	
	@Override
	protected Component initContent() {
		VerticalLayout layout = new VerticalLayout();
		CheckBox filter = new CheckBox(caption);
		filter.addValueChangeListener(listener);
		layout.addComponent(filter);
		layout.setWidth("100%");
		layout.setStyleName("filter-checkbox");
		return layout;
	}

	@Override
	public Class<? extends String> getType() {
		return String.class;
	}

}
