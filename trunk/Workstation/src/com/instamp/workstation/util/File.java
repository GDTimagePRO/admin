package com.instamp.workstation.util;

import java.io.ByteArrayInputStream;
import java.io.InputStream;

import com.vaadin.server.StreamResource.StreamSource;

public class File implements StreamSource {
	
	StringBuilder file;
	
	public File(String text) {
		file = new StringBuilder(text);
	}
	
	public File() {
		file = new StringBuilder();
	}
	
	public void addLine(String line) {
		file.append(line);
		file.append(System.getProperty("line.separator"));
	}
	
	public void addText(String text) {
		file.append(text);
	}

	@Override
	public InputStream getStream() {
		return new ByteArrayInputStream(file.toString().getBytes());
	}

}
