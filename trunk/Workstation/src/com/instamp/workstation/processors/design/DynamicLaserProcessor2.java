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

public class DynamicLaserProcessor2 extends PrintProcessor {
	private float _pageHeight = Utilities.pointsToMillimeters(PageSize.A4
			.rotate().getHeight());
	private float _pageWidth = Utilities.pointsToMillimeters(PageSize.A4
			.rotate().getWidth());
	private float _marginLeft = 10f;
	private float _marginTop = 5f;
	private float _vSpacing = 2f;
	private float _hSpacing = 8f;
	private float _mWidth;
	private float _mHeight;
	private final String _name = "DynamicLaser 2";

	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;

	protected DynamicLaserProcessor2() {
		super("Dynamic Laser 2", "Print Designs", false);
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
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
		JSONObject j;
		try {
			j = new JSONObject(config);
			_pageHeight = (float) j
					.getDouble(PageSizeMarginProcessorConfig.PAGE_HEIGHT);
			_pageWidth = (float) j
					.getDouble(PageSizeMarginProcessorConfig.PAGE_WIDTH);
			_marginLeft = (float) j
					.getDouble(PageSizeMarginProcessorConfig.MARGIN_LEFT);
			_marginTop = (float) j
					.getDouble(PageSizeMarginProcessorConfig.MARGIN_TOP);
			_vSpacing = (float) j
					.getDouble(PageSizeMarginProcessorConfig.VERTICAL_SPACING);
			_hSpacing = (float) j
					.getDouble(PageSizeMarginProcessorConfig.HORIZONTAL_SPACING);
			_mWidth = (float) j
					.getDouble(PageSizeMarginProcessorConfig.IMAGE_HEIGHT);
			_mHeight = (float) j
					.getDouble(PageSizeMarginProcessorConfig.IMAGE_WIDTH);

			if (j.getString(PageSizeMarginProcessorConfig.MEASUREMENT).equals(
					"in")) {
				_pageHeight = Utilities.inchesToMillimeters(_pageHeight);
				_pageWidth = Utilities.inchesToMillimeters(_pageWidth);
				_marginLeft = Utilities.inchesToMillimeters(_marginLeft);
				_marginTop = Utilities.inchesToMillimeters(_marginTop);
				_vSpacing = Utilities.inchesToMillimeters(_vSpacing);
				_hSpacing = Utilities.inchesToMillimeters(_hSpacing);
				_mWidth = Utilities.inchesToMillimeters(_mWidth);
				_mHeight = Utilities.inchesToMillimeters(_mHeight);
			}
		} catch (JSONException e) {
			e.printStackTrace();
		}
		_config = config;
	}

	private void addDesigntoPDF(DesignDetails design, Pdf pdf, float x, float y, float width, float height)
			throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id="
				+ design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		pdf.addImageAt(image, x, y,width, height);
	}

	private float newHeight(DesignDetails design) {
		float ratioY, ratioX;

		ratioY = design.productFrameHeight / _mHeight;

		ratioX = design.productFrameWidth / _mWidth;

		// Returns the maximum height, if the image height exceeds that
		if ((ratioY > ratioX) && (ratioY > 1)) {
			return _mHeight;
		}
		else if (ratioX > 1) {
			return design.productFrameHeight / ratioX;
		} else {
			return design.productFrameHeight;
		}

	}

	private float newWidth(DesignDetails design) {
		float ratioY, ratioX;

		ratioY = design.productFrameHeight / _mHeight;

		ratioX = design.productFrameWidth / _mWidth;

		if ((ratioX > ratioY) && (ratioX > 1)) {
			return _mWidth;
		} else if (ratioY > 1) {
			return design.productFrameWidth / ratioY;
		} else {
			return design.productFrameWidth;
		}

	}

	protected void print(Observer observer, DesignDetails[] designs)
			throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(
				Utilities.millimetersToPoints(_pageWidth),
				Utilities.millimetersToPoints(_pageHeight));
		Pdf p = new Pdf(pageSize);
		final float marginy = _pageHeight - _marginTop;
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;

		
		//Default values in the event that no data is entered for max width or height, sets the maximum width and height equal to that of the page.
		if (_mWidth <= 0) { 
			_mWidth = _pageWidth;
		}
		if (_mHeight <= 0) {
			_mHeight = _pageHeight;
		}
		
		
		for (int i = 0; i < designs.length; i++) {
			float imageWidth = newWidth(designs[i]);
			float imageHeight = newHeight(designs[i]);
			
			//If the image width would put it outside of the page margins, bump it down a line
			if (x + imageWidth + 10 > _pageWidth) {
				y = y - _vSpacing - maxY;
				x = _marginLeft;
			}
			
			//If the image line would go off the page, add a new page
			if (y - imageHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}

			addDesigntoPDF(designs[i], p, x, y - imageHeight, imageWidth, imageHeight);
			x += imageWidth + _hSpacing;

			//Determines the height of the line
			if (imageHeight > maxY) {
				maxY = imageHeight;
			}
			observer.setProgress((float) (i + 1) / total, "Processing : "
					+ designs[i].designId);
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
