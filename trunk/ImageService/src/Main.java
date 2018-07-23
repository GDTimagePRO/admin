import java.io.File;
import java.io.FileInputStream;
import java.io.InputStreamReader;

import javax.imageio.ImageIO;

import com.google.gson.Gson;


public class Main
{

	public static void main(String[] args)
	{
		
		try
		{
			
			String id = "2041";
			//String id = "2040"; 
			//String id = "2037_scl"; 
			
			SCLRenderer renderer = new SCLRenderer();			
			//JavaCanvas canvas = renderer.render(new FileInputStream("C:\\Users\\QuetechDev01\\Desktop\\Broken\\_test_\\2013_scl.txt"));
			//JavaCanvas canvas = renderer.render(new FileInputStream("C:\\Users\\QuetechDev01\\Desktop\\batch\\" + id + "_scl.txt"));
			JavaCanvas canvas = renderer.render(new FileInputStream("C:\\_v3_images\\original\\system\\designs\\" + id + "_scl.txt"));
			
			

//			JavaCanvas canvas = new  JavaCanvas(5000, 5000);
//			
//
//			canvas.setFont("70px Ribbon");
//			
//			int x = 250;
//			int y = 250;
//			
//			canvas.scale(20, 20);
//			canvas.translate(-400, -200);
//
//			canvas.beginPath();
//			canvas.moveTo(0, y);
//			canvas.lineTo(1500, y);
//			canvas.stroke();
//			
//			canvas.setTextBaseline("top");
//			canvas.fillText("y Top y", 50, y);
//			
//			canvas.setTextBaseline("bottom");
//			canvas.fillText("y Bottom y", 250, y);
//
//			canvas.setTextBaseline("middle");
//			canvas.fillText("y Middle y", 500, y);
//
//			canvas.setTextBaseline("alphabetic");
//			canvas.fillText("y Alpha y", 750, y);

			String fileName = "C:\\Users\\QuetechDev01\\Desktop\\batch\\" + id + "_out.png";			
			File outFile = new File(fileName);
			if(outFile.exists()) outFile.delete();			
			ImageIO.write(canvas.getImage(), "png", outFile);
			
		}
		catch(Exception e) { e.printStackTrace(); }
	}
}
