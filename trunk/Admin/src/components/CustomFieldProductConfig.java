package components;

import java.util.ArrayList;

import model.ProductConfigJson;
import model.ProductConfigJson.Color;
import model.ProductConfigJson.Overlay;

import com.vaadin.data.util.BeanItemContainer;
import com.vaadin.ui.AbstractSelect.NewItemHandler;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomField;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.ListSelect;
import com.vaadin.ui.NativeButton;
import com.vaadin.ui.TextField;
import com.vaadin.ui.VerticalLayout;

public class CustomFieldProductConfig extends CustomField<String> {
	
	final private TextField overlay;
	final private TextField weight;
	final private ListSelect select;
	final private TextField colorName;
	final private TextField colorValue;
	final private NativeButton add;
	final private NativeButton remove;
	final private TextField x1;
	final private TextField y1;
	final private TextField x2;
	final private TextField y2;
	final private BeanItemContainer<Color> container;
	private ProductConfigJson configJson = null;
	
	public CustomFieldProductConfig() {
		this.setCaption("Config");
		weight = new TextField();
		weight.setInputPrompt("Weight");
		overlay = new TextField();
		overlay.setInputPrompt("Overlay Image");
		select = new ListSelect();
		colorName = new TextField();
		colorName.setInputPrompt("Color Name");
		colorValue = new TextField();
		colorValue.setInputPrompt("#FFFFFF");
		add = new NativeButton("+");
		remove = new NativeButton("-");
		x1 = new TextField();
		x1.setValue("-0.0");
		x1.setInputPrompt("X1");
		x2 = new TextField();
		x2.setValue("1.0");
		x2.setInputPrompt("X2");
		y1 = new TextField();
		y1.setValue("-0.0");
		y1.setInputPrompt("Y1");
		y2 = new TextField();
		y2.setValue("1.0");
		y2.setInputPrompt("Y2");
		select.setNullSelectionAllowed(false);
		//select.setNewItemsAllowed(true);
		container = new BeanItemContainer<Color>(Color.class);
		select.setContainerDataSource(container);
		select.setItemCaptionPropertyId("caption");
		select.setImmediate(true);
		add.addClickListener(new ClickListener() {
			@Override
			public void buttonClick(ClickEvent event) {
                boolean newItem = true;
                for (final Object itemId : select.getItemIds()) {
                    if (colorName.getValue().equalsIgnoreCase(select.getItemCaption(itemId))) {
                        newItem = false;
                        break;
                    }
                }
                if (newItem) {
                	ProductConfigJson.Color newColor = new ProductConfigJson.Color(colorName.getValue(), colorValue.getValue());
                	container.addBean(newColor);
                	select.select(newColor);
                	colorValue.setValue("");
                	colorName.setValue("");
                }
            }
		});
		remove.addClickListener(new ClickListener() {
			@Override
			public void buttonClick(ClickEvent event) {
				Object item = select.getValue();
				container.removeItem(item);
			}
			
		});
	}
	
	@Override
	protected Component initContent() {
		configJson = ProductConfigJson.getProductConfig(getValue());
		if (configJson != null) {
			if (configJson.overlay != null) {
				overlay.setValue(configJson.overlay.image);
			}
			if (configJson.colors != null) {
				for (Color c : configJson.colors) {
					container.addBean(c);
				}
			}
			if (configJson.weight != 0) {
				weight.setValue(String.valueOf(configJson.weight));
			}
		}
		
		x1.setStyleName("small-text-box");
		y1.setStyleName("small-text-box");
		x2.setStyleName("small-text-box");
		y2.setStyleName("small-text-box");
		colorValue.setWidth("202px");
		colorName.setWidth("202px");
		add.setStyleName("remove-button");
		remove.setStyleName("remove-button");
		
		VerticalLayout layout = new VerticalLayout();
		HorizontalLayout overlayLayout = new HorizontalLayout();
		layout.addComponent(weight);
		layout.addComponent(overlay);
		overlayLayout.addComponent(x1);
		overlayLayout.addComponent(y1);
		overlayLayout.addComponent(x2);
		overlayLayout.addComponent(y2);
		layout.addComponent(overlayLayout);
		layout.addComponent(select);
		HorizontalLayout h2 = new HorizontalLayout();
		h2.addComponent(colorName);
		h2.addComponent(add);
		layout.addComponent(h2);
		HorizontalLayout hl = new HorizontalLayout();
		hl.addComponent(colorValue);
		hl.addComponent(remove);
		layout.addComponent(hl);
		return layout;
	}
	
	@Override
	public String getValue() {
		if (configJson != null) {
			return configJson.getJson();
		} else {
			return getInternalValue();
		}
	}
	
	@Override
	public void commit() {
		if (configJson == null) {
			configJson = new ProductConfigJson();
		}
		configJson.colors = new ArrayList<Color>(container.getItemIds());
		try {
			configJson.weight = Integer.parseInt(weight.getValue());
		} catch (NumberFormatException e) {
			configJson.weight = 0;
			weight.setValue("0");
		}
		if (!overlay.getValue().isEmpty()) {
			if (configJson.overlay == null) {
					configJson.overlay = new Overlay(overlay.getValue(), Float.parseFloat(x1.getValue()), Float.parseFloat(y1.getValue()), Float.parseFloat(x2.getValue()), Float.parseFloat(y2.getValue()));
			} else {
				configJson.overlay.image = overlay.getValue();
				configJson.overlay.x1 = Float.parseFloat(x1.getValue());
				configJson.overlay.y1 = Float.parseFloat(y1.getValue());
				configJson.overlay.x2 = Float.parseFloat(x2.getValue());
				configJson.overlay.y2 = Float.parseFloat(y2.getValue());
			}
		}
		setInternalValue(configJson.getJson());
		super.commit();
	}

	@Override
	public Class<? extends String> getType() {
		return String.class;
	}

}
