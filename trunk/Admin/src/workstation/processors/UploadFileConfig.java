package workstation.processors;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;

import com.vaadin.ui.Button;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.UI;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.Upload.SucceededEvent;
import com.vaadin.ui.Upload.SucceededListener;
import com.vaadin.ui.Window;
import com.vaadin.ui.Upload;
import com.vaadin.ui.Upload.Receiver;
import com.vaadin.ui.Window.CloseEvent;
import com.vaadin.ui.Window.CloseListener;

import concurrency.JobManager.IObserverListener;

public class UploadFileConfig extends Window {
	
	public class UploadFileReceiver implements Receiver, SucceededListener {
		
		private String _saveLocation;
		private OutputStream _outFile = null;
		private InputStream _inFile = null;
		private String _filename = null;
		private File _file = null;
		
		public UploadFileReceiver(String saveLocation) {
			super();
			this._saveLocation = new String(saveLocation);
		}
				
		@Override
		public OutputStream receiveUpload(String filename, String mimeType) {
			try {
				_filename = _saveLocation+filename;
				_file = new File(_filename);
				System.out.println(_filename);
				if(!_file.exists()) {
					_file.createNewFile();
				}
				_outFile = new FileOutputStream(_file);
				_inFile = new FileInputStream(_file);
				
			} catch(Exception e) {
				e.printStackTrace();
			}
			return _outFile;
		}

		@Override
		public void uploadSucceeded(SucceededEvent event) {
			try {
				_inFile = new FileInputStream(_file);				
			} catch (Exception e) {
				e.printStackTrace();
			}	
		}
		
		public InputStream getInFile() {
			return this._inFile;
		}
		
		public String getFilename() {
			return this._filename;
		}
	}
	
	protected final FormLayout _rootLayout = new FormLayout();
	private final UploadProcessor _processor;
	private UploadFileReceiver _receiver;
	private Upload _uploader;
	private String _saveLocation;
	
	private boolean _dirty = false;
	
	
	public UploadFileConfig() {
		this._processor = null;
		this._saveLocation = "C:/testtemp/";
	}
	
	public UploadFileConfig(UploadProcessor processor, String saveLocation) {
		this._processor = processor;
		this._saveLocation = new String(saveLocation);
	}
	
	public InputStream getInFile() {
		return _receiver.getInFile();
	}
	
	protected void createLayout() {
		_receiver = new UploadFileReceiver(_saveLocation);
		_uploader = new Upload("Upload File", _receiver);
		this.setCaption("File Uploader");
		setContent(_rootLayout);
		setModal(true);
		setWidth("450px");
		_rootLayout.addComponent(_uploader);
		
		final Button submit = new Button("Start");
		submit.addClickListener(new ClickListener() {
			public void buttonClick(ClickEvent event) {
				_dirty = true;
				_processor.setFilename(_receiver.getFilename());
				_processor.start(null, (IObserverListener) _processor.getObserverUI());
				close();
			}
		});
		_rootLayout.addComponent(submit);
		
	}
	
	public void show() {
		
		createLayout();
		
		addCloseListener( new CloseListener() {

			private static final long serialVersionUID = 8286158088971375063L;

			@Override
			public void windowClose(CloseEvent e)
			{
				if (!_dirty) {
					DefaultObserverUI a = (DefaultObserverUI)_processor.getObserverUI();
					a.removeSelf();
				}
				UI.getCurrent().removeWindow(UploadFileConfig.this);
			}
		}); 
		
		UI.getCurrent().addWindow(this);
		center();
	}
	
	public void hide() {
		UI.getCurrent().removeWindow(this);
	}
	
	
}
