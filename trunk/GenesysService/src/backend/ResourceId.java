package backend;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;

import backend.ResourceManager;


public class ResourceId
{
	public static final String PARAM_PREFIX = "_@@(";
	public static final String PARAM_SUFFIX = ")";
	public static final String PARAM_GRADIENT = "GR1_";
	public static final String PARAM_LINEAR_TINT = "LTNT_";
	public static final String PARAM_MIRROR_HORIZONTAL = "MIRH";
	public static final String PARAM_MIRROR_VERTICAL = "MIRV";
	public static final String PARAM_MONOCHROME = "MONOC";
			
	public String type;
	public String group;
	public String path;
	
	public ResourceId(String tempGroup, String tempPath, String tempType)
	{
		group = tempGroup;
		type = tempType;
		path = tempPath;
	}
	public ResourceId(String tempGroup, String tempPath)
	{
		group = tempGroup;
		type = ResourceManager.TYPE_ORIGINAL;
		path = tempPath;
	}
	public ResourceId(){
	}

	public String[] getParams(String path)
	{
		if(path == null) return null;
		int iStart = path.lastIndexOf(PARAM_PREFIX);
		if(iStart == -1) return null;
		iStart += PARAM_PREFIX.length();
		int iEnd = path.indexOf(PARAM_SUFFIX, iStart);
		if(iEnd == -1) return null;
	
		String value = path.substring(iStart, iEnd - iStart);
		return value.split(",");
	}
			
	public Boolean isParamRoot(String path)
	{
		if(path == null) return true;
		int iStart = path.lastIndexOf(PARAM_PREFIX);
		if(iStart == -1) return true;
		iStart += PARAM_PREFIX.length();
		int iEnd = path.indexOf(PARAM_SUFFIX, iStart);
		if(iEnd == -1) return true;
		return false;
	}
	
	public String getPathWithoutParams(String path)
	{
		if(path == null) return path;
		int iStart = path.lastIndexOf(PARAM_PREFIX);
		if(iStart == -1) return path;
		int iEnd = path.indexOf(PARAM_SUFFIX, iStart);
		if(iEnd == -1) return path;
		
		return (path.substring(0, iStart) + path.substring(iEnd + PARAM_SUFFIX.length()));
	}
	
	public String setParams(String path, String params)
	{
		String paramString = PARAM_PREFIX;
		int paramCount = 0;
		while(paramCount <= params.length())
		{
			if(paramCount > 0) paramString.concat(",");
			paramString.concat(String.valueOf(params.charAt(paramCount)));
			paramCount ++;
							
		}
		paramString.concat(PARAM_SUFFIX);
	
		path = getPathWithoutParams(path);
		if(paramCount > 0)
		{		
			int iPos = path.lastIndexOf(".");
			if(iPos == -1)
			{
				path = paramString;
			}
			else
			{
				path = (path.substring(0, iPos) + paramString + path.substring(iPos));
			}
		}
		return path; 
	}
	
	// Overloaded method
	public String getPath(Boolean createDir)
	{
		String path = ResourceManager.getPath(group, null, type);
		File testPath = new File(path);
		
		if(!testPath.exists()) 
			testPath.mkdir();
		return path + "/" + this.path;
	}
	public String getPath(){
		String path = ResourceManager.getPath(group, null, type);
		return path + "/" + this.path;
	}
	
