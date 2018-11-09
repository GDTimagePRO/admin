package model;

import java.sql.Connection;
import java.sql.Date;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;

public class Design2 {
	private int design_id;
	private String design_json;
	private String design_config_json;
	private Date date_rendered;
	private Date date_changed;
	private int design_state;
	private int product;
	private int order_item;
	public OrderItem2 orderItem;
	
	private static final String DATABASE = "jdbc:mysql://localhost:3306/gdt_core?zeroDateTimeBehavior=convertToNull";
	private static final String USERNAME = "root";
	//private static final String PASSWORD = "Loucks74";
	private static final String PASSWORD = "D@n13lD@ng28";
	
	Connection conn = null;
	java.sql.Statement stmt = null;
	ResultSet rs = null;

	public Design2() {
		// TODO Auto-generated constructor stub
	}

	public Design2(int design_id, String design_json, String design_config_json,
			Date date_rendered, Date date_changed, int design_state, int product, int order_item) {
		super();
		this.design_id = design_id;
		this.design_json = design_json;
		this.design_config_json = design_config_json;
		this.date_rendered = date_rendered;
		this.date_changed = date_changed;
		this.design_state = design_state;
		this.product = product;
		this.order_item = order_item;
	}

	public int getDesign_id() {
		return design_id;
	}

	public void setDesign_id(int design_id) {
		this.design_id = design_id;
	}

	public String getDesign_json() {
		return design_json;
	}

	public void setDesign_json(String design_json) {
		this.design_json = design_json;
	}

	public String getDesign_config_json() {
		return design_config_json;
	}

	public void setDesign_config_json(String design_config_json) {
		this.design_config_json = design_config_json;
	}

	public Date getDate_rendered() {
		return date_rendered;
	}

	public void setDate_rendered(Date date_rendered) {
		this.date_rendered = date_rendered;
	}

	public Date getDate_changed() {
		return date_changed;
	}

	public void setDate_changed(Date date_changed) {
		this.date_changed = date_changed;
	}

	public int getDesign_state() {
		return design_state;
	}

	public void setDesign_state(int design_state) {
		this.design_state = design_state;
	}

	public int getProduct() {
		return product;
	}

	public void setProduct(int product) {
		this.product = product;
	}

	public int getOrder_item() {
		return order_item;
	}

	public void setOrder_item(int order_item) {
		this.order_item = order_item;
	}
	
	public float getFrameHeight(int product_id)
	{
		float result = 0;
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			stmt = conn.createStatement();
			rs = stmt.executeQuery("SELECT frame_height FROM products WHERE product_id = " + product_id + ";");
			rs.next();
			result = rs.getFloat(0);
			rs.close();
			stmt.close();
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return result;
	}

	public float getFrameWidth(int product_id)
	{
		float result = 0;
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			stmt = conn.createStatement();
			String query = "SELECT frame_width FROM products WHERE product_id = " + product_id + ";";
			rs = stmt.executeQuery(query);
			if(rs.next())
				result = rs.getFloat(0);
			rs.close();
			stmt.close();
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return result;
	}
	
	public float getHeight(int product_id)
	{
		float result = 0;
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			stmt = conn.createStatement();
			String query = "SELECT height FROM products WHERE product_id = " + product_id + ";";
			rs = stmt.executeQuery(query);
			if(rs.next())
				result = rs.getFloat(0);
			rs.close();
			stmt.close();
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return result;
	}
	
	public float getWidth(int product_id)
	{
		float result = 0;
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			stmt = conn.createStatement();
			rs = stmt.executeQuery("SELECT width FROM products WHERE product_id = " + product_id + ";");
			rs.next();
			result = rs.getFloat(0);
			rs.close();
			stmt.close();
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return 10;
	}
	
	public String getDesignImageId_HD()
	{
		return  "designs/" + this.getDesign_id() + "_hd.png" ;
	}
	
	public OrderItem2 getOrderItem(int order_item)
	{
		OrderItem2 orderItem = new OrderItem2();
		try {
			conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
			stmt = conn.createStatement();
			rs = stmt.executeQuery("SELECT * FROM designs WHERE order_item = " + order_item + ";");
			
			if(rs.next())
			{
				orderItem.setId(rs.getInt(1));
				orderItem.setNumber(rs.getInt(2));
				orderItem.setOrder_item_config_json(rs.getString(3));
				orderItem.setOrder_item_options(rs.getString(4));
				orderItem.setProcessing_stage(rs.getInt(5));
				orderItem.setExternal_order(rs.getInt(6));
				orderItem.setBarcode(rs.getString(7));
			}
			rs.close();
			stmt.close();
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		this.orderItem = orderItem;
		return orderItem;
	}
}
