package model;

import java.util.Date;

public class Order {
	private int internal_order_id;
	private int external_order_id;
	private String order_options;
	private Date date_created;
	private int shipping_information;
	private int order_status;
	private int external_system;

	public Order() {
		
	}

	public int getInternal_order_id() {
		return internal_order_id;
	}

	public void setInternal_order_id(int internal_order_id) {
		this.internal_order_id = internal_order_id;
	}

	public int getExternal_order_id() {
		return external_order_id;
	}

	public void setExternal_order_id(int external_order_id) {
		this.external_order_id = external_order_id;
	}

	public String getOrder_options() {
		return order_options;
	}

	public void setOrder_options(String order_options) {
		this.order_options = order_options;
	}

	public Date getDate_created() {
		return date_created;
	}

	public void setDate_created(Date date_created) {
		this.date_created = date_created;
	}

	public int getShipping_information() {
		return shipping_information;
	}

	public void setShipping_information(int shipping_information) {
		this.shipping_information = shipping_information;
	}

	public int getOrder_status() {
		return order_status;
	}

	public void setOrder_status(int order_status) {
		this.order_status = order_status;
	}

	public int getExternal_system() {
		return external_system;
	}

	public void setExternal_system(int external_system) {
		this.external_system = external_system;
	}
	
}
