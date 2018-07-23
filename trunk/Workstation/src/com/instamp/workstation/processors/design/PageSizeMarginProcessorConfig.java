package com.instamp.workstation.processors.design;

import org.json.JSONException;
import org.json.JSONObject;

import com.instamp.workstation.concurrency.JobManager.IObserverListener;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.processors.design.PageSizeProcessorConfig.Measurements;
import com.vaadin.data.Property.ValueChangeEvent;
import com.vaadin.data.Property.ValueChangeListener;
import com.vaadin.data.util.ObjectProperty;
import com.vaadin.ui.Button;
import com.vaadin.ui.Component;
import com.vaadin.ui.OptionGroup;
import com.vaadin.ui.TextField;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;

public class PageSizeMarginProcessorConfig extends PageSizeProcessorConfig {

	public static final String MARGIN_LEFT = "marginLeft";
	public static final String MARGIN_TOP = "marginTop";
	
	
	private static Boolean dLaser2 = false;
	protected final ObjectProperty<Float> _marginTop;
	protected final ObjectProperty<Float> _marginLeft;

	public PageSizeMarginProcessorConfig(DesignDetails[] designs,
			DesignProcessor processor) {
		super(designs, processor);
		_marginTop = new ObjectProperty<Float>(0f);
		_marginLeft = new ObjectProperty<Float>(0f);
	}

	public PageSizeMarginProcessorConfig(DesignDetails[] designs,
			DesignProcessor processor, float width, float height,
			float leftMargin, float topMargin) {
		super(designs, processor, width, height);
		dLaser2 = false;
		_marginTop = new ObjectProperty<Float>(topMargin);
		_marginLeft = new ObjectProperty<Float>(leftMargin);
	}
	
	public PageSizeMarginProcessorConfig(DesignDetails[] designs,
			DesignProcessor processor, float width, float height,
			float leftMargin, float topMargin, float _vSpacing, float _hSpacing) {
		super(designs, processor, width, height);
		_marginTop = new ObjectProperty<Float>(topMargin);
		_marginLeft = new ObjectProperty<Float>(leftMargin);
		dLaser2 = true;
		this._vSpacing.setValue(_vSpacing);		
		this._hSpacing.setValue(_hSpacing);
	}

	@Override
	public void createLayout() {
		addPageSizeItems();
		addMarginComponents();
		if (dLaser2){
			addImageSpacing();
			setImageSize();
		}
		addFinishItems();

		orientation.addValueChangeListener(new ValueChangeListener() {
			@Override
			public void valueChange(ValueChangeEvent event1) {
				float temp = _marginLeft.getValue();
				_marginLeft.setValue(_marginTop.getValue());
				_marginTop.setValue(temp);
			}
		});
		measurement.addValueChangeListener(new ValueChangeListener() {
			@Override
			public void valueChange(ValueChangeEvent event1) {

				if (measurement.getValue() == Measurements.IN) {
					_marginLeft.setValue(_marginLeft.getValue() * 0.0393701f);
					_marginTop.setValue(_marginTop.getValue() * 0.0393701f);

				} else {
					_marginLeft.setValue(_marginLeft.getValue() / 0.0393701f);
					_marginTop.setValue(_marginTop.getValue() / 0.0393701f);
				}
			}

		});

	}

	protected void addMarginComponents() {
		final TextField leftBox = new TextField("Left Margin", _marginLeft);
		leftBox.setRequired(true);
		leftBox.setRequiredError("Must specify a left margin");
		_rootLayout.addComponent(leftBox);
		final TextField topBox = new TextField("Top Margin", _marginTop);
		topBox.setRequired(true);
		topBox.setRequiredError("Must specify a top margin");
		_rootLayout.addComponent(topBox);
	}

	@Override
	protected JSONObject getJson() {
		JSONObject j = super.getJson();
		try {
			j.put(PageSizeMarginProcessorConfig.MARGIN_LEFT,
					(double) _marginLeft.getValue());
			j.put(PageSizeMarginProcessorConfig.MARGIN_TOP,
					(double) _marginTop.getValue());
		} catch (JSONException e) {
			e.printStackTrace();
		}
		return j;
	}

}
