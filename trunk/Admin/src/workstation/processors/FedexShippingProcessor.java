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

public class FedexShippingProcessor extends PrintProcessor {

	private final String _name = "FedEx_shipping";

	protected FedexShippingProcessor() {
		super("Fedex Shipping CSV", "Print shipping csv", true);
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
		addString(line, "Company Name");
		addString(line, "Customer Name");
		addString(line, "Address 1");
		addString(line, "Address 2");
		addString(line, "City");
		addString(line, "State");
		addString(line, "Zip");
		addString(line, "Phone");
		addString(line, "Email");
		addString(line, "Weight");
		line.append("Reference Information");
		return line.toString();
	}

	private void addShippingInfo(File f, OrderItem order) throws ParseException {
		ShippingInformation info = order.getShippingInformation();
		StringBuilder line = new StringBuilder();
		addString(line, info.getCompany());
		addString(line, info.getFirstName() + " " + info.getLastName());
		addString(line, info.getAddress1());
		addString(line, info.getAddress2());
		addString(line, info.getCity());
		addString(line, info.getStateProvince());
		addString(line, info.getZipPostalCode());
		addString(line, "");
		addString(line, info.getEmailAddress());
		int weight = 0;
		for (Design design : order.getDesigns()) {
			ProductConfigJson configJson = ProductConfigJson.getProductConfig(design.getProduct().getConfigJson());
			weight += configJson.weight;
		}
		addString(line, String.valueOf(weight));
		line.append("");
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
