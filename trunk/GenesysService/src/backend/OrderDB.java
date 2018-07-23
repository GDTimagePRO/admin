package backend;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;
import java.util.Queue;

import backend.order.Barcode;
import backend.order.OrderItem;
import backend.order.PlasticCategory;
import backend.order.ProcessingStage;
import backend.order.Product;
import backend.order.ProductCategory;

class OrderDB
{
    public static final int CUSTOMER_ID_NA        = 0;
    public static final int CUSTOMER_ID_MASON_ROW = 1;
    //const DEBUG = TRUE;

    private Connection connection = null;

    public OrderDB(Connection connection)
    {
       this.connection = connection;
    }


    //==============================================================================
    // Barcode
    //==============================================================================

    private static final String BARCODES_FIELDS = "barcode, customer_id, UNIX_TIMESTAMP(date_created) AS date_created, config_json, master, UNIX_TIMESTAMP(date_used) AS date_used";

    public Barcode loadBarcode(ResultSet row) throws SQLException
    {
        Barcode result = new Barcode();

        result.barcode = row.getString("barcode");
        result.customerId = row.getInt("customer_id");
        result.dateCreated =row.getInt("date_created");
        result.configJSON = row.getString("config_json");
        result.master = row.getString("master");
        result.dateUsed = row.getInt("date_used");

        return result;
    }

    public boolean createBarcode(Barcode barcode)
    {
    	String query = "INSERT INTO barcodes(barcode, customer_id, date_created, config_json, master, date_used) VALUES (?, ?, FROM_UNIXTIME(?), ?, ?, FROM_UNIXTIME(?))";
    	try (PreparedStatement ps = connection.prepareStatement(query)) {
	    	ps.setString(1, barcode.barcode);
	    	ps.setInt(2,  barcode.customerId);
	    	if(barcode.dateCreated != null)
	        {
	    		 ps.setInt(3, barcode.dateCreated);
	        } else {
	        	ps.setInt(3, (int)(System.currentTimeMillis() / 1000));
	        }
	    	ps.setString(4, barcode.configJSON);
	    	ps.setString(5, barcode.master);
	    	ps.setInt(6, barcode.dateUsed);

    		ps.executeUpdate();
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}

        return true;
    }

    public boolean updateBarcode(Barcode barcode)
    {
    	String query = "UPDATE barcodes SET config_json=?, master=?, date_used=FROM_UNIXTIME(?) WHERE barcode = ? AND customer_id=?";
    	
		try (PreparedStatement ps = connection.prepareStatement(query)){
	    	ps.setString(1, barcode.configJSON);
	    	ps.setString(2, barcode.master);
	    	ps.setInt(3, barcode.dateUsed);
	    	ps.setString(4, barcode.barcode);
	    	ps.setInt(5, barcode.customerId);
   
        	ps.executeUpdate();
        } catch (SQLException e) {
        	//TODO: logging
        	return false;
        }
        return true;
    }

    public Barcode getBarcodeByBarcode(int customerId, String barcode)
    {
    	String query = "SELECT " + BARCODES_FIELDS + " FROM barcodes WHERE barcode=? AND customer_id=?";
    	Barcode b = null;
    	try (PreparedStatement ps = connection.prepareStatement(query)) {
        	ps.setString(1, barcode);
        	ps.setInt(2, customerId);
        	ResultSet rs = ps.executeQuery();

	    	if (rs.next()) {
	    		b = loadBarcode(rs);
	    	}
    	} catch (SQLException e) {
    		//TODO: logging
    		return null;
    	}
        return b;
    }

    public List<Barcode> getBarcodeListByLastGenerated()
    {
        String query = "select " + BARCODES_FIELDS +  " from barcodes where date_created like (SELECT max(date_created) FROM barcodes)";

        
        List<Barcode> barcodes = new ArrayList<Barcode>();
        
        try (PreparedStatement ps = connection.prepareStatement(query)) {
    		ResultSet rs = ps.executeQuery();
	    	while (rs.next()) {
	    		barcodes.add(loadBarcode(rs));
	    	}
    	} catch (SQLException e) {
    		//TODO: logging
    		return null;
    	}

        return barcodes;
    }

