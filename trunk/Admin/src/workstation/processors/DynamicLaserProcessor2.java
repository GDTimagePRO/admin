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

public class DynamicLaserProcessor2 extends PrintProcessor {
	private float _pageHeight = 11f;
	private float _pageWidth = 8.5f;
	private float _marginLeft = 0.5f;
	private float _marginRight = 0.5f;
	private float _marginTop = 0.5f;
	private float _marginBottom = 0.5f;
	private float _vSpacing = 0.5f;
	private float _hSpacing = 0.5f;
	private float _mWidth = 100.0f;
	private float _mHeight = 100.0f;
	private final String _name = "DynamicLaser 2";

	private PageSizeMarginProcessorConfig configUI = null;
	private PageSizeMarginProcessorConfig2 configUI2 = null;
	private String _config = null;

	protected DynamicLaserProcessor2() {
		super("Dynamic Laser 2", "Print Designs", false);
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		if (configUI == null) {
			configUI = new PageSizeMarginProcessorConfig(designs, this,
					_pageWidth, _pageHeight, _marginLeft, _marginTop,
					_vSpacing, _hSpacing);
			configUI.show();
		}
		return configUI;
	}

	@Override
	public String saveConfig() {
		return _config;
	}

	@Override
	public void loadConfig(String config) {
		Gson gson = new Gson();
		PageSizeMarginConfig configObject = gson.fromJson(config, PageSizeMarginConfig.class);
		_pageHeight = configObject.getPageHeight();
		_pageWidth = configObject.getPageWidth();
		_marginLeft = configObject.getMarginLeft();
		_marginRight = _marginLeft;
		_marginTop = configObject.getMarginTop();
		_marginBottom = _marginTop;
		_vSpacing = configObject.getVerticalSpacing();
		_hSpacing = _vSpacing;
		_mWidth = configObject.getImageWidth();
		_mHeight = _mWidth;

		if (configObject.getMeasurement().equals("in")) {
			_pageHeight = Utilities.inchesToMillimeters(_pageHeight);
			_pageWidth = Utilities.inchesToMillimeters(_pageWidth);
			_marginLeft = Utilities.inchesToMillimeters(_marginLeft);
			_marginRight = Utilities.inchesToMillimeters(_marginRight);
			_marginTop = Utilities.inchesToMillimeters(_marginTop);
			_marginBottom = Utilities.inchesToMillimeters(_marginBottom);
			_vSpacing = Utilities.inchesToMillimeters(_vSpacing);
			_hSpacing = Utilities.inchesToMillimeters(_hSpacing);
		}
		_config = config;
	}

	private void addDesigntoPDF(Design design, Pdf pdf, float x, float y)
			throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id="
				+ design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		image.scaleToFit(Utilities.millimetersToPoints(_hSpacing),
				Utilities.millimetersToPoints(_vSpacing));
		pdf.addImageAt(image, x, y,
				newWidth(design), newHeight(design));
	}
	
	private void addDesigntoPDF2(Design2 design, Pdf pdf, float x, float y)
			throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id="
				 + "designs/199142_hd.png"); //design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getFrameHeight(design.getProduct());
		float productFrameWidth = design.getFrameWidth(design.getProduct());
		float productHeight = design.getHeight(design.getProduct());
		float productWidth = design.getWidth(design.getProduct());
		image.scaleToFit(Utilities.millimetersToPoints(_hSpacing),
				Utilities.millimetersToPoints(_vSpacing));
		pdf.addImageAt(image, x, y,
				newWidth2(design), newHeight2(design));
	}

	private float newHeight(Design design) {
		float productFrameHeight = design.getProduct().getFrameHeight();
		return productFrameHeight * (_mHeight / 100);
	}

	private float newWidth(Design design) {
		float productFrameWidth = design.getProduct().getFrameWidth();
		return productFrameWidth * (_mWidth / 100);
	}
	
	private float newHeight2(Design2 design) {
		float productFrameHeight = design.getFrameHeight(design.getProduct());
		return productFrameHeight * (_mHeight / 100);
	}

	private float newWidth2(Design2 design) {
		float productFrameWidth = design.getFrameWidth(design.getProduct());
		return productFrameWidth * (_mWidth / 100);
	}

	protected void print(Observer observer, Design[] designs)
			throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(
				Utilities.millimetersToPoints(_pageWidth),
				Utilities.millimetersToPoints(_pageHeight));
		Pdf p = new Pdf(pageSize);
		final float marginy = _pageHeight - _marginTop;
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;

		if (_mWidth <= 0) {
			_mWidth = 100;
		}
		if (_mHeight <= 0) {
			_mHeight = 100;
		}
		
		for (int i = 0; i < designs.length; i++) {
			if (_pageWidth - (x + newWidth(designs[i])) < _marginRight) {
				y = y - maxY - _vSpacing;
				x = _marginLeft;
				maxY = 0;
			}
			if (y - newHeight(designs[i]) < _marginBottom) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
				maxY = 0;
			}

			addDesigntoPDF(designs[i], p, x, y - newHeight(designs[i]));
			x += newWidth(designs[i]) + _hSpacing;

			if (newHeight(designs[i]) > maxY) {
				maxY = (float) newHeight(designs[i]);
			}
			observer.setProgress((float) (i + 1) / total, "Processing : "
					+ designs[i].getId());
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_"
				+ new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

	@Override
	public Component getConfigUI2(List<Design2> designs) {
		if (configUI2 == null) {
			configUI2 = new PageSizeMarginProcessorConfig2(designs, this,
					_pageWidth, _pageHeight, _marginLeft, _marginTop,
					_vSpacing, _hSpacing);
			configUI2.show();
		}
		return configUI2;
	}

	@Override
	protected void print2(Observer observer, Design2[] designs) throws Exception {
		Rectangle pageSize = new Rectangle(
				Utilities.millimetersToPoints(_pageWidth),
				Utilities.millimetersToPoints(_pageHeight));
		Pdf p = new Pdf(pageSize);
		final float marginy = _pageHeight - _marginTop;
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;

		if (_mWidth <= 0) {
			_mWidth = 100;
		}
		if (_mHeight <= 0) {
			_mHeight = 100;
		}
		
		for (int i = 0; i < designs.length; i++) {
			if (_pageWidth - (x + newWidth2(designs[i])) < _marginRight) {
				y = y - maxY - _vSpacing;
				x = _marginLeft;
				maxY = 0;
			}
			if (y - newHeight2(designs[i]) < _marginBottom) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
				maxY = 0;
			}

			addDesigntoPDF2(designs[i], p, x, y - newHeight2(designs[i]));
			x += newWidth2(designs[i]) + _hSpacing;

			if (newHeight2(designs[i]) > maxY) {
				maxY = (float) newHeight2(designs[i]);
			}
			observer.setProgress((float) (i + 1) / total, "Processing : "
					+ designs[i].getDesign_id());
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_"
				+ new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}
}
