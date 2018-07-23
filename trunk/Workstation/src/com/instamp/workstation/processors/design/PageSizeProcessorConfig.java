package com.instamp.workstation.processors.design;

import org.json.JSONException;
import org.json.JSONObject;

import com.instamp.workstation.concurrency.JobManager.IObserverListener;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.vaadin.data.Container;
import com.vaadin.data.Container.ItemSetChangeEvent;
import com.vaadin.data.Container.ItemSetChangeListener;
import com.vaadin.data.Container.PropertySetChangeEvent;
import com.vaadin.data.Container.PropertySetChangeListener;
import com.vaadin.data.Property;
import com.vaadin.data.Property.ValueChangeEvent;
import com.vaadin.data.Property.ValueChangeListener;
import com.vaadin.data.util.ObjectProperty;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.OptionGroup;
import com.vaadin.ui.TextField;
import com.vaadin.ui.UI;
import com.vaadin.ui.Window;

//TODO: Add top and left margin
//TODO: Allow extension to add extra parts for particular reports
//TODO: Add page sizing to all? reports
public class PageSizeProcessorConfig extends Window {
	
	public enum Measurements {
		MM,
		IN
	}
	
	final String portrait = "portrait", landScape = "landscape";
	public static final String MEASUREMENT = "measurement";
	public static final String PAGE_WIDTH = "pageWidth";
	public static final String PAGE_HEIGHT = "pageHeight";
	public static final String IMAGE_HEIGHT = "imageHeight";
	public static final String IMAGE_WIDTH = "imageWidth";
	public static final String VERTICAL_SPACING = "vSpacing";
	public static final String HORIZONTAL_SPACING = "hSpacing";
	
	protected final FormLayout _rootLayout = new FormLayout();
	private final DesignProcessor _processor;
	protected final ObjectProperty<Float> _pageHeight;
	protected final ObjectProperty<Float> _pageWidth;
	protected final ObjectProperty<Float> _vSpacing = new ObjectProperty<Float>(0f);
	protected final ObjectProperty<Float> _hSpacing = new ObjectProperty<Float>(0f);
	protected final ObjectProperty<Float> _mWidth = new ObjectProperty<Float>(0f);
	protected final ObjectProperty<Float> _mHeight = new ObjectProperty<Float>(0f);
	protected final ObjectProperty<Measurements> _measure = new ObjectProperty<Measurements>(Measurements.MM);
	protected final ObjectProperty<String> _orientation = new ObjectProperty<String>(landScape);
	private final DesignDetails[] _designs;
	private boolean _dirty = false;
	
	final OptionGroup measurement = new OptionGroup("Measurement");
	final OptionGroup orientation = new OptionGroup("Orientation");
	
	
	public float getPageHeight() {
		return _pageHeight.getValue();
	}
	
	public float getPageWidth() {
		return _pageWidth.getValue();
	}
	
	public Measurements getMesurement() {
		return _measure.getValue();
	}
	
	public PageSizeProcessorConfig(DesignDetails[] designs, DesignProcessor processor) {
		_processor = processor;
		_pageHeight = new ObjectProperty<Float>(0f);
		_pageWidth = new ObjectProperty<Float>(0f);
		_designs = designs;
	}
	
	public PageSizeProcessorConfig(DesignDetails[] designs, DesignProcessor processor, float width, float height) {
		_processor = processor;
		_pageHeight = new ObjectProperty<Float>(height);
		_pageWidth = new ObjectProperty<Float>(width);
		_designs = designs;
	}
	
	public PageSizeProcessorConfig(DesignDetails[] designs, DesignProcessor processor, float width, float height, float spacingX, float spacingY) {
		_processor = processor;
		_pageHeight = new ObjectProperty<Float>(height);
		_pageWidth = new ObjectProperty<Float>(width);
		
		_designs = designs;
	}
	
	
	
	
	protected void createLayout() {
		addPageSizeItems();
		addFinishItems();
	}
	
