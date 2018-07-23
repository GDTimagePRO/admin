package com.instamp.workstation.data;

import java.io.Serializable;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.Collection;
import java.util.Date;
import java.util.HashMap;
import java.util.LinkedList;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import com.google.gson.Gson;
import com.instamp.workstation.ui.components.DesignListView.KeyValuePair;
import com.vaadin.data.util.BeanItemContainer;
import com.vaadin.data.util.sqlcontainer.connection.JDBCConnectionPool;

public final class GenesysDB implements AutoCloseable
{	
	public static final int PROCESSING_STAGE_PENDING_CANCELED		= 100;
	public static final int PROCESSING_STAGE_PENDING_CONFIRMATION	= 200;
	public static final int PROCESSING_STAGE_PENDING_CART_ORDER		= 300;
	public static final int PROCESSING_STAGE_PENDING_RENDERING		= 350;
	public static final int PROCESSING_STAGE_READY					= 400;
	public static final int PROCESSING_STAGE_PRINTED				= 425;
	public static final int PROCESSING_STAGE_SHIPPED				= 450;
	public static final int PROCESSING_STAGE_ARCHIVED				= 500;
	
	public static final int DESIGN_STATE_PENDING_SCL_DATA			= 0;
	public static final int DESIGN_STATE_PENDING_CONFIRMATION		= 10;
	public static final int DESIGN_STATE_PENDING_SCL_RENDERING		= 20;
	public static final int DESIGN_STATE_READY						= 30;
	public static final int DESIGN_STATE_ARCHIVED					= 40;
	
	public static final int PRODUCT_CATEGORY_RUBBER					= 1;
	public static final int PRODUCT_CATEGORY_NAME					= 2;
	public static final int PRODUCT_CATEGORY_EMBOSSER				= 3;
	public static final int PRODUCT_CATEGORY_FLASH					= 4;
	public static final int PRODUCT_CATEGORY_DATER					= 5;
	public static final int PRODUCT_CATEGORY_BADGE					= 6;
	public static final int PRODUCT_CATEGORY_SIGN					= 7;
	public static final int PRODUCT_CATEGORY_TAG					= 8;
	public static final int PRODUCT_CATEGORY_POLYMER				= 9;
	
	public static final class DesignColor
	{
		public String name;
		public String value;
	}

	public static final class DesignColorPalette
	{
		public DesignColor ink;
	}
	
	private static final class ParsedDesignSceneData
	{
		DesignColorPalette colors;
	}

	private static final class ParsedDesignData
	{
		ParsedDesignSceneData scene;
	}
	
	public static final class DesignDetails implements Serializable
	{
		private static final long serialVersionUID = 6130198496336151681L;
		
		public int designId;
		public int designState;
		public Date designDateRendered;
		
		public int orderItemId;		
		public int orderItemCustomerId;
		public int orderItemProcessingStageId;
		public Date orderItemDateCreated;
		public long orderItemExtOrderId;
		public int orderItemExtOrderStatus;
		public int orderItemExtUserId;
		public String orderItemExtSystemName;

		public int productId;
		public String productCode;
		public String productLongName;
		public int productTypeId;
		public int productCategoryId;
		public float productWidth;
		public float productHeight;
		public float productFrameWidth;
		public float productFrameHeight;
		public DesignColorPalette designColors;
		public String productCategoryName;
		
		public String getDesignImageId_Preview()
		{
			return  "designs/" + designId + "_prev.png" ;
		}

		public String getDesignImageId_Thumbnail()
		{
			return "thumbs." + getDesignImageId_Preview();
		}
		
		public String getDesignImageId_HD()
		{
			return  "designs/" + designId + "_hd.png" ;
		}

		public String getDesignImageId_EmbosserM()
		{
			return "embosser_m." + getDesignImageId_HD();
		}

		public String getDesignImageId_EmbosserF()
		{
			return "embosser_f." + getDesignImageId_HD();
		}

