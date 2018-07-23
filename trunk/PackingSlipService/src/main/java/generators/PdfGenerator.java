package generators;

import java.io.IOException;
import java.io.OutputStream;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.LinkedHashMap;

import javax.servlet.ServletContext;

import webServices.Order;

import com.itextpdf.text.Document;
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.PageSize;
import com.itextpdf.text.Paragraph;
import com.itextpdf.text.Phrase;
import com.itextpdf.text.pdf.PdfPCell;
import com.itextpdf.text.pdf.PdfPTable;
import com.itextpdf.text.pdf.PdfWriter;
import com.itextpdf.text.Image;


public class PdfGenerator {

	@SuppressWarnings("unchecked")
	public void addOrder(Document doc, Image img, Order order) throws DocumentException
	{
		 PdfPTable titleTable = new PdfPTable(2);
         titleTable.setHorizontalAlignment(0);
         titleTable.setSpacingBefore(10);
         titleTable.setSpacingAfter(10);
         titleTable.getDefaultCell().setPaddingTop(0);
         titleTable.getDefaultCell().setBorder(0);
         Paragraph titleParagraph = new Paragraph();
         titleTable.addCell(img);
         PdfPCell titleCell = new PdfPCell();
         titleCell.setBorder(0);
         titleCell.setPaddingTop(20);
         titleCell.setPaddingLeft(20);
         titleParagraph.add(new Phrase("Picking Slip"));
         titleParagraph.add(new Phrase("\nOrder #: " + order.getId() + "\n"));
         titleParagraph.add(new Phrase(order.getCreatedOnUtc()));
         titleCell.addElement(titleParagraph);
         titleTable.addCell(titleCell);
         titleTable.setSpacingAfter(26.0f);
         doc.add(titleTable);
         PdfPTable infoTable = new PdfPTable(2);
         infoTable.setHorizontalAlignment(0);
         infoTable.setSpacingBefore(10);
         infoTable.setSpacingAfter(10);
         infoTable.getDefaultCell().setBorder(0);
         infoTable.addCell(new Phrase("Billing Information"));
         infoTable.addCell(new Phrase("Shipping Information"));
         if (order.getBillingAddress().getCompany() != null && !order.getBillingAddress().getCompany().isEmpty())
             infoTable.addCell(new Phrase("Company: " + order.getBillingAddress().getCompany()));
         else
             infoTable.addCell(new Phrase(""));
         if (order.getShippingAddress().getCompany() != null && !order.getShippingAddress().getCompany().isEmpty())
             infoTable.addCell(new Phrase("Company: " + order.getShippingAddress().getCompany()));
         else
             infoTable.addCell(new Phrase(""));

         infoTable.addCell(new Phrase("Name: " + order.getBillingAddress().getFirstName() + " " + order.getBillingAddress().getLastName()));
         infoTable.addCell(new Phrase("Name: " + order.getShippingAddress().getFirstName() + " " + order.getShippingAddress().getLastName()));
         infoTable.addCell(new Phrase("Phone: " + order.getBillingAddress().getPhoneNumber()));
         infoTable.addCell(new Phrase("Phone: " + order.getShippingAddress().getPhoneNumber()));
         infoTable.addCell(new Phrase("Address: " + order.getBillingAddress().getAddress1()));
         infoTable.addCell(new Phrase("Address: " + order.getShippingAddress().getAddress1()));
         
         if (order.getBillingAddress().getAddress2() != null && !order.getBillingAddress().getAddress2().isEmpty())
             infoTable.addCell(new Phrase("Address 2: " + order.getBillingAddress().getAddress2()));
         else
             infoTable.addCell(new Phrase(""));

         if (order.getShippingAddress().getAddress2() != null && !order.getShippingAddress().getAddress2().isEmpty())
             infoTable.addCell(new Phrase("Address 2: " + order.getShippingAddress().getAddress2()));
         else
             infoTable.addCell(new Phrase(""));

         infoTable.addCell(new Phrase(String.format("%s, %s %s", order.getBillingAddress().getCity(), order.getBillingAddress().getStateProvince(), order.getBillingAddress().getZipPostalCode())));
         infoTable.addCell(new Phrase(String.format("%s, %s %s", order.getShippingAddress().getCity(), order.getShippingAddress().getStateProvince(), order.getShippingAddress().getZipPostalCode())));
         infoTable.addCell(new Phrase(order.getBillingAddress().getCountry()));
         infoTable.addCell(new Phrase(order.getShippingAddress().getCountry()));

         infoTable.addCell(new Phrase(order.getPaymentMethod()));
         infoTable.addCell(new Phrase(order.getShippingMethod()));
         doc.add(infoTable);
         if (!order.getDiscountCoupon().isEmpty())
             doc.add(new Paragraph("Voucher: " + order.getDiscountCoupon()));

         PdfPTable itemTable = new PdfPTable(5);
         itemTable.setHeaderRows(0);
         itemTable.setSpacingBefore(10);
         itemTable.setWidthPercentage(100.0f);
         itemTable.addCell(new Phrase("Name"));
         itemTable.addCell(new Phrase("Category"));
         itemTable.addCell(new Phrase("SKU"));
         itemTable.addCell(new Phrase("Order Qty"));
         itemTable.addCell(new Phrase("Qty Shipped"));


         for (LinkedHashMap<String, String> i : order.getProducts().toArray(new LinkedHashMap[order.getProducts().size()]))
         {
        	 LinkedHashMap<String, String> item = i;
             if (item.containsKey("imageUrl"))
             {
                 PdfPCell nameParagraph = new PdfPCell();
                 nameParagraph.addElement(new Phrase(item.get("name")));
                 Image productImage = null;
                 try
                 {
                     productImage = Image.getInstance(new URL(item.get("imageUrl")));
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
                 itemTable.addCell(new Phrase(item.get("name")));
             }
             if (item.containsKey("categoryName"))
             {
                 itemTable.addCell(new Phrase(item.get("categoryName")));
             }
             else
             {
                 itemTable.addCell(new Phrase(""));
             }
             itemTable.addCell(new Phrase(item.get("manufacturerPartNumber")));
             itemTable.addCell(new Phrase(item.get("quantity")));
             itemTable.addCell(new Phrase(item.get("quantityshipped")));
         }
         doc.add(itemTable);
         
	}
	
	public void createPdf(OutputStream output, Order[] orders, ServletContext context) throws DocumentException, MalformedURLException, IOException {
		Image img = Image.getInstance(context.getRealPath("images/logo.gif"));
		Document document = new Document(PageSize.A4);
		PdfWriter.getInstance(document, output);
		document.open();
		for (int i = 0; i < orders.length; i++)
		{
			addOrder(document, img, orders[i]);
			if (i < orders.length)
				document.newPage();
		}
        
        
        document.close();
	}
}
