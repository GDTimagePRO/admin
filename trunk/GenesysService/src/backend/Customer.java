package backend;

import com.google.gson.Gson;

class Customer
{
	
	public class CustomerConfig
    {
        public String theme;
        public String render_email; // svg | png
        public String vars; //{TITLE:"Theme Page Title}"
    }
	
    public static final char KEY_INTERNAL = '*';

    public int id = -1;
    public String idKey = null;
    public String domain = null;
    public String description = null;
    public String emailAddress = null;
    public String configJSON = null;

    /**
     * @return CustomerConfig
     */
    public CustomerConfig getConfigObj()
    {
        if(configJSON == null) return new CustomerConfig();
        Gson g = new Gson();
        return g.fromJson(configJSON, CustomerConfig.class);
    }
}