package com.instamp.workstation.processors.design;

import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;

import com.dekconsulting.jsontozpl.*;

import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.Properties;

import javax.naming.InitialContext;
import javax.naming.NamingException;

import org.json.JSONException;

import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.instamp.workstation.Application;
import com.instamp.workstation.concurrency.JobManager.Observer;
import com.instamp.workstation.data.GenesysDB;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.data.GenesysDB.DesignJson;
import com.instamp.workstation.util.File;
import com.instamp.workstation.util.Zip;
import com.itextpdf.text.DocumentException;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;


//TODO: Remove
//Index has hard coded with/height since we print from flash
//Better solution would be to have it show up in the workstation or attach index width/height to their primary items


public class RGCIndexProcessor extends PrintProcessor {
	
	//DEFAULTS
	/*private float _pageHeight = 25.4f;
	private float _pageWidth = 108f;
	private float _marginLeft = 0f;
	private float _marginTop = 0f;
	private final String _name = "RGCIndex";
	public static float _productWidth = 51.214f;
	public static float _productHeight = 17.859f;*/
	
	private final String _name = "RGCIndex";

	protected RGCIndexProcessor() {
		super("RGC Index", "Print RGC Index Designs", true);
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		return null;
	}
	

	/*private void addDesigntoPDF(DesignDetails design, Pdf pdf, float x, float y) throws DocumentException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float centerX = (_pageWidth - _marginLeft - (_productWidth * 2f)) / 2f;
		float centerY = (_pageHeight - _marginTop - _productHeight) / 2f;
		pdf.addImageAt(image, x + centerX, y - centerY, _productWidth, _productHeight);
		pdf.addImageAt(image, x + centerX + _productWidth, y - centerY, _productWidth, _productHeight);
	}*/
	/*
	 * 
	 * For indexes the image is grabbed from the flash but sized to a hard coded size
	 * 
	 */
	/*protected void print(Observer observer, DesignDetails[] designs) throws DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			observer.logState("Processing : " +  designs[i].designId);
			if (y - _productHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF(designs[i], p, x, y - _productHeight);
			x = _marginLeft;
			y -= (2 + designs[i].productHeight);
			observer.setProgress((float)(i+1) / total);
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) +  ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}*/
	
	protected void print(Observer observer, DesignDetails[] designs) throws DocumentException, IOException, SQLException, NamingException, JSONException {
		HashMap<Integer, DesignJson> designJson;
		try (GenesysDB db = new GenesysDB(GenesysDB.getConnectionPool())) {
			designJson = db.getDesignJson(designs);
		}
		InitialContext context = new InitialContext();
		String propertiesFile = (String) context.lookup(Application.PropertiesFile);
		Properties prop = new Properties();
		InputStream input = new FileInputStream(propertiesFile);
		prop.load(input);
		float total = designs.length;
		File f = new File();
		JsonParser parser = new JsonParser();
		for(int i=0; i<designs.length; i++)
		{
			JsonObject config = parser.parse(designJson.get(designs[i].designId).configJson).getAsJsonObject();
			int template_id = config.get("templ_id").getAsInt();
			String name = "RGC_" + template_id;
			String settings = prop.getProperty(name);
			String zpl = convert.getFlashIndex(designJson.get(designs[i].designId).designJson, settings, template_id);
			f.addText(zpl);
			observer.setProgress((float)(i+1) / total);
		}
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(f, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) +  ".zpl");
		downloadResource.setMIMEType("application/zpl");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
		
	}
}
