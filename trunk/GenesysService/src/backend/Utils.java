package backend;

public class Utils {
	public static String getPost(String id, String standard)
	{		
		if(null != (_POST[id])) return _POST[id];
		return standard;
	}
	public static String getPost(String id)
	{		
		if(null != (_POST[id])) return _POST[id];
		return  "";
	}

	int startsWith(String haystack, String needle)
	{
		haystack = haystack.substring(0, needle.length());
		
		return haystack.compareTo(needle);
	}
	
	Boolean endsWith(String haystack, String needle)
	{
		int length = needle.length();
		if (needle.length() == 0) {
			return true;
		}
	
		return haystack.substring(-needle.length()) == needle;
	}
}
