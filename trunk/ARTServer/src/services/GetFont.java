package services;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;


import data.ResourceManager;

@WebServlet("/GetFont/*")
public class GetFont extends HttpServlet {

	private static InputStream getFontFile(String name)
	{
		try
		{
			String path = null;	
			
			if(name.indexOf('/') > -1)
			{
				return ResourceManager.parseId(name).getInputStream();
			}
			else						
			{
				return new FileInputStream(new File(ResourceManager.getPath(ResourceManager.GROUP_LEGACY_FONTS, name +".ttf")));
			}
		}
		catch(Exception e) { throw new RuntimeException("Error loading font : " + name + " Path is: " + (name.indexOf('/') > -1 ? ResourceManager.parseId(name).getPath() : ResourceManager.getPath(ResourceManager.GROUP_LEGACY_FONTS, name +".ttf"))); }
	}
	
	@Override
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException
	{	
		response.setHeader("Access-Control-Allow-Origin", "*");
		response.setHeader("Cache-Control", "public, max-age=604800");
		String fontPath = request.getPathInfo().substring(1);
		response.setContentType("application/x-font-ttf");
		byte[] buffer = new byte[1024];
		InputStream is = getFontFile(fontPath);
		OutputStream os = response.getOutputStream();
		
		int bytesRead;
		while((bytesRead = is.read(buffer)) > -1)
		{
			os.write(buffer,0,bytesRead);
		}
	    os.flush();
	    os.close();
	}
	
}
