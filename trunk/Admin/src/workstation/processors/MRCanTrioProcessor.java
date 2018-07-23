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
import com.itextpdf.text.PageSize;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class MRCanTrioProcessor extends PrintProcessor {
	
	//DEFAULTS
	private final float _frameLineWidth = 0.0762f;
	private final float _orderSeperation = 19.225f;
	private float _pageHeight = 11f;
	private float _pageWidth = 8.5f;
	private float _marginLeft = 0.25f;
	private float _marginTop = 0.5f;
	private final String _name = "MRCANTrio";
	
	protected MRCanTrioProcessor() {
		super("MR Can Trio", "Print MR Can Trio Designs", false);
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
		pdf.addLineAt(_frameLineWidth, x, y, x + productFrameWidth, y, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y, x, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x + productFrameWidth, y, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
		pdf.addLineAt(_frameLineWidth, x, y + productFrameHeight, x + productFrameWidth, y + productFrameHeight, 0, 0, 0);
	}

	private void addDesigntoPDF(Design design1, Design design2, Design design3, Pdf pdf, float x, float y) throws DocumentException, IOException {
		float textPosition = y + 5f + design1.getProduct().getFrameHeight();
		pdf.addTextAt("Order # " + design1.getOrderItem().getExternalOrderId(), x, textPosition);
		x += 41.275f;
		pdf.addTextAt(design1.getDesignData().scene.colors.ink.name, x, textPosition);
		addImageToPDF(design1, pdf, x, y);
		x += design1.getProduct().getFrameWidth();
		if (design2 != null) {
			pdf.addTextAt(design2.getDesignData().scene.colors.ink.name, x, textPosition);
			addImageToPDF(design2, pdf, x, y);
			x += design2.getProduct().getFrameWidth();
			if (design3 != null) {
				pdf.addTextAt(design3.getDesignData().scene.colors.ink.name, x, textPosition);
				addImageToPDF(design3, pdf, x, y);
			}
		}
	}
	
	protected void print(Observer observer, Design[] designs) throws DocumentException, IOException {
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
		
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop - designs[0].getProduct().getFrameHeight();
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float total = designs.length;
		
		while (i < designs.length) {
			Design d1 = null, d2 = null, d3 = null;
			
			if (y <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
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
			y -= (_orderSeperation + d1.getProduct().getFrameHeight());
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