		public String getDesignImageId_SVG()
		{
			return  "designs/" + designId + "_hd.svg" ;
		}
		
	}
	
	private static final String CUSTOMER_SQL = "SELECT id, description FROM Customers";
	private static final String PRODUCT_TYPE_SQL = "SELECT id, name FROM products_category";
	
	public static final String UPDATE_ORDER_STATUS = "UPDATE order_items SET processing_stages_id = %d WHERE id IN (%s)";
	public static final String GET_DESIGN_JSON = "SELECT id, config_json, design_json FROM designs WHERE id IN (%s)";
	
	public static final String GET_ADDRESS_FROM_DESIGNS = "SELECT d.id, first_name, last_name, address_1, address_2, city, state_province, zip_postal_code, country FROM designs as d JOIN shipping_information on order_item_id = order_id WHERE d.id IN (%s)";
	
	public static final String GET_CUSTOMER_ADDRESSES = "SELECT id, address, logo FROM customers";
	
	private static final String COMMON_SQL = 

			"SELECT " +
			
				"d.id AS 'd_id', " +
				"d.state AS 'd_state', " +
				"UNIX_TIMESTAMP(d.date_rendered) AS 'd_date_rendered', " +
				"d.design_json AS 'designJSON', " +
				
				
				"oi.id AS 'oi_id', " +		
				"oi.customer_id AS 'oi_customer_id', " +
				"oi.processing_stages_id AS 'oi_processing_stages_id', " +
				"UNIX_TIMESTAMP(oi.date_created) AS 'oi_date_created', " +
				"oi.external_order_id AS 'oi_external_order_id', " +
				"oi.external_order_status AS 'orderItemExtOrderStatus', " +
				"oi.external_user_id AS 'orderItemExtUserId', " +
				"oi.external_system_name AS 'oi_external_system_name', " +

				"p.id AS 'productId', " +
				"p.code AS 'productCode', " +
				"p.long_name AS 'productLongName', " +
				"p.product_type_id AS 'productTypeId', " +
				"p.category_id AS 'p_category_id', " +
				"p.width AS 'productWidth', " +
				"p.height AS 'productHeight', " +
				"p.frame_width AS 'productFrameWidth', " +
				"p.frame_height AS 'productFrameHeight', " +

				"pc.name AS 'pc_name' " +
					
			"FROM " +
				
				"designs d, " +
				"products p, " +
				"order_items oi, " +
				"products_category pc " +
				
			"WHERE " + 
				"d.order_item_id = oi.id AND " +  
				"d.product_id = p.id  AND " +
				"p.category_id = pc.id ";

	private final Connection _conn;
	private final JDBCConnectionPool _connectionPool;
	
	public static SimpleJ2EEConnectionPool getConnectionPool() throws NamingException {
		InitialContext ctx = new InitialContext();
		DataSource ds = (DataSource) ctx.lookup("genesys_core");
        return new SimpleJ2EEConnectionPool(ds);
	}
	
	public GenesysDB(JDBCConnectionPool connectionPool) throws SQLException
	{
		_connectionPool = connectionPool;
		_conn = _connectionPool.reserveConnection();
	}
	
	private static Date dateReadTimestamp(ResultSet rs, String key) throws SQLException
	{
		long time = rs.getLong(key);
		if(time == 0) return null;
		return new Date(time * 1000);
	}
		
