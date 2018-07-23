package workstation.processors;


import java.io.IOException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import model.Customer;
import model.Design;
import model.ShippingInformation;
import workstation.util.Pdf;

import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Image;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class ShippingLabelProcessor extends PrintProcessor {

	// DEFAULTS
	private final float _imageSpacing = 5f;
	private final float _textSpacing = 3.5f;
	private final float _fontSize = 11f;
	private float _pageHeight = 279.4f;
	private float _pageWidth = 215.9f;
	private float _marginLeft = 7f;
	private float _marginTop = 14f;
	private final String _name = "MobileLabels";

	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;

	protected ShippingLabelProcessor() {
		super("Shipping Label", "Print shipping labels", true);
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}

	private void addDesign(Pdf pdf, Design design,
			ShippingInformation shipping, Customer customer, float x,
			float y) throws IOException, DocumentException {
		URL imageURL = new URL(getGenesysURL() + "/GetImage?id="
				+ design.getDesignImageId_Thumbnail());
		float textSpacing = Utilities.pointsToMillimeters(_fontSize);
		float imageW, imageH, logoW, logoH;
		Image image = Image.getInstance(imageURL);
		float _fontSize = this._fontSize;
		float stickerHeight = 25.4f;
		float stickerWidth = 66.75f;
		float tempFont = _fontSize;

		ArrayList<String> customerAddressArray = new ArrayList<String>();
		int lastIndex = 0;
		while (customer.getAddress().indexOf("\\n", lastIndex) != -1) {
			customerAddressArray.add(customer.getAddress().substring(lastIndex,
					customer.getAddress().indexOf("\\n", lastIndex)));
			lastIndex = 2 + customer.getAddress().indexOf("\\n", lastIndex);
		}
		if (lastIndex < customer.getAddress().length()) {
			customerAddressArray.add(customer.getAddress().substring(lastIndex,
					customer.getAddress().length()));
		}

		image.scaleToFit(
				Utilities.millimetersToPoints(stickerWidth
						- pdf.getStringWidth("Order # " + design.getOrderItem().getExternalOrderId(),
								_fontSize) - 5f),
				Utilities.millimetersToPoints(21f));
		imageH = Utilities.pointsToMillimeters(image.getScaledHeight());
		imageW = Utilities.pointsToMillimeters(image.getScaledWidth());

		Image logo = Image.getInstance(customer.getLogo());
		logo.scaleToFit(Utilities.millimetersToPoints(17f),
				Utilities.millimetersToPoints(19.95f));
		logoW = Utilities.pointsToMillimeters(logo.getScaledWidth());
		logoH = Utilities.pointsToMillimeters(logo.getScaledHeight());

		// First Sticker
		pdf.addImageAt(image, x + 0.8f, y - imageH - 1f);

		// Adds the administrator address to the top
		float textHeight = y;
		if (customerAddressArray.get(0) != null) {
			while (pdf.getStringWidth(customerAddressArray.get(0), tempFont) > (stickerWidth - imageW - 4f)) {
				tempFont -= 0.5f;
			}
			textHeight -= Utilities.pointsToMillimeters(tempFont);
			pdf.addTextAt(customerAddressArray.get(0), tempFont, x + imageW
					+ 2.8f, textHeight);
		}
		String column1 [] = {"Order # " + design.getOrderItem().getExternalOrderId(),  
				"Name: " + design.getProduct().getCode(),
				"Colors: " + design.getDesignData().scene.colors.ink.name};
		for (int i = 0; i < column1.length; i ++){
			tempFont = _fontSize;
			while (pdf.getStringWidth(column1[i], tempFont) > (stickerWidth - imageW - 4f)) {
				tempFont -= 0.5f;
			}
			textHeight -= (Utilities.pointsToMillimeters(tempFont) + 2);
			pdf.addTextAt(column1[i], tempFont, x
					+ imageW + 2.8f, textHeight);
		}

		
		
		// Second Sticker
		x += stickerWidth + 3.175f;

		for (int i = 0; i < customerAddressArray.size(); i++) {
			pdf.addTextAt(customerAddressArray.get(i), _fontSize,
					x + logoW + 3, y - (textSpacing + 2f
							)* (i + 1));
		}
		pdf.addImageAt(logo, x - 1f, y - logoH - 1f);

		// Third Column
		x += stickerWidth + 3.175f;
		textHeight = y;
		if (shipping != null){
			String column3 [] = {"Order # " + design.getOrderItem().getExternalOrderId(), "\n",
					shipping.getFirstName() + " " + shipping.getLastName(), 
				    "", "",
				    shipping.getCity() + " " + shipping.getStateProvince() + " "+ shipping.getZipPostalCode()};
			if (!shipping.getAddress1().isEmpty()) { 
				column3[3] = shipping.getAddress1();
			}
			if (!shipping.getAddress2().isEmpty()) { 
				column3[4] = shipping.getAddress2();
			}
			for (int i = 0; i < column3.length; i ++){
				tempFont = _fontSize - 0.5f;
				while (pdf.getStringWidth(column3[i], tempFont) > (stickerWidth - 4f)) {
					tempFont -= 0.5f;
				}
				if (!column3[i].isEmpty()){
					textHeight -= (Utilities.pointsToMillimeters(tempFont));
				}
				pdf.addTextAt(column3[i], tempFont, x
					 + 2.8f, textHeight);
			}
		}
		
		
		
	}

	@Override
	protected void print(Observer observer, Design[] designs)
			throws Exception {
		Rectangle pageSize = new Rectangle(
				Utilities.millimetersToPoints(_pageWidth),
				Utilities.millimetersToPoints(_pageHeight));
		Pdf pdf = new Pdf(pageSize);
		final float marginy = _pageHeight - _marginTop;
		float x = _marginLeft, y = marginy;
		float rowHeight = 25.4f;
		float total = designs.length;

		for (int i = 0; i < designs.length; i++) {

			if (y - rowHeight < 0f) {
				pdf.addNewPage();
				x = _marginLeft;
				y = marginy;
			}

			addDesign(pdf, designs[i], designs[i].getOrderItem().getShippingInformation(),
					designs[i].getOrderItem().getCustomer(), x, y);
			y -= rowHeight;

			observer.setProgress((float) (i + 1) / total, "Processing : "
					+ designs[i].getId());
		}

		pdf.close();
		StreamResource downloadResource = new StreamResource(pdf, _name + "_"
				+ new SimpleDateFormat("dd-MM-yy").format(new Date()) + ".pdf");
		observer.setProgress(1, "Done");
		downloadResource.setMIMEType("application/pdf");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}
