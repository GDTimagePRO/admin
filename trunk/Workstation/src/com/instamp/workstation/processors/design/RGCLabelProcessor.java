package com.instamp.workstation.processors.design;

import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.URL;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.Properties;

import javax.naming.InitialContext;
import javax.naming.NamingException;

import org.json.JSONException;
import org.json.JSONObject;

import com.dekconsulting.jsontozpl.convert;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.instamp.workstation.Application;
import com.instamp.workstation.concurrency.JobManager.Observer;
import com.instamp.workstation.data.GenesysDB;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.data.GenesysDB.DesignJson;
import com.instamp.workstation.util.File;
import com.instamp.workstation.util.Pdf;
import com.instamp.workstation.util.Zip;
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Image;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

public class RGCLabelProcessor extends PrintProcessor {
	
	//DEFAULTS
	/*private float _marginLeft = 0f;
	private float _marginTop = 0f;
	private final String _name = "RGCAgent";
	private float _pageWidth = 56.44f;
	private float _pageHeight = 69.85f;
	
	
	private PageSizeProcessorConfig configUI = null;
	private String _config = null;*/
	
	private final String _name = "RGCAgent";
	
	protected RGCLabelProcessor() {
		super("RGC Agent ID", "Print RGC Agent ID Designs", false);
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		return null;
	}
	
	/*@Override
	public Component getConfigUI(DesignDetails[] designs) {
		if (configUI == null) {
			configUI = new PageSizeProcessorConfig(designs, this, _pageWidth, _pageHeight);
			configUI.show();
		}
		return configUI;
	}
	
	@Override
	public String saveConfig() { return _config; }
	
	@Override
	public void loadConfig(String config) {
		JSONObject j;
		try {
			j = new JSONObject(config);
			_pageHeight = (float) j.getDouble(PageSizeProcessorConfig.PAGE_HEIGHT);
			_pageWidth = (float) j.getDouble(PageSizeProcessorConfig.PAGE_WIDTH);
			
			if (j.getString(PageSizeProcessorConfig.MEASUREMENT).equals("in")) {
				_pageHeight = Utilities.inchesToMillimeters(_pageHeight);
				_pageWidth = Utilities.inchesToMillimeters(_pageWidth);			
			}
		} catch (JSONException e) {
			e.printStackTrace();
		}
		_config = config;
	}
	
	private void addDesigntoPDF(DesignDetails design, Pdf pdf, float x, float y) throws DocumentException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float centerX = (_pageWidth - _marginLeft - (design.productHeight * 2f)) / 2f;
		float centerY = (_pageHeight - _marginTop - design.productWidth) / 2f;
		pdf.addImageRotatedAt(image, x + centerX, y - design.productWidth - centerY, design.productWidth, design.productHeight, 90f);
		pdf.addImageRotatedAt(image, x+ centerX + design.productHeight, y - design.productWidth- centerY, design.productWidth, design.productHeight, 90f);
	}
	
	protected void print(Observer observer, DesignDetails[] designs) throws DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			observer.logState("Processing : " +  designs[i].designId);
			
			addDesigntoPDF(designs[i], p, x, y);
			x = _marginLeft;
			y = marginy;
			p.addNewPage();
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
		for(int i=0; i<designs.length; i++)
		{
			String settings = prop.getProperty("RGC_Label");
			String zpl = convert.getAgentLabel(designJson.get(designs[i].designId).designJson, settings);
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
