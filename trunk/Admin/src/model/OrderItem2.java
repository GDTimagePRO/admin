package model;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;

public class OrderItem2 {
	private int id;
	private int number;
	private String order_item_config_json;
	private String order_item_options;
	private int processing_stage;
	private int external_order;
	private String barcode;
	
	private static final String DATABASE = "jdbc:mysql://localhost:3306/gdt_core?zeroDateTimeBehavior=convertToNull";
	private static final String USERNAME = "root";
	//private static final String PASSWORD = "Loucks74";
	private static final String PASSWORD = "D@n13lD@ng28";
	
	Connection conn = null;
	java.sql.Statement stmt = null;
	ResultSet rs = null;

	public OrderItem2() {
		// TODO Auto-generated constructor stub
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public int getNumber() {
		return number;
	}

	public void setNumber(int number) {
		this.number = number;
	}

	public String getOrder_item_config_json() {
		return order_item_config_json;
	}

	public void setOrder_item_config_json(String order_item_config_json) {
		this.order_item_config_json = order_item_config_json;
	}
	
	public String getOrder_item_options() {
		return order_item_options;
	}

	public void setOrder_item_options(String order_item_options) {
		this.order_item_options = order_item_options;
	}

	public int getProcessing_stage() {
		return processing_stage;
	}

	public void setProcessing_stage(int processing_stage) {
		this.processing_stage = processing_stage;
	}

	public int getExternal_order() {
		return external_order;
	}

	public void setExternal_order(int external_order) {
		this.external_order = external_order;
	}

	public String getBarcode() {
		return barcode;
	}

	public void setBarcode(String barcode) {
		this.barcode = barcode;
	}
	
	public Order getOrder(int external_order)
	{
		Order order = new Order();
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			stmt = conn.createStatement();
			rs = stmt.executeQuery("SELECT * FROM order_items WHERE external_order = " + external_order + ";");
			
			if(rs.next())
			{
				order.setInternal_order_id(rs.getInt(1));
				order.setExternal_order_id(rs.getInt(2));
				order.setOrder_options(rs.getString(3));
				order.setDate_created(rs.getDate(4));
				order.setShipping_information(rs.getInt(5));
				order.setOrder_status(rs.getInt(6));
				order.setExternal_system(rs.getInt(7));
			}
			rs.close();
			stmt.close();
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return order;
	}

}
