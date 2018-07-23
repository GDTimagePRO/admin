package components;

import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomField;

public class CustomFieldButton extends CustomField<String> {

	private final ClickListener click;
	private final String caption;
	
	public CustomFieldButton(String caption, ClickListener click) {
		this.caption = caption;
		this.click = click;
	}
	
	@Override
	protected Component initContent() {
		Button filter = new Button(caption);
		filter.addClickListener(click);
		filter.setWidth("100%");
		filter.setHeight("24px");
		this.setStyleName("filter-button");
		return filter;
	}

	@Override
	public Class<? extends String> getType() {
		return String.class;
	}

}