	private static DesignDetails loadDesignDetails(ResultSet rs, Gson gson) throws SQLException
	{
		DesignDetails result = new DesignDetails();
		
		result.designId = rs.getInt("d_id");
		result.designState = rs.getInt("d_state");
		result.designDateRendered = dateReadTimestamp(rs, "d_date_rendered");
		
		result.orderItemId = rs.getInt("oi_id");
		result.orderItemCustomerId = rs.getInt("oi_customer_id");
		result.orderItemProcessingStageId = rs.getInt("oi_processing_stages_id");
		
		result.orderItemDateCreated = dateReadTimestamp(rs, "oi_date_created"); 
		result.orderItemExtOrderId = rs.getLong("oi_external_order_id");
		result.orderItemExtOrderStatus = rs.getInt("orderItemExtOrderStatus");
		result.orderItemExtUserId = rs.getInt("orderItemExtUserId");
		result.orderItemExtSystemName = rs.getString("oi_external_system_name");

		result.productId = rs.getInt("productId");
		result.productCode = rs.getString("productCode");
		result.productLongName = rs.getString("productLongName");
		result.productTypeId = rs.getInt("productTypeId");
		result.productCategoryId = rs.getInt("p_category_id");
		result.productWidth = rs.getFloat("productWidth");
		result.productHeight = rs.getFloat("productHeight");
		result.productFrameWidth = rs.getFloat("productFrameWidth");
		result.productFrameHeight = rs.getFloat("productFrameHeight");
		
		result.productCategoryName =  rs.getString("pc_name");

		
		ParsedDesignData designData = gson.fromJson(rs.getString("designJSON"), ParsedDesignData.class);
		
		result.designColors = designData.scene.colors;
		
		return result;
	}
	
	public DesignDetails getDesignDetailsByDesignId(int designId) throws SQLException
	{
		Statement stmt = _conn.createStatement();
		String sql = COMMON_SQL + "AND d.id = " + designId;
		ResultSet rs = stmt.executeQuery(sql);
		
		if(!rs.next()) return null;
		
		Gson gson = new Gson();
		return loadDesignDetails(rs, gson);
	}

	public Collection<DesignDetails> getDesignDetailsByDesignId(int[] designIds, String orderBy, boolean asc) throws SQLException
	{
		LinkedList<DesignDetails> result = new LinkedList<>();
		if((designIds != null) && (designIds.length > 0))
		{
			Gson gson = new Gson();
			StringBuilder sb = new StringBuilder();
			sb.append(COMMON_SQL);
			sb.append("AND d.id IN (");
			
			for(int i=0; i<designIds.length; i++)
			{
				if(i != 0) sb.append(',');
				sb.append(designIds[i]);
			}
			sb.append(')');
			
			if (orderBy != null) {
				sb.append(" ORDER BY ");
				sb.append(orderBy);
				if (asc) {
					sb.append(" ASC");
				} else {
					sb.append(" DESC");
				}
			}
			
			
			Statement stmt = _conn.createStatement();
			ResultSet rs = stmt.executeQuery(sb.toString());
			
			while(rs.next())
			{
				result.add(loadDesignDetails(rs, gson));
			}
			rs.close();
			stmt.close();
			if(result.size() != designIds.length)
			{
				return null;
			}
		}
		
		return result;
	}
	
	public BeanItemContainer<KeyValuePair> getCustomers() throws SQLException {
		BeanItemContainer<KeyValuePair> customers = new BeanItemContainer<>(KeyValuePair.class);
		Statement stmt = _conn.createStatement();
		ResultSet rs = stmt.executeQuery(CUSTOMER_SQL);
		while (rs.next()) {
			customers.addBean(new KeyValuePair(rs.getInt("id"), rs.getString("description")));
		}
		rs.close();
		stmt.close();
		return customers;
	}
	
	public BeanItemContainer<KeyValuePair> getProductTypes() throws SQLException {
		BeanItemContainer<KeyValuePair> products = new BeanItemContainer<>(KeyValuePair.class);
		Statement stmt = _conn.createStatement();
		ResultSet rs = stmt.executeQuery(PRODUCT_TYPE_SQL);
		while (rs.next()) {
			products.addBean(new KeyValuePair(rs.getInt("id"), rs.getString("name")));
		}
		rs.close();
		stmt.close();
		return products;
	}
	
	public void updateOrderState(DesignDetails[] designs, int OrderState) throws SQLException {
		Statement stmt = _conn.createStatement();
		StringBuilder ids = new StringBuilder();
		for (DesignDetails design : designs) {
			ids.append(design.orderItemId);
			ids.append(",");
		}
		ids.deleteCharAt(ids.length()-1);
		String query = String.format(UPDATE_ORDER_STATUS, OrderState, ids);
		stmt.executeUpdate(query);
		stmt.close();
	}
	
