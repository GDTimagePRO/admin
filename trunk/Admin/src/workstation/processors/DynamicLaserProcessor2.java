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

public class DynamicLaserProcessor2 extends PrintProcessor {
	private float _pageHeight = 11f;
	private float _pageWidth = 8.5f;
	private float _marginLeft = 0.5f;
	private float _marginTop = 0.5f;
	private float _vSpacing = 2.5f;
	private float _hSpacing = 2.5f;
	private float _mWidth;
	private float _mHeight;
	private final String _name = "DynamicLaser 2";

	private PageSizeMarginProcessorConfig configUI = null;
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
		_marginTop = configObject.getMarginTop();
		_vSpacing = configObject.getVerticalSpacing();
		_hSpacing = configObject.getHorizontalSpacing();
		_mWidth = configObject.getImageWidth();
		_mHeight = configObject.getImageHeight();

		if (configObject.getMeasurement().equals("in")) {
			_pageHeight = Utilities.inchesToMillimeters(_pageHeight);
			_pageWidth = Utilities.inchesToMillimeters(_pageWidth);
			_marginLeft = Utilities.inchesToMillimeters(_marginLeft);
			_marginTop = Utilities.inchesToMillimeters(_marginTop);
			_vSpacing = Utilities.inchesToMillimeters(_vSpacing);
			_hSpacing = Utilities.inchesToMillimeters(_hSpacing);
			_mWidth = Utilities.inchesToMillimeters(_mWidth);
			_mHeight = Utilities.inchesToMillimeters(_mHeight);
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
		pdf.addImageAt(image, x
				+ (productFrameWidth - productWidth) / 2f, y
				+ (productFrameHeight - productHeight) / 2f,
				productWidth, productHeight);
	}

	private float newHeight(Design design) {
		float ratioY, ratioX;
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		
		ratioY = productFrameHeight / _mHeight;

		ratioX = productFrameWidth / _mWidth;

		if ((ratioY > ratioX) && (ratioY > 1)) {
			return _mHeight;
		} else if (ratioX > 1) {
			return productFrameHeight / ratioX;
		} else {
			return productFrameWidth;
		}

	}

	private float newWidth(Design design) {
		float ratioY, ratioX;
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		
		ratioY = productFrameHeight / _mHeight;

		ratioX = productFrameWidth / _mWidth;

		if ((ratioX > ratioY) && (ratioX > 1)) {
			return _mWidth;
		} else if (ratioY > 1) {
			return productFrameHeight / ratioX;
		} else {
			return productFrameWidth;
		}

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
			_mWidth = _pageWidth;
		}
		if (_mHeight <= 0) {
			_mHeight = _pageHeight;
		}
		for (int i = 0; i < designs.length; i++) {
			if (x + newWidth(designs[i]) + 10 > _pageWidth) {
				y = y - _vSpacing;
				x = _marginLeft;
			}
			if (y - newHeight(designs[i]) <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
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
}
