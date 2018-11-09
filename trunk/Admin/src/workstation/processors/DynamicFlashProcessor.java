package workstation.processors;


import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

import model.Design;
import model.Design2;
import workstation.processors.PageSizeMarginProcessorConfig.PageSizeMarginConfig;
import workstation.util.Pdf;

import com.google.gson.Gson;
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Image;
import com.itextpdf.text.PageSize;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class DynamicFlashProcessor extends PrintProcessor {

	//DEFAULTS
	private final float _imageSpacing = 0.5f;
	private float _pageHeight = 11f;
	private float _pageWidth = 8.5f;
	private float _marginLeft = 0.5f;
	private float _marginTop = 0.5f;
	private final String _name = "DynamicFlash";
	private final float _frameLineWidth = 0.0762f;
	private final float _textSpacingColor = 3.5f;
	private final float _textSpacingId = 1.75f;
	private final float _fontSize = 6f;
	
	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;

	
	protected DynamicFlashProcessor() {
		super("Dynamic Flash", "Print Dynamic Flash Designs", false);
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
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
		Gson gson = new Gson();
		PageSizeMarginConfig configObject = gson.fromJson(config, PageSizeMarginConfig.class);
		_pageHeight = configObject.getPageHeight();
		_pageWidth = configObject.getPageWidth();
		_marginLeft = configObject.getMarginLeft();
		_marginTop = configObject.getMarginTop();
		
		if (configObject.getMeasurement().equals("in")) {
			_pageHeight = Utilities.inchesToMillimeters(_pageHeight);
			_pageWidth = Utilities.inchesToMillimeters(_pageWidth);
			_marginLeft = Utilities.inchesToMillimeters(_marginLeft);
			_marginTop = Utilities.inchesToMillimeters(_marginTop);
		}
		_config = config;
	}
	
	private void addDesigntoPDF(Design design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2f, y + (productFrameHeight - productHeight) / 2f, productWidth, productHeight);
		pdf.addLineAt(_frameLineWidth, x, y, x + productFrameWidth, y, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y, x, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x + productFrameWidth, y, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y + productFrameHeight, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addTextAt("Order # " + design.getOrderItem().getExternalOrderId(), _fontSize, 90f, x + productFrameWidth + _textSpacingId, y + 4f);
		pdf.addTextAt("Color " + design.getDesignData().scene.colors.ink.name, _fontSize, 90f, x + productFrameWidth + _textSpacingColor, y + 4f);
	}
	
	private void addDesigntoPDF2(Design2 design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + "designs/199142_hd.png"); //design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getFrameHeight(design.getProduct());
		float productFrameWidth = design.getFrameWidth(design.getProduct());
		float productHeight = design.getHeight(design.getProduct());
		float productWidth = design.getWidth(design.getProduct());
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2f, y + (productFrameHeight - productHeight) / 2f, productWidth, productHeight);
		pdf.addLineAt(_frameLineWidth, x, y, x + productFrameWidth, y, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y, x, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x + productFrameWidth, y, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y + productFrameHeight, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addTextAt("Order # " + design.getOrderItem(design.getOrder_item()), _fontSize, 90f, x + productFrameWidth + _textSpacingId, y + 4f);
		//pdf.addTextAt("Color " + design.getDesignData().scene.colors.ink.name, _fontSize, 90f, x + productFrameWidth + _textSpacingColor, y + 4f);
	}

	protected void print(Observer observer, Design[] designs) throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			float productFrameHeight = designs[i].getProduct().getFrameHeight();
			float productFrameWidth = designs[i].getProduct().getFrameWidth();
			
			if (x + productFrameWidth + 10 > _pageWidth) {
				y = y - maxY - _marginTop;
				maxY = 0;
				x = _marginLeft;
			}
			if (y - productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF(designs[i], p, x, y - productFrameHeight);
			x +=productFrameWidth + _marginLeft;
			
			if (productFrameHeight > maxY) {
				maxY = (float) productFrameHeight;
			}
			observer.setProgress((float)(i+1) / total, "Processing : " + designs[i].getId());
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

	@Override
	public Component getConfigUI2(List<Design2> designs) {
		if (configUI == null) {
			//configUI = new PageSizeMarginProcessorConfig(designs, this, _pageWidth, _pageHeight, _marginLeft, _marginTop);
			configUI.show();
		}
		return configUI;
	}

	@Override
	protected void print2(Observer observer, Design2[] designs) throws Exception {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			float productFrameHeight = designs[i].getFrameHeight(designs[i].getProduct());
			float productFrameWidth = designs[i].getFrameWidth(designs[i].getProduct());
			
			if (x + productFrameWidth + 10 > _pageWidth) {
				y = y - maxY - _marginTop;
				maxY = 0;
				x = _marginLeft;
			}
			if (y - productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF2(designs[i], p, x, y - productFrameHeight);
			x +=productFrameWidth + _marginLeft;
			
			if (productFrameHeight > maxY) {
				maxY = (float) productFrameHeight;
			}
			observer.setProgress((float)(i+1) / total, "Processing : " + designs[i].getDesign_id());
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}