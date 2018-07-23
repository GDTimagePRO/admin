package backend;

import java.net.URL;

public class Theme_Interface {
	class ThemeInterface {
		public String[] vars;
		public customerId = NULL;
		public themeName = NULL;
		public homeURL = NULL;
		public sessionId = NULL;
		public config = NULL;
	
		public String HOME_URL = "";

		public String getVar(String name)
		{
			if(vars[name] != null) return vars[name];
			if((config != null) && (config.vars != null))
			{
				if(config.vars.name != null) return config.vars.name; 
			}
			return "%" + name + "%"; 
		}
	
		public function getVarHTML(String name)
		{
			return htmlentities(this.getVar(name));   
		}
	
		public URL getImageUrl(String imageId, Boolean noCaching)
		{
			return Settings.getImageUrl(imageId, noCaching);
		}
		
		public URL getImageUrl(String imageId)
		{
			return Settings.getImageUrl(imageId, false);
		}
	
		public URL systemURL(URL url)
		{
			if(sessionId == null) return url;
			String temp = url.toString();
			temp.concat(temp.contains("?") ? "&" : "?");		
			url = temp.concat("sid=" + sessionId);
			return url; 
		}
	}
}