	public String getOriginalPath()
	{
		return ResourceManager.getPath(group, path,ResourceManager.TYPE_ORIGINAL);
	}
	
	
	/*
	 * Modified the name of the variable to getDir, because the getDir specifies whether or not to include the subfiles that lie within the directories
	   as opposed to ignoring the directories and just including the files in the immediate directory
	 * The arrayList was necessary because of the modifications of the appendTo array, we can't add to arrays in java so painful methods were required
	 */
	private String[] getFileList_Internal(String idPrefix, String path,String[] appendTo, Boolean getDir)
	{
		ArrayList<String> temp = new ArrayList(Arrays.asList(appendTo)); // Adds the current contents of appendTo to the temporary array
		File filePath = new File(path);
		String [] data = filePath.list(); // Returns the names of all the files and directories in the path
		for (int i = 0; i < data.length; i ++){
			if (data[i] != "." && data[i] != ".."){
				File fileName = new File(data[i]);
				if (fileName.isFile()){
					if(isParamRoot(data[i])){
						temp.add(idPrefix + data[i]);
					}
				}
				else if(getDir){
					temp = new ArrayList(Arrays.asList(getFileList_Internal(idPrefix, path, temp.toArray(new String[0]), getDir))); // Redeclares the temp variable with the additional subfiles added
				}
			}
		}
		
		return temp.toArray(new String[0]); // Converts the arrayList to an array of Strings
	}
	
	/**
	 * @param Boolean $getDir
	 * @return array
	 */
	String[] getFileList(Boolean getDir, String [] appendTo) //Both the recursive and appendTo variables require defaulting
	{		
		String path = ResourceManager.getPath(group,this.path,type);
		if(path == null) return appendTo; // If the specified path doesn't exist then there are no filenames to return
		appendTo = getFileList_Internal(getId() + "/", path + "/", appendTo, getDir);
		return appendTo;
	}
	String[] getFileList(Boolean getDir)
	{		
		String[] appendTo = null;
		String path = ResourceManager.getPath(group,this.path,type);
		if(path == null) return appendTo;
		return getFileList_Internal(getId() + "/", path + "/", appendTo, getDir);
	}
	String[] getFileList(String [] appendTo)
	{		
		String path = ResourceManager.getPath(group,this.path,type);
		
		if(path == null) return appendTo; 
		appendTo = getFileList_Internal(getId() + "/", path + "/", appendTo, null);
		return appendTo;
	}
	String[] getFileList() 
	{		
		String[] appendTo = null;
		String path = ResourceManager.getPath(group,this.path,type);	
		if(path == null) return appendTo; // If the specified path doesn't exist then there are no filenames to return
		appendTo = getFileList_Internal(getId() + "/", path + "/", appendTo, null);
		return appendTo;
	}
	
	
	public String getId()
	{
		return ResourceManager.getId(group, path, type);
	}
	
	long getDateChanged()
	{
		File f = new File(getPath());
		if(!f.isFile()) return 0;
		return f.lastModified();
	}
	
	public Boolean isDirty()
	{			
		if(!exists()) return true;
		if(type == ResourceManager.TYPE_ORIGINAL) return false;
		
		String pathOriginal = getOriginalPath();
		File f = new File(pathOriginal);
		if(!f.isFile()) return true;
		
		File g = new File(getPath());
		
		return (f.lastModified() > g.lastModified()); 
	}
	
	public Boolean exists()
	{
		File f = new File(getPath());
		return !(f.isFile());
	}
	
	Boolean delete()
	{
		File f = new File(getPath());
		return f.delete();
	}		
	
	Boolean update()
	{
		return ResourceManager.update(group, path,type);
	}
	
	Boolean setData(String data)
	{
		File file = new File(getPath(true));
		if (!file.exists()){
			try {
				file.createNewFile();
			} catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}
		try{
			BufferedWriter bufferedWriter = new BufferedWriter(new FileWriter(file));
	        bufferedWriter.write(data);
	        bufferedWriter.close();
	        return true;
		}
        
		catch(IOException e){
			return false;
		}
	}
	
	
	// The bufferedReader input is probably incomplete, unsure of formating for input at this time.
	String getData(Boolean updateIfDirty) throws FileNotFoundException //Requires a default of false for updateIfDirty
	{
		if(updateIfDirty && isDirty())
		{
			if(!ResourceManager.update(group, path,type))
			{
				return null;
			}
		}
		
		BufferedReader in = new BufferedReader(new FileReader(new File(getPath())));
		String data;
		try {
			data = in.readLine();
			if(data == null) return null;
			return data;
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return null;
	}
			
	public ResourceId fromId(String id)
	{
		return ResourceManager.parseId(id);
	}
}