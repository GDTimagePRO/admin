package services;

import graphics.Potrace;

import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

import javax.imageio.ImageIO;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.google.gson.Gson;

import data.ImageResourceManager;
import data.ResourceId;

@WebServlet("/ResourceOp")
public class ResourceOp  extends HttpServlet 
{
	public static final String OP_COPY = "copy";
	public static final String OP_TRACE = "trace";
	public static final String OP_PROCESS_USER_UPLOADED_IMAGE = "process_user_uploaded_image";
	public static final String OP_INFO = "info";
	
	private static final long serialVersionUID = 8798871135969773772L;

	public static final class Params
	{
		public String opName;
		public String srcId;
		public String destId;
		public int dpi;
		public String param1;

	}
	
	public static final class Result
	{
		public int errorCode;
		public String errorMessage;
		public String[] data;
		
		public Result(int errorCode, String errorMessage)
		{
			this.errorCode = errorCode;
			this.errorMessage = errorMessage;
			this.data = null;
		}

		public Result(String[] data)
		{
			this.errorCode = 0;
			this.errorMessage = null;
			this.data = data;
		}
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

		Gson gson = new Gson();
		
		try
		{
			Params params = gson.fromJson(request.getParameter("params"), Params.class);
			if(OP_COPY.equals(params.opName))
			{
				ResourceId srcRID = ResourceId.fromId(params.srcId);
				if(srcRID.isDirty()) srcRID.update();
				
				ResourceId destRID = ResourceId.fromId(params.destId);
				
				byte[] buffer = new byte[1024];
				InputStream is = srcRID.getInputStream();
				OutputStream os = destRID.getOutputStream();
				
				int bytesRead;
				while((bytesRead = is.read(buffer)) > -1)
				{
					os.write(buffer,0,bytesRead);
				}
				
				is.close();
				os.close();
				response.getWriter().write(gson.toJson(new Result(0, null)));
			}
			else if(OP_TRACE.equals(params.opName))
			{
				if(params.destId.toLowerCase().endsWith(".svg"))
				{
					if(params.dpi < 1) throw new RuntimeException("Invalid DPI value : " + params.dpi);
					
					ResourceId srcRID = ResourceId.fromId(params.srcId);
					if(srcRID.isDirty()) srcRID.update();
					
					String srcPath = srcRID.getPath();
					if(!(new File(srcPath)).exists()) throw new RuntimeException("Source id does not exit : " + params.srcId);
															
					ResourceId destRID = ResourceId.fromId(params.destId);
					String destPath = destRID.getPath();
					
					if(Potrace.trace(srcPath, destPath, params.dpi))
					{
						response.getWriter().write(gson.toJson(new Result(0, null)));
					}
					else
					{
						response.getWriter().write(gson.toJson(new Result(1, "Image trace failed.")));
					}
				}
				else throw new RuntimeException("Unknown output format : " + params.destId); 
			}
			else if(OP_PROCESS_USER_UPLOADED_IMAGE.equals(params.opName))
			{
				ResourceId srcRID = ResourceId.fromId(params.srcId);
				String srcPath = srcRID.getPath();
				BufferedImage image = ImageIO.read(new File(srcPath));
				
				if(image.getType() != BufferedImage.TYPE_4BYTE_ABGR)
				{
					BufferedImage newImage  = new BufferedImage(image.getWidth(), image.getHeight(), BufferedImage.TYPE_4BYTE_ABGR);
					newImage.getGraphics().drawImage(image, 0, 0, null);
					image = newImage;
				}
				
				File destFileParent = new File(srcPath).getParentFile();
				File destFile;
				
				int iFile = 0;
				while(true)
				{
					destFile = new File(destFileParent, "UU" + iFile + ".png"); 
					if(!destFile.exists()) break;
					iFile++;
				}
				
				File srcFile = new File(srcPath);
				srcFile.delete();				
				ImageResourceManager.saveImage(image, destFile.getPath());
				
				String srcFileName = srcFile.getName();
				String destFileName = destFile.getName();				
				
				srcRID.path = srcRID.path.substring(0, srcRID.path.length() - srcFileName.length()) + destFileName; 
						
				response.getWriter().write(gson.toJson(new Result(new String[] {srcRID.getId()} )));
			}			
			else if(OP_INFO.equals(params.opName))
			{
				ResourceId srcRID = ResourceId.fromId(params.srcId).getOriginalParamRoot();
				BufferedImage img = ImageIO.read(new File(srcRID.getPath()));
				response.getWriter().write("{\"width\":" + img.getWidth() + ",\"height\":" + img.getHeight() + "}"); 
			}
		}
		catch(Exception e)
		{
			e.printStackTrace();
			response.getWriter().write(gson.toJson(new Result(1, e.getMessage())));
			//response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
		}
	}
}
