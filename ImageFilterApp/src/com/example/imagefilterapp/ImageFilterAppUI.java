package com.example.imagefilterapp;

import java.awt.image.BufferedImage;
import java.io.ByteArrayInputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

import javax.imageio.ImageIO;
import javax.servlet.ServletContext;
import javax.servlet.annotation.WebServlet;

import org.apache.tomcat.util.http.fileupload.ByteArrayOutputStream;

import com.vaadin.annotations.PreserveOnRefresh;
import com.vaadin.annotations.Push;
import com.vaadin.annotations.Theme;
import com.vaadin.annotations.VaadinServletConfiguration;
import com.vaadin.data.Property;
import com.vaadin.data.Property.ValueChangeEvent;
import com.vaadin.server.Page;
import com.vaadin.server.StreamResource;
import com.vaadin.server.VaadinRequest;
import com.vaadin.server.VaadinServlet;
import com.vaadin.server.VaadinSession;
import com.vaadin.shared.ui.colorpicker.Color;
import com.vaadin.ui.Alignment;
import com.vaadin.ui.ColorPickerArea;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.Image;
import com.vaadin.ui.Label;
import com.vaadin.ui.Link;
import com.vaadin.ui.Notification;
import com.vaadin.ui.Panel;
import com.vaadin.ui.ProgressBar;
import com.vaadin.ui.Slider;
import com.vaadin.ui.UI;
import com.vaadin.ui.Upload;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Upload.FailedEvent;
import com.vaadin.ui.Upload.Receiver;
import com.vaadin.ui.Upload.StartedEvent;
import com.vaadin.ui.Upload.SucceededEvent;
import com.vaadin.ui.components.colorpicker.ColorChangeEvent;
import com.vaadin.ui.components.colorpicker.ColorChangeListener;

@Push
@SuppressWarnings("serial")
@Theme("imagefilterapp")
@PreserveOnRefresh
public class ImageFilterAppUI extends UI
{
	@WebServlet(value = "/*", asyncSupported = true)
	@VaadinServletConfiguration(productionMode = false, ui = ImageFilterAppUI.class)	
	public static class Servlet extends VaadinServlet {}
	
	private static class ImageTintFilterResource implements StreamResource.StreamSource
	{
		private final File _imageFile;
		private final java.awt.Color _color;
		private final float _alpha;
		private final float _inflection;		
		
		@Override
		public InputStream getStream()
		{
			BufferedImage image = ImageUtils.loadImage(_imageFile);
			if(image == null) return null;
			ImageUtils.applyLinearColorTint(image, _color, _inflection, _alpha);
			
			try
			{
				ByteArrayOutputStream buffer = new ByteArrayOutputStream();
				ImageIO.write(image, "png", buffer);
				return new ByteArrayInputStream(buffer.toByteArray());
			}
			catch(IOException e)
			{
				e.printStackTrace();
				return null;
			}
		}		
		
		public ImageTintFilterResource(File imageFile, java.awt.Color color, float alpha, float inflection)
		{
			_imageFile = imageFile;
			_color = color;
			_inflection = inflection;
			_alpha = alpha;
		}		
	}
	
	private File _uploadedFileSmall = null;
	private File _uploadedFile = null;
	private File _uploadTempDir = null;
	
	private final Image _imagePreview = new Image();
	private final Link _downloadLink = new Link();
			
	private final ColorPickerArea _tintColorPickerArea = new ColorPickerArea();
	private final Slider _alphaSlider = new Slider();
	private final Slider _inflectionSlider = new Slider();
	
	
	private void updatePreview()
	{
		if(_uploadedFileSmall == null) return;
		
		Color color = _tintColorPickerArea.getColor();
		java.awt.Color awtColor = new java.awt.Color(color.getRed(), color.getGreen(), color.getBlue()); 
		float alpha = (float)(double)_alphaSlider.getValue(); 
		float inflection = (float)(double)_inflectionSlider.getValue();
		
		StreamResource previewResource = new StreamResource( 
				new ImageTintFilterResource( _uploadedFileSmall, awtColor, alpha, inflection),
				System.currentTimeMillis() + "-" + _uploadedFileSmall.hashCode() + ".png" 
			);
		
		previewResource.setCacheTime(0);
		_imagePreview.setSource(previewResource);
				
		StreamResource downloadResource = new StreamResource( 
				new ImageTintFilterResource( _uploadedFile, awtColor, alpha, inflection),
				"image.png" 
			);
		
		downloadResource.setCacheTime(0);
		_downloadLink.setResource(downloadResource);
	}
	
