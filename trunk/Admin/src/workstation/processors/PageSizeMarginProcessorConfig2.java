package workstation.processors;

import java.util.List;

import com.google.gson.Gson;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.data.Property.ValueChangeEvent;
import com.vaadin.data.Property.ValueChangeListener;
import com.vaadin.data.util.ObjectProperty;
import com.vaadin.ui.TextField;

import model.Design;
import model.Design2;
import workstation.processors.PageSizeMarginProcessorConfig.PageSizeMarginConfig;
import workstation.processors.PageSizeProcessorConfig.Measurements;
import workstation.processors.PageSizeProcessorConfig.PageSizeConfig;

public class PageSizeMarginProcessorConfig2 extends PageSizeProcessorConfig2 {

	public static class PageSizeMarginConfig extends PageSizeConfig {
		private float marginTop;
		private float marginLeft;
		
		public PageSizeMarginConfig() {
			
		}
		
		public PageSizeMarginConfig(String measurement, float pageWidth,
				float pageheight, float vSpacing, float hSpacing, float mWidth,
				float mHeight, float marginTop, float marginLeft) {
			super(measurement, pageWidth, pageheight, vSpacing, hSpacing, mWidth, mHeight);
			this.marginLeft = marginLeft;
			this.marginTop = marginTop;
		}
		
		public float getMarginTop() {
			return marginTop;
		}

		public float getMarginLeft() {
			return marginLeft;
		}
		
		public void setMarginTop(float marginTop) {
			this.marginTop = marginTop;
		}

		public void setMarginLeft(float marginLeft) {
			this.marginLeft = marginLeft;
		}
	}
	
	public static final String MARGIN_LEFT = "marginLeft";
	public static final String MARGIN_TOP = "marginTop";
	
	
	private static Boolean dLaser2 = false;
	protected final ObjectProperty<Float> _marginTop;
	protected final ObjectProperty<Float> _marginLeft;

	public PageSizeMarginProcessorConfig2(List<Design2> designs,
			DesignProcessor processor) {
		super(designs, processor);
		_marginTop = new ObjectProperty<Float>(0f);
		_marginLeft = new ObjectProperty<Float>(0f);
	}

	public PageSizeMarginProcessorConfig2(List<Design2> designs,
			DesignProcessor processor, float width, float height,
			float leftMargin, float topMargin) {
		super(designs, processor, width, height);
		dLaser2 = false;
		_marginTop = new ObjectProperty<Float>(topMargin);
		_marginLeft = new ObjectProperty<Float>(leftMargin);
	}
	
	public PageSizeMarginProcessorConfig2(List<Design2> designs,
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
	protected String getJson() {
		Gson gson = new Gson();
		PageSizeMarginConfig config = new PageSizeMarginConfig();
		super.fillConfig(config);
		config.setMarginLeft(_marginLeft.getValue());
		config.setMarginTop(_marginTop.getValue());
		return gson.toJson(config);
	}
}
