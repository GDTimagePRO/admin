package workstation.processors;

import java.util.List;

import javax.naming.InitialContext;
import javax.persistence.EntityManager;
import javax.transaction.UserTransaction;

import model.Design;
import model.Design2;
import model.DesignsStateName;
import model.OrderItem;
import model.OrderItemsProcessingStageName;

import com.google.gson.Gson;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.addon.jpacontainer.provider.jndijta.JndiAddresses;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.Component;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.UI;
import com.vaadin.ui.Window;

import concurrency.JobManager.IObserverListener;
import concurrency.JobManager.Observer;

public class OrderStatusProcessor extends DesignProcessor {
	
	protected static class Config {
		private int processingStage;
		
		public int getProcessingStage() {
			return processingStage;
		}
		
		public void setProcessingStage(int processingStage) {
			this.processingStage = processingStage;
		}
	}
	
	protected static class StatusSelector extends Window {
		public static final String PROCESSING_STAGE = "processing_stage";
		protected final FormLayout _rootLayout = new FormLayout();
		private final DesignProcessor _processor;
		private final List<EntityItem<Design>> _designs;
		private boolean _dirty = false;
		
		public StatusSelector(List<EntityItem<Design>> designs, DesignProcessor processor) {
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
			
			final ComboBox orderStatus = new ComboBox("Order Status");
			orderStatus.setContainerDataSource(JPAContainerFactory.makeJndi(OrderItemsProcessingStageName.class));
			orderStatus.setItemCaptionPropertyId("name");
			_rootLayout.addComponent(orderStatus);
			final Button submit = new Button("Start");
			submit.addClickListener(new ClickListener() {
				public void buttonClick(ClickEvent event) {
					_dirty = true;
					Integer o = (Integer)orderStatus.getValue();
					Gson gson = new Gson();
					Config c = new Config();
					c.setProcessingStage(o);
					_processor.loadConfig(gson.toJson(c));
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
	private Boolean done = false;

	protected OrderStatusProcessor() {
		super("Change Order Status", "Change order status");
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
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
		Gson gson = new Gson();
		Config c = gson.fromJson(config, Config.class);
		_orderState = c.getProcessingStage();
		_config = config;
	}

	@Override
	protected void run(final Observer observer, final List<EntityItem<Design>> designs) {
		final float total = designs.size();
		float i = 0;
		int j = 0;
		observer.logState("Starting");
		try {
			InitialContext initialContext = new InitialContext();
            EntityManager lookup = designs.get(0).getContainer().getEntityProvider().getEntityManager();
            UserTransaction transaction = (UserTransaction)new InitialContext().lookup("java:comp/UserTransaction");
            transaction.begin();
			for (EntityItem<Design> d : designs) {
				if (_orderState == OrderItem.PROCESSING_STAGE_PENDING_RENDERING) {
					d.getEntity().setDesignsStateName(lookup.find(DesignsStateName.class, Design.DESIGN_STATE_INQUEUE));
				} else if (_orderState == OrderItem.PROCESSING_STAGE_PRINTED || _orderState == OrderItem.PROCESSING_STAGE_READY) {
					d.getEntity().setDesignsStateName(lookup.find(DesignsStateName.class, Design.DESIGN_STATE_RENDERED));
				} else if (_orderState == OrderItem.PROCESSING_STAGE_ARCHIVED) {
					d.getEntity().setDesignsStateName(lookup.find(DesignsStateName.class, Design.DESIGN_STATE_ARCHIVED));
				}
				d.getEntity().getOrderItem().setProcessingStagesId(lookup.find(OrderItemsProcessingStageName.class, _orderState));
				lookup.merge(d.getEntity());
				lookup.merge(d.getEntity().getOrderItem());
				i++;
				observer.setProgress(i / total);
			}
			transaction.commit();
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
			
		//designs.get(0).getContainer().refresh(); //causes a deadlock error, no idea why
        observer.setProgress(1, "Done");
		observer.submitResult(null);
	}

	@Override
	public Component getConfigUI2(List<Design2> designs) {
		if (configUI == null) {
			//configUI = new StatusSelector(designs, this);
			configUI.show();
		}
		return configUI;
	}

	@Override
	protected void run2(Observer observer, List<Design2> designs) {
		
	}
}
