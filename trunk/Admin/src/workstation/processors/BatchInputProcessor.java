package workstation.processors;

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Reader;
import java.net.URLEncoder;
import java.util.List;

/*import org.apache.poi.hssf.usermodel.HSSFCell;
import org.apache.poi.hssf.usermodel.HSSFRow;
import org.apache.poi.hssf.usermodel.HSSFSheet;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.poifs.filesystem.POIFSFileSystem;*/









import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;

import org.json.JSONArray;
import org.json.JSONObject;

import util.HTTPHelper;
import model.Design;
import model.DesignTemplate;
import model.Product;

import com.admin.ui.AdminSerlvetListener;
import com.google.gson.Gson;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class BatchInputProcessor extends UploadProcessor {
	
	private UploadFileConfig _configUI = null;
	
	protected BatchInputProcessor() {
		super("GDT CSV", "GDT CSV");
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		if (_configUI == null) {
			_configUI = new UploadFileConfig(this, "/tmp/inf_");
			//_configUI = new UploadFileConfig(this, "C:/testtemp/");
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
				configJSON = new JSONObject(barcodeJSON.getString("configJSON"));
				templateID = configJSON.getJSONArray("items").getJSONObject(0).getInt("templ_id");
				productID = configJSON.getJSONArray("items").getJSONObject(0).getInt("prod_id");
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
			
			for(int i = 0, j = 0; i < elementArray.length(); i++ ) {
				try {
					if( elementArray.getJSONObject(i).getString("className").equals("TextElement") ) {
						if(rowData[10+j].charAt(0) == '\"') {
							elementArray.getJSONObject(i).put("title",rowData[10+j].substring(1, rowData[10+j].length()-1));
						} else {
							elementArray.getJSONObject(i).put("title",rowData[10+j]);
						}
						
						j++;
					}
				} catch( ArrayIndexOutOfBoundsException e ) {
					elementArray.getJSONObject(i).put("title","");
				}
				
			}
			System.out.println(elementArray);
			designJSON.put("elements", elementArray);
			try {
				String response = HTTPHelper.getOutputFromURL(url+"services/manual_insert.php?externalOrderId="+externalID+
						"&customerId="+barcodeJSON.getString("customerId")+
						"&barcode="+rowData[1]+
						"&configJSON="+URLEncoder.encode(configJSON.getJSONArray("items").getJSONObject(0).toString(), "UTF-8")+
						"&designJSON="+URLEncoder.encode(designJSON.toString(), "UTF-8")+
						"&productId="+URLEncoder.encode(productID+"", "UTF-8")+
						"&externalSystem=batchinput"+
						"&shippingInfo="+URLEncoder.encode(shippingString, "UTF-8"));
						
				if(response.indexOf("1") > 0) {
					observer.setProgress(0, "Error creating order #"+externalID+" :: "+response);
					return;
				}
					
			} catch (IOException e1) {
				e1.printStackTrace();
			}
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

}
