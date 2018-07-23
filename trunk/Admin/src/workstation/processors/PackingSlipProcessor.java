package workstation.processors;


import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import model.Customer;
import model.Design;
import model.OrderItem;
import model.ShippingInformation;
import workstation.util.Pdf;

import com.itextpdf.text.*;
import com.itextpdf.text.pdf.*;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class PackingSlipProcessor extends PrintProcessor {

	// DEFAULTS
	private final float _imageSpacing = 5f;
	private final float _textSpacing = 3.5f;
	private final float _fontSize = 11f;
	private float _pageHeight = 279.4f;
	private float _pageWidth = 215.9f;
	private float _marginLeft = 7f;
	private float _marginTop = 14f;
	private final String _name = "PackingSlip";

	private PageSizeMarginProcessorConfig configUI = null;
	private String _config = null;

	protected PackingSlipProcessor() {
		super("Packing Slip", "Print packing slips", true);
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}
	
	private class PSFooter extends PdfPageEventHelper 
	{
		 public Phrase address;
		 public Phrase email;
		 
		 public PSFooter() 
		 {
			 address = new Phrase("ABC Inc. 123 Xyz Ave, Jkl, ON");
			 email = new Phrase("123@abc.xyz");
		 }
		 
		 public PSFooter( String nameAddress, String email ) 
		 {
			 this.address = new Phrase(nameAddress);
			 this.email = new Phrase(email);
		 }
		 
		 public void onEndPage(PdfWriter writer, Document document) 
		 {	 
			 ColumnText.showTextAligned(writer.getDirectContent(), Element.ALIGN_RIGHT, email, document.right(), document.bottom() - 10, 0);
			 ColumnText.showTextAligned(writer.getDirectContent(), Element.ALIGN_LEFT, address, document.left(), document.bottom() - 10, 0);
		 }
	}
	
	public void addOrder(Pdf doc, Image img, OrderItem order) throws DocumentException
	{
		 //logo placement
		 PdfPTable logoTable = new PdfPTable(1);
         logoTable.setHorizontalAlignment(0);
         logoTable.setSpacingBefore(10);
         logoTable.setSpacingAfter(10);
         logoTable.getDefaultCell().setPaddingTop(0);
         logoTable.getDefaultCell().setBorder(0);
         logoTable.getDefaultCell().setFixedHeight(100f);
         logoTable.addCell(img);
         logoTable.setSpacingAfter(10f);
         doc.add(logoTable);
         //end; logo placement
         
         //shipping and order info
         PdfPTable infoTable = new PdfPTable(2);
         infoTable.setHorizontalAlignment(0);
         infoTable.setSpacingBefore(10);
         infoTable.setSpacingAfter(10);
         //infoTable.addCell(new Phrase("Billing Information"));
         
         //shipping info
         PdfPCell shippingCell =  new PdfPCell();
         shippingCell.setBorder(0);
         Paragraph shippingParagraph = new Paragraph();
         shippingParagraph.add(new Phrase("Shipping Information\n"));
         //if (order.getShippingInformation().getCompany() != null && !order.getShippingInformation().getCompany().isEmpty())
         //    infoTable.addCell(new Phrase("Company: " + order.getShippingInformation().getCompany()));
         //else
         //    infoTable.addCell(new Phrase(""));
         if (order.getShippingInformation().getCompany() != null && !order.getShippingInformation().getCompany().isEmpty())
        	 shippingParagraph.add(new Phrase(order.getShippingInformation().getCompany() + "\n"));
         else
        	 shippingParagraph.add(new Phrase(""));

         //infoTable.addCell(new Phrase("Name: " + order.getShippingInformation().getFirstName() + " " + order.getShippingInformation().getLastName()));
         shippingParagraph.add(new Phrase(order.getShippingInformation().getFirstName() + " " + order.getShippingInformation().getLastName() + "\n"));
         //infoTable.addCell(new Phrase("Address: " + order.getShippingInformation().getAddress1()));
         shippingParagraph.add(new Phrase(order.getShippingInformation().getAddress1() + "\n"));
         
         //if (order.getShippingInformation().getAddress2() != null && !order.getShippingInformation().getAddress2().isEmpty())
         //    infoTable.addCell(new Phrase("Address 2: " + order.getShippingInformation().getAddress2()));
         //else
         //    infoTable.addCell(new Phrase(""));

         if (order.getShippingInformation().getAddress2() != null && !order.getShippingInformation().getAddress2().isEmpty())
        	 shippingParagraph.add(new Phrase(order.getShippingInformation().getAddress2() + "\n"));
         else
        	 shippingParagraph.add(new Phrase(""));

         //infoTable.addCell(new Phrase(String.format("%s, %s %s", order.getShippingInformation().getCity(), order.getShippingInformation().getStateProvince(), order.getShippingInformation().getZipPostalCode())));
         shippingParagraph.add(new Phrase(String.format("%s, %s %s\n", order.getShippingInformation().getCity(), order.getShippingInformation().getStateProvince(), order.getShippingInformation().getZipPostalCode())));
         //infoTable.addCell(new Phrase(order.getShippingInformation().getCountry()));
         shippingParagraph.add(new Phrase(order.getShippingInformation().getCountry()));

         //infoTable.addCell(new Phrase(order.getPaymentMethod()));
         //infoTable.addCell(new Phrase(order.getShippingInformation().getShippingMethod()));
         shippingCell.addElement(shippingParagraph);
         infoTable.addCell(shippingCell);
         //end; shipping info
         
         //order info
         PdfPCell orderCell = new PdfPCell();
         orderCell.setVerticalAlignment(Element.ALIGN_TOP);
         Paragraph orderParagraph = new Paragraph();
         orderCell.setBorder(0);
         orderParagraph.add(new Phrase("Packing Slip"));
         orderParagraph.add(new Phrase("\nOrder #: " + order.getId() + "\n"));
         orderParagraph.add(new Phrase(order.getDateCreated().toString()));
         orderCell.addElement(orderParagraph);
         infoTable.addCell(orderCell);
         //end; order info
         
         //end; shipping and order info
         
         doc.add(infoTable);
         
         //if (!order.getDiscountCoupon().isEmpty())
         //   doc.add(new Paragraph("Voucher: " + order.getDiscountCoupon()));

         PdfPTable itemTable = new PdfPTable(5);
         itemTable.setHeaderRows(0);
         itemTable.setSpacingBefore(10);
         itemTable.setWidthPercentage(100.0f);
         itemTable.addCell(new Phrase("Name"));
         itemTable.addCell(new Phrase("Category"));
         itemTable.addCell(new Phrase("SKU"));
         itemTable.addCell(new Phrase("Order Qty"));
         itemTable.addCell(new Phrase("Qty Shipped"));


         for (Design item : order.getDesigns())
         {
             if (item.getDesignImageId_Thumbnail() != null)
             {
                 PdfPCell nameParagraph = new PdfPCell();
                 nameParagraph.addElement(new Phrase(item.getProduct().getCode()));
                 Image productImage = null;
                 try
                 {
                     productImage = Image.getInstance(new URL(getGenesysURL() + "/GetImage?id=" + item.getDesignImageId_Thumbnail()));
                     productImage.setSpacingBefore(5);
                     nameParagraph.addElement(productImage);
                 }
                 catch (Exception e)
                 {
                     //Logging?
                 }
                 itemTable.addCell(nameParagraph);
             }
             else
             {
                 itemTable.addCell(new Phrase(item.getProduct().getCode()));
             }
                 itemTable.addCell(new Phrase(item.getProduct().getProductsCategory().getName()));
             itemTable.addCell(new Phrase(order.getBarcode().getBarcode().getBarcode()));
             itemTable.addCell(new Phrase("1"));
             itemTable.addCell(new Phrase(" "));
         }
         doc.add(itemTable);
                  
	}

	@Override
	protected void print(Observer observer, Design[] designs)
			throws Exception {
		Pdf pdf = new Pdf(PageSize.A4);
		float total = designs.length;
		Customer customer;
		String logoPath;
		Image img;
		String address;
		String[] parts;
		PSFooter footer = new PSFooter();
		for (int i = 0; i < designs.length; i++) {
			
			
			if( designs[i].getOrderItem().getExternalSystemName().contains("Redemption") ) {
				customer = designs[i].getProduct().getCustomer();
			} else {
				customer = designs[i].getOrderItem().getCustomer();
			}
			
			logoPath = customer.getLogo();
			img = Image.getInstance(logoPath);
			address = new String("");
			parts = customer.getAddress().split("\\\\n");
			for( int j = 1; j < parts.length; j++ ) {
				System.out.println(parts[j]);
				address = address + " " + parts[j]; 
			}
			//address = customer.getAddress().replace("\\n", " ");
			footer.address = new Phrase(address);
			footer.email = new Phrase(customer.getEmailAddress());
			pdf.writer.setPageEvent(footer);
			addOrder(pdf, img, designs[i].getOrderItem());
			pdf.addNewPage();
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
