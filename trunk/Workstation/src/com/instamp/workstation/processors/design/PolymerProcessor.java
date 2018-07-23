package com.instamp.workstation.processors.design;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;

import org.json.JSONException;
import org.json.JSONObject;

import com.instamp.workstation.concurrency.JobManager.Observer;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.util.Pdf;
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Image;
import com.itextpdf.text.PageSize;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

public class PolymerProcessor extends PrintProcessor {

	//DEFAULTS
	private final float _imageSpacing = 5f;
	private float _pageHeight = Utilities.pointsToMillimeters(PageSize.A4.rotate().getHeight());
	private float _pageWidth = Utilities.pointsToMillimeters(PageSize.A4.rotate().getWidth());
	private float _marginLeft = 10f;
	private float _marginTop = 5f;
	private final String _name = "Polymer";
	
	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;

	
	protected PolymerProcessor() {
		super("Polymer", "Print Polymer Designs", false);
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		if (configUI == null) {
			configUI = new PageSizeMarginProcessorConfig(designs, this, _pageWidth, _pageHeight, _marginLeft, _marginTop);
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
			_pageHeight = (float) j.getDouble(PageSizeMarginProcessorConfig.PAGE_HEIGHT);
			_pageWidth = (float) j.getDouble(PageSizeMarginProcessorConfig.PAGE_WIDTH);
			_marginLeft = (float) j.getDouble(PageSizeMarginProcessorConfig.MARGIN_LEFT);
			_marginTop = (float) j.getDouble(PageSizeMarginProcessorConfig.MARGIN_TOP);
			
			if (j.getString(PageSizeMarginProcessorConfig.MEASUREMENT).equals("in")) {
				_pageHeight = Utilities.inchesToMillimeters(_pageHeight);
				_pageWidth = Utilities.inchesToMillimeters(_pageWidth);
				_marginLeft = Utilities.inchesToMillimeters(_marginLeft);
				_marginTop = Utilities.inchesToMillimeters(_marginTop);
			}
		} catch (JSONException e) {
			e.printStackTrace();
		}
		_config = config;
	}
	
	private void addDesigntoPDF(DesignDetails design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=inverted.designs%2F" + design.designId + "_hd.png");
		Image image = Image.getInstance(imageUrl);
		pdf.addImageAt(image, x + (design.productFrameWidth - design.productWidth) / 2, y + (design.productFrameHeight - design.productHeight) / 2, design.productWidth, design.productHeight);
	}

	protected void print(Observer observer, DesignDetails[] designs) throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize, 0, 0, 0);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			if (x + designs[i].productFrameWidth + 10 > _pageWidth) {
				y = y - maxY - _imageSpacing;
				x = _marginLeft;
			}
			if (y - designs[i].productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF(designs[i], p, x, y - designs[i].productFrameHeight);
			x += designs[i].productFrameWidth + _imageSpacing;
			
			if (designs[i].productFrameHeight > maxY) {
				maxY = (float) designs[i].productFrameHeight;
			}
			observer.setProgress((float)(i+1) / total, "Processing : " +  designs[i].designId);
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}