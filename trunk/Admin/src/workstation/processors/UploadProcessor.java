package workstation.processors;

import java.io.InputStream;

public abstract class UploadProcessor extends DesignProcessor {

	protected UploadProcessor(String name, String description) {
		super(name, description);
	}
	
	protected static InputStream inFile = null;
	protected static String filename = null;
	
	public String getFilename() {
		return filename;
	}

	public void setFilename(String filename) {
		this.filename = filename;
	}

	public InputStream getInFile() {
		return inFile;
	}

	public void setInFile(InputStream inFile) {
		this.inFile = inFile;
	}

	private static final long serialVersionUID = 1L;
}
