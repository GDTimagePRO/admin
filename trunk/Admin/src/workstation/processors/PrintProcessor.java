package workstation.processors;

import java.util.List;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.persistence.EntityManager;
import javax.transaction.HeuristicMixedException;
import javax.transaction.HeuristicRollbackException;
import javax.transaction.NotSupportedException;
import javax.transaction.RollbackException;
import javax.transaction.SystemException;
import javax.transaction.UserTransaction;

import model.Design;
import model.OrderItem;
import model.OrderItemsProcessingStageName;

import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.provider.jndijta.JndiAddresses;
import com.vaadin.ui.UI;

import concurrency.JobManager.Observer;

public abstract class PrintProcessor extends DesignProcessor {

	protected boolean index;
	private Boolean done = false;
	
	protected PrintProcessor(String name, String description, boolean index) {
		super(name, description);
		this.index = index;
	}
	
	protected abstract void print(Observer observer, Design[] designs) throws Exception;
	
	protected void updateOrderItems(final List<EntityItem<Design>> designs) {
		
		try {
			InitialContext initialContext = new InitialContext();
            EntityManager lookup = designs.get(0).getContainer().getEntityProvider().getEntityManager();
            UserTransaction transaction = (UserTransaction)new InitialContext().lookup("java:comp/UserTransaction");
            transaction.begin();
			for (EntityItem<Design> d : designs) {
				d.getEntity().getOrderItem().setProcessingStagesId(lookup.find(OrderItemsProcessingStageName.class, OrderItem.PROCESSING_STAGE_PRINTED));
				lookup.merge(d.getEntity().getOrderItem());
			}
			transaction.commit();
		} catch (NamingException | NotSupportedException | SystemException | SecurityException | IllegalStateException | RollbackException | HeuristicMixedException | HeuristicRollbackException e) {
			e.printStackTrace();
		}

		UI.getCurrent().access(new Runnable() {

			@Override
			public void run() {
				designs.get(0).getContainer().refresh();
				synchronized(done) {
					done = true;
				}
			}
			
		});
		
		while (!done) { 
			try {
				Thread.sleep(50);
			} catch (InterruptedException e) {
			
			} 
		}
	}
	
	@Override
	protected void run(Observer observer, List<EntityItem<Design>> designs) {
		try {
			Design[] designsArray = new Design[designs.size()];
			int i = 0;
			for (EntityItem<Design> d : designs) {
				designsArray[i] = d.getEntity();
				i++;
			}
			print(observer, designsArray);
			if (!index) {
				updateOrderItems(designs);
			}
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
		cleanup();
	}

}