	public class DesignJson {
		public String designJson;
		public String configJson;
		
		public DesignJson(String d, String c) {
			designJson = d;
			configJson = c;
		}
	}
	
	public HashMap<Integer, DesignJson> getDesignJson(DesignDetails[] designs) throws SQLException {
		HashMap<Integer, DesignJson> results = new HashMap<Integer, DesignJson>(designs.length);
		Statement stmt = _conn.createStatement();
		StringBuilder ids = new StringBuilder();
		for (DesignDetails design : designs) {
			ids.append(design.designId);
			ids.append(",");
		}
		ids.deleteCharAt(ids.length()-1);
		String query = String.format(GET_DESIGN_JSON, ids.toString());
		ResultSet rs = stmt.executeQuery(query);
		while (rs.next()) {
			results.put(rs.getInt("id"), new DesignJson(rs.getString("design_json"), rs.getString("config_json")));
		}
		rs.close();
		stmt.close();
		return results;
	}
	
	public DesignJson getDesignJson(String designId) throws SQLException {
		DesignJson result = null;
		Statement stmt = _conn.createStatement();
		String query = String.format(GET_DESIGN_JSON, designId);
		ResultSet rs = stmt.executeQuery(query);
		while (rs.next()) {
			result = new DesignJson(rs.getString("design_json"), rs.getString("config_json"));
		}
		rs.close();
		stmt.close();
		return result;
	}
	
	public class ShippingInformation {
		public String first_name;
		public String last_name;
		public String address_1;
		public String address_2;
		public String city;
		public String state_province;
		public String zip_postal_code;
		public String country;
		
		public ShippingInformation(String fn, String ln, String a1, String a2, String c, String sp, String zpc, String count) {
			first_name = fn;
			last_name = ln;
			address_1 = a1;
			address_2 = a2;
			city = c;
			state_province = sp;
			zip_postal_code = zpc;
			country = count;
			
		}
	}
	
	public HashMap<Integer, ShippingInformation> getShippingInformation(DesignDetails[] designs) throws SQLException {
		HashMap<Integer, ShippingInformation> results = new HashMap<Integer, ShippingInformation>(designs.length);
		Statement stmt = _conn.createStatement();
		StringBuilder ids = new StringBuilder();
		for (DesignDetails design : designs) {
			ids.append(design.designId);
			ids.append(",");
		}
		ids.deleteCharAt(ids.length()-1);
		String query = String.format(GET_ADDRESS_FROM_DESIGNS, ids.toString());
		ResultSet rs = stmt.executeQuery(query);
		while (rs.next()) {
			results.put(rs.getInt("id"), new ShippingInformation(rs.getString("first_name"), rs.getString("last_name"), rs.getString("address_1"),
					rs.getString("address_2"), rs.getString("city"), rs.getString("state_province"), rs.getString("zip_postal_code"), 
					rs.getString("country")));
		}
		rs.close();
		stmt.close();
		return results;
	}
	
	public class CustomerAddress {
		public String address;
		public String logo;
		
		public CustomerAddress(String address, String logo) {
			this.address = address;
			this.logo = logo;
		}
	}
	
	public HashMap<Integer, CustomerAddress> getCustomerAddresses() throws SQLException {
		HashMap<Integer, CustomerAddress> results = new HashMap<Integer, CustomerAddress>();
		Statement stmt = _conn.createStatement();
		String query = GET_CUSTOMER_ADDRESSES;
		ResultSet rs = stmt.executeQuery(query);
		while (rs.next()) {
			results.put(rs.getInt("id"), new CustomerAddress(rs.getString("address"), rs.getString("logo")));
		}
		rs.close();
		stmt.close();
		return results;
	}
	
	
	public void close() {
		_connectionPool.releaseConnection(_conn);
	}
}


















