package com.instamp.workstation.processors.design;

import org.json.JSONException;
import org.json.JSONObject;

import com.instamp.workstation.concurrency.JobManager.IObserverListener;
import com.instamp.workstation.concurrency.JobManager.Observer;
import com.instamp.workstation.data.GenesysDB;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.ui.components.DesignListView.KeyValuePair;
import com.vaadin.data.util.BeanItemContainer;
import com.vaadin.ui.Button;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.Component;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.UI;
import com.vaadin.ui.Window;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;

public class OrderStatusProcessor extends DesignProcessor {
	
	protected static class StatusSelector extends Window {
		public static final String PROCESSING_STAGE = "processing_stage";
		protected final FormLayout _rootLayout = new FormLayout();
		private final DesignProcessor _processor;
		private final DesignDetails[] _designs;
		private boolean _dirty = false;
		
		public StatusSelector(DesignDetails[] designs, DesignProcessor processor) {
			_processor = processor;
			_designs = designs;
		}
		
		protected void createLayout() {
			this.setCaption("Order State Selection");
			setContent(_rootLayout);
			setModal(true);
			setWidth("400px");
			setWidth("300px");
			_rootLayout.setSizeUndefined();
			
			BeanItemContainer<KeyValuePair> orderItemProcessingStages = new BeanItemContainer<>(KeyValuePair.class);
			//designStates.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_PENDING_CANCELED, "CANCELED"));
			//designStates.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_PENDING_CONFIRMATION, "EDITING"));
			orderItemProcessingStages.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_PENDING_CART_ORDER, "IN CART"));
			orderItemProcessingStages.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_PENDING_RENDERING, "IN QUEUE"));
			orderItemProcessingStages.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_READY, "READY"));
			orderItemProcessingStages.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_PRINTED, "PRINTED"));
			orderItemProcessingStages.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_SHIPPED, "SHIPPED"));
			orderItemProcessingStages.addBean(new KeyValuePair(GenesysDB.PROCESSING_STAGE_ARCHIVED, "ARCHIVED"));
			final ComboBox orderStatus = new ComboBox("Order Status", orderItemProcessingStages);
			_rootLayout.addComponent(orderStatus);
			final Button submit = new Button("Start");
			submit.addClickListener(new ClickListener() {
				public void buttonClick(ClickEvent event) {
					_dirty = true;
					JSONObject jObject = new JSONObject();
					KeyValuePair o = (KeyValuePair)orderStatus.getValue();
					try {
						jObject.put(PROCESSING_STAGE, o.getKey());
					} catch (JSONException e) {
						e.printStackTrace();
					}
					_processor.loadConfig(jObject.toString());
					_processor.start(_designs, (IObserverListener) _processor.getObserverUI());
					close();
				}
			});
			_rootLayout.addComponent(submit);
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
					UI.getCurrent().removeWindow(StatusSelector.this);
				}
			}); 
			
			UI.getCurrent().addWindow(this);
			center();
		}
		
		public void hide() {
			UI.getCurrent().removeWindow(this);
		}
	}
	
	private StatusSelector configUI = null;
	private String _config;
	private int _orderState;

	protected OrderStatusProcessor() {
		super("Change Order Status", "Change order status");
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		if (configUI == null) {
			configUI = new StatusSelector(designs, this);
			configUI.show();
		}
		return configUI;
	}
	
	@Override
	public String saveConfig() { return _config; }
	
	@Override
	public void loadConfig(String config) {
		JSONObject j;
		try {
			j = new JSONObject(config);
			_orderState  = j.getInt(StatusSelector.PROCESSING_STAGE);
		} catch (JSONException e) {
			e.printStackTrace();
		}
		_config = config;
	}

	@Override
	protected void run(Observer observer, DesignDetails[] designs) {
		try {
			observer.logState("Starting");
			try (GenesysDB db = new GenesysDB(GenesysDB.getConnectionPool())) {
				db.updateOrderState(designs, _orderState);
			}
			observer.setProgress(1, "Done");
			observer.submitResult(null);
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
		
	}

}
