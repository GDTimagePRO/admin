package backend;

import com.google.gson.Gson;

public class Order {
	public static class ConfigItem
    {
        public static final String TEMPLATE_CATEGORY_ID_WILDCARD = "*";

        public Integer productId = null;
	    public Integer templateId = null;
	    public String templateCategoryId = null;
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
            result.productId = ((primary.productId == null) ?  secondary.productId : primary.productId);
            result.templateId = (primary.templateId == null) ?  secondary.templateId : primary.templateId;
            result.templateCategoryId = (primary.templateCategoryId == null) ?  secondary.templateCategoryId : primary.templateCategoryId;
            result.colors = (primary.colors == null) ?  secondary.colors : primary.colors;
            result.misc = primary.misc == null ?  secondary.misc : primary.misc;
            return result;
        }

        public String toJSON()
        {
        	Gson gson = new Gson();
            return gson.toJson(this);
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

    public static class Config
    {
        public static final Object UI_MODE_NORMAL = null;
        public static final String UI_MODE_SIMPLE = "simple";

        public String  uiMode = null;
        public String items = null;
        public String theme = null;

        public String toJSON()
        {
            Gson g = new Gson();
            return g.toJson(this);
        }

        public static Config fromJSON(String json)
        {
            Gson g = new Gson();
            return g.fromJson(json, Config.class);
        }
    }


}
