package backend;

import com.google.gson.Gson;

import backend.Order.ConfigItem;


class Design
{
	
	public class DesignTemplateCategory
	{
		public int id = -1;
		public String customerId = new String();
		public String name = new String();
	}

	public static class DesignTemplate
	{
		public int id = -1;
		public String name;
		public String categoryId;
		public String productTypeId;
		public String designJSON;		
		public String configJSON;
		
		public ConfigItem getConfigItem()
		{
			try
			{
				return ConfigItem.fromJSON(configJSON);
			}
			catch (Exception $e)
			{
				return null;	
			}
		}
		
		void setConfigItem(ConfigItem value)
		{
			configJSON = value.toJSON();
		}		
		
		public static String previewImageId(int designTemplateId)
		{
			return ResourceManager.getId(ResourceManager.GROUP_DESIGNE_TEMPLATES, (designTemplateId + "_prev.png"),
					ResourceManager.TYPE_ORIGINAL);
		}

		public String getPreviewImageId() { return DesignTemplate.previewImageId(id); }
	}
	
	//note: SCL : Simple Canvas Log aka Trace
	public static final int STATE_PENDING_SCL_DATA		= 0;
	public static final int STATE_PENDING_CONFIRMATION	= 10;
	public static final int STATE_PENDING_SCL_RENDERING	= 20;
	public static final int STATE_READY					= 30;
	public static final int STATE_ARCHIVED				= 40;
	
	public int id = -1;
	public String orderItemId;
	public String productTypeId;
	public String configJSON;
	public String designJSON;
	public String dateChanged;
	public String state;
	public String productId;
	public String dateRendered;
	
	
	// Requires tweaking
	public static String colorsFromJSON(String json)
	{
		/*Gson g = new Gson();
		designState = json_decode(json);
		if(isset(designState.scene.colors)) return designState.scene.colors;
		
		colors = new StdClass();
		colors.ink = new StdClass();
		colors.ink.name = "Black";
		colors.ink.value = "000000";
			
		return colors;*/
		return null;
	}
	
	public String getColorsFromJSON()
	{			
		return colorsFromJSON(designJSON);
	}
	
	public ConfigItem getConfigItem()
	{
		return ConfigItem.fromJSON(configJSON);
	}
	
	public void setConfigItem(ConfigItem value)
	{
		configJSON = value.toJSON();
	}
	
	public static String previewImageId(int designId)
	{
		return ResourceManager.getId(ResourceManager.GROUP_DESIGNES, (designId + "_prev.png"),
				ResourceManager.TYPE_ORIGINAL);
	}
	
	public static String highDefImageId(int designId)
	{
		return ResourceManager.getId(ResourceManager.GROUP_DESIGNES, (designId + "_hd.png"),
				ResourceManager.TYPE_ORIGINAL);
	}
	
	public static String highDefSvgId(int designId)
	{
		return ResourceManager.getId(ResourceManager.GROUP_DESIGNES, (designId + "_hd.svg"),
				ResourceManager.TYPE_ORIGINAL);
	}
	
	public String getPreviewImageId() { return previewImageId(id); }
	public String getHighDefImageId() { return highDefImageId(id); }
	public String getHighDefSvgId() { return highDefSvgId(id); }
}
	