    public List<Barcode> getBarcodeList()
    {
        String query = "SELECT " + BARCODES_FIELDS + " FROM barcodes ORDER BY date_created";
        List<Barcode> barcodes = new ArrayList<Barcode>();
        
        try (PreparedStatement ps = connection.prepareStatement(query)) {
    		ResultSet rs = ps.executeQuery();
	    	while (rs.next()) {
	    		barcodes.add(loadBarcode(rs));
	    	}
    	} catch (SQLException e) {
    		//TODO: logging
    		return null;
    	}

        return barcodes;
    }

    public boolean deleteBarcode(int customerId, String barcode)
    {
        String query = "DELETE FROM barcodes WHERE barcode=? AND customer_id=?";
        try (PreparedStatement ps = connection.prepareStatement(query)){
	        ps.setString(1, barcode);
	        ps.setInt(2, customerId);
	        ps.executeUpdate();
	        return true;
        } catch (SQLException e) {
        	//TODO: logging
        	return false;
        }
    }


    //==============================================================================
    // Customer
    //==============================================================================
    public Customer loadCustomer(ResultSet row) throws SQLException
    {
        Customer result = new Customer();

        result.id = row.getInt("id");
        result.idKey = row.getString("id_key");
        result.domain = row.getString("domain");
        result.description = row.getString("description");
        result.emailAddress = row.getString("email_address");
        result.configJSON = row.getString("config_json");

        return result;
    }

