package workstation.processors;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Reader;
import java.io.StringReader;
import java.net.URLEncoder;
import java.util.List;

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

public class JaneBatchInputProcessor extends UploadProcessor {
	private UploadFileConfig _configUI = null;
	
	protected JaneBatchInputProcessor() {
		super("Jane.com CSV", "Jane.com CSV");
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

	@Override
	protected void run(Observer observer, List<EntityItem<Design>> designs) {
		try {
			setInFile(new FileInputStream(filename));
		} catch (FileNotFoundException e1) {
			observer.setProgress(0, "No file uploaded");
			e1.printStackTrace();
		}

        String[] parts = null;//lines from jane.com csv
        StringBuilder gdtCSV = new StringBuilder(); //string representation of the converted csv
        
		BufferedReader br = new BufferedReader(new InputStreamReader(getInFile()));
		
		try {
            //headers for std gdt csv
            String headers = "OrderID,Design/Barcode,First Name,Last Name,Street Name,City,State/Prov,Zip/Postal,Country,Qty,Text optional 1,Text optional 2,Text optional 3\n";
            gdtCSV.append(headers);
            
            String line = br.readLine();//read headers from jane csv
            line = br.readLine();
            while (line != null) {//read all lines from jane csv
                if (!line.equals("") && line.charAt(0)!= ',') {//exclude empty lines
                    parts = line.split(",(?=([^\"]*\"[^\"]*\")*[^\"]*$)");
                    gdtCSV.append(parts[0]);//write order id
                    gdtCSV.append(",");
                    gdtCSV.append(parts[3]);//write design/barcode
                    gdtCSV.append(",");
                    String[] nameSplit = parts[1].split("[ ]{1,}");//split name by whitespace
                    gdtCSV.append(nameSplit[0]);//write first name
                    gdtCSV.append(",");
                    if (nameSplit.length > 1) { //write last name if provided
                        gdtCSV.append(nameSplit[nameSplit.length-1]);
                        gdtCSV.append(", ");
                    }else{
                        gdtCSV.append("");
                        gdtCSV.append(",");
                    }
                    gdtCSV.append(parts[5]);// write street add
                    gdtCSV.append(",");
                    String cityState = null;
                    if (parts[2].charAt(0) == '\"') {// split location to use as city and state
                        cityState = parts[2].substring(1, parts[2].length() - 1);
                    } else {
                        cityState = parts[2].substring(0, parts[2].length() - 1);
                    }
                    String[] cityStateSplit = cityState.split(", ");
                    gdtCSV.append(cityStateSplit[0]);
                    gdtCSV.append(",");
                    if (cityStateSplit.length > 1) {
                        gdtCSV.append(cityStateSplit[1]);
                        gdtCSV.append(", ");
                    }
                    gdtCSV.append(parts[7]);// write zip/postal code
                    gdtCSV.append(",");
                    if (parts[7].matches("[0-9]+") && parts[7].length() > 2) {//fill in country based on postal zip/postal code
                        gdtCSV.append("USA");
                    } else {
                        gdtCSV.append("CANADA");
                    }
                    gdtCSV.append(",");
                    if(parts.length < 10)//write qty
                    {
                    	gdtCSV.append("1");
                    }else{
                    	gdtCSV.append(parts[9]);
                    }
                    gdtCSV.append(",");
                    gdtCSV.append(parts[4]);//write opt text 1
                    gdtCSV.append(",");
                    gdtCSV.append(parts[5]);//write opt text 2
                    gdtCSV.append(",");
                    gdtCSV.append(parts[6]);//write opt text 3
                    gdtCSV.append(",");
                    gdtCSV.append("\n");
                }
                line = br.readLine();
            }
            BatchInputProcessor bip = new BatchInputProcessor();
            bip.writeDesigns(observer, new BufferedReader(new StringReader(gdtCSV.toString())));
        } catch (IOException e) {
            e.printStackTrace();
        }
		observer.setProgress(1, "Done");
	}
}
