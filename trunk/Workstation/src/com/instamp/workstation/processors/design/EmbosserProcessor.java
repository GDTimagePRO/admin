package com.instamp.workstation.processors.design;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;

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

public class EmbosserProcessor  extends PrintProcessor {
	
	//DEFAULTS
	private final float _cutLineShort = 0.5f;
	private final float _cutLineWidth = 0.0762f;
	private final float _spaceBetweenImagePairs = 1f;
	private float _pageHeight = Utilities.pointsToMillimeters(PageSize.A4.rotate().getHeight());
	private float _pageWidth = Utilities.pointsToMillimeters(PageSize.A4.rotate().getWidth());
	private float _marginLeft = 10f;
	private float _marginTop = 5f;
	private final String _name = "Embosser";

	protected EmbosserProcessor() {
		super("Embosser", "Print Embosser Designs", false);
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		return null;
	}

	private void addDesigntoPDF(DesignDetails design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_EmbosserM());
		Image image = Image.getInstance(imageUrl);
		pdf.addImageAt(image, x + (design.productFrameWidth - design.productWidth) / 2, y + (design.productFrameHeight - design.productHeight) / 2, design.productWidth, design.productHeight);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y, x + design.productFrameWidth - _cutLineShort, y);
		pdf.addLineAt(_cutLineWidth, x, y + _cutLineShort, x, y + design.productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + design.productFrameWidth, y + _cutLineShort, x + design.productFrameWidth, y + design.productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y + design.productFrameHeight, x + design.productFrameWidth - _cutLineShort, y + design.productFrameHeight);
	}
	
	private void addFlipDesigntoPDF(DesignDetails design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_EmbosserF());
		Image image = Image.getInstance(imageUrl);
		pdf.addImageAt(image, x + (design.productFrameWidth - design.productWidth) / 2, y + (design.productFrameHeight - design.productHeight) / 2, design.productWidth, design.productHeight);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y, x + design.productFrameWidth - _cutLineShort, y);
		pdf.addLineAt(_cutLineWidth, x, y + _cutLineShort, x, y + design.productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + design.productFrameWidth, y + _cutLineShort, x + design.productFrameWidth, y + design.productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y + design.productFrameHeight, x + design.productFrameWidth - _cutLineShort, y + design.productFrameHeight);
	}
	
	protected void print(Observer observer, DesignDetails[] designs) throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			if (x + designs[i].productFrameWidth + 10 > _pageWidth) {
				y = y - maxY;
				x = _marginLeft;
			}
			if (y - designs[i].productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF(designs[i], p, x, y - designs[i].productFrameHeight);
			x += designs[i].productFrameWidth + _spaceBetweenImagePairs;
			
			addFlipDesigntoPDF(designs[i], p, x, y - designs[i].productFrameHeight);
			x += designs[i].productFrameWidth + _spaceBetweenImagePairs;
			
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
