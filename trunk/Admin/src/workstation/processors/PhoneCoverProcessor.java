package workstation.processors;


import java.awt.Graphics2D;
import java.awt.image.BufferedImage;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

import javax.imageio.ImageIO;

import model.Design;
import workstation.processors.PageSizeMarginProcessorConfig.PageSizeMarginConfig;
import workstation.util.Pdf;

import com.google.gson.Gson;
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Image;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class PhoneCoverProcessor extends PrintProcessor {

	//DEFAULTS
	private final float _imageSpacing = 5f;
	private final float _textSpacing = 3.5f;
	private final float _fontSize = 10f;
	private float _pageHeight = 11f;
	private float _pageWidth = 8.5f;
	private final float _frameLineWidth = 0.0762f;
	private float _marginLeft = 0.5f;
	private float _marginTop = 0.5f;
	private final String _name = "DyeSublimation";
	
	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;
	
	protected PhoneCoverProcessor() {
		super("Dye Sublimation", "Print dye sublimation", false);
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
		BufferedImage img = ImageIO.read(imageUrl);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		BufferedImage resizedImage = new BufferedImage((int)((300 * productWidth) / 25.4), (int)((productHeight * 300) / 25.4), img.getType());
		Graphics2D g = resizedImage.createGraphics();
		g.drawImage(img, 0, 0, (int)((300 * productWidth) / 25.4), (int)((productHeight * 300) / 25.4), null);
		g.dispose();
		
		Image image = Image.getInstance(resizedImage, null);
		//Crop based on frame size
		if (productFrameWidth < productWidth || productFrameHeight < productHeight) {
			image.scaleAbsolute(Utilities.millimetersToPoints(productWidth), Utilities.millimetersToPoints(productHeight));
			float x1 = (productWidth - productFrameWidth) / 2;
			float y1 = (productHeight - productFrameHeight) / 2;
			float w = productFrameWidth;
			float h = productFrameHeight;
			image = pdf.cropImage(image, x1, y1, w, h);
			pdf.addImageAt(image, x, y, productFrameWidth, productFrameHeight);
		} else {
			pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2f, y + (productFrameHeight - productHeight) / 2f, productWidth, productHeight);
		}
		
		pdf.addLineAt(_frameLineWidth, x, y, x + productFrameWidth, y, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y, x, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x + productFrameWidth, y, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y + productFrameHeight, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addTextAt("Order # " + design.getOrderItem().getExternalOrderId(), _fontSize, 90f, x + productFrameWidth + _textSpacing, y + 4f);
		pdf.addTextAt("SKU: " + design.getProduct().getCode(), _fontSize, 90f, x + productFrameWidth + _textSpacing, y + pdf.getStringWidth("Order # " + design.getOrderItem().getExternalOrderId(), _fontSize) + 5f);
	}

	protected void print(Observer observer, Design[] designs) throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length + 1;
		
		for(int i=0; i<designs.length; i++)
		{
			float productFrameHeight = designs[i].getProduct().getFrameHeight();
			float productFrameWidth = designs[i].getProduct().getFrameWidth();
			
			if (x + productFrameWidth + 5f > _pageWidth) {
				y = y - maxY - 2;
				x = _marginLeft;
			}
			if (y - productFrameHeight < 0f) {
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
		observer.logState("Compressing");
		StreamResource downloadResource = new StreamResource(new Pdf.CompressedPdf(p), _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		observer.setProgress(1, "Done");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}
