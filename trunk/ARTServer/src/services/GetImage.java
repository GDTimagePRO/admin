package services;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.Date;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import data.ResourceId;
import data.ResourceManager;

@WebServlet("/GetImage")
public class GetImage extends HttpServlet
{
	private static final long serialVersionUID = 4328729185853005580L;

	@Override
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException
	{		
		try
		{
			response.setHeader("Access-Control-Allow-Origin", "*");
	
			ResourceId rid = ResourceManager.parseId(request.getParameter("id"));
			if(rid == null)
			{
				response.sendError(HttpServletResponse.SC_NOT_FOUND);
				return;
			}
			
			if(rid.isDirty()) rid.update();
		
			long timeChanged = rid.getDateChanged();
			if(timeChanged == 0)
			{
				response.sendError(HttpServletResponse.SC_NOT_FOUND);
				return;
			}
	
			
			if("true".equals(request.getParameter("nocache")))
			{
				response.setHeader("Cache-Control", "no-store, private, no-cache, must-revalidate");
				response.setHeader("Cache-Control", "pre-check=0, post-check=0, max-age=0, max-stale = 0");
				response.setHeader("Pragma", "public");
				response.setHeader("Pragma", "no-cache");
				response.setDateHeader("Expires", 0);
				response.setDateHeader("Last-Modified", timeChanged);
			}
			else
			{
				response.setHeader("Cache-Control", "private, max-age=10800, pre-check=10800");
				response.setHeader("Pragma", "private");
				response.setDateHeader("Expires", new Date().getTime() + 172800000); //2 days
				response.setDateHeader("Last-Modified", timeChanged);
				
				long clientLastModified = request.getDateHeader("If-Modified-Since");
				
				if((clientLastModified > -1) && (clientLastModified == timeChanged))
				{
					response.sendError(HttpServletResponse.SC_NOT_MODIFIED);
					return;
				}
			}
				
			if(rid.path.endsWith(".png"))
			{				
				response.setContentType("image/png");
			}
			else if(rid.path.endsWith(".jpg"))
			{
				response.setContentType("image/jpeg");
			}
			else if(rid.path.endsWith(".svg"))
			{
				response.setContentType("image/svg+xml");
			}
			
			if(request.getParameter("saveas") != null)
			{
				response.setHeader("Content-Disposition","attachment; filename=\"" + request.getParameter("saveas") + "\"");
			}
			
			byte[] buffer = new byte[1024];
			InputStream is = rid.getInputStream();
			OutputStream os = response.getOutputStream();
			
			int bytesRead;
			while((bytesRead = is.read(buffer)) > -1)
			{
				os.write(buffer,0,bytesRead);
			}
			is.close();
			os.close();
		}
		catch (IOException e) {
			e.printStackTrace();
		}
		catch (Exception e)
		{
			response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
			e.printStackTrace();
		}		
	}
}
