package data;

import graphics.JavaCanvas;
import graphics.Potrace;
import graphics.filters.CommonImageFilters;

import java.awt.Color;
import java.awt.Graphics2D;
import java.awt.RenderingHints;
import java.awt.image.BufferedImage;
import java.io.File;

import javax.imageio.ImageIO;

public class ImageResourceManager
{	
	//different thumbnail size for schwaab
	//private final static int thumbnail_size = 341;
	private final static int thumbnail_size = 125;
	
	public static boolean applyColorGradient(BufferedImage image, Color startColor, Color endColor)
	{
		if(image == null) return false;
			
		int width = image.getWidth();
		int height = image.getHeight();

		float baseR = startColor.getRed();
		float baseG = startColor.getGreen();
		float baseB = startColor.getBlue();
		
		float deltaR = endColor.getRed() - baseR;
		float deltaG = endColor.getGreen() - baseG;
		float deltaB = endColor.getBlue() - baseB;
		
		final int colorComponents = image.getColorModel().getNumComponents();
		if((colorComponents == 3) || (colorComponents == 4)) 
		{
			float[] pixels = image.getData().getPixels(0, 0, width, height, (float[])null);
			
			final int len = pixels.length;
			for(int i = 0; i < len; i += colorComponents)
			{
				float lumMul = (pixels[i] * 0.2126f + pixels[i + 1] * 0.7152f + pixels[i + 2] * 0.0722f) / 255f; 				
				pixels[i] = baseR + deltaR * lumMul;
				pixels[i + 1] = baseG + deltaG * lumMul;
				pixels[i + 2] = baseB + deltaB * lumMul;
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
			return true;
		}
		else
		{
			throw new RuntimeException("The source image is using an unsupported color model.");
		}
	}
	
	public static boolean applyMirrorVertical(BufferedImage image)
	{
		if(image == null) return false;
			
		int width = image.getWidth();
		int height = image.getHeight();
		final int colorComponents = image.getColorModel().getNumComponents();
		int rowSize = width * colorComponents;  
		
		if((colorComponents == 3) || (colorComponents == 4)) 
		{
			int[] pixels = image.getData().getPixels(0, 0, width, height, (int[])null);
			int swapRowCount = height >> 1; 
			for(int iRow = 0; iRow< swapRowCount; iRow++)
			{
				int offsetA = iRow * rowSize;
				int offsetB = (height - iRow - 1) * rowSize;
				
				for(int iCol=0; iCol<rowSize; iCol++)
				{
					int i1 = offsetA + iCol;
					int i2 = offsetB + iCol;
					
					int tmp = pixels[i1];
					pixels[i1] = pixels[i2];
					pixels[i2] = tmp; 
				}
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
			return true;
		}
		else
		{
			throw new RuntimeException("The source image is using an unsupported color model.");
		}
	}

	public static boolean applyMirrorHorizontal(BufferedImage image)
	{
		if(image == null) return false;
			
		int width = image.getWidth();
		int height = image.getHeight();
		final int colorComponents = image.getColorModel().getNumComponents();
		int rowSize = width * colorComponents;  
		
		if((colorComponents == 3) || (colorComponents == 4)) 
		{
			int[] pixels = image.getData().getPixels(0, 0, width, height, (int[])null);
			int swapColCount = width >> 1;
				
			for(int iRow = 0; iRow<height; iRow++)
			{
				int offset = iRow * rowSize;
				
				for(int iCol=0; iCol<swapColCount; iCol++)
				{
					int i1 = offset + iCol * colorComponents;
					int i2 = offset + (width - iCol - 1) * colorComponents;
						
					for(int i=0; i<colorComponents; i++)
					{
						int ii1 = i1 + i;
						int ii2 = i2 + i;
						
						int tmp = pixels[ii1];
						pixels[ii1] = pixels[ii2];
						pixels[ii2] = tmp; 
					}
				}
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
			return true;
		}
		else
		{
			throw new RuntimeException("The source image is using an unsupported color model.");
		}
	}
	
	
	
	public static boolean applyLinearColorTint(BufferedImage image, Color color, float inflectionPoint, float alpha)
	{
		if(image == null) return false;
			
		int width = image.getWidth();
		int height = image.getHeight();

		alpha /= 255f;
		if(alpha < 0) alpha = 0;
		if(alpha > 1) alpha = 1;
		float alphaInv = 1 - alpha;
		
		inflectionPoint /= 255f;
		if(inflectionPoint < 0) inflectionPoint = 0;
		if(inflectionPoint > 1) inflectionPoint = 1;

		float colorR = color.getRed();
		float colorG = color.getGreen();
		float colorB = color.getBlue();

		float ipMul1 = (inflectionPoint > 0) ? 1f / inflectionPoint : 0;
		
		float deltaR1 = colorR * ipMul1;
		float deltaG1 = colorG * ipMul1;
		float deltaB1 = colorB * ipMul1;
		
		float ipMul2 =  (inflectionPoint < 1) ? 1f / (1 - inflectionPoint) : 0;
		
		float deltaR2 = (255 - colorR) * ipMul2;
		float deltaG2 = (255 - colorG) * ipMul2;
		float deltaB2 = (255 - colorB) * ipMul2;	
		
		final int colorComponents = image.getColorModel().getNumComponents();
		if((colorComponents == 3) || (colorComponents == 4)) 
		{
			float[] pixels = image.getData().getPixels(0, 0, width, height, (float[])null);
			
			final int len = pixels.length;
			for(int i = 0; i < len; i += colorComponents)
			{
				float r = pixels[i];
				float g = pixels[i + 1];
				float b = pixels[i + 2];
				
				float lumMul = (r * 0.2126f + g * 0.7152f + b * 0.0722f) / 255f; 				
				
				if(lumMul < inflectionPoint)
				{
					pixels[i] = (deltaR1 * lumMul) * alpha + r * alphaInv;
					pixels[i + 1] = (deltaG1 * lumMul) * alpha + g * alphaInv;
					pixels[i + 2] = (deltaB1 * lumMul) * alpha + b * alphaInv;
				}
				else
				{
					lumMul -= inflectionPoint;
					pixels[i] = (colorR + deltaR2 * lumMul) * alpha + r * alphaInv;
					pixels[i + 1] = (colorG + deltaG2 * lumMul) * alpha + g * alphaInv;
					pixels[i + 2] = (colorB + deltaB2 * lumMul) * alpha + b * alphaInv;					
				}
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
			return true;
		}
		else
		{
			throw new RuntimeException("The source image is using an unsupported color model.");
		}
	}

	private static boolean  applyMonochrome(BufferedImage originalImage)
	{		
		int imgType = originalImage.getType();
		if(imgType == BufferedImage.TYPE_3BYTE_BGR) 
		{
			int width = originalImage.getWidth();
			int height = originalImage.getHeight();
			int[] pixels = originalImage.getData().getPixels(0, 0, width, height, (int[])null);
			int len = pixels.length;
			
			for(int i=0; i<len; i+=3)
			{
				float lum = 0.2126f * (float)pixels[i] + 
						0.7152f * (float)pixels[i+1] + 
						0.0722f * (float)pixels[i+2];

				if(lum < 128f)
				{
					pixels[i] = 0;
					pixels[i+1] = 0;
					pixels[i+2] = 0;
				}
				else
				{
					pixels[i] = 255;
					pixels[i+1] = 255;
					pixels[i+2] = 255;
				}
			}

			originalImage.getRaster().setPixels(0, 0, width, height, pixels);
		}
		else if(imgType == BufferedImage.TYPE_4BYTE_ABGR) 
		{
			int width = originalImage.getWidth();
			int height = originalImage.getHeight();
			int[] pixels = originalImage.getData().getPixels(0, 0, width, height, (int[])null);
			int len = pixels.length;
			
			for(int i=0; i<len; i+=4)
			{
				int a = i + 3; 
				if(pixels[a] < 128)
				{
					pixels[i] = 255;
					pixels[i+1] = 255;
					pixels[i+2] = 255;
					pixels[a] = 0;
					
				}
				else
				{
					pixels[a] = 255;
					
					float lum = 0.2126f * (float)pixels[i] + 
								0.7152f * (float)pixels[i+1] + 
								0.0722f * (float)pixels[i+2];
					
					if(lum < 128f)
					{
						pixels[i] = 0;
						pixels[i+1] = 0;
						pixels[i+2] = 0;
					}
					else
					{
						pixels[i] = 255;
						pixels[i+1] = 255;
						pixels[i+2] = 255;
					}
				}
			}

			originalImage.getRaster().setPixels(0, 0, width, height, pixels);
		}
		else return false;
		return true;
	}	

	public static void processUserUpload(BufferedImage originalImage, String colorModel)
	{
		if(colorModel == null) return;
		if(colorModel.equalsIgnoreCase("1_BIT"))
		{
			applyMonochrome(originalImage);
		}
	}

	//___66666___
	//__6555556__
	//_654444456_
	//65443334456
	//65432223456
	//65432123456
	//65432223456
	//65443334456
	//_654444456_
	//__6555556__
	//___66666___
	
	private static final int[][] ROUND_REGION_X_OFFSET = new int[][] {
		new int[] { 0 },
		new int[] { 1, 1, 1 },
		new int[] { 1, 2, 2, 2, 1 },
		new int[] { 2, 3, 3, 3, 3, 3, 2 },
		new int[] { 2, 3, 4, 4, 4, 4, 4, 3, 2 },
		new int[] { 2, 3, 4, 5, 5, 5, 5, 5, 4, 3, 2 }
	};

	public static BufferedImage embosserMale(BufferedImage originalImage)
	{		
		final int GAP_SIZE = 1;
		final int[] OFFSET_X = ROUND_REGION_X_OFFSET[GAP_SIZE];

		int imgType = originalImage.getType();
		if(imgType != BufferedImage.TYPE_3BYTE_BGR)
		{
			throw new RuntimeException("The input image is not 3 byte RGB.");
		}
		
		int width = originalImage.getWidth();
		int height = originalImage.getHeight();
		int[] pixels = originalImage.getData().getPixels(0, 0, width, height, (int[])null);
		int[] result = new int[width * height];
		
		int rowSize = width * 3;
		
		for(int iwy=0; iwy<height; iwy++)
		{
			int iwyOffsetSingleByte = iwy * width; 
			
			for(int iwx=0; iwx<width; iwx++)				
			{
				int iy_min = iwy - GAP_SIZE;
				int iy = Math.max(iy_min, 0) ;
				int windowEndY = Math.min(iwy + GAP_SIZE, height-1);
				
				for(; iy <= windowEndY; iy++)				
				{
					int iyOffset = iy * rowSize;
					
					int offsetX = OFFSET_X[iy - iy_min];
					int ix = Math.max(iwx - offsetX, 0); ;
					int windowEndX = Math.min(iwx + offsetX, width-1);
					
					for(; ix <= windowEndX; ix++)
					{
						if(pixels[iyOffset + ix * 3] < 128) break;							
					}
					if(ix <= windowEndX) break;
				}
				
				if(iy <= windowEndY)
				{
					result[iwyOffsetSingleByte + iwx] = 1;
				}
				else
				{
					result[iwyOffsetSingleByte + iwx] = 0;
				}
			}
		}
		
		BufferedImage resultImage = new BufferedImage(width,height,BufferedImage.TYPE_BYTE_BINARY);
		resultImage.getRaster().setPixels(0, 0, width, height, result);
		return resultImage;
	}	

	public static BufferedImage embosserFemale(BufferedImage originalImage)
	{		
		final int GAP_SIZE = 4;
		final int[] OFFSET_X = ROUND_REGION_X_OFFSET[GAP_SIZE];

		int imgType = originalImage.getType();
		if(imgType != BufferedImage.TYPE_3BYTE_BGR)
		{
			throw new RuntimeException("The input image is not 3 byte RGB.");
		}
		
		int width = originalImage.getWidth();
		int height = originalImage.getHeight();
		int[] pixels = originalImage.getData().getPixels(0, 0, width, height, (int[])null);
		int[] result = new int[width * height];
		
		int rowSize = width * 3;
		
		for(int iwy=0; iwy<height; iwy++)
		{
			int iwyOffsetSingleByte = iwy * width; 
			
			for(int iwx=0; iwx<width; iwx++)				
			{
				int iy_min = iwy - GAP_SIZE;
				int iy = Math.max(iy_min, 0) ;
				int windowEndY = Math.min(iwy + GAP_SIZE, height-1);
				
				for(; iy <= windowEndY; iy++)				
				{
					int iyOffset = iy * rowSize;
					
					int offsetX = OFFSET_X[iy - iy_min];
					int ix = Math.max(iwx - offsetX, 0); ;
					int windowEndX = Math.min(iwx + offsetX, width-1);
					
					for(; ix <= windowEndX; ix++)
					{
						if(pixels[iyOffset + ix * 3] < 128) break;							
					}
					if(ix <= windowEndX) break;
				}
				
				if(iy <= windowEndY)
				{
					result[iwyOffsetSingleByte + (width - iwx - 1)] = 0;
				}
				else
				{
					result[iwyOffsetSingleByte + (width - iwx - 1)] = 1;
				}
			}
		}
		
		BufferedImage resultImage = new BufferedImage(width,height,BufferedImage.TYPE_BYTE_BINARY);
		resultImage.getRaster().setPixels(0, 0, width, height, result);
		return resultImage;
	}	
	
	
	public static BufferedImage loadImage(String fileName)
	{
		try
		{
			return ImageIO.read(new File(fileName));
		}
		catch(Exception e)
		{
			return null;
		}
	}

	public static boolean saveImage(BufferedImage image, String fileName)
	{
		File file = new File(fileName); 
		try
		{
			if(fileName.endsWith(".png"))
			{
				file.getParentFile().mkdirs();			
				return ImageIO.write(image, "png", file);
			}
			else if(fileName.endsWith(".jpg"))
			{
				file.getParentFile().mkdirs();			
				return ImageIO.write(image, "jpeg", file);
			}
			return false;
		}
		catch(Exception e)
		{
			return false;
		}
	}
	
	public static BufferedImage createImage(int width, int height, boolean alphaChannel)
	{
		return new BufferedImage(
				width, 
				height, 
				alphaChannel ? BufferedImage.TYPE_4BYTE_ABGR : BufferedImage.TYPE_3BYTE_BGR
			);
	}

	public static boolean resizeImage(String srcFileName, String destFileName, int maxWidth, int maxHeight, boolean allowAlphaChannel)
	{
		BufferedImage image = loadImage(srcFileName);
		if(image == null) return false;
			
		int targetWidth = 0;
		int targetHeight = 0;

		if(image.getWidth() > image.getHeight())
		{
			targetWidth = maxWidth;
			targetHeight = (int)Math.round((float)maxWidth / (float)image.getWidth() * (float)image.getHeight());
		}
		else
		{
			targetHeight = maxHeight;
			targetWidth = (int)Math.round((float)maxHeight / (float)image.getHeight() * (float)image.getWidth());
		}

		while(true)
		{
			int stepWidth = image.getWidth() / 2;
			int stepHeight = image.getHeight() / 2;
			if((stepWidth <= targetWidth + 15) && (stepHeight<= targetHeight + 15))
			{
				stepWidth = targetWidth;
				stepHeight = targetHeight;
			}
			
			BufferedImage newImage = createImage(stepWidth,stepHeight, image.getTransparency() != BufferedImage.OPAQUE);
			//BufferedImage newImage = createImage(stepWidth,stepHeight, true);
			
			Graphics2D g2d = newImage.createGraphics();
			g2d.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
			g2d.setRenderingHint(RenderingHints.KEY_DITHERING, RenderingHints.VALUE_DITHER_ENABLE);
			g2d.setRenderingHint(RenderingHints.KEY_INTERPOLATION, RenderingHints.VALUE_INTERPOLATION_BICUBIC);
			g2d.setRenderingHint(RenderingHints.KEY_RENDERING, RenderingHints.VALUE_RENDER_QUALITY);

			if((!allowAlphaChannel) || (image.getTransparency() == BufferedImage.OPAQUE))
			{
				g2d.setColor(Color.WHITE);
				g2d.fillRect(0, 0, newImage.getWidth(), newImage.getHeight());
			}
			
			g2d.drawImage(image, 0, 0, newImage.getWidth(), newImage.getHeight(), null);
			g2d.dispose();
			image = newImage;
			
			if((stepWidth == targetWidth) && (stepHeight == targetHeight))
			{
				boolean result = true;
				if(!saveImage(newImage, destFileName)) result = false;
				return result;
			}
		}		
	}

	public static boolean setImageColor(String srcFileName, String destFileName, Color fgColor)
	{
		BufferedImage image = loadImage(srcFileName);
		if(image == null) return false;
			
		int width = image.getWidth();
		int height = image.getHeight();

		float baseR = fgColor.getRed();
		float baseG = fgColor.getGreen();
		float baseB = fgColor.getBlue();
		
		final int colorComponents = image.getColorModel().getNumComponents();
		if(colorComponents == 3)
		{
			float[] pixels = image.getData().getPixels(0, 0, width, height, (float[])null);
			
			final int len = pixels.length;
			for(int i = 0; i < len; i += 3)
			{
				float alpha = pixels[i] * 0.2126f + pixels[i + 1] * 0.7152f + pixels[i + 2] * 0.0722f; 				
				float cm = 1f - (alpha / 255f); 
				pixels[i] = baseR * cm + alpha;
				pixels[i + 1] = baseG * cm + alpha;
				pixels[i + 2] = baseB * cm + alpha;
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
		}
		else if(colorComponents == 4)
		{
			float[] pixels = image.getData().getPixels(0, 0, width, height, (float[])null);
			
			final int len = pixels.length;
			for(int i = 0; i < len; i += 4)
			{
				if(pixels[i + 3] < 20)
				{
					pixels[i + 3] = 0; 
				}
				else
				{
					pixels[i + 3] = (255.0f - (pixels[i] * 0.2126f + pixels[i + 1] * 0.7152f + pixels[i + 2] * 0.0722f)) * (pixels[i + 3] / 255.0f);
					pixels[i] = baseR;
					pixels[i + 1] = baseG;
					pixels[i + 2] = baseB;				
				}				
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
		}
		else
		{
			throw new RuntimeException("The source image is using an unsupported color model.");
		}
		
		boolean result = true;
		if(!saveImage(image, destFileName)) result = false;
		return result;
	}

	/**
	 * Generates the specified resource if possible. Types are applied after parameters.
	 * @param group
	 * @param relPath
	 * @param type
	 * @return
	 */
	public static boolean update(String group, String relPath, String type)
	{		
		if(relPath.endsWith(".svg"))
		{
			int dpiStart = relPath.lastIndexOf('-');
			if(dpiStart < 0) return false;
			
			String dpiString = relPath.substring(dpiStart + 1, relPath.length() -4);
			if(!dpiString.startsWith("dpi")) return false;
			
			int dpi = Integer.parseInt(dpiString.substring(3));
			if(dpi < 1) return false;
			
			String relPathPng = relPath.substring(0, dpiStart) + ".png";
			ResourceId pngRid = new ResourceId(group, relPathPng, type);
			if(pngRid.isDirty())
			{
				if(!pngRid.update()) return false;
			}
			
			String srcPath = pngRid.getPath();
			String destPath = ResourceManager.getPath(group, relPath, type);
			return Potrace.trace(srcPath, destPath, dpi); 
		}
		else
		{			
			String destFileName = ResourceManager.getPath(group, relPath, type);
			ResourceId rid = new ResourceId(group, relPath, type);
			ResourceId ridParamRoot  = rid.getParamRoot();
			
			//Check if we have parameters to apply
			if(type.startsWith(ResourceManager.TYPE_THUMBNAIL_COLOR))
			{
				String colorName = type.substring(ResourceManager.TYPE_THUMBNAIL_COLOR.length());
				String webColorType = ResourceManager.TYPE_WEB_COLOR + colorName.toUpperCase();
				
				ResourceId ridColored = new ResourceId(group, relPath, webColorType);
				if(ridColored.isDirty())
				{
					if(!ridColored.update()) return false;
				}
				
				return resizeImage(
						ResourceManager.getPath(group, relPath, webColorType),
						destFileName,
						thumbnail_size, thumbnail_size, 
						true
					);
			}
			else if(type.startsWith(ResourceManager.TYPE_WEB_COLOR))
			{
				String colorName =  type.substring(ResourceManager.TYPE_WEB_COLOR.length()).toUpperCase();
				Color color = JavaCanvas.parseColor(colorName);
				if(color == null) return false;
	
				ResourceId ridWeb = new ResourceId(group, relPath, ResourceManager.TYPE_WEB);
				if(ridWeb.isDirty())
				{
					if(!ridWeb.update()) return false;
				}
	
				return setImageColor(ridWeb.getPath(), destFileName, color);
			}
			else if(rid != ridParamRoot) 
			{
				//ensure that ridParamRoot exists				
				if(ridParamRoot.isDirty())
				{
					//fail if the original is missing
					if(ridParamRoot.type.equals(ResourceManager.TYPE_ORIGINAL)) return false;

					//if not the original then make it
					if(!ridParamRoot.update()) return false;
				}
				
				//generate rid from ridParamRoot
				
				BufferedImage image = loadImage(ridParamRoot.getPath());
				if(image == null) return false;
				
				String[] params = rid.getParams();
				for(int i=0; i<params.length; i++)
				{
					String param = params[i];
					if(param.startsWith(ResourceId.PARAM_GRADIENT))
					{
						int offset = ResourceId.PARAM_GRADIENT.length();
						if(param.length() != offset + 12) return false;
						Color startColor = JavaCanvas.parseColor(param.substring(offset, offset + 6));
						Color endColor = JavaCanvas.parseColor(param.substring(offset + 6));
						if(!applyColorGradient(image, startColor, endColor)) return false;
					}
					else if(param.startsWith(ResourceId.PARAM_LINEAR_TINT))
					{
						int offset = ResourceId.PARAM_LINEAR_TINT.length();
						if(param.length() != offset + 10) return false;
						Color color = JavaCanvas.parseColor(param.substring(offset, offset + 6));
						float inflectionPoint = Integer.parseInt(param.substring(offset + 6, offset + 8), 16);
						float alpha = Integer.parseInt(param.substring(offset + 8), 16);
						if(!applyLinearColorTint(image, color, inflectionPoint, alpha)) return false;
					}
					else if(param.equals(ResourceId.PARAM_MIRROR_HORIZONTAL))
					{
						if(!applyMirrorHorizontal(image)) return false;
					}
					else if(param.equals(ResourceId.PARAM_MIRROR_VERTICAL))
					{
						if(!applyMirrorVertical(image)) return false;
					}
					else if(param.equals(ResourceId.PARAM_MONOCHROME))
					{
						if(!applyMonochrome(image)) return false;
					}
					else return false;
				}
				
				return saveImage(image, rid.getPath());									
			}	
			else //We have no parameters
			{
				ResourceId originalRid = new ResourceId(group, relPath, ResourceManager.TYPE_ORIGINAL);
				if(originalRid.isDirty())
				{
					if(!originalRid.update()) return false;
				}
				
				String srcFileName = originalRid.getPath();
						
				if(type.equals(ResourceManager.TYPE_THUMBNAIL))
				{
					return resizeImage(srcFileName, destFileName, thumbnail_size, thumbnail_size, true);
				}
				else if(type.equals(ResourceManager.TYPE_WEB))
				{
					return resizeImage(srcFileName, destFileName, 600, 600, true);
				}
				else if(type.equals(ResourceManager.TYPE_INVERTED))
				{
					BufferedImage image = loadImage(srcFileName);
					if(image == null) return false;
					
					CommonImageFilters.invert(image);
					
					boolean result = true;
					if(!saveImage(image, destFileName)) result = false;
					return result;
				}
				else if(type.equals(ResourceManager.TYPE_EMBOSSER_M))
				{
					BufferedImage image = loadImage(srcFileName);
					if(image == null) return false;
					
					image = embosserMale(image);				
					return saveImage(image, destFileName);
				}
				else if(type.equals(ResourceManager.TYPE_EMBOSSER_F))
				{
					BufferedImage image = loadImage(srcFileName);
					if(image == null) return false;
					
					image = embosserFemale(image);				
					return saveImage(image, destFileName);
				}
			}			
			
			return false;
		}
	}
}
