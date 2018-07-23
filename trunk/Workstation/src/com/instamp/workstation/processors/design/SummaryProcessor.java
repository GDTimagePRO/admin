package com.instamp.workstation.processors.design;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;

import org.apache.poi.hssf.usermodel.HSSFSheet;

import com.instamp.workstation.concurrency.JobManager.Observer;
import com.instamp.workstation.data.GenesysDB.DesignDetails;
import com.instamp.workstation.util.XLS;
import com.vaadin.server.StreamResource;
import com.vaadin.ui.Component;


//TODO: Add all columns from data table

public class SummaryProcessor extends DesignProcessor {

	protected SummaryProcessor() {
		super("Summary Report", "Create Summary Report");
	}

	@Override
	public Component getConfigUI(DesignDetails[] designs) {
		return null;
	}

	@Override
	protected void run(Observer observer, DesignDetails[] designs) {
		XLS xls = new XLS();
		int i = 1;
		int total = designs.length;
		
		HashMap<String, HSSFSheet> sheets = new HashMap<String, HSSFSheet>();
		HSSFSheet summarySheet = xls.createNewWorksheet("Summary Report", "Submit Time", "Order ID", "Design ID", "Product", "Color");
		
		for (DesignDetails d : designs) {
			observer.logState( "Processing : " +  d.designId);
			xls.writeCell(summarySheet, d.orderItemDateCreated);
			xls.writeCell(summarySheet, d.orderItemExtOrderId);
			xls.writeCell(summarySheet, d.designId);
			xls.writeCell(summarySheet, d.productLongName);
			xls.writeCell(summarySheet, d.designColors.ink.name);
			xls.createNewRow(summarySheet);
			
			if (!sheets.keySet().contains(d.productCategoryName)) {
				HSSFSheet sheet = xls.createNewWorksheet(d.productCategoryName, "Submit Time", "Order ID", "Design ID", "Product", "Color");
				sheets.put(d.productCategoryName, sheet);
			}
			
			HSSFSheet sheet = sheets.get(d.productCategoryName);
			xls.writeCell(sheet, d.orderItemDateCreated);
			xls.writeCell(sheet, d.orderItemExtOrderId);
			xls.writeCell(sheet, d.designId);
			xls.writeCell(sheet, d.productLongName);
			xls.writeCell(sheet, d.designColors.ink.name);
			xls.createNewRow(sheet);
			
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
