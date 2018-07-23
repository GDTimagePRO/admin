package services;

import java.awt.Color;
import java.awt.Graphics2D;
import java.awt.RenderingHints;
import java.awt.image.BufferedImage;
import java.io.IOException;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import data.ImageResourceManager;
import data.ResourceId;
import data.ResourceManager;

@WebServlet("/CreateImageCollage")
public class CreateImageCollage extends HttpServlet
{
	private static final long serialVersionUID = 6201551326609416574L;

	
	public static boolean createCollage(String destFileName, String[] srcFileNames, int width, int height)
	{
		int cells_X;
		int cells_Y;
		int firstCell;
		
		switch(srcFileNames.length)
		{
		case 1:
			cells_X = 1;
			cells_Y = 1;
			firstCell = 0;
			break;
			
		case 2:
			cells_X = 1;
			cells_Y = 2;			
			firstCell = 0;
			break;

		case 3:
			cells_X = 2;
			cells_Y = 2;
			firstCell = 1;
			break;
			
		default:
			cells_X = 2;
			cells_Y = 2;
			firstCell = 0;
			break;
		}
		
		int frameWidth = width / cells_X;
		int frameHeight = height / cells_Y;

		
		double frameSlope = (double)frameHeight / (double)frameWidth; 
		

		BufferedImage newImage = ImageResourceManager.createImage(width, height, false);
			
		Graphics2D g2d = newImage.createGraphics();
		g2d.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
		g2d.setRenderingHint(RenderingHints.KEY_DITHERING, RenderingHints.VALUE_DITHER_ENABLE);
		g2d.setRenderingHint(RenderingHints.KEY_INTERPOLATION, RenderingHints.VALUE_INTERPOLATION_BICUBIC);
		g2d.setRenderingHint(RenderingHints.KEY_RENDERING, RenderingHints.VALUE_RENDER_QUALITY);
		
		g2d.setColor(Color.WHITE);
		g2d.fillRect(0, 0, newImage.getWidth(), newImage.getHeight());
			
		for(int i = 0; i < srcFileNames.length; i++)
		{
			BufferedImage srcImage = ImageResourceManager.loadImage(srcFileNames[i]);
		
			if(srcImage != null)
			{
				int destWidth;
				int destHeight;
				
				double imageSlope = (double)srcImage.getHeight() / (double)srcImage.getWidth();
								
				if(imageSlope > frameSlope)
				{
					destHeight = frameHeight;
					destWidth = (int)Math.round((double)frameHeight / (double)srcImage.getHeight() * (double)srcImage.getWidth());
				}
				else
				{
					destWidth = frameWidth;
					destHeight = (int)Math.round((double)frameWidth / (double)srcImage.getWidth() * (double)srcImage.getHeight());
				}
				
				int cell = firstCell + i;
				int row = cell / cells_X;
				int col = cell % cells_X;
				int destX;
				int destY;
				
				if(row == 0)
				{
					destX = (frameWidth - destWidth) / 2 + col * frameWidth - (firstCell * frameWidth) / 2;
					destY = (frameHeight - destHeight) / 2 + row * frameHeight;
				}
				else
				{
					destX = (frameWidth - destWidth) / 2 + col * frameWidth;
					destY = (frameHeight - destHeight) / 2 + row * frameHeight;
				}	
				
				g2d.drawImage(srcImage, 
						destX, destY,
						destWidth, 
						destHeight,
						null
					);
			}
		}

		boolean result = true;
		if(!ImageResourceManager.saveImage(newImage, destFileName)) result = false;
		return result;
	}

	@Override
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException
	{
		response.setHeader("Access-Control-Allow-Origin", "*");
		response.setHeader("Cache-Control", "no-store, private, no-cache, must-revalidate");
		response.setHeader("Cache-Control", "pre-check=0, post-check=0, max-age=0, max-stale = 0");
		response.setHeader("Pragma", "public");
		response.setHeader("Pragma", "no-cache");
		response.setDateHeader("Expires", 0);
		
		try
		{
			int width = Integer.parseInt(request.getParameter("width"));
			int height = Integer.parseInt(request.getParameter("height"));

			ResourceId destRID = ResourceManager.parseId(request.getParameter("destId"));
			if(destRID == null) throw new RuntimeException("Invalid destId value");
			
			String[] srcStrIds = request.getParameter("srcIds").split(",");
			String[] srcFileNames = new String[srcStrIds.length];
			for(int i=0; i<srcStrIds.length; i++)
			{
				ResourceId srcRID = ResourceManager.parseId(srcStrIds[i].trim()); 
				if(srcRID == null) throw new RuntimeException("Invalid srcIds value");
				if(srcRID.isDirty()) 
				{
					if(!srcRID.update()) throw new RuntimeException("Unable to update " + srcStrIds[i]);
				}
				srcFileNames[i] = srcRID.getPath(); 
			}
			
			if(createCollage(destRID.getPath(), srcFileNames, width, height))
			{
				response.getWriter().write("true");
			}
			else
			{
				response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
			}
		}
		catch(Exception e)
		{
			e.printStackTrace();
			response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
		}
	}
}
