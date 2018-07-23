package graphics.filters;

import java.awt.image.BufferedImage;

public class CommonImageFilters
{
	public static void invert(BufferedImage image)
	{		
		if(	(image.getType() == BufferedImage.TYPE_3BYTE_BGR) ||
			(image.getType() == BufferedImage.TYPE_4BYTE_ABGR) )
		{
			int width = image.getWidth();
			int height = image.getHeight();
			int[] pixels = image.getData().getPixels(0, 0, width, height, (int[])null);
			int len = pixels.length;
			
			for(int i=0; i<len; i++)
			{
				pixels[i] = 255 - pixels[i];
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
		}
		else if(image.getType() == BufferedImage.TYPE_4BYTE_ABGR)
		{
			int width = image.getWidth();
			int height = image.getHeight();
			int[] pixels = image.getData().getPixels(0, 0, width, height, (int[])null);
			int len = pixels.length;

			for(int i=1; i<len; i+=4)
			{
				pixels[i] = 255 - pixels[i];
				pixels[i+1] = 255 - pixels[i+1];
				pixels[i+2] = 255 - pixels[i+2];
			}
			
			image.getRaster().setPixels(0, 0, width, height, pixels);
		}
		else throw new RuntimeException("Image type is not supported by CommonImageFilters.invert");
	}
	
}


