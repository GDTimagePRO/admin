package workstation.processors;

import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Map;
import java.util.Set;

import workstation.util.Pdf;

import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Element;
import com.itextpdf.text.Image;
import com.itextpdf.text.PageSize;
import com.itextpdf.text.Paragraph;
import com.itextpdf.text.Phrase;
import com.itextpdf.text.pdf.PdfPCell;
import com.itextpdf.text.pdf.PdfPTable;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import model.Customer;
import model.Design;
import model.Design2;
import model.OrderItem;
import concurrency.JobManager.Observer;

import com.google.gson.Gson;
import com.google.gson.JsonParser;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;

public class ProductionSheetProcessor extends PrintProcessor {

	private final String _name = "ProductionSheet";
	
	protected ProductionSheetProcessor() {
		super("Production Sheet", "Print order options for production purposes", true);
	}
	
	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
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
         if (order.getShippingInformation().getCompany() != null && !order.getShippingInformation().getCompany().isEmpty())
        	 shippingParagraph.add(new Phrase(order.getShippingInformation().getCompany() + "\n"));
         else
        	 shippingParagraph.add(new Phrase(""));

         shippingParagraph.add(new Phrase(order.getShippingInformation().getFirstName() + " " + order.getShippingInformation().getLastName() + "\n"));
         shippingParagraph.add(new Phrase(order.getShippingInformation().getAddress1() + "\n"));
         
         if (order.getShippingInformation().getAddress2() != null && !order.getShippingInformation().getAddress2().isEmpty())
        	 shippingParagraph.add(new Phrase(order.getShippingInformation().getAddress2() + "\n"));
         else
        	 shippingParagraph.add(new Phrase(""));

         shippingParagraph.add(new Phrase(String.format("%s, %s %s\n", order.getShippingInformation().getCity(), order.getShippingInformation().getStateProvince(), order.getShippingInformation().getZipPostalCode())));
         shippingParagraph.add(new Phrase(order.getShippingInformation().getCountry()));

         shippingCell.addElement(shippingParagraph);
         infoTable.addCell(shippingCell);
         //end; shipping info
         
         //order info
         PdfPCell orderCell = new PdfPCell();
         orderCell.setVerticalAlignment(Element.ALIGN_TOP);
         Paragraph orderParagraph = new Paragraph();
         orderCell.setBorder(0);
         orderParagraph.add(new Phrase("Production Sheet"));
         orderParagraph.add(new Phrase("\nOrder #: " + order.getId() + "\n"));
         orderParagraph.add(new Phrase(order.getDateCreated().toString()));
        
         
         JsonParser parser = new JsonParser();
         String optionsJSON = order.getExternalOrderOptions();
         if(optionsJSON != null && optionsJSON.length() > 0) {
        	 JsonElement element = parser.parse(optionsJSON);
             JsonObject obj = element.getAsJsonObject();
             Set<Map.Entry<String, JsonElement>> entries = obj.entrySet();
             
             for( Map.Entry<String, JsonElement> entry : entries ) {
            	 orderParagraph.add("\n" + entry.getKey() + ": ");
            	 orderParagraph.add(entry.getValue().getAsString());
             }   
         }
         
         orderCell.addElement(orderParagraph);
         infoTable.addCell(orderCell);
         //end; order info
         //end; shipping and order info
         
         doc.add(infoTable);
         
         for (Design item : order.getDesigns()) {
        	//order options
             PdfPTable displayTable = new PdfPTable(2);
             displayTable.setWidthPercentage(100);
             displayTable.setHorizontalAlignment(0);
             displayTable.setSpacingBefore(10);
             displayTable.setSpacingAfter(10);
             
             //order options
             PdfPTable optionsTable = new PdfPTable(2);
             optionsTable.getDefaultCell().setBorder(0);
             optionsTable.addCell(new Phrase("Order #"));
             optionsTable.addCell(new Phrase((Integer.toString(order.getId()))));
             
             optionsTable.addCell(new Phrase("Order Qty"));
             if( order.getShippingInformation().getQuantity() != 0 )
            	 optionsTable.addCell(new Phrase(order.getShippingInformation().getQuantity()));
             else
            	 optionsTable.addCell(new Phrase("1"));
             optionsTable.addCell(new Phrase("Model/SKU #"));
             optionsTable.addCell(new Phrase(order.getBarcode().getBarcode().getBarcode()));
             
             //custom options
             parser = new JsonParser();
             optionsJSON = item.getExternalDesignOptions();
             System.out.println(optionsJSON);
             if(optionsJSON != null  && optionsJSON.length() > 0) {
            	 JsonElement element = parser.parse(optionsJSON);
                 JsonObject obj = element.getAsJsonObject();
                 Set<Map.Entry<String, JsonElement>> entries = obj.entrySet();
                 
                 for( Map.Entry<String, JsonElement> entry : entries ) {
                	 optionsTable.addCell(entry.getKey());
                	 optionsTable.addCell(entry.getValue().getAsString());
                 }   
             }
             //end; custom options
             
             displayTable.addCell(optionsTable);
             //end; order options
             
             //product image
             Image productImage = null;
             try {
            	 productImage = Image.getInstance(new URL(getGenesysURL() + "/GetImage?id=" + item.getDesignImageId_HD()));
             } catch(Exception e) {}
             productImage.setSpacingBefore(5);
             displayTable.addCell(productImage);
             //end; product image
             
             doc.add(displayTable);
         }
         
                  	
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
		for (int i = 0; i < designs.length; i++) {
			customer = designs[i].getOrderItem().getCustomer();
			
			logoPath = customer.getLogo();
			img = Image.getInstance(logoPath);
			address = new String("");
			parts = customer.getAddress().split("\\\\n");
			for( int j = 1; j < parts.length; j++ ) {
				System.out.println(parts[j]);
				address = address + " " + parts[j]; 
			}
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

	@Override
	public Component getConfigUI2(List<Design2> designs) {
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	protected void print2(Observer observer, Design2[] designs) throws Exception {
		// TODO Auto-generated method stub
		
	}
}
