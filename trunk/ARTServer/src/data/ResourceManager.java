package data;

import java.io.File;
import java.util.Hashtable;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;

import services.ContextListener;


public class ResourceManager
{
	public static String ROOT_DIRECTORY;
	public static String BIN_DIRECTORY			= ROOT_DIRECTORY + "/_bin";
	
	public static String GROUP_SESSION			= "session";
	public static String GROUP_DESIGNES			= "designs";			//Images for designs table
	public static String GROUP_DESIGNE_TEMPLATES	= "design_templates";	//Images for design_templates table
	public static String GROUP_ORDER_ITEMS		= "order_items";		//Images for order_items table
	public static String GROUP_CUSTOMER			= "customer";
	public static String GROUP_LEGACY_IMAGES		= "old_db";
	public static String GROUP_LEGACY_FONTS		= "fonts";
	
	public static String TYPE_ORIGINAL			= "original";
	public static String TYPE_THUMBNAIL			= "thumbs";
	public static String TYPE_THUMBNAIL_COLOR		= "thumbs_";
	public static String TYPE_WEB					= "web";
	public static String TYPE_WEB_COLOR			= "web_";
	public static String TYPE_INVERTED			= "inverted";
	public static String TYPE_EMBOSSER_M			= "embosser_m";
	public static String TYPE_EMBOSSER_F			= "embosser_f";
	
	public static String DIR_CUSTOMER_IMAGES		= "images"; 
	public static String DIR_CUSTOMER_FONTS		= "fonts"; 
	public static String DIR_CUSTOMER_THEME		= "themes"; 
	
	private static Hashtable<String, String> _groupPathMap = new Hashtable<>();
	static {
		_groupPathMap.put(GROUP_SESSION,				"system/session");
		_groupPathMap.put(GROUP_DESIGNES,				"system/designs");
		_groupPathMap.put(GROUP_DESIGNE_TEMPLATES,		"system/design_templates");
		_groupPathMap.put(GROUP_ORDER_ITEMS,			"system/order_items");
		_groupPathMap.put(GROUP_CUSTOMER,				"customer");
		_groupPathMap.put(GROUP_LEGACY_IMAGES,			"legacy/images");
		_groupPathMap.put("masonrow",					"legacy/images");
		_groupPathMap.put(GROUP_LEGACY_FONTS,			"legacy/fonts");
	}
	
	public static void init() throws NamingException {
		Context context = new InitialContext();	
		ResourceManager.ROOT_DIRECTORY = (String) context.lookup(ContextListener.BaseFolder);
		BIN_DIRECTORY = ROOT_DIRECTORY + "/_bin"; 
	}
	
	public static String groupToPath(String group) { return _groupPathMap.get(group); }

	public static String getMimeType(String id)
	{
		int startPos = id.lastIndexOf('.');
		if(startPos < 0) return "";
		
		String ext = id.substring(startPos + 1).toLowerCase();
		
		if(ext.equals("png")) return "image/png";
		if(ext.equals("jpg")) return "image/jpeg";
		if(ext.equals("svg")) return "image/svg+xml";
		if(ext.equals("pdf")) return "application/pdf";
		
		return "";
	}
	
	public static String getPath(String group, String relPath, String type, boolean createDir)
	{
		String path = 
			ROOT_DIRECTORY + "/" + 
			type + "/" + 
			groupToPath(group);
		
		if(createDir) (new File(path)).mkdirs();
		if(relPath != null) path += "/" + relPath;
		return path;
	}

	public static String getPath(String group, String relPath, String type)
	{
		return getPath(group, relPath, type, false);
	}

	public static String getPath(String group, String relPath)
	{
		return getPath(group, relPath, TYPE_ORIGINAL, false);
	}
	
	public static boolean update(String group, String relPath, String type)
	{
		if(relPath.endsWith(".png") || (relPath.endsWith(".jpg")) || (relPath.endsWith(".svg")))
		{
			return ImageResourceManager.update(group, relPath, type);
		}
		return false;
	}
	
	public static String getId(String group, String relPath, String type)
	{
		String id;
		if(type.equals(TYPE_ORIGINAL))
		{
			id = group;
		}
		else
		{
			id = type + "." + group; 				
		}
		
		if(relPath != null) id += "/" + relPath;		
		return id;
	}

	public static String getId(String group, String relPath)
	{
		return getId(group, relPath, null);
	}

	public static String getId(String group)
	{
		return getId(group, null, TYPE_ORIGINAL);
	}

	public static ResourceId parseId(String id)
	{
		if(id == null) return null;
		
		String[] bigParts = id.split("/", 2);
		String[] smallParts = bigParts[0].split("\\.",3);

		ResourceId result = new ResourceId();			
		
		if(bigParts.length > 1) result.path = bigParts[1]; 
				
				
		if(smallParts.length == 1)
		{
			result.type = TYPE_ORIGINAL;	
			result.group = smallParts[0];
		}
		else if(smallParts.length == 2)			
		{
			result.type = smallParts[0];	
			result.group = smallParts[1];
		}
		else return null;
		
		return result;
	}
	
	public static String idToPath(String id)
	{
		ResourceId rid = parseId(id);
		if(rid == null) return null;
		return rid.getPath();
	}
}
