package workstation.processors;


import java.io.IOException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Arrays;
import java.util.Comparator;
import java.util.Date;
import java.util.List;

import model.Design;
import workstation.util.Pdf;

import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Image;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class TrioProcessor extends PrintProcessor {
	
	//DEFAULTS
	private final float _lineWidth = 0.2f;
	private float _pageHeight = 140f;
	private float _pageWidth = 108f;
	private float _marginLeft = 10f;
	private float _marginTop = 10f;
	private final String _name = "Trio";
	
	protected TrioProcessor() {
		super("Trio", "Print Trio Designs", false);
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}
	
	private void addImageToPDF(Design design, Pdf pdf, float x, float y) throws DocumentException, IOException {
		URL imageUrl1 = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image1 = Image.getInstance(imageUrl1);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		
		pdf.addImageAt(image1, x + (productFrameWidth - productWidth) / 2, y + (productFrameHeight - productHeight) / 2, productWidth, productHeight);
		pdf.addLineAt(_lineWidth, x, y, x + productFrameWidth, y, 0, 0, 0);
		pdf.addLineAt(_lineWidth, x, y, x, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_lineWidth, x + productFrameWidth, y, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_lineWidth, x, y + productFrameHeight, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
	}

	private void addDesigntoPDF(Design design1, Design design2, Design design3, Pdf pdf, float x, float y) throws DocumentException, IOException {
		pdf.addTextAt("Order # " + design1.getOrderItem().getExternalOrderId(), 15f, 61.641f, 130.292f);
		float productFrameHeight = design1.getProduct().getFrameHeight();
		
		addImageToPDF(design1, pdf, 7.422f, 126.883f - productFrameHeight);
		pdf.addTextAt(design1.getDesignData().scene.colors.ink.name, 57f, 120f);
		
		if (design2 != null) {
			addImageToPDF(design2, pdf, 56f, 92.907f - productFrameHeight);
			pdf.addTextAt(design2.getDesignData().scene.colors.ink.name, 10f, 65f);
			if (design3 != null) {
				addImageToPDF(design3, pdf, 7.422f, 56.249f - productFrameHeight);
				pdf.addTextAt(design3.getDesignData().scene.colors.ink.name, 57f, 25f);
			}
		}
	}

	protected void print(Observer observer, Design[] designs) throws DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		int i = 0;
		Arrays.sort(designs, new Comparator<Design>() {
			@Override
			public int compare(Design arg0, Design arg1) {
					if (arg0.getOrderItem().getId() != arg1.getOrderItem().getId()) {
						return arg0.getOrderItem().getId() - arg1.getOrderItem().getId();
					}
					return arg0.getId() - arg1.getId();
			}
		});
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float total = designs.length;
		
		while (i < designs.length) {
			Design d1 = null, d2 = null, d3 = null;
			
			d1 = designs[i];
			observer.logState("Processing : " +  d1.getOrderItem().getId());
			i++;
			if (i < designs.length && d1.getOrderItem().getId() == designs[i].getOrderItem().getId()) {
				d2 = designs[i];
				i++;
				if (i < designs.length && d1.getOrderItem().getId() == designs[i].getOrderItem().getId()) {
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
