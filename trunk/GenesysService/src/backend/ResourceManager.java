package backend;

import java.io.*;
import java.nio.file.Files;
import java.util.ArrayList;
import java.util.Arrays;

	
public class ResourceManager {
	
	// Errors/Incomplete code
	/*
	 *  the private function in the resourceManager class, contains
	    file naming conventions, group designe templates, is spelled wrong 
	    (system/design_templ ates), possible consistency error
	 *  The update function doesn't do anything, it simply returns false regardless of the input
	    unsure of the original purpose of the function
	 *  
	 */
	
	public static final String GROUP_SESSION				= "session";
	public static final String GROUP_DESIGNES			= "designs";			//Images for designs table
	public static final String GROUP_DESIGNE_TEMPLATES	= "design_templates";	//Images for design_templates table
	public static final String GROUP_ORDER_ITEMS			= "order_items";		//Images for order_items table
	public static final String GROUP_CUSTOMER			= "customer";
	public static final String GROUP_LEGACY_IMAGES		= "old_db";
	public static final String GROUP_LEGACY_FONTS		= "fonts";
	
	public static final String TYPE_ORIGINAL				= "original";
	public static final String TYPE_THUMBNAIL			= "thumbs";
	public static final String TYPE_THUMBNAIL_COLOR		= "thumbs_";
	public static final String TYPE_WEB					= "web";
	public static final String TYPE_WEB_COLOR			= "web_";
	
	public static final String DIR_CUSTOMER_IMAGES		= "images";
	public static final String DIR_CUSTOMER_FONTS		= "fonts";
	public static final String DIR_CUSTOMER_THEMES		= "themes";
	
	
	public static String GROUP_TO_PATH_MAP (String groupName)
	{
		String [] group = {"GROUP_SESSION", "GROUP_DESIGNES", "GROUP_DESIGNE_TEMPLATES", "GROUP_ORDER_ITEMS", "GROUP_CUSTOMER", "GROUP_LEGACY_IMAGES", "masonrow", "GROUP_LEGACY_FONTS"};
		String [] data = {"system/session", "system/designs", "system/design_templates", "system/order_items", "customer", "legacy/images", "legacy/images", "legacy/fonts"};
		for(int i = 0; i < group.length; i++){
			if(groupName == group[i]) return data[i];
		}
		return null;
	}
	
	/*
	 * The getPath function is used to create a usable directory
	 * the relPath variable is passed in from another class typically and is a point from which 
	   a working directory is to be created. It's concatenated on the end of the path.
	 */
	
	public static String getPath(String group, String relPath, String type)
	{
		String path = new String();
		path = (Settings.DIR_DATA_ROOT + "/" + type + "/"  + GROUP_TO_PATH_MAP(group));
		if (relPath != null){
			path.concat("/" + relPath);
		}
		return path;
	}
	
	public static String getPath(String group, String relPath){
		String path = new String();
		path = (Settings.DIR_DATA_ROOT + "/" + TYPE_ORIGINAL + "/" + GROUP_TO_PATH_MAP(group) + "/" + relPath);
		return path;
	}
	
	public static String getPath(String group){
		String path = new String();
		path = (Settings.DIR_DATA_ROOT + "/" + TYPE_ORIGINAL + "/" + GROUP_TO_PATH_MAP(group));
		return path;
	}
	
	
	// The original function appears incomplete, modified to return the path, of the theme dirrectory
	/*public  String getThemeDirectory(String customerId, String themeName)
	{
		String path = new String(Settings.DIR_DATA_ROOT + "/" + TYPE_ORIGINAL + "/" + GROUP_TO_PATH_MAP("GROUP_CUSTOMER")
			+ "/" + customerId + "/" + DIR_CUSTOMER_THEMES + "/" + themeName);
		return path;
	}*/
	
	
	// No idea if this code is supposed to perform any functions, but it doesn't.
	public static Boolean update(String group, String relPath, String type)
	{
		return false;
	}		
	
	
	
	// The third overload method is redundant
	public static String getId(String group, String relPath, String type)
	{
		String id = new String(); 
		if(type == TYPE_ORIGINAL)
		{
			id = group;
		}
		else
		{
			id = (type + "." + group); 				
		}
		
		if(relPath != null) id.concat("/" + relPath);
		
		return id;
	}
	
	public static String getId(String group, String relPath)
	{
		String id = new String(); 
		if(relPath != null) id = (group + "/" + relPath);
		return id;
	}
	
	public static String getId(String group)
	{		
		return group;
	}
	
	
	public static ResourceId parseId(String id)
	{
		String[] bigParts = id.split("/", 2);
		String[] smallParts = bigParts[0].split(".", 3);
	
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

