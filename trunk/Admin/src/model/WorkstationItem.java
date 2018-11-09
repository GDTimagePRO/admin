package model;

import java.sql.Date;

public class WorkstationItem {
	private String customer;
	private String website_order_id;
	private int genesys_id;
	private int design_id;
	private String order_state;
	private String design_state;
	private Date last_updated;
	private Date date_rendered;
	private String external_system_name;
	private String type;
	

	public WorkstationItem() {
		
		
	}


	public String getCustomer() {
		return customer;
	}


	public void setCustomer(String customer) {
		this.customer = customer;
	}


	public String getWebsite_order_id() {
		return website_order_id;
	}


	public void setWebsite_order_id(String website_order_id) {
		this.website_order_id = website_order_id;
	}


	public int getGenesys_id() {
		return genesys_id;
	}


	public void setGenesys_id(int genesys_id) {
		this.genesys_id = genesys_id;
	}


	public int getDesign_id() {
		return design_id;
	}


	public void setDesign_id(int design_id) {
		this.design_id = design_id;
	}


	public String getOrder_state() {
		return order_state;
	}


	public void setOrder_state(String order_state) {
		this.order_state = order_state;
	}


	public String getDesign_state() {
		return design_state;
	}


	public void setDesign_state(String design_state) {
		this.design_state = design_state;
	}


	public Date getLast_updated() {
		return last_updated;
	}


	public void setLast_updated(Date last_updated) {
		this.last_updated = last_updated;
	}


	public Date getDate_rendered() {
		return date_rendered;
	}


	public void setDate_rendered(Date date_rendered) {
		this.date_rendered = date_rendered;
	}


	public String getExternal_system_name() {
		return external_system_name;
	}


	public void setExternal_system_name(String external_system_name) {
		this.external_system_name = external_system_name;
	}


	public String getType() {
		return type;
	}


	public void setType(String type) {
		this.type = type;
	}


	public WorkstationItem(String customer, int website_order_id, int genesys_number, int genesys_id, int design_id, String order_state,
			String design_state, Date last_updated, Date date_rendered, String external_system_name, String type) {
		super();
		this.customer = customer;
		this.website_order_id = website_order_id + "-" + genesys_number;
		this.genesys_id = genesys_id;
		this.design_id = design_id;
		this.order_state = order_state;
		this.design_state = design_state;
		this.last_updated = last_updated;
		this.date_rendered = date_rendered;
		this.external_system_name = external_system_name;
		this.type = type;
	}

}
