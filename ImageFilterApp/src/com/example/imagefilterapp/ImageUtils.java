package com.example.imagefilterapp;

import java.awt.Color;
import java.awt.Graphics2D;
import java.awt.RenderingHints;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;

import javax.imageio.ImageIO;

public final class ImageUtils
{
	public static BufferedImage loadImage(File from)
	{
		try { return ImageIO.read(from); }
		catch(Exception e)
		{
			//e.printStackTrace();
			return null;
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
		
		float[] pixels = image.getData().getPixels(0, 0, width, height, (float[])null);
		
		for(int i = 0; i < pixels.length; i += 3)
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
	
	
	public static BufferedImage make24BitImage(File src, int maxWidth, int maxHeight)
	{
		BufferedImage image = loadImage(src);
		if(image == null) return null;
			
		int destWidth = maxWidth;
		int destHeight = maxHeight;

		if((maxWidth > 0) && (maxHeight > 0))
		{
			if(image.getWidth() > image.getHeight())
			{
				destWidth = maxWidth;
				destHeight = (int)Math.round((float)maxWidth / (float)image.getWidth() * (float)image.getHeight());
			}
			else
			{
				destHeight = maxHeight;
				destWidth = (int)Math.round((float)maxHeight / (float)image.getHeight() * (float)image.getWidth());
			}
		}
		else if(image.getType() == BufferedImage.TYPE_3BYTE_BGR)
		{
			return image;
		}
		
		BufferedImage newImage = new BufferedImage(destWidth,destHeight,BufferedImage.TYPE_3BYTE_BGR);
		Graphics2D g2d = newImage.createGraphics();
		g2d.setRenderingHint(RenderingHints.KEY_DITHERING, RenderingHints.VALUE_DITHER_ENABLE);
		g2d.setRenderingHint(RenderingHints.KEY_INTERPOLATION, RenderingHints.VALUE_INTERPOLATION_BICUBIC);
		g2d.setRenderingHint(RenderingHints.KEY_RENDERING, RenderingHints.VALUE_RENDER_QUALITY);
		
		if(image.getType() != BufferedImage.TYPE_3BYTE_BGR)
		{
			g2d.setColor(Color.WHITE);
			g2d.fillRect(0, 0, newImage.getWidth(), newImage.getHeight());
		}
		
		g2d.drawImage(image, 0, 0, newImage.getWidth(), newImage.getHeight(), null);
		g2d.dispose();
		
		return newImage;
	}
	
	public static File makeSmallPng(File src, int maxWidth, int maxHeight)
	{
		BufferedImage newImage = make24BitImage(src, maxWidth, maxHeight);
		if(newImage == null) return null;
		
		String destFileName = src.getName();
		int newFileNameLen = destFileName.lastIndexOf(".");
		if(newFileNameLen > -1) destFileName = destFileName.substring(0, newFileNameLen);
		destFileName += "_small.png";
		
		File dest = new File(src.getParentFile(), destFileName);
		
		try
		{			
			ImageIO.write(newImage, "png", dest);
			return dest;
		}
		catch(IOException e)
		{
			//e.printStackTrace();
			return null;
		}
	}
}
