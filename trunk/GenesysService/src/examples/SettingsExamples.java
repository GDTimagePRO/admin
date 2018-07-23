package examples;

import java.io.IOException;
import java.io.InputStreamReader;
import java.net.URL;

import com.google.gson.Gson;


public class SettingsExamples {
	
	public static class Params {
		public String opName;
		public int srcId;
		public int destId;
		
		
		
	}


	public final static String SERVICE_RESOURCE_OP = "http://localhost:8080/ARTServer/ResourceOp";
	
	public static final class Result
	{
		public int errorCode;
		public String errorMessage;
		public String[] data;
	}
	
	public static Result resourceOpCopy(final int sourceId, final int destintationId) throws IOException
	{
		//Create anonymous class to hold our parameters to be converted to JSON
		
		
		//Convert the anonymous class to json
		Gson g = new Gson();
		String json_param = g.toJson(params);
		
		//Create a new url to retrieve result from
		URL resource_service = new URL(SERVICE_RESOURCE_OP + "?params=" + json_param);
		
		//Create a reader for the URL stream
		InputStreamReader stream = new InputStreamReader(resource_service.openStream());
		
		return g.fromJson(stream, Result.class);
	}
}


