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
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

public class PhoneCoverProcessor extends PrintProcessor {

	//DEFAULTS
	private final float _imageSpacing = 5f;
	private final float _textSpacing = 3.5f;
	private final float _fontSize = 10f;
	private float _pageHeight = 431.8f;
	private float _pageWidth = 279.4f;
	private final float _frameLineWidth = 0.0762f;
	private float _marginLeft = 5f;
	private float _marginTop = 5f;
	private final String _name = "DyeSublimation";
	
	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;
	
	protected PhoneCoverProcessor() {
		super("dye sublimation", "Print dye sublimation", false);
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
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		
		//Crop based on frame size
		if (design.productFrameWidth < design.productWidth || design.productFrameHeight < design.productHeight) {
			image.scaleAbsolute(Utilities.millimetersToPoints(design.productWidth), Utilities.millimetersToPoints(design.productHeight));
			float x1 = (design.productWidth - design.productFrameWidth) / 2;
			float y1 = (design.productHeight - design.productFrameHeight) / 2;
			float w = design.productFrameWidth;
			float h = design.productFrameHeight;
			image = pdf.cropImage(image, x1, y1, w, h);
			pdf.addImageAt(image, x, y, design.productFrameWidth, design.productFrameHeight);
		} else {
			pdf.addImageAt(image, x + (design.productFrameWidth - design.productWidth) / 2f, y + (design.productFrameHeight - design.productHeight) / 2f, design.productWidth, design.productHeight);
		}
		
		pdf.addLineAt(_frameLineWidth, x, y, x + design.productFrameWidth, y, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y, x, y + design.productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x + design.productFrameWidth, y, x + design.productFrameWidth, y + design.productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y + design.productFrameHeight, x + design.productFrameWidth, y + design.productFrameHeight, 0, 0, 0);
		pdf.addTextAt("Order # " + design.orderItemExtOrderId, _fontSize, 90f, x + design.productFrameWidth + _textSpacing, y + 4f);
		pdf.addTextAt("SKU: " + design.productCode, _fontSize, 90f, x + design.productFrameWidth + _textSpacing, y + pdf.getStringWidth("Order # " + design.orderItemExtOrderId, _fontSize) + 5f);
	}

	protected void print(Observer observer, DesignDetails[] designs) throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length + 1;
		
		for(int i=0; i<designs.length; i++)
		{
			if (x + designs[i].productFrameWidth + 5f > _pageWidth) {
				y = y - maxY - 2;
				x = _marginLeft;
			}
			if (y - designs[i].productFrameHeight < 0f) {
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
		observer.logState("Compressing");
		StreamResource downloadResource = new StreamResource(new Pdf.CompressedPdf(p), _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		observer.setProgress(1, "Done");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}
