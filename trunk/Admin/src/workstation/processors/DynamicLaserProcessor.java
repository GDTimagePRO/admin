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

public class DynamicLaserProcessor extends PrintProcessor {

	//TODO: Remove massive code duplication between pdf processors
	
	//DEFAULTS
	private final float _cutLineShort = 0.5f;
	private final float _cutLineWidth = 0.0762f;
	private float _pageHeight = 11f;
	private float _pageWidth = 8.5f;
	private float _marginLeft = 0.5f;
	private float _marginTop = 0.5f;
	private final String _name = "DynamicLaser";
	
	private PageSizeMarginProcessorConfig configUI = null;
	private PageSizeMarginProcessorConfig2 configUI2 = null;
	private String _config = null;
	
	protected DynamicLaserProcessor() {
		super("Dynamic Laser", "Print Designs", false);
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
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y, x + productFrameWidth - _cutLineShort, y);
		pdf.addLineAt(_cutLineWidth, x, y + _cutLineShort, x, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + productFrameWidth, y + _cutLineShort, x + productFrameWidth, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y + productFrameHeight, x + productFrameWidth - _cutLineShort, y + productFrameHeight);
	}
	
	private void addDesigntoPDF2(Design2 design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + "designs/199142_hd.png"); //design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getFrameHeight(design.getProduct());
		float productFrameWidth = design.getFrameWidth(design.getProduct());
		float productHeight = design.getHeight(design.getProduct());
		float productWidth = design.getWidth(design.getProduct());
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2f, y + (productFrameHeight - productHeight) / 2f, productWidth, productHeight);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y, x + productFrameWidth - _cutLineShort, y);
		pdf.addLineAt(_cutLineWidth, x, y + _cutLineShort, x, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + productFrameWidth, y + _cutLineShort, x + productFrameWidth, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y + productFrameHeight, x + productFrameWidth - _cutLineShort, y + productFrameHeight);
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
				y = y - maxY;
				x = _marginLeft;
			}
			if (y - productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF(designs[i], p, x, y - productFrameHeight);
			x += productFrameWidth;
			
			if (productFrameHeight > maxY) {
				maxY = (float) productFrameHeight;
			}
			observer.setProgress((float)(i+1) / total, "Processing : " +  designs[i].getId());
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
		if (configUI2 == null) {
			configUI2 = new PageSizeMarginProcessorConfig2(designs, this, _pageWidth, _pageHeight, _marginLeft, _marginTop);
			configUI2.show();
		}
		return configUI2;
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
				y = y - maxY;
				x = _marginLeft;
			}
			if (y - productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF2(designs[i], p, x, y - productFrameHeight);
			x += productFrameWidth;
			
			if (productFrameHeight > maxY) {
				maxY = (float) productFrameHeight;
			}
			observer.setProgress((float)(i+1) / total, "Processing : " +  designs[i].getDesign_id());
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}
