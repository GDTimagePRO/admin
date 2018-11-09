package workstation.processors;

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.URLEncoder;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.List;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;

import org.json.JSONArray;
import org.json.JSONObject;

import com.admin.ui.AdminSerlvetListener;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.VaadinService;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;
import model.Design;
import model.Design2;
import util.HTTPHelper;

public class BatchInputProcessor2 extends UploadProcessor {
	/**
	 * 
	 */
	private static final long serialVersionUID = 1L;
	private static final String DATABASE = "jdbc:mysql://localhost:3306/gdt_core?zeroDateTimeBehavior=convertToNull";
	private static final String USERNAME = "root";
	//private static final String PASSWORD = "Loucks74";
	private static final String PASSWORD = "D@n13lD@ng28";
	Connection conn = null;
	java.sql.Statement stmt = null;
	ResultSet rs = null;
	
	private UploadFileConfig _configUI = null;
	
	protected BatchInputProcessor2() {
		super("GDT CSV", "GDT CSV");
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		if (_configUI == null) {
			_configUI = new UploadFileConfig(this, VaadinService.getCurrent().getBaseDirectory().getAbsolutePath());
			//_configUI = new UploadFileConfig(this, "D:/");
			_configUI.show();
		}
		return _configUI;
	}
	
	@Override
	public String saveConfig() {
		return null;
		
	}
	
