package backend;

import java.io.*;
import java.net.MalformedURLException;
import java.net.URL;

import com.google.gson.Gson;

public class Settings {
	public static final class Result {
		public static final int CODE_OK = 0;
		public int errorCode;
		public String errorMessage;
		public String data;
	}

	public static final String ADMIN_LOGIN = "admin";
	public static final String ADMIN_PASSWORD = "woot";

	public static final String DB_SERVER = "localhost";
	public static final String DB_USER_NAME = "root";
	public static final String DB_PASSWORD = "abc123";
	public static final String DB_SCHEMA_NAME = "genesys_core";
	public static final String DIR_DATA_ROOT = "C:/_genesys_data_";

	public static final int HD_IMAGE_DPI = 600;

	public static final String HOME_URL = "localhost/ssi_30_mm/";

	public static final String SERVICE_RENDER_SCENE = "http://localhost:8080/ARTServer/RenderScene";
	public static final String SERVICE_GET_IMAGE = "http://localhost:8080/ARTServer/GetImage";
	public static final String SERVICE_RESOURCE_OP = "http://localhost:8080/ARTServer/ResourceOp";
	public static final String SERVICE_CREATE_COLLAGE = "http://localhost:8080/ARTServer/CreateImageCollage";
	public static final String SERVICE_SEND_MAIL = "http://localhost:8080/ARTServer/SendMail";

	/**
	 * @return ResourceOpResult
	 */
	public static Result resourceOpCopy(int srcId, int destId)throws IOException {
		Gson gson = new Gson();
		String data = ("{\"opName\":\"copy\"," + "\"srcId\":\"" + srcId
				+ "\",\"destId\":\"" + destId + "\"}");
		URL search = new URL(SERVICE_RESOURCE_OP + "?params=" + data);
		InputStreamReader output = new InputStreamReader(search.openStream());
		return gson.fromJson(output, Result.class);
	}

	/**
	 * @return ResourceOpResult
	 */
	public static Result resourceOpTrace(int srcId, int destId, int dpi)throws IOException {
		Gson gson = new Gson();
		String data = ("{\"opName\":\"trace\"," 
				+ "\"srcId\":\"" + srcId
				+ "\",\"destId\":\"" + destId + "\"," 
				+ "\"dpi\":" + dpi + "\"}");
		URL search = new URL(SERVICE_RESOURCE_OP + "?params=" + data);
		InputStreamReader output = new InputStreamReader(search.openStream());
		return gson.fromJson(output, Result.class);
	}

	/**
	 * @return ResourceOpResult
	 */
	public static Result resourceOpProcessUserUploadedImage(int srcId,int colorModel) throws IOException {
		Gson gson = new Gson();
		String data = ("{\"opName\":\"copy\","
				+ "\"srcId\":\"" + srcId + "\",\""
				+ "colorModel\":\"" + colorModel + "\"}");
		URL search = new URL(SERVICE_RESOURCE_OP + "?params=" + data);
		InputStreamReader output = new InputStreamReader(search.openStream());

		return gson.fromJson(output, Result.class);
	}

	public static URL getImageUrl(String imageId) throws MalformedURLException {
		URL ImageURL = new URL(SERVICE_GET_IMAGE + "?id=" + imageId);
		return ImageURL;
	}

	public static URL getImageUrl(String imageId, Boolean noCaching)throws MalformedURLException {
		String search = new String(SERVICE_GET_IMAGE + "?id=" + imageId);
		if (noCaching)
			search.concat("&nocache=true");
		URL imageURL = new URL(search);
		return imageURL;
	}

	public static URL getImageUrl(String imageId, String saveas) throws MalformedURLException {
		URL ImageURL = new URL(SERVICE_GET_IMAGE + "?id=" + imageId + "&saveas="
				+ saveas);
		return ImageURL;
	}

	public static URL getImageUrl(String imageId, Boolean noCaching, String saveas)throws MalformedURLException {
		String search = new String(SERVICE_GET_IMAGE + "?id=" + imageId);
		if (noCaching) search.concat("&nocache=true");
		search.concat("&savas=" + saveas);
		URL imageURL = new URL(search);
		return imageURL;
	}

}