	@Override
	protected void init(VaadinRequest request)
	{
		ServletContext context =  VaadinServlet.getCurrent().getServletContext();
		_uploadTempDir = new File(context.getRealPath("/uploads/" + VaadinSession.getCurrent().getSession().getId()));
		
		_imagePreview.setVisible(false);
		_downloadLink.setVisible(false);
		_downloadLink.setCaption("Download");
		_downloadLink.setTargetName("_blank");

		
		final FormLayout contentLayout = new FormLayout();
		contentLayout.setMargin(true);

		_tintColorPickerArea.addColorChangeListener(new ColorChangeListener()
		{
			@Override
			public void colorChanged(ColorChangeEvent event)
			{
				updatePreview();				
			}
		});
		
		_tintColorPickerArea.setWidth("200px");
		_tintColorPickerArea.setHeight("20px");
		_tintColorPickerArea.setColor(new Color(112,66,20));
		contentLayout.addComponent(_tintColorPickerArea);
		
		_alphaSlider.addValueChangeListener(new Property.ValueChangeListener()
		{
			@Override
			public void valueChange(ValueChangeEvent event)
			{
				updatePreview();				
			}
		});
		
		_alphaSlider.setCaption("Alpha");
		_alphaSlider.setMax(255.0);
		_alphaSlider.setValue(128.0);
		_alphaSlider.setWidth("200px");
		contentLayout.addComponents(_alphaSlider);
		
		_inflectionSlider.addValueChangeListener(new Property.ValueChangeListener()
		{
			@Override
			public void valueChange(ValueChangeEvent event)
			{
				updatePreview();
			}
		});
		_inflectionSlider.setCaption("Inflection");
		_inflectionSlider.setMax(255.0);
		_inflectionSlider.setValue(128.0);
		_inflectionSlider.setWidth("200px");
		contentLayout.addComponents(_inflectionSlider);
				
		final Receiver imageReceiver = new Receiver()
		{
			@Override
			public OutputStream receiveUpload(String filename, String mimeType)
			{
				_uploadTempDir.mkdirs();
				_uploadedFile = new File(_uploadTempDir,filename);
				try
				{					
					return new FileOutputStream(_uploadedFile);
				}
				catch(final FileNotFoundException e) 
				{
					e.printStackTrace();
					return null;
				}
			}
		};

		final ProgressBar imageUploadProgressBar = new ProgressBar();
		imageUploadProgressBar.setVisible(false);
		contentLayout.addComponent(imageUploadProgressBar);
		
		final Upload imageUpload = new Upload();
		imageUpload.setReceiver(imageReceiver);

		imageUpload.addStartedListener(new Upload.StartedListener()
		{
			public void uploadStarted(StartedEvent event)
			{
				imageUploadProgressBar.setVisible(true);
				imageUpload.setComponentError(null);
			}
		});
		
		imageUpload.addProgressListener(new Upload.ProgressListener()
		{
			public void updateProgress(long readBytes, long contentLength)
			{
				imageUploadProgressBar.setValue((float)((double)readBytes / (double)contentLength));	
			}
		});
		
		imageUpload.addFailedListener(new Upload.FailedListener()
		{
			public void uploadFailed(FailedEvent event)
			{
				imageUploadProgressBar.setVisible(false);
			}
		});
		
		imageUpload.addSucceededListener(new Upload.SucceededListener()
		{
			public void uploadSucceeded(SucceededEvent event)
			{
				_uploadedFileSmall = ImageUtils.makeSmallPng(_uploadedFile, 300, 240);
				imageUpload.setVisible(true);
				imageUploadProgressBar.setVisible(false);
				updatePreview();
				_imagePreview.setVisible(true);
				_downloadLink.setVisible(true);
			}
		});		
		
		VerticalLayout contentWrapperLayout = new VerticalLayout(_imagePreview, _downloadLink, contentLayout, imageUpload);
		contentWrapperLayout.setComponentAlignment(_imagePreview, Alignment.MIDDLE_CENTER);		
		contentWrapperLayout.setComponentAlignment(_downloadLink, Alignment.TOP_CENTER);		
		
		final Panel contentPanel = new Panel(contentWrapperLayout);
		contentPanel.setSizeUndefined();

		final VerticalLayout rootLayout = new VerticalLayout();
		rootLayout.setSizeFull();		
		rootLayout.addComponent(contentPanel);		
		rootLayout.setComponentAlignment(contentPanel, Alignment.MIDDLE_CENTER);
		
		setContent(rootLayout);
	}
}