	protected void writeDesigns(Observer observer, BufferedReader reader) {
		String[] columnHeaders = null;
		String[] rowData;
		String inline = "";
		int cols = 0;
		String externalID = null;
		int templateID = 0;
		int productID = 0;
		JSONObject designJSON = null;
		JSONObject configJSON = null;
		JSONArray elementArray = null;
		JSONObject barcodeJSON = null;
		String shippingString = null;
		Context context = null;
		String url = null;
		try {
			context = new InitialContext();
		} catch (NamingException e1) {
			e1.printStackTrace();
		}
		try {
			url = (String) context.lookup(AdminSerlvetListener.APIURL);
		} catch (NamingException e1) {
			e1.printStackTrace();
		}
				
		try {
			columnHeaders = reader.readLine().split(",");
			cols = columnHeaders.length;
			inline = reader.readLine();
		} catch (IOException e) {
			e.printStackTrace();
		}
		
		while(inline != null) {
			rowData = inline.split(",(?=([^\"]*\"[^\"]*\")*[^\"]*$)");
			externalID = rowData[0];
			try {
				barcodeJSON = new JSONObject(HTTPHelper.getOutputFromURL(url+"services/get_barcode.php?barcode="+URLEncoder.encode(rowData[1], "UTF-8")));
				System.out.println(url+"services/get_barcode.php?barcode="+URLEncoder.encode(rowData[1], "UTF-8"));
				configJSON = new JSONObject(barcodeJSON.getString("configJSON"));
				System.out.println(configJSON);
				templateID = configJSON.getJSONArray("items").getJSONObject(0).getInt("templ_id");
				System.out.println(templateID);
				productID = configJSON.getJSONArray("items").getJSONObject(0).getInt("prod_id");
				System.out.println(productID);
				designJSON = new JSONObject(new JSONObject(HTTPHelper.getOutputFromURL(url+"services/get_design_template.php?templateId="+URLEncoder.encode(String.valueOf(templateID), "UTF-8"))).getString("designJSON"));
			} catch (IOException e) {
				e.printStackTrace();
			}
			
			elementArray = designJSON.getJSONArray("elements");
			shippingString = "{" +    
				    "\"first_name\":\""+rowData[2]+"\"," +
				    "\"last_name\":\""+rowData[3]+"\"," +
				    "\"address_1\":\""+rowData[4]+"\"," +
				    "\"city\": \""+rowData[5]+"\"," +
				    "\"state_province\":\""+rowData[6]+"\"," +
				    "\"zip_postal_code\":\""+rowData[7]+"\"," +
				    "\"country\":\""+rowData[8]+"\"," +
				    "\"ship_qty\":\""+rowData[9]+"\"" +
				"}";
			try {
				conn = DriverManager.getConnection(DATABASE, USERNAME,PASSWORD);
				stmt = conn.createStatement();
				String query = "INSERT INTO shipping_information(first_name, last_name, address_1, city,"
						+ "state_province, zip_postal_code, country, ship_quantity) VALUES("
						+ "'" + rowData[2] + "', '" + rowData[3] + "', '" + rowData[4] + "', '', '" +
						rowData[5] + "', '" + rowData[6] + "', " + rowData[7] + ", '" + rowData[8] + "', '', '', " + 
						rowData[9] + ", null)";
				stmt.executeUpdate(query);
				/*query = "SELECT MAX(id) FROM shipping_information";
				rs = stmt.executeQuery(query);
				int id=0;
				if(rs.next())
				{
					id = rs.getInt(0);
				}
				query = "INSERT INTO orders(external_order_id, order_options, date_created, shipping_information, "
						+ "order_status, external_system) VALUES(" + rowData[0] + ", null, NOW(), " + id + ", 0, 0);";
				stmt.executeUpdate(query);*/
				rs.close();
				stmt.close();
				conn.close();
			} catch (SQLException e2) {
				// TODO Auto-generated catch block
				e2.printStackTrace();
			}
			for(int i = 0, j = 0; i < elementArray.length(); i++ ) {
				try {
					if(elementArray.getJSONObject(i).has("textFormat")) {
						if( elementArray.getJSONObject(i).getString("className").equals("TextElement") ) {
							if(elementArray.getJSONObject(i).getInt("textFormat") == 0)
							{
								if(rowData[10+j].charAt(0) == '\"') {
									elementArray.getJSONObject(i).put("title",rowData[10+j].substring(1, rowData[10+j].length()-1));
								} else {
									elementArray.getJSONObject(i).put("title",rowData[10+j]);
								}
							} else if (elementArray.getJSONObject(i).getInt("textFormat") == 1)
							{
								if(rowData[10+j].charAt(0) == '\"') {
									elementArray.getJSONObject(i).put("title",rowData[10+j].substring(1, rowData[10+j].length()-1).toUpperCase());
								} else {
									elementArray.getJSONObject(i).put("title",rowData[10+j].toUpperCase());
								}
							}
							
							j++;
						}
					} else {
						if( elementArray.getJSONObject(i).getString("className").equals("TextElement") ) {
							if(rowData[10+j].charAt(0) == '\"') {
								elementArray.getJSONObject(i).put("title",rowData[10+j].substring(1, rowData[10+j].length()-1));
							} else {
								elementArray.getJSONObject(i).put("title",rowData[10+j]);
							}
							
							j++;
						}
					}
					
				} catch( ArrayIndexOutOfBoundsException e ) {
					elementArray.getJSONObject(i).put("title","");
				}
				
			}
			designJSON.put("elements", elementArray);
			/*try {
				String response = HTTPHelper.getOutputFromURL(url+"services/manual_insert.php?externalOrderId="+externalID+
						"&customerId="+barcodeJSON.getString("customerId")+
						"&barcode="+rowData[1]+
						"&configJSON="+URLEncoder.encode(configJSON.getJSONArray("items").getJSONObject(0).toString(), "UTF-8")+
						"&designJSON="+URLEncoder.encode(designJSON.toString(), "UTF-8")+
						"&productId="+URLEncoder.encode(productID+"", "UTF-8")+
						"&externalSystem=batchinput");
						
				if(response.indexOf("1") > 0) {
					observer.setProgress(0, "Error creating order #"+externalID+" :: "+response);
					return;
				}
					
			} catch (IOException e1) {
				e1.printStackTrace();
			}*/
			try {
				inline = reader.readLine();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}

	@Override
	protected void run(Observer observer, List<EntityItem<Design>> designs) {

		try {
			setInFile(new FileInputStream(filename));
		} catch (FileNotFoundException e1) {
			observer.setProgress(0, "No file uploaded");
			e1.printStackTrace();
		}

		BufferedReader reader = new BufferedReader(new InputStreamReader(getInFile()));
		writeDesigns(observer, reader);
		observer.setProgress(1, "Done");
	}

	@Override
	public Component getConfigUI2(List<Design2> designs) {
		if (_configUI == null) {
			_configUI = new UploadFileConfig(this, VaadinService.getCurrent().getBaseDirectory().getAbsolutePath());
			//_configUI = new UploadFileConfig(this, "D:/");
			_configUI.show();
		}
		return _configUI;
	}

	@Override
	protected void run2(Observer observer, List<Design2> designs) {
		try {
			setInFile(new FileInputStream(filename));
		} catch (FileNotFoundException e1) {
			observer.setProgress(0, "No file uploaded");
			e1.printStackTrace();
		}

		BufferedReader reader = new BufferedReader(new InputStreamReader(getInFile()));
		writeDesigns(observer, reader);
		observer.setProgress(1, "Done");
	}
}
