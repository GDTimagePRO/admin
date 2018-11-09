package workstation.processors;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.StringReader;
import java.util.List;

import org.apache.poi.ss.usermodel.Cell;
import org.apache.poi.ss.usermodel.CellType;
import org.apache.poi.ss.usermodel.Row;
import org.apache.poi.xssf.usermodel.XSSFSheet;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;

import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.FileDownloader;
import com.vaadin.server.FileResource;
import com.vaadin.server.Resource;
import com.vaadin.server.VaadinService;
import com.vaadin.ui.Button;
import com.vaadin.ui.Component;

import components.WorkstationObservablesUI;
import concurrency.JobManager.Observer;
import model.Design;
import model.Design2;

public class ZulilyXSLProcessor2 extends UploadProcessor {
	private UploadFileConfig _configUI = null;
	File fedEdDownloadFile;
		
		protected ZulilyXSLProcessor2() {
			super("Zulily.com XSL", "Zulily.com XSL");
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
	        StringBuilder fedEdCSV = new StringBuilder(); //string representation of the converted csv
	        
			
			
			try {
	            //headers for std gdt csv
	            String gdtHeaders = "OrderID, Design #,First Name,Last Name,Location,City,State/Pro,Zip/Postal,Country,Quantity,"
	            		+ "Pesonalization 1,Personalization 2,Personalization 3,Personalization 4,Personalization 5\n";
	          //headers for std gdt csv
	            String fedEdHeaders = "Order Date,Order Id (long),Product Id,Vendor SKU,Product Name,Size,Color,Qty Ordered,Ship First Name,"
	            		+ "Ship Last Name,Ship Company Name,Shipping Address 1,Shipping Address 2,Ship City,Ship Region,"
	            		+ "Ship Postal Code,Ship Phone,Personalization Type 1,Personalization 1,Personalization Type 2,"
	            		+ "Personalization 2,Personalization Type 3,Personalization 3,Personalization Type 4,Personalization 4,"
	            		+ "Personalization Type 5,Personalization 5,Personalization Type 6,Personalization 6,Personalization Type 7,"
	            		+ "Personalization 7,Personalization Type 8,Personalization 8,Personalization Type 9,Personalization 9,"
	            		+ "Personalization Type 10,Personalization 10,Acct#,ShippingMethod,Gift Message,Weight\n";
	            gdtCSV.append(gdtHeaders);
	            fedEdCSV.append(fedEdHeaders);

	         // Get the workbook object for XLS file
	            XSSFWorkbook workbook = new XSSFWorkbook(getInFile());

	            // Get first sheet from the workbook
	            XSSFSheet sheet = workbook.getSheetAt(0);
	            Boolean isFirstRow = false;
	            for(Row row : sheet) {
	            	if(!isFirstRow)
	            	{
	            		isFirstRow = true;
	            		continue;
	            	}
	            	StringBuffer sBuf = new StringBuffer();
	                for (int i = 0; i < 23; i++) {
	                    Cell cell = row.getCell(i);
	                    if(cell == null) {
	                        sBuf.append(",");
	                        continue;
	                    }
	                    cell.setCellType(CellType.STRING);
	                    if(cell.getStringCellValue().contains(","))
	                        sBuf.append("\"" + cell.getStringCellValue() + "\",");
	                	else
	                		sBuf.append(cell.getStringCellValue() + ",");
	                }   
	                sBuf.append(" , , , , , , , , , , , , , ,10484,Parcel Post, ,1,");
	                fedEdCSV.append(sBuf + "\n");
	            	}
	            
	            fedEdDownloadFile = new File("test.csv");
	    		
	    		try {
	    			FileWriter fileWriter = new FileWriter(fedEdDownloadFile);
	    			fileWriter.write(fedEdCSV.toString());
	    			fileWriter.close();
	    		} catch (IOException e) {
	    			// TODO Auto-generated catch block
	    			e.printStackTrace();
	    		}

	            // Get first sheet from the workbook
	            
	            BufferedReader br = new BufferedReader(new FileReader(fedEdDownloadFile));
	            String line = br.readLine();//read headers from jane csv
	            line = br.readLine();
	            while (line != null) {//read all lines from jane csv
	                if (!line.equals("") && line.charAt(0)!= ',') {//excl
	                    parts = line.split(",(?=([^\"]*\"[^\"]*\")*[^\"]*$)");
	                    gdtCSV.append(parts[1]);//write order id
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[3]);//write design/barcode
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[8]);//write first name
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[9]);// write last name
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[11]);// write location
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[13]);// write city
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[14]);// write state
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[15]);// write zip code
	                    gdtCSV.append(",");
	                    gdtCSV.append(" ");// write country
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[7]);// write quantity
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[18]);// write personalization 1
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[20]);// write personalization 2
	                    gdtCSV.append(",");
	                    gdtCSV.append(parts[22]);// write personalization 3
	                    gdtCSV.append(",");
	                    gdtCSV.append("");// write personalization 4
	                    gdtCSV.append(",");
	                    gdtCSV.append("");// write personalization 5
	                    gdtCSV.append(",");
	                    gdtCSV.append("\n");
	                }
	                line = br.readLine();
	            }
	            BatchInputProcessor2 bip = new BatchInputProcessor2();
	            bip.writeDesigns(observer, new BufferedReader(new StringReader(gdtCSV.toString())));
	            
	    		cleanup();
	        } catch (IOException e) {
	            e.printStackTrace();
	        }
			observer.setProgress(1, "Done");
			WorkstationObservablesUI observableWindow = new WorkstationObservablesUI();
			observableWindow.show();
			Button dl = new Button();
			dl.setCaption("Download CSV");
			observableWindow.addObservable(dl);
			
				Resource res = new FileResource(fedEdDownloadFile);
				FileDownloader fd = new FileDownloader(res);
				fd.extend(dl);
			
		}

		@Override
		public Component getConfigUI2(List<Design2> designs) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		protected void run2(Observer observer, List<Design2> designs) {
			// TODO Auto-generated method stub
			
		}
}