    public Customer getCustomerById(int id)
    {
        String query = "SELECT * FROM customers WHERE id=?";
        Customer c = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setInt(1, id);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	c = loadCustomer(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return c;
    }

    public Customer getCustomerByKey(String idKey)
    {
        String query = "SELECT * FROM customers WHERE id_key=?";
        Customer c = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setString(1, idKey);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	c = loadCustomer(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return c;
    }

    public List<Customer> getCustomerList()
    {
        String query = "SELECT * FROM Customers ORDER BY id";

        List<Customer> c = new ArrayList<Customer>();
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ResultSet rs =  ps.executeQuery();
	        while (rs.next()) {
	        	c.add(loadCustomer(rs));
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return c;
    }

    //==============================================================================
    // OrderItem
    //==============================================================================
    private static final String ORDER_ITEMS_FIELDS = "id, customer_id, barcode, processing_stages_id, UNIX_TIMESTAMP(date_created) as date_created, config_json, external_order_id, external_order_status, external_user_id, external_system_name";

    public OrderItem loadOrderItem(ResultSet row) throws SQLException
    {
        OrderItem result = new OrderItem();

        result.id = row.getInt("id");
        result.customerId = row.getInt("customer_id");
        result.barcode = row.getString("barcode");
        result.processingStagesId = row.getInt("processing_stages_id");
        result.creationDate = row.getString("date_created");
        result.configJSON = row.getString("config_json");
        result.externalOrderId = row.getInt("external_order_id");
        result.externalOrderStatus = row.getInt("external_order_status");
        result.externalUserId = row.getInt("external_user_id");
        result.externalSystemName = row.getString("external_system_name");

        return result;
    }

    public boolean createOrderItem(OrderItem orderItem)
    {
        String query = "INSERT INTO order_items(customer_id, barcode, processing_stages_id, date_created, config_json, external_order_id, external_order_status, external_user_id, external_system_name) VALUES (?, ?, ?, FROM_UNIXTIME(?), ?, ?, ?, ? , ?)";
        
        try (PreparedStatement ps = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)){
	    	ps.setInt(1, orderItem.customerId);
	    	ps.setString(2, orderItem.barcode);
	    	ps.setInt(3, orderItem.processingStagesId);
	    	ps.setInt(4, (int)(System.currentTimeMillis() / 1000));
	    	ps.setString(5, orderItem.configJSON);
	    	ps.setInt(6, orderItem.externalOrderId);
	    	ps.setInt(7, orderItem.externalOrderStatus);
	    	ps.setInt(8, orderItem.externalUserId);
	    	ps.setString(9, orderItem.externalSystemName);

    		if (ps.executeUpdate() == 0) {
    			return false;
    		}
    		ResultSet generatedKeys = ps.getGeneratedKeys();
            if (generatedKeys.next()) {
                orderItem.id = generatedKeys.getInt(1);
            }
            else {
                return false;
            }
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}

        return true;
    }

    public boolean updateOrderItem(OrderItem orderItem)
    {
        StringBuilder query = new StringBuilder();
        query.append("UPDATE order_items SET ");
        Queue<Object> params = new LinkedList<Object>();
        boolean first = true;

        if(orderItem.customerId != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("customer_id=?");
            params.add(orderItem.customerId);
        }

        if(orderItem.processingStagesId != null)
        {
        	if(first) { first = false; } else { query.append(", "); }
            query.append("processing_stages_id=?");
            params.add(orderItem.processingStagesId);
        }

        if(orderItem.barcode != null)
        {
        	if(first) { first = false; } else { query.append(", "); }
            query.append("barcode=?");
            params.add(orderItem.barcode);
        }

        if(orderItem.configJSON != null)
        {
        	if(first) { first = false; } else { query.append(", "); }
            query.append("config_json=?");
            params.add(orderItem.configJSON);
        }

        if(orderItem.externalOrderId != null)
        {
        	if(first) { first = false; } else { query.append(", "); }
            //query = query.sprintf("external_order_id=%d", orderItem.externalOrderId);
            query.append("external_order_id=?");
            params.add(orderItem.externalOrderId);
        }

        if(orderItem.externalOrderStatus != null)
        {
        	if(first) { first = false; } else { query.append(", "); }
            query.append("external_order_status=?");
            params.add(orderItem.externalOrderStatus);
        }

        if(orderItem.externalUserId != null)
        {
        	if(first) { first = false; } else { query.append(", "); }
            query.append("external_user_id=");
            params.add(orderItem.externalUserId);
        }

        if(orderItem.externalSystemName != null)
        {
        	if(first) { first = false; } else { query.append(", "); }
            query.append("external_system_name=?");
            params.add(orderItem.externalSystemName);
        }

        query.append(" WHERE id=?");
        params.add(orderItem.id);
        
        try (PreparedStatement ps = connection.prepareStatement(query.toString())) {
	    	for (int i = 1; i <= params.size(); i++) {
	    		ps.setObject(i, params.poll());
	    	}

    		ps.executeUpdate();
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}
        return true;
    }


    public OrderItem getOrderItemById(int id)
    {
        String query = "SELECT " + ORDER_ITEMS_FIELDS + " FROM order_items WHERE id=?";
        OrderItem o = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setInt(1, id);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	o = loadOrderItem(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return o;
        
    }
    
    public OrderItem getOrderItemByBarcode(String barcode) {
    	return getOrderItemByBarcode(barcode, false);
    }

    public OrderItem getOrderItemByBarcode(String barcode, boolean withData)
    {
        String query = "SELECT " + ORDER_ITEMS_FIELDS + " FROM order_items WHERE barcode=?";
        OrderItem o = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setString(1, barcode);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	o = loadOrderItem(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return o;
    }

    //==============================================================================
    // PlasticCategory
    //==============================================================================

    public PlasticCategory loadPlasticCategory(ResultSet row) throws SQLException
    {
    	PlasticCategory result = new PlasticCategory();

        result.id = row.getInt("id");
        result.material = row.getString("material");

        return result;
    }


    public boolean createPlasticCategory(PlasticCategory plasticCategory)
    {
        String query = "INSERT INTO plastic_categories(material) VALUES (?)";

        try (PreparedStatement ps = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
	    	ps.setString(1, plasticCategory.material);
	    	
    		if (ps.executeUpdate() == 0) {
    			return false;
    		}
    		ResultSet generatedKeys = ps.getGeneratedKeys();
            if (generatedKeys.next()) {
                plasticCategory.id = generatedKeys.getInt(1);
            }
            else {
                return false;
            }
            generatedKeys.close();
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}

        return true;
    }

    public PlasticCategory getPlasticCategoryById(int id)
    {
        String query = "SELECT * FROM plastic_categories WHERE id=?";

        PlasticCategory p = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setInt(1, id);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	p = loadPlasticCategory(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }


    //==============================================================================
    // ProcessingStage
    //==============================================================================


    public ProcessingStage loadProcessingStage(ResultSet row) throws SQLException
    {
    	ProcessingStage result = new ProcessingStage();

        result.id = row.getInt("id");
        result.keyName = row.getString("key_name");
        result.name = row.getString("name");
        result.shortName = row.getString("short_name");

        return result;
    }


    public boolean createProcessingStage(ProcessingStage processingStage)
    {
        String query = "INSERT INTO processing_stages(key_name, name, short_name) VALUES (?,?,?)";

        try (PreparedStatement ps = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
        	ps.setString(1, processingStage.keyName);
        	ps.setString(2, processingStage.name);
        	ps.setString(3, processingStage.shortName);
	    	
    		if (ps.executeUpdate() == 0) {
    			return false;
    		}
    		ResultSet generatedKeys = ps.getGeneratedKeys();
            if (generatedKeys.next()) {
            	processingStage.id = generatedKeys.getInt(1);
            }
            else {
                return false;
            }
            generatedKeys.close();
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}

        return true;
    }

    public ProcessingStage getProcessingStageById(int id)
    {
        String query = "SELECT * FROM processing_stages WHERE id=?";
        
        ProcessingStage p = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setInt(1, id);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	p = loadProcessingStage(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }


    //==============================================================================
    // ProductCategory
    //==============================================================================


    public ProductCategory loadProductCategory(ResultSet row) throws SQLException
    {
        ProductCategory result = new ProductCategory();

        result.id = row.getInt("id");
        result.name = row.getString("name");

        return result;
    }


    public boolean createProductCategory(ProductCategory productCategory)
    {
        String query = "INSERT INTO products_category( name ) VALUES ( ? )";
        
        try (PreparedStatement ps = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
        	ps.setString(1, productCategory.name);
	    	
    		if (ps.executeUpdate() == 0) {
    			return false;
    		}
    		ResultSet generatedKeys = ps.getGeneratedKeys();
            if (generatedKeys.next()) {
            	productCategory.id = generatedKeys.getInt(1);
            }
            else {
                return false;
            }
            generatedKeys.close();
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}

        return true;
    }


    public List<ProductCategory> getProductCategoryList()
    {
        String query = "SELECT id, name FROM products_category";

        List<ProductCategory> pc = new ArrayList<ProductCategory>();
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ResultSet rs =  ps.executeQuery();
	        while (rs.next()) {
	        	pc.add(loadProductCategory(rs));
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return pc;
    }

    public ProductCategory getProductCategoryById(int id)
    {
        String query = "SELECT * FROM products_category WHERE id=?";

        ProductCategory p = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setInt(1, id);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	p = loadProductCategory(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }

    public ProductCategory getProductCategoryByName(String name)
    {
        String query = "SELECT * FROM products_category WHERE id=?";
        
        ProductCategory p = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setString(1, name);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	p = loadProductCategory(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }


    //==============================================================================
    // Product
    //==============================================================================

    public Product loadProduct(ResultSet row) throws SQLException
    {
        Product result = new Product();

        result.id = row.getInt("id");
        result.code = row.getString("code");
        result.width = row.getInt("width");
        result.height = row.getInt("height");
        result.longName = row.getString("long_name");
        result.categoryId = row.getInt("category_id");
        result.allowGraphics = row.getBoolean("allow_graphics");
        result.shapeId = row.getInt("shape_id");
        result.frameWidth = row.getInt("frame_width");
        result.frameHeight = row.getInt("frame_height");
        result.productTypeId = row.getInt("product_type_id");
        result.colorModel = row.getString("color_model");
        result.configJSON = row.getString("config_json");

        return result;
    }


    public boolean createProduct(Product product)
    {
        String query = "INSERT INTO products( code, width, height, long_name, category_id, allow_graphics, shape_id, frame_width, frame_height, product_type_id, color_model, config_json ) VALUES ( %, %, %, %, %, %, %, %, %, %, %, % )";


        try (PreparedStatement ps = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
        	ps.setString(1, product.code);
        	ps.setInt(2, product.width);
        	ps.setInt(3, product.height);
        	ps.setString(4, product.longName);
        	ps.setInt(5, product.categoryId);
        	ps.setBoolean(6, product.allowGraphics);
        	ps.setInt(7, product.shapeId);
        	ps.setInt(8, product.frameWidth);
        	ps.setInt(9, product.frameHeight);
        	ps.setInt(10, product.productTypeId);
        	ps.setString(11, product.colorModel);
        	ps.setString(12, product.configJSON);
	    	
    		if (ps.executeUpdate() == 0) {
    			return false;
    		}
    		ResultSet generatedKeys = ps.getGeneratedKeys();
            if (generatedKeys.next()) {
            	product.id = generatedKeys.getInt(1);
            }
            else {
                return false;
            }
            generatedKeys.close();
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}

        return true;
    }

    public Product getProductById(int id)
    {
        String query = "SELECT * FROM products WHERE id=?";

        Product p = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setInt(1, id);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	p = loadProduct(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }

    public Product getProductByCode(String code)
    {
        String query = "SELECT * FROM products WHERE code=?";

        Product p = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setString(1, code);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	p = loadProduct(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }

    public Product getProductByOrderItemId(int orderItemId)
    {
        String query = "SELECT products.* FROM products, barcodes, order_items  WHERE (order_items.barcode = barcodes.barcode) AND (barcodes.product_id = products.id) AND (order_items.id = ?)";

        Product p = null;
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ps.setInt(1, orderItemId);
	        ResultSet rs =  ps.executeQuery();
	        if (rs.next()) {
	        	p = loadProduct(rs);
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }

    public List<Product> getProducts()
    {
        String query = "SELECT * FROM products ORDER BY id";

        List<Product> p = new ArrayList<Product>();
        try (PreparedStatement ps = connection.prepareStatement(query)) {
	        ResultSet rs =  ps.executeQuery();
	        while (rs.next()) {
	        	p.add(loadProduct(rs));
	        }
        } catch (SQLException e) {
        	//TODO: logging
        	return null;
        }
        return p;
    }

    public List<Product> getProductList()
    {
        return getProducts();
    }

    public boolean deleteProduct(int id)
    {
        String query = "DELETE FROM products WHERE id=?";
        try (PreparedStatement ps = connection.prepareStatement(query)){
	        ps.setInt(1, id);
	        ps.executeUpdate();
	        return true;
        } catch (SQLException e) {
        	//TODO: logging
        	return false;
        }
    }


    public boolean updateProduct(Product product)
    {
        StringBuilder query = new StringBuilder();
        Queue<Object> params = new LinkedList<Object>();
        query.append("UPDATE products SET ");
        boolean first = true;

        
        if(product.code != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("code=?");
            params.add(product.code);
        }
        
        if(product.width != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("width=?");
            params.add(product.width);
        }
        
        if(product.height != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("height=?");
            params.add(product.height);
        }

        if(product.longName != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("long_name=?");
            params.add(product.longName);
        }

        if(product.categoryId != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("category_id=?");
            params.add(product.categoryId);
        }
        
        if(product.allowGraphics != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("allow_graphics=?");
            params.add(product.allowGraphics);
        }
        
        if(product.shapeId != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("shape_id=?");
            params.add(product.shapeId);
        }

        if(product.frameWidth != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("frame_width=?");
            params.add(product.frameWidth);
        }

        if(product.frameHeight != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("frame_height=?");
            params.add(product.frameHeight);
        }
        
        if(product.productTypeId != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("product_type_id=?");
            params.add(product.productTypeId);
        }
        
        if(product.colorModel != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("color_model=?");
            params.add(product.colorModel);
        }

        if(product.configJSON != null)
        {
            if(first) { first = false; } else { query.append(", "); }
            query.append("config_json=?");
            params.add(product.configJSON);
        }


        query.append(" WHERE id=?");
        params.add(product.id);
        
        try (PreparedStatement ps = connection.prepareStatement(query.toString())) {
	    	for (int i = 1; i <= params.size(); i++) {
	    		ps.setObject(i, params.poll());
	    	}

    		ps.executeUpdate();
    	} catch (SQLException e) {
    		//TODO: logging
    		return false;
    	}
        return true;
    }
}