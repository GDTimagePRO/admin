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
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

public class TrioIndexProcessor extends PrintProcessor {
	
	//DEFAULTS
	private final float _lineWidth = 0.5f;
	private final float _imageScale = 4.7f;
	private float _marginLeft = 10f;
	private float _marginTop = 10f;
	private final String _name = "TrioIndex";
	public static float _productWidth = 51.214f;
	public static float _productHeight = 17.859f;
	private float _pageWidth = 54.45f;
	private float _pageHeight = 86.2f;
	
	protected TrioIndexProcessor() {
		super("Trio Index", "Print Trio Index Designs", true);
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		return null;
	}

	private void addImageToPDF(DesignDetails design, Pdf pdf, float x, float y) throws DocumentException, IOException {
		URL imageUrl1 = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image1 = Image.getInstance(imageUrl1);
		pdf.addImageAt(image1, x, y, design.productWidth, design.productHeight, _imageScale);
	}

	private void addDesigntoPDF(DesignDetails design1, DesignDetails design2, DesignDetails design3, Pdf pdf, float x, float y) throws DocumentException, IOException {
		pdf.addTextAt("Order # " + design1.orderItemExtOrderId, 10f, 5f, 82.2f);
		
		pdf.addLineAt(_lineWidth, 5f, 81.2f, 49.45f, 81.2f, 0, 0, 0);
		pdf.addLineAt(_lineWidth, 49.45f, 81.2f, 49.45f, 6f, 0, 0, 0);
		pdf.addLineAt(_lineWidth, 49.45f, 6f, 5f, 6f, 0, 0, 0);
		pdf.addLineAt(_lineWidth, 5f, 6f, 5f, 81.2f, 0, 0, 0);
		
		addImageToPDF(design1, pdf, 16.43f, 78.2f - (design1.productHeight*(_imageScale / 10f)));
		pdf.addTextAt("1", 12f, 7f, 76.2f);
		
		if (design2 != null) {
			addImageToPDF(design2, pdf, 16.43f, 53.61f -  (design1.productHeight*(_imageScale / 10f)));
			pdf.addTextAt("2", 12f, 7f, 45.6f);
			if (design3 != null) {
				addImageToPDF(design3, pdf, 16.43f, 29.02f -  (design1.productHeight*(_imageScale / 10f)));
				pdf.addTextAt("3", 12f, 7f, 15f);
			}
		}
	}

	protected void print(Observer observer, DesignDetails[] designs) throws DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
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
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float total = designs.length;
		
		while (i < designs.length) {
			DesignDetails d1 = null, d2 = null, d3 = null;
			
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
			y = marginy;
			p.addNewPage();
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
