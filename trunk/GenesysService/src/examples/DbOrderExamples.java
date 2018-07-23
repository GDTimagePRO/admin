package examples;

import java.net.URL;

import com.google.gson.Gson;

public class DbOrderExamples {
	
	
	public static class ConfigItem
	{
	    public static final char TEMPLATE_CATEGORY_ID_WILDCARD = '*';

	    //Types probably need modification, not sure where they are used yet
	    public Integer productId = null;
	    public Integer templateId = null;
	    public Integer templateCategoryId = null;
	    public Integer colors = null;
	    public Integer misc = null;

	    /**
	     * @return ConfigItem
	     */
	    public static ConfigItem merge(ConfigItem primary, ConfigItem secondary)
	    {
	        if(primary == null) primary = new ConfigItem();
	        if(secondary == null) secondary = new ConfigItem();

	        ConfigItem result = new ConfigItem();
	        result.productId = primary.productId == null ?  secondary.productId : primary.productId;
	        result.templateId = primary.templateId == null ?  secondary.templateId : primary.templateId;
	        result.templateCategoryId = primary.templateCategoryId == null ?  secondary.templateCategoryId : primary.templateCategoryId;
	        result.colors = primary.colors == null ?  secondary.colors : primary.colors;
	        result.misc = primary.misc == null ?  secondary.misc : primary.misc;
	        return result;
	    }

	    //These two functions need to be reworked as what the php was doing makes little sense in java
	    public String toJSONObject()
	    {
	    	Gson gson = new Gson();
	    	return gson.toJson(this);
	    }

	    public String toJSON()
	    {
	        return toJSONObject();
	    }

	    public static ConfigItem fromJSONObject(String json)
	    {
	    	Gson gson = new Gson();
	    	return gson.fromJson(json, ConfigItem.class);
	    }

	    public static ConfigItem fromJSON(String json)
	    {
	        if(json == null || json.isEmpty()) return new ConfigItem();
	        return fromJSONObject(json);
	    }
	}
}
