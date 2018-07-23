package com.instamp.workstation.util;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.Date;
import java.util.HashMap;

import org.apache.poi.hssf.usermodel.HSSFSheet;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.ss.usermodel.*;

import com.vaadin.server.StreamResource.StreamSource;

public class XLS implements StreamSource {
	
	protected class Position {
		
		public int row;
		public int cell;
		public Row curRow;
		
		public Position(int row, int cell) {
			this.row = row;
			this.cell = cell;
			curRow = null;
		}
	}

	HSSFWorkbook workbook = new HSSFWorkbook();
	CellStyle cellStyle;
	HashMap<HSSFSheet, Position> worksheets;
	
	
	public XLS() {
		worksheets = new HashMap<HSSFSheet, Position>();
		CreationHelper createHelper = workbook.getCreationHelper();
		cellStyle = workbook.createCellStyle(); 
		cellStyle.setDataFormat(createHelper.createDataFormat().getFormat("yyyy-dd-MM hh:mm:ss"));
	}
	
	public HSSFSheet createNewWorksheet(String worksheetName) {
		HSSFSheet sheet = workbook.createSheet(worksheetName);
		Position p = new Position(0, 0);
		worksheets.put(sheet, p);
		createNewRow(sheet);
		return sheet;
	}
	
	public HSSFSheet createNewWorksheet(String worksheetName, String... columns) {
		HSSFSheet sheet = workbook.createSheet(worksheetName);
		Position p = new Position(0, 0);
		worksheets.put(sheet, p);
		createNewRow(sheet);
		for (String c : columns) {
			writeCell(sheet, c);
		}
		createNewRow(sheet);
		return sheet;
	}
	
	public void createNewRow(HSSFSheet sheet) {
		Position p = worksheets.get(sheet);
		p.curRow = sheet.createRow(p.row++);
		p.cell = 0;
	}
	
	public void writeCell(HSSFSheet sheet, Date data) {
		Position p = worksheets.get(sheet);
		Cell cell = p.curRow.createCell(p.cell++);
		cell.setCellStyle(cellStyle);
		cell.setCellValue(data);
	}
	
	public void writeCell(HSSFSheet sheet, String data) {
		Position p = worksheets.get(sheet);
		Cell cell = p.curRow.createCell(p.cell++);
		cell.setCellValue(data);
	}
	
	public void writeCell(HSSFSheet sheet, double data) {
		Position p = worksheets.get(sheet);
		Cell cell = p.curRow.createCell(p.cell++);
		cell.setCellValue(data);
	}
	
	@Override
	public InputStream getStream() {
		for (HSSFSheet sheet : worksheets.keySet()) {
			for (int i = 0; i < sheet.getRow(0).getPhysicalNumberOfCells(); i++) {
				sheet.autoSizeColumn(i);
			}
		}
		ByteArrayOutputStream stream  = new ByteArrayOutputStream();
		try {
		    workbook.write(stream);
		} catch (IOException e) {
			return null;
		} finally {
			try {
				stream.close();
			} catch (IOException e) {
			}
		}
		return new ByteArrayInputStream(stream.toByteArray());
	}

	
}
