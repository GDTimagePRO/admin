package workstation.processors;


import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

import model.Design;
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

public class PolymerProcessor extends PrintProcessor {

	//DEFAULTS
	private final float _imageSpacing = 5f;
	private float _pageHeight = 11f;
	private float _pageWidth = 8.5f;
	private float _marginLeft = 0.5f;
	private float _marginTop = 0.5f;
	private final String _name = "Polymer";
	
	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;

	
	protected PolymerProcessor() {
		super("Polymer", "Print Polymer Designs", false);
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
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=inverted.designs%2F" + design.getId() + "_hd.png");
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2, y + (productFrameHeight - productHeight) / 2, productWidth, productHeight);
	}

	protected void print(Observer observer, Design[] designs) throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize, 0, 0, 0);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			float productFrameHeight = designs[i].getProduct().getFrameHeight();
			float productFrameWidth = designs[i].getProduct().getFrameWidth();
			
			if (x + productFrameWidth + 10 > _pageWidth) {
				y = y - maxY - _imageSpacing;
				x = _marginLeft;
			}
			if (y - productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF(designs[i], p, x, y - productFrameHeight);
			x += productFrameWidth + _imageSpacing;
			
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

}
