package workstation.processors;


import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
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
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}

	private void addDesigntoPDF(Design design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_EmbosserM());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		pdf.addRectangleUnder(productFrameWidth, productFrameHeight, x, y, 0, 0, 0);
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2, y + (productFrameHeight - productHeight) / 2, productWidth, productHeight);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y, x + productFrameWidth - _cutLineShort, y);
		pdf.addLineAt(_cutLineWidth, x, y + _cutLineShort, x, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + productFrameWidth, y + _cutLineShort, x + productFrameWidth, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y + productFrameHeight, x + productFrameWidth - _cutLineShort, y + productFrameHeight);
	}
	
	private void addFlipDesigntoPDF(Design design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_EmbosserF());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2, y + (productFrameHeight - productHeight) / 2, productWidth, productHeight);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y, x + productFrameWidth - _cutLineShort, y);
		pdf.addLineAt(_cutLineWidth, x, y + _cutLineShort, x, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + productFrameWidth, y + _cutLineShort, x + productFrameWidth, y + productFrameHeight - _cutLineShort);
		pdf.addLineAt(_cutLineWidth, x + _cutLineShort, y + productFrameHeight, x + productFrameWidth - _cutLineShort, y + productFrameHeight);
	}
	
	protected void print(Observer observer, Design[] designs) throws MalformedURLException, DocumentException, IOException {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			float productFrameHeight = designs[i].getProduct().getFrameHeight();
			float productFrameWidth = designs[i].getProduct().getFrameWidth();
			
			if (x + productFrameWidth + 10 > _pageWidth) {
				y = y - maxY;
				x = _marginLeft;
			}
			if (y - productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF(designs[i], p, x, y - productFrameHeight);
			x += productFrameWidth + _spaceBetweenImagePairs;
			
			addFlipDesigntoPDF(designs[i], p, x, y - productFrameHeight);
			x += productFrameWidth + _spaceBetweenImagePairs;
			
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
