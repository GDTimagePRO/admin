package workstation.processors;



import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

import model.Design;
import model.OrderItem;
import model.ProductConfigJson;
import model.ShippingInformation;

import org.apache.poi.hssf.usermodel.HSSFSheet;

import workstation.util.XLS;






import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;


//TODO: Add all columns from data table

public class SummaryProcessor extends DesignProcessor {

	protected SummaryProcessor() {
		super("Summary Report", "Create Summary Report");
	}

	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}
	
	private void addShippingInfo(XLS x, HSSFSheet sheet, OrderItem order) throws ParseException {
		ShippingInformation info = order.getShippingInformation();
		String orderId = String.valueOf(order.getExternalOrderId());
		int idLen = orderId.length();
		if ( !order.getExternalSystemName().equals("Redemption") ) {
			idLen = orderId.length();
			x.writeCell(sheet, orderId.substring(0,idLen-3) + "-" + orderId.substring(idLen-3, idLen) );
		} else {
			x.writeCell(sheet, orderId);
		}
		String gdtID = String.valueOf(order.getId());
		x.writeCell(sheet, gdtID);
		SimpleDateFormat format = new SimpleDateFormat("MM/dd/yyyy");
		x.writeCell(sheet, format.format(order.getDateCreated()));
		x.writeCell(sheet, "");
		x.writeCell(sheet, "Thick Envelopes");
		x.writeCell(sheet, "First-Class Mail");
		x.writeCell(sheet, "");
		x.writeCell(sheet, "");
		/*int weight = 0;
		for (Design design : order.getDesigns()) {
			ProductConfigJson configJson = ProductConfigJson.getProductConfig(design.getProduct().getConfigJson());
			weight += configJson.weight;
		}*/
		x.writeCell(sheet, "");
		x.writeCell(sheet, "");
		x.writeCell(sheet, "");
		x.writeCell(sheet, "");
		x.writeCell(sheet, "");
		x.writeCell(sheet, info.getFirstName() + " " + info.getLastName());
		x.writeCell(sheet, info.getFirstName());
		x.writeCell(sheet, info.getLastName());
		x.writeCell(sheet, info.getCompany());
		x.writeCell(sheet, info.getAddress1());
		x.writeCell(sheet, info.getAddress2());
		x.writeCell(sheet, info.getCity());
		x.writeCell(sheet, info.getStateProvince());
		x.writeCell(sheet, info.getZipPostalCode());
		x.writeCell(sheet, info.getCountry());
		x.writeCell(sheet, info.getEmailAddress());
		x.createNewRow(sheet);
	}

	@Override
	protected void run(Observer observer, List<EntityItem<Design>> designs) {
		XLS xls = new XLS();
		int i = 1;
		int total = designs.size();
		List<Long> processedOrders = new ArrayList<Long>();
		
		HashMap<String, HSSFSheet> sheets = new HashMap<String, HSSFSheet>();
		HSSFSheet summarySheet = xls.createNewWorksheet("Summary Report", "Submit Time", "Order ID", "Design ID", "Product", "Color");
		HSSFSheet shippingSheet = xls.createNewWorksheet(
			"Shipping Info", "OrderID", "GDT ID", "OrderDate", "Status", "Mailpiece", "Mailclass", "TrackingService",
			"PackageValue", "Weight", "Length", "Width", "Height", "PrintedMessage", "Fullname", "Firstname",
			"Lastname", "Company", "Address1", "Address2", "City", "State/Province", "Zip/Postal Code", "Country", "Email"  
														);
		
		for (EntityItem<Design> ed : designs) {
			Design d = ed.getEntity();
			observer.logState("Processing : " +  d.getId());
			//Main worksheet
			xls.writeCell(summarySheet, d.getOrderItem().getDateCreated()); 
			String orderId = String.valueOf(d.getOrderItem().getExternalOrderId());
			if ( !d.getOrderItem().getExternalSystemName().equals("Redemption") ) {
				int idLen = orderId.length();
				xls.writeCell(summarySheet, orderId.substring(0,idLen-3) + "-" + orderId.substring(idLen-3, idLen) );
			} else {
				xls.writeCell(summarySheet, orderId);
			}
			xls.writeCell(summarySheet, d.getId());
			if (d.getProduct() != null) {
				xls.writeCell(summarySheet, d.getProduct().getLongName());
			} else {
				xls.writeCell(summarySheet, "");
			}
			xls.writeCell(summarySheet, d.getDesignData().scene.colors.ink.name);
			xls.createNewRow(summarySheet);
			
			//if an order hasn't already been given a spot on the shipping table 
			if ( !(processedOrders.contains(d.getOrderItem().getExternalOrderId())) ) {
				processedOrders.add(d.getOrderItem().getExternalOrderId());
				try{
					addShippingInfo(xls, shippingSheet, d.getOrderItem());
				} catch( ParseException e) {
					//NOOP
				}
			}
			
			if (d.getProduct() != null) { //creates a new worksheet for each type of product
				if (!sheets.keySet().contains(d.getProduct().getProductsCategory().getName())) {
					HSSFSheet sheet = xls.createNewWorksheet(d.getProduct().getProductsCategory().getName(), "Submit Time", "Order ID", "Design ID", "Product", "Color");
					sheets.put(d.getProduct().getProductsCategory().getName(), sheet);
				}
				
				HSSFSheet sheet = sheets.get(d.getProduct().getProductsCategory().getName());
				xls.writeCell(sheet, d.getOrderItem().getDateCreated());
				orderId = String.valueOf(d.getOrderItem().getExternalOrderId());
				if ( !d.getOrderItem().getExternalSystemName().equals("Redemption") ) {
					int idLen = orderId.length();
					xls.writeCell(sheet, orderId.substring(0,idLen-3) + "-" + orderId.substring(idLen-3, idLen) );
				} else {
					xls.writeCell(sheet, orderId);
				}
				
				xls.writeCell(sheet, d.getId());
				xls.writeCell(sheet, d.getProduct().getLongName());
				xls.writeCell(sheet, d.getDesignData().scene.colors.ink.name);
				xls.createNewRow(sheet);
			}
			
			observer.setProgress((float)(i) / total);
			i++;
		}
		observer.setProgress(1, "Done");
		StreamResource downloadResource = new StreamResource(xls, "Summary-" + new SimpleDateFormat("dd-MM-yy").format(new Date()) +  ".xls");
		downloadResource.setMIMEType("application/vnd.ms-excel");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
		cleanup();
	}

}
