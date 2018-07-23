package services;

import graphics.JavaCanvas;
import graphics.Potrace;

import java.awt.image.BufferedImage;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.util.Iterator;

import javax.imageio.IIOImage;
import javax.imageio.ImageIO;
import javax.imageio.ImageTypeSpecifier;
import javax.imageio.ImageWriteParam;
import javax.imageio.ImageWriter;
import javax.imageio.metadata.IIOInvalidTreeException;
import javax.imageio.metadata.IIOMetadata;
import javax.imageio.metadata.IIOMetadataNode;
import javax.imageio.stream.ImageOutputStream;
import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.script.Invocable;
import javax.script.ScriptEngine;
import javax.script.ScriptEngineManager;
import javax.script.ScriptException;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.google.common.io.Files;

import data.ImageResourceManager;
import data.ResourceId;


@WebServlet("/RenderImage")
public class RenderDesign extends HttpServlet{

	private static final long serialVersionUID = -8004529290618211550L;
	
	private String getRenderJson(int width, int height, String json, String imageURL) throws ScriptException, NoSuchMethodException, NamingException {
		ScriptEngineManager manager = new ScriptEngineManager();	
	    ScriptEngine engine = manager.getEngineByName("JavaScript");

	    
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/elements/prototype_element.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/elements/border_element.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/elements/image_element.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/elements/line_element.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/elements/text_element.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/maps.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/patterns.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/drawables.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/json2.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/widgets.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/script_container.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/scene.js")));
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/system.js")));
	    
	    engine.eval(new InputStreamReader(getClass().getClassLoader().getResourceAsStream("/js/init.js")));
		Invocable inv = (Invocable) engine;
        return (String)inv.invokeFunction("javaInit", width, height, json, imageURL);
	}
	
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException
	{
		response.setHeader("Access-Control-Allow-Origin", "*");			
		response.setHeader("Pragma-directive", "no-cache");
		response.setHeader("Cache-directive", "no-cache");
		response.setHeader("Cache-control", "no-cache");
		response.setHeader("Pragma", "no-cache");
		response.setDateHeader("Expires", 0);
		
		
		String json = request.getParameter("designJson");
		int imgWidth = Integer.parseInt(request.getParameter("width"));
		int imgHeight = Integer.parseInt(request.getParameter("height"));
		String fillColor = request.getParameter("fillColor");
		String destId = request.getParameter("destId");
		String type = request.getParameter("type");
		String color = request.getParameter("color");
		String s_dpi = request.getParameter("dpi");
		String filter = request.getParameter("filter");
		if (filter == null) filter = "";
		int dpi = 300;
		if (s_dpi != null && !s_dpi.isEmpty()) {
			dpi = Integer.parseInt(s_dpi);
		}
		String imageurl = request.getParameter("imageURL");
		
		if (json.isEmpty()) {
			response.sendError(HttpServletResponse.SC_NOT_FOUND);
			return;
		}
		String SSRDO = "";
		try {
			SSRDO = getRenderJson(imgWidth, imgHeight, json, imageurl);
		} catch (ScriptException e) {
			e.printStackTrace();
		} catch (NoSuchMethodException e) {
			e.printStackTrace();
		} catch (NamingException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		JavaCanvas canvas = RenderScene.getCanvas(SSRDO, imgWidth, imgHeight, "", fillColor, destId);
		
		if (type.equals("svg")) {
			byte[] buffer = new byte[1024];
			String name = destId;
			if (name.length() < 3) {
				name = "00" + name;
			}
			File svg = File.createTempFile(name, ".svg");
			File bmp = File.createTempFile(name, ".bmp");
			
			if (!bmp.exists() || !svg.exists()) {
				response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to create temporary files");
				return;
			}
			
			BufferedImage image = canvas.getImage();
			if (filter.equals("embosser_f")) {
				image = ImageResourceManager.embosserFemale(image);
			} else if (filter.equals("embosser_m")) {
				image = ImageResourceManager.embosserMale(image);
			}
			ImageIO.write(image, "bmp", bmp);
			boolean c = false;
			if (color != null && color.equals("true")) c = true;
			if (!Potrace.trace(bmp.getAbsolutePath(), svg.getAbsolutePath(), dpi, c)) {
				response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Potrace failed");
				return;
			}
			svg = new File(svg.getAbsolutePath());
			FileInputStream is = new FileInputStream(svg);
			OutputStream os = response.getOutputStream();
			
			int bytesRead;
			while((bytesRead = is.read(buffer)) > -1)
			{
				os.write(buffer,0,bytesRead);
			}
			
			is.close();
			os.close();
			svg.delete();
			bmp.delete();
		} else {
			BufferedImage image = canvas.getImage();
			if (filter.equals("embosser_f")) {
				image = ImageResourceManager.embosserFemale(image);
			} else if (filter.equals("embosser_m")) {
				image = ImageResourceManager.embosserMale(image);
			}
			for (Iterator<ImageWriter> iw = ImageIO.getImageWritersByFormatName("png"); iw.hasNext();) {
				ImageWriter writer = iw.next();
				ImageWriteParam writeParam = writer.getDefaultWriteParam();
				IIOMetadata metadata = writer.getDefaultImageMetadata(ImageTypeSpecifier.createFromBufferedImageType(image.getType()), writeParam);
				if (metadata.isReadOnly() || !metadata.isStandardMetadataFormatSupported()) {
			          continue;
	          	}
				setDPI(metadata, dpi);
				ResourceId destRID = ResourceId.fromId(destId);
				OutputStream destOS = destRID.getOutputStream();
				ImageIO.write(canvas.getImage(), "png", destOS);
				destOS.close();
				final ImageOutputStream stream = ImageIO.createImageOutputStream(response.getOutputStream());
				
				writer.setOutput(stream);
				writer.write(metadata, new IIOImage(image, null, metadata), writeParam);
			}
			ImageIO.write(image, "png", response.getOutputStream());
		}
	}
	
	private void setDPI(IIOMetadata metadata, int dpi) throws IIOInvalidTreeException {

	    // for PMG, it's dots per millimeter
	    double dotsPerMilli = ((1.0 * dpi) / 10) / 2.54;
	    
	    IIOMetadataNode horiz = new IIOMetadataNode("HorizontalPixelSize");
	    horiz.setAttribute("value", Double.toString(dotsPerMilli));

	    IIOMetadataNode vert = new IIOMetadataNode("VerticalPixelSize");
	    vert.setAttribute("value", Double.toString(dotsPerMilli));

	    IIOMetadataNode dim = new IIOMetadataNode("Dimension");
	    dim.appendChild(horiz);
	    dim.appendChild(vert);

	    IIOMetadataNode root = new IIOMetadataNode("javax_imageio_1.0");
	    root.appendChild(dim);

	    metadata.mergeTree("javax_imageio_1.0", root);
	 }
}
