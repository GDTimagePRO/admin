package workstation.processors;


import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

import model.Design;
import model.Design2;
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

public class EmbosserIndexProcessor extends PrintProcessor {

	//DEFAULTS
	private final float _spaceBetweenTextImage = 2f;
	private final float _spaceBetweenImagePairs = 1f;
	private float _pageHeight = Utilities.pointsToMillimeters(PageSize.A4.rotate().getHeight());
	private float _pageWidth = Utilities.pointsToMillimeters(PageSize.A4.rotate().getWidth());
	private float _marginLeft = 10f;
	private float _marginTop = 5f;
	private final String _name = "EmbosserIndex";
	
	protected EmbosserIndexProcessor() {
		super("Embosser Index", "Print Embosser Index Designs", true);
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}

	private void addDesigntoPDF(Design design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getProduct().getFrameHeight();
		float productFrameWidth = design.getProduct().getFrameWidth();
		float productHeight = design.getProduct().getHeight();
		float productWidth = design.getProduct().getWidth();
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2f, y + (productFrameHeight - productHeight) / 2f, productWidth, productHeight);
		pdf.addTextAt("Order # " + design.getOrderItem().getExternalOrderId(), 8f, 90f, x + productFrameWidth + _spaceBetweenTextImage, y + 4f);
	}
	
	private void addDesigntoPDF2(Design2 design, Pdf pdf, float x, float y) throws DocumentException, MalformedURLException, IOException {
		URL imageUrl = new URL(getGenesysURL() + "/GetImage?id=" + "designs/199142_hd.png"); //design.getDesignImageId_HD());
		Image image = Image.getInstance(imageUrl);
		float productFrameHeight = design.getFrameHeight(design.getProduct());
		float productFrameWidth = design.getFrameWidth(design.getProduct());
		float productHeight = design.getHeight(design.getProduct());
		float productWidth = design.getWidth(design.getProduct());
		pdf.addImageAt(image, x + (productFrameWidth - productWidth) / 2f, y + (productFrameHeight - productHeight) / 2f, productWidth, productHeight);
		pdf.addTextAt("Order # " + design.getOrderItem(design.getOrder_item()), 8f, 90f, x + productFrameWidth + _spaceBetweenTextImage, y + 4f);
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
			x += (productFrameWidth + _spaceBetweenImagePairs)*2;
			
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

	@Override
	public Component getConfigUI2(List<Design2> designs) {
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	protected void print2(Observer observer, Design2[] designs) throws Exception {
		Rectangle pageSize = new Rectangle(Utilities.millimetersToPoints(_pageWidth), Utilities.millimetersToPoints(_pageHeight));
		final float marginy = _pageHeight - _marginTop;
		Pdf p = new Pdf(pageSize);
		float x = _marginLeft, y = marginy;
		float maxY = 0;
		float total = designs.length;
		
		for(int i=0; i<designs.length; i++)
		{
			float productFrameHeight = designs[i].getFrameHeight(designs[i].getProduct());
			float productFrameWidth = designs[i].getFrameWidth(designs[i].getProduct());
			
			if (x + productFrameWidth + 10 > _pageWidth) {
				y = y - maxY;
				x = _marginLeft;
			}
			if (y - productFrameHeight <= 2) {
				p.addNewPage();
				x = _marginLeft;
				y = marginy;
			}
			
			addDesigntoPDF2(designs[i], p, x, y - productFrameHeight);
			x += (productFrameWidth + _spaceBetweenImagePairs)*2;
			
			if (productFrameHeight > maxY) {
				maxY = (float) productFrameHeight;
			}
			observer.setProgress((float)(i+1) / total, "Processing : " +  designs[i].getDesign_id());
		}
		p.close();
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(p, _name + "_" + new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}