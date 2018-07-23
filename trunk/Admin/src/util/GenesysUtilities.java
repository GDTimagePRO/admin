package util;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;

import com.admin.ui.AdminSerlvetListener;

public class GenesysUtilities {
	
	public static String getTemplateImageURL(Object imageId) {
		String url;
		String genesys_url = "http://genesys.in-stamp.com:8080/ARTServer";
		try {
			Context context = new InitialContext();
			genesys_url = (String) context.lookup(AdminSerlvetListener.GenesysURL);
		} catch (NamingException e) {
			e.printStackTrace();
		}
		url = genesys_url + "/GetImage?id=thumbs.design_templates/" + imageId + "_prev.png";
		return url;
	}
	
}