	protected void addFinishItems() {
		measurement.setImmediate(true);
		measurement.addItem(Measurements.MM);
		measurement.setItemCaption(Measurements.MM, "mm");
		measurement.addItem(Measurements.IN);
		measurement.setItemCaption(Measurements.IN, "in");
		measurement.setPropertyDataSource(_measure);
		
		measurement.addValueChangeListener(new ValueChangeListener(){
			@Override
			public void valueChange(ValueChangeEvent event) {
				// If it was mm and is now in inches, convert from mm to inches
				if(measurement.getValue() == Measurements.IN){
					_pageHeight.setValue(_pageHeight.getValue() * 0.0393701f);
	                _pageWidth.setValue(_pageWidth.getValue() * 0.0393701f);
	                _vSpacing.setValue(_vSpacing.getValue() * 0.0393701f);
	                _hSpacing.setValue(_hSpacing.getValue() * 0.0393701f);
	                _mWidth.setValue(_mWidth.getValue() * 0.0393701f);
	                _mHeight.setValue(_mHeight.getValue() * 0.0393701f);
	                
	                
				}
				else{
					_pageHeight.setValue(_pageHeight.getValue() / 0.0393701f);
	                _pageWidth.setValue(_pageWidth.getValue() / 0.0393701f);
	                _vSpacing.setValue(_vSpacing.getValue() / 0.0393701f);
	                _hSpacing.setValue(_hSpacing.getValue() / 0.0393701f);
	                _mWidth.setValue(_mWidth.getValue() / 0.0393701f);
	                _mHeight.setValue(_mHeight.getValue() / 0.0393701f);
				}
			}
		
			
		});
		_rootLayout.addComponent(measurement);
		
		
		orientation.setImmediate(true);
		orientation.addItem(landScape);
		orientation.setItemCaption(landScape, "Landscape");
		orientation.addItem(portrait);
		orientation.setItemCaption(portrait, "Portrait");
		orientation.setPropertyDataSource(_orientation);
		orientation.addValueChangeListener(new ValueChangeListener(){
			@Override
			public void valueChange(ValueChangeEvent event) {
				
					float temp = _pageHeight.getValue();
	                _pageHeight.setValue(_pageWidth.getValue());
	                _pageWidth.setValue(temp);
	                
	                temp = _vSpacing.getValue();
	                _vSpacing.setValue(_hSpacing.getValue());
	                _hSpacing.setValue(temp);
	         	
			}
			});
		
		_rootLayout.addComponent(orientation);
		
		
		final Button submit = new Button("Start");
		submit.addClickListener(new ClickListener() {
			public void buttonClick(ClickEvent event) {
				_dirty = true;
				_processor.loadConfig(getJson().toString());
				_processor.start(_designs, (IObserverListener) _processor.getObserverUI());
				close();
			}
		});
		_rootLayout.addComponent(submit);
	}

	protected void addImageSpacing() {
		final TextField vSpace = new TextField ("Vertical Spacing", _vSpacing);
		vSpace.setRequired(true);
		vSpace.setRequiredError("Must specify a length");
		_rootLayout.addComponent(vSpace);
		
		final TextField hSpace = new TextField ("Horizontal Spacing", _hSpacing);
		hSpace.setRequired(true);
		hSpace.setRequiredError("Must specify a length");
		_rootLayout.addComponent(hSpace);
	}
	
	protected void setImageSize() {
		final TextField maxWidth = new TextField ("Image Width(max)", _mWidth);
		_rootLayout.addComponent(maxWidth);
		
		final TextField maxLength = new TextField ("Image Length(max)", _mHeight);
		_rootLayout.addComponent(maxLength);
	}
	
	protected JSONObject getJson() {
		JSONObject j = new JSONObject();
		try {
			j.put(PageSizeProcessorConfig.MEASUREMENT, _measure.getValue().name().toLowerCase());
			j.put(PageSizeProcessorConfig.PAGE_WIDTH, (double)_pageWidth.getValue());
			j.put(PageSizeProcessorConfig.PAGE_HEIGHT, (double)_pageHeight.getValue());
			j.put(PageSizeProcessorConfig.HORIZONTAL_SPACING, (float)_hSpacing.getValue());
			j.put(PageSizeProcessorConfig.VERTICAL_SPACING, (float)_vSpacing.getValue());
			j.put(PageSizeProcessorConfig.IMAGE_HEIGHT, (float)_mHeight.getValue());
			j.put(PageSizeProcessorConfig.IMAGE_WIDTH, (double)_mWidth.getValue());
		} catch (JSONException e) {
			e.printStackTrace();
		}
		return j;
	}
	
	protected void addPageSizeItems() {
		this.setCaption("Page Size Selection");
		setContent(_rootLayout);
		setModal(true);
		setWidth("400px");
		setWidth("300px");
		_rootLayout.setSizeUndefined();
		final TextField widthBox = new TextField("Width", _pageWidth);
		widthBox.setRequired(true);
		widthBox.setRequiredError("Must specify a width");
		_rootLayout.addComponent(widthBox);
		final TextField heightBox = new TextField("Height", _pageHeight);
		heightBox.setRequired(true);
		heightBox.setRequiredError("Must specify a height");
		_rootLayout.addComponent(heightBox);
		
	}
	
	public void show() {
		createLayout();
		
		addCloseListener( new CloseListener() {

			private static final long serialVersionUID = 8286158088971375063L;

			@Override
			public void windowClose(CloseEvent e)
			{
				if (!_dirty) {
					DefaultObserverUI a = (DefaultObserverUI)_processor.getObserverUI();
					a.removeSelf();
				}
				UI.getCurrent().removeWindow(PageSizeProcessorConfig.this);
			}
		}); 
		
		UI.getCurrent().addWindow(this);
		center();
	}
	
	public void hide() {
		UI.getCurrent().removeWindow(this);
	}
}
