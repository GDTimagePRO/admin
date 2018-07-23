package backend;

import java.io.InputStreamReader;
import java.net.URL;

import backend.Settings.Result;

import com.google.gson.Gson;


public class Email_Service {
	class EmailServiceAttachmentParams
	{
		public rid;
		public sid;
		public String fileName;
		
		public EmailServiceAttachmentParams(rid,String fileName, sid)
		{
			this.rid = rid;
			this.sid = sid;
			this.fileName = fileName;
		}
	}
	
	class EmailServiceParams
	{
		public String from = null;
		public String[] to;
		public String subject = "";
		public String messageHTML = "";
		public attachments = array();
	}
	
	class EmailServiceResult
	{
		public String errorCode;
		public String errorMessage;
	}
	
	public class EmailService
	{
		/**
		 * @param EmailServiceParams params
		 * @return EmailServiceResult
		 */
		public EmailServiceResult sendMail(EmailServiceParams params)
		{
			Gson gson = new Gson();
			URL search = new URL(Settings.SERVICE_SEND_MAIL + "?params=" + gson.toJson(params));
			InputStreamReader output = new InputStreamReader(search.openStream());
			return gson.fromJson(output, EmailServiceResult.class);
		}
	}
}
}
