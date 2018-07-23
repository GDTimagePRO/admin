package com.instamp.workstation.util;

import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.zip.ZipEntry;
import java.util.zip.ZipOutputStream;

import com.vaadin.server.StreamResource.StreamSource;

public class Zip implements StreamSource {
	ByteArrayOutputStream output; 
	ZipOutputStream stream;
	
	public Zip() {
		output = new ByteArrayOutputStream();
		stream = new ZipOutputStream(output);
	}
	
	public void addFile(String name, byte[] file) throws IOException {
		ZipEntry entry = new ZipEntry(name);
		entry.setSize(file.length);
		stream.putNextEntry(entry);
		stream.write(file);
		stream.closeEntry();
	}

	@Override
	public InputStream getStream() {
		try {
			stream.flush();
			stream.close();
		} catch (IOException e) {
			//TODO: Logging
		}
		return new ByteArrayInputStream(output.toByteArray());
	}
}
