package graphics;

import java.awt.image.BufferedImage;
import java.io.BufferedReader;
import java.io.File;
import java.io.InputStreamReader;

import javax.imageio.ImageIO;

import data.ResourceManager;

public class Potrace
{
	private static final String POTREACE_PATH = (ResourceManager.BIN_DIRECTORY + "/potrace.exe").replace('/', '\\');
	private static final String COLOR_TRACE_PATH = (ResourceManager.BIN_DIRECTORY + "/color_trace/color_trace_multi.exe").replace('/', '\\');

	public static boolean trace(String inputPath, String outputPath, int dpi)
	{
		return trace(inputPath, outputPath, dpi, false);
	}
	
	public static boolean trace(String inputPath, String outputPath, int dpi, boolean color)
	{
		File tempFile = null;
		try
		{
			inputPath = inputPath.replace('/', '\\');
			outputPath = outputPath.replace('/', '\\');
			
			if(!outputPath.toLowerCase().endsWith(".svg")) return false;

			if(!inputPath.toLowerCase().endsWith(".bmp"))
			{
				BufferedImage image = ImageIO.read(new File(inputPath));					
				tempFile = File.createTempFile("potrace_temp", Long.toString(System.nanoTime()) + '_' + inputPath.hashCode() + ".bmp");
				ImageIO.write(image, "bmp", tempFile);
				inputPath = tempFile.getAbsolutePath();
			}
			
			String args = "";
			if (!color) {
				args = POTREACE_PATH  + " \"" + inputPath + "\" -o \"" + outputPath + "\" --svg -r " + dpi + " --longcurve --turdsize 0"; 
			} else {
				args = COLOR_TRACE_PATH + " -i \"" + inputPath + "\" -d \"" + outputPath + "\" -c 10 -D 0 -O 0";
			}
			
			Process process = Runtime.getRuntime().exec(args);
			BufferedReader result = new BufferedReader(new InputStreamReader(process.getInputStream()));
			int exitCode = process.waitFor();
			
			if(exitCode == 0) return true;
			
			System.out.println(
					"Trace failed [ "  + exitCode + " ] : " + 
					org.apache.commons.io.IOUtils.toString(result)
				);
			return false;
		}
		catch(Exception e)
		{
			throw new RuntimeException("Trace error : " + e.getMessage());
		}
		finally
		{
			if(tempFile != null && tempFile.exists()) tempFile.delete();
		}
	}
	
}
