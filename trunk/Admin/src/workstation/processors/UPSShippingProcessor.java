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

public class UPSShippingProcessor extends PrintProcessor {

	private final String _name = "UPS_Shipping";
	
	protected UPSShippingProcessor() {
		super("UPS Shipping CSV", "Print shipping csv", true);
	}
	
	@Override
	public Component getConfigUI(List<EntityItem<Design>> designs) {
		return null;
	}
	
	private void addString(StringBuilder sb, String s) {
		sb.append(s);
		sb.append(",");
	}
	
	private String createHeader() {
		StringBuilder line = new StringBuilder();
		addString(line, "CustomerID");
		addString(line, "CompanyOrName");
		addString(line, "Attention");
		addString(line, "Address1");
		addString(line, "Address2");
		addString(line, "CityOrTown");
		addString(line, "StateProvinceCounty");
		addString(line, "PostalCode");
		addString(line, "CountryTerritory");
		addString(line, "Telephone");
		addString(line, "EmailAddress");
		addString(line, "ServiceType");
		addString(line, "PackageType");
		addString(line, "NumberofPackages");
		addString(line, "ShipmentActualWeight");
		addString(line, "BillingOption");
		addString(line, "DescriptionOfGoods");
		line.append("Reference1");
		return line.toString();
	}
	
	private void addShippingInfo(File f, OrderItem order) throws ParseException {
		ShippingInformation info = order.getShippingInformation();
		StringBuilder line = new StringBuilder();
		//CustomerID
		String orderId = String.valueOf(order.getExternalOrderId());
		addString(line, orderId);
		//CompanyOrName
		addString(line, info.getFirstName() + " " + info.getLastName());
		//Attention
		addString(line, info.getFirstName() + " " + info.getLastName());
		//Address1
		addString(line, info.getAddress1());
		//Address2
		addString(line, info.getAddress2());
		//CityOrTown
		addString(line, info.getCity());
		//StateProvinceCounty
		addString(line, info.getStateProvince());
		//PostalCode
		addString(line, info.getZipPostalCode());
		//CountryTerritory
		if(info.getCountry().equals("United States")) {
			addString(line, "US");
		} else if (info.getCountry().equals("Canada")) {
			addString(line, "CA");
		} else {
			addString(line, info.getCountry());
		}
		//Telephone
		addString(line, "");
		//EmailAddress
		addString(line, info.getEmailAddress());
		//ServiceType
		addString(line, "USL");
		//PackageType
		addString(line, "CP");
		//NumberOfPackages
		addString(line, "1");
		//ShipmentActualWeight
		addString(line, "1");
		//BillingOption
		addString(line, "PP");
		//DescriptionOfGoods
		addString(line, "Stamps");
		//Reference1
		line.append("OrderID: " + orderId);
		f.addLine(line.toString());
	}
	
	@Override
	protected void print(Observer observer, Design[] designs) throws Exception {
		File f = new File();
		int total = designs.length;
		f.addLine(createHeader());
		addShippingInfo(f, designs[0].getOrderItem());
		for (int i = 1; i < designs.length; i++) {
			if (i < designs.length && designs[i-1].getOrderItem().getId() != designs[i].getOrderItem().getId()) {
				addShippingInfo(f,  designs[i].getOrderItem());
			}
			observer.setProgress((float) (i + 1) / total, "Processing : " + designs[i].getId());
		}
		StreamResource downloadResource = new StreamResource(f, _name + ".csv");
		observer.setProgress(1, "Done");
		downloadResource.setMIMEType("text/csv");
		downloadResource.setCacheTime(0);
		observer.submitResult(downloadResource);
	}

}
