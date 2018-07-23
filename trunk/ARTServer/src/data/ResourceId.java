package data;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;

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
	
	public String[] getParams()
	{
		if(this.path == null) return null; 
		int iStart = this.path.lastIndexOf(PARAM_PREFIX);
		if(iStart < 0) return null;
		iStart += PARAM_PREFIX.length();
		int iEnd = this.path.indexOf(PARAM_SUFFIX, iStart);
		if(iEnd < 0) return null;
		
		String value = this.path.substring(iStart,iEnd);
		return value.split(",");
	}
	
	
	public static boolean isParamRoot(String path)
	{
		if(path == null) return true;
		int iStart = path.lastIndexOf(PARAM_PREFIX);
		if(iStart < 0) return true;
		int iEnd = path.indexOf(PARAM_SUFFIX, iStart);
		if(iEnd < 0) return true;
		return false;
	}	
	
	public static String getPathWithoutParams(String path)
	{
		if(path == null) return null;
		int iStart = path.lastIndexOf(PARAM_PREFIX);
		if(iStart < 0) return path;
		int iEnd = path.indexOf(PARAM_SUFFIX, iStart);
		if(iEnd < 0) return path;
		return path.substring(0, iStart) + path.substring(iEnd + PARAM_SUFFIX.length());		
	}
	
	public String getPathWithoutParams()
	{
		return getPathWithoutParams(this.path);
	}
	
	public void setParams(String[] params)
	{
		String paramString = PARAM_PREFIX;
		int paramCount = 0;
		for(int i=0; i<params.length; i++)
		{
			if(params[i] == null) continue;
			if(paramCount > 0) paramString+= ',';
			paramString += params[i];
			paramCount++;
		}
		paramString += PARAM_SUFFIX;
		
		this.path = getPathWithoutParams();
		if(paramCount < 1) return;
		
		int iPos = this.path.lastIndexOf('.');
		if(iPos < 0)
		{
			this.path = this.path + paramString;  
		}
		else
		{
			this.path = this.path.substring(0,iPos) + paramString + this.path.substring(iPos);  
		}
	}
	
	public ResourceId getParamParent()
	{
		String[] params = getParams();
		if(params == null) return null;
		ResourceId paramParent = new ResourceId(this.group, this.getPathWithoutParams(), this.type);
		params[params.length - 1] = null;
		paramParent.setParams(params);
		return paramParent;		
	}
		
	/**
	 * @return Returns an RID that has no parameters or this.
	 */
	public ResourceId getParamRoot()
	{
		String paramRootPath = this.getPathWithoutParams();
		if(paramRootPath == this.path) return this;
		return new ResourceId(this.group, paramRootPath, this.type);
	}

	/**
	 * @return Returns an RID that has no parameters and has a type of ResourceManager.TYPE_ORIGINAL or this.
	 */
	public ResourceId getOriginalParamRoot()
	{
		String paramRootPath = this.getPathWithoutParams();
		
		if((paramRootPath == this.path) && type.equals(ResourceManager.TYPE_ORIGINAL)) return this;
		return new ResourceId(this.group, paramRootPath, ResourceManager.TYPE_ORIGINAL);
	}

	public ResourceId()
	{
		type = ResourceManager.TYPE_ORIGINAL;
		group = null;
		path = null;
	}

	public String getFileName()
	{
		if(this.path == null) return null;
		int startPos = this.path.lastIndexOf("/");
		if(startPos < 0) return this.path;
		return this.path.substring(startPos + 1);
	}
	
	public String getMimeType()
	{
		if(this.path == null) return "";
		return ResourceManager.getMimeType(this.path);
	}
	
	
	public ResourceId(String group, String path, String type)	
	{
		this.type = type;
		this.group = group;
		this.path = path;
	}
	
	public ResourceId(String group, String path)	
	{
		this.type = ResourceManager.TYPE_ORIGINAL;
		this.group = group;
		this.path = path;
	}

	public ResourceId(String group)	
	{
		this.type = ResourceManager.TYPE_ORIGINAL;
		this.group = group;
		this.path = null;
	}

	public ResourceId getOriginal()
	{
		return new ResourceId(group, path, ResourceManager.TYPE_ORIGINAL);
	}
	
	public String getPath( boolean createDir)
	{
		return ResourceManager.getPath(
				group, 
				path,
				type,
				createDir
			);
	}
	
	public String getPath()
	{
		return getPath(false);
	}
		
	public String getOriginalPath()
	{
		return ResourceManager.getPath(
				group, 
				path,
				ResourceManager.TYPE_ORIGINAL
			);
	}
	
	public String[] getFileList()
	{
		File groupDirectory = new File(ResourceManager.getPath(
				group,
				path,
				type
			));
		
		if(!groupDirectory.exists()) return new String[0];
			
		String idPrefix =  getId() + "/";
		
		String[] files = groupDirectory.list();
		int count = 0;
		for(int i=0; i<files.length; i++)
		{
			if(!isParamRoot(files[i]))
			{
				files[i] = null;
			}
			else
			{
				files[i] = idPrefix + files[i];
				count++;
			}
		}
		
		String[] result = new String[count];
		count = 0;
		for(int i=0; i<files.length; i++)
		{
			if(files[i] != null)
			{
				result[count] = files[i]; 
				count++;
			}
		}
			
		return result;
	}
	
	public String getId()
	{
		return ResourceManager.getId(
				this.group, 
				this.path,
				this.type 
			);
	}
	
	public boolean exists()
	{
		return (new File(getPath())).exists();
	}
	
	public long getDateChanged()
	{
		File file = new File(getPath());
		if(!file.exists()) return 0;
		return file.lastModified();
	}
	
	public boolean isDirty()
	{			
		
		ResourceId root = getOriginalParamRoot();
		
		if(this == root) return false;
		if(!exists()) return true;
		
		File rootFile = new File(root.getPath());
		if(!rootFile.exists()) return true;

		File thisFile = new File(getPath());
		return rootFile.lastModified() > thisFile.lastModified(); 
	}
	
	public boolean delete()
	{
		File file = new File(getPath());
		if(!file.exists()) return false;
		return file.delete(); 
	}		
	
	public boolean update()
	{
		return ResourceManager.update(group, path, type);
	}
	
	public OutputStream getOutputStream() throws FileNotFoundException
	{
		File file = new File(getPath());
		file.getParentFile().mkdirs();
		return new FileOutputStream(file);
	}
	
	public InputStream getInputStream(boolean updateIfDirty) throws FileNotFoundException
	{
		if(updateIfDirty && isDirty())
		{
			if(!update()) throw new FileNotFoundException("The file is dirty and the update failed.");			
		}
		
		return new FileInputStream(new File(getPath()));
	}

	public InputStream getInputStream() throws FileNotFoundException
	{
		return getInputStream(false);
	}

	public static ResourceId fromId(String id)
	{
		return ResourceManager.parseId(id);
	}
}
