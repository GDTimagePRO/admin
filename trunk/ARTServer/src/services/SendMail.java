package services;

import java.io.IOException;
import java.net.URL;

import javax.activation.DataHandler;
import javax.activation.FileDataSource;
import javax.activation.URLDataSource;
import javax.mail.Message;
import javax.mail.Multipart;
import javax.mail.Session;
import javax.mail.Transport;
import javax.mail.internet.InternetAddress;
import javax.mail.internet.MimeBodyPart;
import javax.mail.internet.MimeMessage;
import javax.mail.internet.MimeMultipart;
import javax.naming.Context;
import javax.naming.InitialContext;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;




import com.google.gson.Gson;

import data.ResourceId;
import data.ResourceManager;

@WebServlet("/SendMail")
public class SendMail extends HttpServlet
{
	private static final long serialVersionUID = 691641653377368791L;

	public static final class AttachmentParams
	{
		public String rid;
		public String sid;
		public String fileName;
		public String url;
	}
	
	public static final class Params
	{
		public String from;
		public String[] to;
		public String subject;
		public String messageHTML;
		public AttachmentParams[] attachments;
	}
	
	public static final class Result
	{
		public int errorCode;
		public String errorMessage;
		
		public Result(int errorCode, String errorMessage)
		{
			this.errorCode = errorCode;
			this.errorMessage = errorMessage;
		}
	}
	
	@Override
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException
	{
		response.setHeader("Cache-Control", "no-store, private, no-cache, must-revalidate");
		response.setHeader("Cache-Control", "pre-check=0, post-check=0, max-age=0, max-stale = 0");
		response.setHeader("Pragma", "public");
		response.setHeader("Pragma", "no-cache");
		response.setDateHeader("Expires", 0);

		Gson gson = new Gson();

		try
		{
			Params params = gson.fromJson(request.getParameter("params"), Params.class);
			
			//Properties props = new Properties();
	        //Session session = Session.getDefaultInstance(props, null);
			
			Context ctx = new InitialContext();
			Session session = (Session)ctx.lookup("default_email_session");			
			
	        Message msg = new MimeMessage(session);
			
	        msg.setSubject(params.subject);
			if(params.from != null) msg.setFrom(new InternetAddress(params.from));
	        
	        for(int i=0; i<params.to.length; i++)
	        {
	        	msg.addRecipient(
	        			Message.RecipientType.TO,
	                    new InternetAddress(params.to[i])
	        		);
	        }
	        
			Multipart mp = new MimeMultipart();
			MimeBodyPart htmlPart = new MimeBodyPart();

			htmlPart.setContent(params.messageHTML, "text/html");
			mp.addBodyPart(htmlPart);
			
			if(params.attachments != null)
			{
				for(int i=0; i<params.attachments.length; i++)
				{
					AttachmentParams ap = params.attachments[i];

					MimeBodyPart attachment = new MimeBodyPart();
					if (ap.rid.isEmpty()) {
						attachment.setDataHandler(new DataHandler(new URL(ap.url)));
						attachment.setFileName(ap.fileName);
					} else {
				        ResourceId rid = ResourceManager.parseId(ap.rid);
				        
				        if(rid.isDirty()) rid.update();			        
				        attachment.setFileName(ap.fileName != null ? ap.fileName : rid.getFileName());
				        
				        //byte[] attachmentData = org.apache.commons.io.FileUtils.readFileToByteArray(new File(rid.getPath()));			        			        
				        //attachment.setContent(attachmentData, rid.getMimeType());
				        attachment.setDataHandler(new DataHandler(new FileDataSource(rid.getPath())));
				        
	
				        if(ap.sid != null) attachment.setContentID("<" + ap.sid + ">");
					}
			        mp.addBodyPart(attachment);
				}
			}
			
			msg.setContent(mp);
			Transport.send(msg);
			
			response.getWriter().write(gson.toJson(new Result(0, null)));
		}
		catch(Exception e)
		{
			e.printStackTrace();
			response.getWriter().write(gson.toJson(new Result(1, e.getMessage())));
		}
	}
}
