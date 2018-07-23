package com.instamp.workstation.processors.design;

import java.sql.SQLException;

import javax.naming.NamingException;

import com.instamp.workstation.concurrency.JobManager.Observer;
import com.instamp.workstation.data.GenesysDB;
import com.instamp.workstation.data.GenesysDB.DesignDetails;

public abstract class PrintProcessor extends DesignProcessor {

	protected boolean index;
	
	protected PrintProcessor(String name, String description, boolean index) {
		super(name, description);
		this.index = index;
	}
	
	protected abstract void print(Observer observer, DesignDetails[] designs) throws Exception;
	
	protected void updateOrderItems(DesignDetails[] designs) throws SQLException, NamingException {
		try (GenesysDB db = new GenesysDB(GenesysDB.getConnectionPool())) {
			db.updateOrderState(designs, GenesysDB.PROCESSING_STAGE_PRINTED);
		}
	}
	
	@Override
	protected void run(Observer observer, DesignDetails[] designs) {
		try {
			print(observer, designs);
			if (!index) {
				updateOrderItems(designs);
			}
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
		cleanup();
	}

}
