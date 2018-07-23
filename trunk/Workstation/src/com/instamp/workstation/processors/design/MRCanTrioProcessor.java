package com.instamp.workstation.processors.design;

import java.io.IOException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Arrays;
import java.util.Comparator;
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

public class MRCanTrioProcessor extends PrintProcessor {
	
	//DEFAULTS
	private final float _frameLineWidth = 0.0762f;
	private final float _orderSeperation = 19.225f;
	private float _pageHeight = Utilities.pointsToMillimeters(PageSize.A4.getHeight());
	private float _pageWidth = Utilities.pointsToMillimeters(PageSize.A4.getWidth());
	private float _marginLeft = 10f;
	private float _marginTop = 24.225f;
	private final String _name = "MRCANTrio";
	
	protected MRCanTrioProcessor() {
		super("MR Can Trio", "Print MR Can Trio Designs", false);
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		return null;
	}
	
	private void addImageToPDF(DesignDetails design, Pdf pdf, float x, float y) throws DocumentException, IOException {
		URL imageUrl1 = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image1 = Image.getInstance(imageUrl1);
		pdf.addImageAt(image1, x + (design.productFrameWidth - design.productWidth) / 2, y + (design.productFrameHeight - design.productHeight) / 2, design.productWidth, design.productHeight);
		pdf.addLineAt(_frameLineWidth, x, y, x + design.productFrameWidth, y, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y, x, y + design.productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x + design.productFrameWidth, y, x + design.productFrameWidth, y + design.productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y + design.productFrameHeight, x + design.productFrameWidth, y + design.productFrameHeight, 0, 0, 0);
	}

	private void addDesigntoPDF(DesignDetails design1, DesignDetails design2, DesignDetails design3, Pdf pdf, float x, float y) throws DocumentException, IOException {
		float textPosition = y + 5f + design1.productFrameHeight;
		pdf.addTextAt("Order # " + design1.orderItemExtOrderId, x, textPosition);
		x += 41.275f;
		pdf.addTextAt(design1.designColors.ink.name, x, textPosition);
		addImageToPDF(design1, pdf, x, y);
		x += design1.productFrameWidth;
		if (design2 != null) {
			pdf.addTextAt(design2.designColors.ink.name, x, textPosition);
			addImageToPDF(design2, pdf, x, y);
			x += design2.productFrameWidth;
			if (design3 != null) {
				pdf.addTextAt(design3.designColors.ink.name, x, textPosition);
				addImageToPDF(design3, pdf, x, y);
			}
		}
	}
	
	protected void print(Observer observer, DesignDetails[] designs) throws DocumentException, IOException {
		int i = 0;
		Arrays.sort(designs, new Comparator<DesignDetails>() {
			@Override
			public int compare(DesignDetails arg0, DesignDetails arg1) {
					if (arg0.orderItemId != arg1.orderItemId) {
						return arg0.orderItemId - arg1.orderItemId;
					}
					return arg0.designId - arg1.designId;
			}
		});
		
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop - designs[0].productFrameHeight;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float total = designs.length;
		
		while (i < designs.length) {
			DesignDetails d1 = null, d2 = null, d3 = null;
			
			if (y <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			d1 = designs[i];
			observer.logState("Processing : " +  d1.orderItemId);
			i++;
			if (i < designs.length && d1.orderItemId == designs[i].orderItemId) {
				d2 = designs[i];
				i++;
				if (i < designs.length && d1.orderItemId == designs[i].orderItemId) {
					d3 = designs[i];
					i++;
				}
			} 
			
			addDesigntoPDF(d1, d2, d3, p, x, y);
			x = _marginLeft;
			y -= (_orderSeperation + d1.productFrameHeight);
			observer.setProgress((float)(i+1) / total);
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}
}
