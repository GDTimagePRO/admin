package workstation.processors;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

import org.apache.poi.hssf.usermodel.HSSFSheet;

import workstation.util.File;
import workstation.util.XLS;
import model.Design;
import model.OrderItem;
import model.ProductConfigJson;
import model.ShippingInformation;

import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;

import concurrency.JobManager.Observer;

public class XLSShippingProcessor extends PrintProcessor {

	private final String _name = "Stamps_com_shipping";
	
	protected XLSShippingProcessor() {
		super("Stamps.com XLS", "Print shipping xls", true);
	}
	
	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}
		
	private void addHeader(XLS x, HSSFSheet sheet) {
		x.writeCell(sheet, "OrderId");
		x.writeCell(sheet, "OrderDate");
		x.writeCell(sheet, "Status");
		x.writeCell(sheet, "Mailpiece");
		x.writeCell(sheet, "Mailclass");
		x.writeCell(sheet, "TrackingService");
		x.writeCell(sheet, "PackageValue");
		x.writeCell(sheet, "Weight");
		x.writeCell(sheet, "Length");
		x.writeCell(sheet, "Width");
		x.writeCell(sheet, "Height");
		x.writeCell(sheet, "PrintedMessage");
		x.writeCell(sheet, "RecepientFullName");
		x.writeCell(sheet, "RecepientFirstName");
		x.writeCell(sheet, "RecepientLastName");
		x.writeCell(sheet, "RecepientCompany");
		x.writeCell(sheet, "RecepientAddress1");
		x.writeCell(sheet, "RecepientAddress2");
		x.writeCell(sheet, "RecepientCity");
		x.writeCell(sheet, "RecepientState");
		x.writeCell(sheet, "RecepientPostalCode");
		x.writeCell(sheet, "RecepientCountry");
		x.writeCell(sheet, "RecepientEmail");
	}
	
	private void addShippingInfo(XLS x, HSSFSheet sheet, OrderItem order) throws ParseException {
		ShippingInformation info = order.getShippingInformation();
		x.createNewRow(sheet);
		String orderId = String.valueOf(order.getExternalOrderId());
		x.writeCell(sheet, orderId);
		SimpleDateFormat format = new SimpleDateFormat("MM/dd/yyyy");
		x.writeCell(sheet, format.format(order.getDateCreated()));
		x.writeCell(sheet, "");
		x.writeCell(sheet, "Thick Envelopes");
		x.writeCell(sheet, "First-Class Mail");
		x.writeCell(sheet, "");
		x.writeCell(sheet, "");
		int weight = 0;
		for (Design design : order.getDesigns()) {
			ProductConfigJson configJson = ProductConfigJson.getProductConfig(design.getProduct().getConfigJson());
			weight += configJson.weight;
		}
		x.writeCell(sheet, weight);
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

	}
	

	@Override
	protected void print(Observer observer, Design[] designs) throws Exception {
		XLS x = new XLS();
		HSSFSheet sheet = x.createNewWorksheet("Shipping");
		int total = designs.length;
		addHeader(x, sheet);
		addShippingInfo(x, sheet, designs[0].getOrderItem());
		for (int i = 1; i < designs.length; i++) {
			if (i < designs.length && designs[i-1].getOrderItem().getId() != designs[i].getOrderItem().getId()) {
				addShippingInfo(x, sheet, designs[i].getOrderItem());
			}
			observer.setProgress((float) (i + 1) / total, "Processing : " + designs[i].getId());
		}
		StreamResource downloadResource = new StreamResource(x, _name + ".xls");
		observer.setProgress(1, "Done");
		downloadResource.setMIMEType("application/vnd.ms-excel");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}
