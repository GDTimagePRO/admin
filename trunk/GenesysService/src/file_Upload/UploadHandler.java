package file_Upload;

import java.awt.AlphaComposite;
import java.awt.Color;
import java.awt.Graphics2D;
import java.awt.Image;
import java.awt.geom.AffineTransform;
import java.awt.image.AffineTransformOp;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Map;

import com.google.common.io.Files;

public class UploadHandler {
	
	/*
	 * Incomplete/Questionable stuff
	 * create_scaled_image: 
	   PHP mkdir settings were in effect that don't necessarily apply to java, worth double checking.
	   the function_exists function in PHP might not be necessary/not fully functional in its current state
	   error_log, function isn't built into java, might create equivalent
	 * get_config_bytes: How can I trim and multiply the same value by 1024, as trim requires a string
	 */
	    public static class Settings
	    {
	    	public String image_id_prefix;
		    public String color_model;
		    public String script_url = get_full_url() + "/";
		    public String upload_dir = dirname(get_server_var(SCRIPT_FILENAME)) + "/files/";
		    public String upload_url = get_full_url() + "/files/";
		    public boolean users_dir = false;
		    public String mkdir_mode = "0755";
		    public String param_name = "files";
		    
		    // Set the following option to 'POST', if your server does not support
		    // DELETE requests. This is a parameter sent to the client:
		    public String delete_type = "DELETE";// The input here is questionable, so the type probably isn't a string
		    String access_control_allow_origin = "*";
		    boolean access_control_allow_credentials = false;
		    public String[] access_control_allow_methods = {
                "OPTIONS",
                "HEAD",
                "GET",
                "POST",
                "PUT",
                "PATCH",
                "DELETE"};
		    public String [] access_control_allow_headers = {
                "Content-Type",
                "Content-Range",
                "Content-Disposition"};
		    String crop;
		    
		    // Image quality controls
		    int jpeg_quality;
		    int png_quality;
		    
		 // Enable to provide file downloads via GET requests to the PHP script:
            //     1. Set to 1 to download files via readfile method through PHP
            //     2. Set to 2 to send a X-Sendfile header for lighttpd/Apache
            //     3. Set to 3 to send a X-Accel-Redirect header for nginx
            // If set to 2 or 3, adjust the upload_url option to the base path of
            // the redirect parameter, e.g. '/files/'.
            boolean download_via_php = false;
            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            int readfile_chunk_size = 10 * 1024 * 1024; // 10 MiB
            // Defines which files can be displayed inline when downloaded:
            //String inline_file_types = "/\.(gif|jpe?g|png)$/i";
            String accept_file_types = ".+\n"; // Defines which files (based on their names) are accepted for upload, must be made case insensitive
            		
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            int max_file_size;
            int min_file_size = 1;
          
            int max_number_of_files; // The maximum number of files for the upload directory
            
            // Image resolution restrictions:
            int max_width = 1;
            int max_height = 1;
            int min_width;
            int min_height;
            
            boolean discard_aborted_uploads = true; // Set the following option to false to enable resumable uploads:
            boolean orient_image = true; // Set to false to disable rotating images based on EXIF meta data:
            
            
            public static String[][] index;
            public static String[][] variables;
            public void set_image_version(String version, String parameter, String value){
            	ArrayList<ArrayList<String>> index = new ArrayList(Arrays.asList(this.index));
            	ArrayList<ArrayList<String>> variables = new ArrayList(Arrays.asList(this.variables));
            	ArrayList<String> temp;
            	for(int i = 0; i < index.size(); i++){
            		if (index.get(i).get(0) == version){
            			temp.add("");
            			while (variables.size() < index.size()){
    						variables.add(temp);
    					}
            			for(int j = 1; j < index.get(i).size(); j++){
            				if(index.get(j).get(i) == parameter){
            					while (variables.get(i).size() < index.get(i).size()){
            						variables.get(i).add("");
            					}
            					variables.get(j).set(i, value);
            				}
            				else if(index.get(i).size() == j){
            					index.get(i).add(parameter);
            				}
            			}
            			i = index.size();
            		}
            		else if(i == index.size()){
            			temp.add(version);
            			temp.add("");
    					index.add(temp);
    					i--;
    					temp.clear();
    				}
            	}
            	this.index = index.toArray(new String[0][0]);
            	this.variables = variables.toArray(new String[0][0]);
            }

            public String image_version(String version, String paramater){
            	for(int i = 0; i < index.length; i ++){
            		if (index[i][0] == version){
            			for(int j = 1; i < index[i].length; j++){
            				if (index[i][j] == paramater){
            					return variables[i][j];
            				}
            			}
            		}
            	}
            }
                
	    }
	    /*
	    public static boolean function_exists(Env env, String name) {
	    	  return name != null && env.findFunction(name) != null;
	    	}
	    */
	    protected Map<String,String> error_messages = new HashMap<>();
	    {
	    error_messages.put("1", "The uploaded file exceeds the upload_max_filesize directive in php.ini");
	    error_messages.put("2", "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form");
	    error_messages.put("3", "The uploaded file was only partially uploaded");
	    error_messages.put("4", "No file was uploaded");
	    error_messages.put("6", "Missing a temporary folder");
	    error_messages.put("7", "Failed to write file to disk");
	    error_messages.put("8", "A PHP extension stopped the file upload");
	    error_messages.put("post_max_size", "The uploaded file exceeds the post_max_size directive in php.ini");
	    error_messages.put("max_file_size", "File is too big");
	    error_messages.put("min_file_size", "File is too small");
	    error_messages.put("accept_file_types", "Filetype not allowed");
	    error_messages.put("max_number_of_files", "Maximum number of files exceeded");
	    error_messages.put("max_width", "Image exceeds maximum width");
	    error_messages.put("min_width", "Image requires a minimum width");
	    error_messages.put("max_height", "Image exceeds maximum height");
	    error_messages.put("min_height", "Image requires a minimum height");
	    }
	    
	    // PHP File Upload error message codes:
	    // http://php.net/manual/en/features.file-upload.errors.php
	    
	    	
	    
	    Settings options = new Settings();
	    // Options default null, initialize default true, error_messages default null, need to merge error_messages and Options
	    UploadHandler(Settings options, boolean initialize, Map error_messages) { 
	        if (options != null) {
	            this.options = merge(this.options, options);
	        }
	        if (error_messages != null) {
	            this.error_messages = merge(this.error_messages, error_messages);
	        }
	        if (initialize) {
	            initialize();
	        }
	    }
	    
	    UploadHandler(Settings options){
	    	if (options != null){
	    		this.options = this.options + options;
	    	}
	    	initialize();
	    }
	    	

	    protected void initialize() {
	        switch (get_server_var("REQUEST_METHOD")) {
	            case "OPTIONS":
	            case "HEAD":
	                this.head();
	                break;
	            case "GET":
	                this.get();
	                break;
	            case "PATCH":
	            case "PUT":
	            case "POST":
	                this.post();
	                break;
	            case "DELETE":
	                this.delete();
	                break;
	            default:
	                this.header("HTTP/1.1 405 Method Not Allowed");
	        }
	    }

	    protected static String get_full_url() {
	        https = !empty(location.pathname("HTTPS")) && _SERVER["HTTPS"] !== "off";
	        return
	            (https ? "https://" : "http://") + 
	            (!empty($_SERVER["REMOTE_USER"]) ? $_SERVER["REMOTE_USER"]."@" : "").
	            (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : ($_SERVER["SERVER_NAME"].
	            ($https && $_SERVER["SERVER_PORT"] === 443 ||
	            $_SERVER["SERVER_PORT"] === 80 ? "" : ":".$_SERVER["SERVER_PORT"]))).
	            substr($_SERVER["SCRIPT_NAME"],0, strrpos($_SERVER["SCRIPT_NAME"], "/"));
	    }

	    protected String get_user_id() {
	        @session_start();
	        return session_id();
	    }

	    protected String get_user_path() {
	        if (options.users_dir) {
	            return get_user_id() + "/";
	        }
	        return "";
	    }
	    
	    
	    // file_name and version are defaulted to null
	    protected String get_upload_path(String file_name, String version) {
	    	String version_path;
	    	file_name = file_name != null ? file_name : "";
	        if (version.isEmpty()) {
	        	version_path = "";
	        }
	        else {
	            String version_dir = options.image_version(version, options.upload_dir);
	            if (version_dir != null) {
	                return version_dir + get_user_path() + file_name;
	            }
	            version_path = version+ "/";
	        }
	        return options.upload_dir + get_user_path() + version_path + file_name;
	    }
	    
	    protected String get_upload_path(String file_name) {
	    	file_name = file_name != null ? file_name : "";
	        
	        return options.upload_dir + get_user_path() + file_name;
	    }
	    
	    protected String get_upload_path() {
	        return options.upload_dir + get_user_path();
	    }

	    protected String get_query_separator(String url) {
	        return url.indexOf("?") == -1 ? "?" : "&";
	    }

	    // Overloaded Methods
	    protected String get_download_url(String file_name, String version, boolean direct) {
	        if (!direct && options.download_via_php) {
	            String url = options.script_url + get_query_separator(options.script_url) + 
	                "file=" + URLEncoder.encode(file_name, "UTF-8");
	            if (version != null) {
	                url.concat("&version=" + URLEncoder.encode(file_name, "UTF-8"));
	            }
	            return url +"&download=1";
	        }
	        String version_path;
	        if (version.isEmpty()) {
	            version_path = "";
	        } else {
	            String version_url = options.image_version(version, "upload_url");
	            if (version_url != null) {
	                return version_url + get_user_path() + URLEncoder.encode(file_name, "UTF-8");
	            }
	            version_path = (URLEncoder.encode(version, "UTF-8") + "/");
	        }
	        return options.upload_url + get_user_path()
	            + version_path + URLEncoder.encode(file_name, "UTF-8");
	    }
	    protected String get_download_url(String file_name, String version) {
	        if (!false && options.download_via_php) {
	            String url = options.script_url + get_query_separator(options.script_url) + 
	                "file=" + URLEncoder.encode(file_name, "UTF-8");
	            if (version != null) {
	                url.concat("&version=" + URLEncoder.encode(file_name, "UTF-8"));
	            }
	            return url +"&download=1";
	        }
	        String version_path;
	        if (version.isEmpty()) {
	            version_path = "";
	        } 
	        else {
	            String version_url = options.image_version(version, "upload_url");
	            if (version_url != null) {
	                return version_url + get_user_path() + URLEncoder.encode(file_name, "UTF-8");
	            }
	            version_path = URLEncoder.encode(version, "UTF-8") + "/";
	        }
	        return options.upload_url + get_user_path()
	            + version_path + URLEncoder.encode(file_name, "UTF-8");
	    }
	    protected String get_download_url(String file_name) {
	        if (!false && options.download_via_php) {
	            String url = options.script_url + get_query_separator(options.script_url) + 
	                "file=" + URLEncoder.encode(file_name, "UTF-8");
	            return url +"&download=1";
	        }
	        return options.upload_url + get_user_path()
	            + URLEncoder.encode(file_name, "UTF-8");
	    }
	    
	    
	    

	    // Fix for overflowing signed 32 bit integers,
	    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
	    protected long fix_integer_overflow(long size) {
	        if (size < 0) {
	            size += 2.0 * (Integer.MAX_VALUE + 1);
	        }
	        return size;
	    }
	    
	    protected long get_file_size(String file_path) {
	        File path = new File(file_path);
	        return fix_integer_overflow(path.length());

	    }
	    
	    protected long get_file_size(String file_path, boolean clear_stat_cache) {
	        if (clear_stat_cache) {
	            clearstatcache(true, file_path);
	        }
	        File file_dir = new File(file_path);
	        return fix_integer_overflow(file_path.length());

	    }

	    protected boolean is_valid_file_object(String file_name) {
	        File file_path = new File(get_upload_path(file_name));
	        if (file_path.exists() && !file_name.startsWith(".")) {
	            return true;
	        }
	        return false;
	    }
	    protected class fileParams{
	    	String name;
	    	long size;
	    	String url;
	    	Map<String,String> version = new HashMap<>();
	    	String deleteType;
	    	String deleteUrl;
	    	boolean deleteWithCredentials;
			public String error;
			public String type;
	    	protected void set_additional_file_properties() {
		    	String deleteType = options.delete_type;
		    	String deleteUrl = (options.script_url
		    	            + get_query_separator(options.script_url) +
		    	            "file=" + URLEncoder.encode(name, "UTF-8"));

		        if (deleteType != "DELETE") {
		            deleteUrl.concat("&_method=DELETE");
		        }
		        if (options.access_control_allow_credentials) {
		            boolean deleteWithCredentials = true;
		            
		        }
	    	}
	    }
	    
	    protected fileParams get_file_object(final String file_name) {
	        if (is_valid_file_object(file_name)) {
	        	fileParams file = new fileParams();
	        	file.name = file_name;
    			file.size = get_file_size(get_upload_path(file_name));
    			file.url = get_download_url(name);
	            for(int i = 0; i < options.index.length(); i ++) {
	            	if (!(options.variables[i][1].isEmpty())) {
	            		File file_path = new File(get_upload_path(file_name, options.index[i][0]));
		                if (file_path.isFile()) {
		                    file.version.put(options.index[i][0], get_download_url(file.name, options.index[i][0]));
		                }
		            }
		        }    
	    		file.set_additional_file_properties();
	            return file;
	        }
	        return null;
	    }

	    // Defaulted to get_file_object
	    protected String[] get_file_objects(String iteration_method) {
	        File upload_dir = new File(get_upload_path());
	        if (!upload_dir.isDirectory()) {
	            return null;
	        }
	        return array_values(array_filter(array_map(
	            array(this, iteration_method), upload_dir.list()
	        )));
	    }

	    protected int count_file_objects() {
	        return get_file_objects("is_valid_file_object").length;
	    }
	    
	    // Requires tweaking   
	    public BufferedImage createResizedCopy(BufferedImage originalImage, int scaledWidth, int scaledHeight)
        {
        	BufferedImage scaledBI = new BufferedImage(scaledWidth, scaledHeight, BufferedImage.TYPE_INT_RGB);
        	Graphics2D g = scaledBI.createGraphics();
          	g.drawImage(originalImage, 0, 0, scaledWidth, scaledHeight, null); 
        	g.dispose();
        	return scaledBI;
        }
	    
	    // Requires tweaking
	    public BufferedImage offSetImage(BufferedImage originalImage, int offSetX, int offSetY, int x, int y)
        {
	    	BufferedImage temp = new BufferedImage(y, x, BufferedImage.TYPE_INT_RGB);
        	Graphics2D g = temp.createGraphics();
          	g.drawImage(originalImage, offSetX, offSetY, x, y, null); 
        	g.dispose();
        	return temp;
        }

	    protected boolean create_scaled_image(String file_name, String version, Settings options) {
	    	int new_width, new_height, dst_x, dst_y;
	        File file_path = new File(get_upload_path(file_name));
	        File new_file_path;
	        if (!version.isEmpty()) {
	            File version_dir = new File(get_upload_path(null, version));
	            if (!version_dir.isDirectory()) {
	                version_dir.mkdirs(); // Uses PHP settings for mkdir, refer to PHP file
	            }
	            new_file_path = new File(get_upload_path(null, version) + "/" + file_name);
	        } 
	        else {
	           new_file_path = new File(get_upload_path(file_name));
	        }
	        
	        // Retrieves dimensions of the image
	        BufferedImage src_img = ImageIO.read(file_path);
	        int img_width = src_img.getWidth();
	        int img_height = src_img.getHeight();
	        if (!(img_width == 0) || !(img_height == 0)) { // If the height or width of file are undefined function fails
	            return false;
	        }
	        int max_width = options.max_width;
	        int max_height = options.max_height;
	        double scale_width = max_width / img_width;
	        double scale_height = max_height / img_height;
	        double scale = scale_width < scale_height ? scale_width : scale_height;
	        if (scale >= 1) {
	            if (file_path != new_file_path) {
	                return file_path.copy( new_file_path);
	            }
	            return true;
	        }
	        
	        BufferedImage new_img;
	        if (options.crop.isEmpty()) {
	            new_width = (int) (img_width * scale);
	            new_height = (int) (img_height * scale);
	             dst_x = 0;
	             dst_y = 0;
	            new_img = new BufferedImage((int)new_width, (int)new_height, BufferedImage.TYPE_INT_RGB);
	        } 
	        else {
	            if ((img_width / img_height) >= (max_width / max_height)) {
	                new_width = img_width / (img_height / max_height);
	                new_height = max_height;
	            } else {
	                new_width = max_width;
	                new_height = img_height / (img_width / max_width);
	            }
	             dst_x = (int) (0 - (new_width - max_width) / 2);
	             dst_y = (int) (0 - (new_height - max_height) / 2);
	            new_img = new BufferedImage(max_width, max_height, BufferedImage.TYPE_INT_RGB);
	            
	        }
	        String write_image;
	        int image_quality, rgb;
	        switch (file_name.toLowerCase().substring(file_name.lastIndexOf("."), 1)) { //Performs different functions based off the image type
	            case "jpg":
	            case "jpeg":
	            	try {
	            	    src_img = ImageIO.read(file_path);
	            	} 
	            	catch (IOException e) {
	            	}
	                write_image = "imagejpeg";
	                image_quality = options.jpeg_quality == null ?
	                    options.jpeg_quality : 75;
	                break;
	            case "gif":
	            	rgb=new Color(0,0,0).getRGB();
	            	new_img.setRGB(new_img.getWidth(),new_img.getHeight(),rgb);
	                try {
	            	    src_img = ImageIO.read(file_path);
	            	} 
	            	catch (IOException e) {
	            	}
	                write_image = "imagegif";
	                break;
	            case "png":
	            	rgb=new Color(0,0,0).getRGB();
	            	new_img.setRGB(new_img.getWidth(),new_img.getHeight(),rgb);
	                imagealphablending(new_img, false);
	                imagesavealpha(new_img, true);
	                try {
	            	    src_img = ImageIO.read(file_path);
	            	} 
	            	catch (IOException e) {
	            	}
	                write_image = "imagepng";
	                image_quality = options.png_quality ?
	                    options.png_quality : 9;
	                break;
	            default:
	                new_img.flush();
	                return false;
	        }
	        
	        src_img = createResizedCopy(src_img, (new_width - dst_x), (new_height - dst_y));
	        new_img = offSetImage(src_img, dst_x, dst_y, new_width, new_height);
	        
	        
	        boolean success = ImageIO.write(new_img, "jpg", new_file_path);
	        // Free up memory (imagedestroy does not delete files):
	        src_img.flush();
	        new_img.flush();
	        return success;
	    }

	    protected String get_error_message(String error) {
	        return error_messages.get(error) != null ?
	            error_messages.get(error) : error;
	    }

	    protected boolean validate(File uploaded_file, fileParams file, String error, int index) {
	        if (error != null) {
	            file.error = get_error_message(error);
	            return false;
	        }
	        // long content_length = fix_integer_overflow(intval(get_server_var("CONTENT_LENGTH")));
	        // post_max_size = this->get_config_bytes(ini_get("post_max_size"));
	        /*
	         * if (post_max_size && (content_length > post_max_size)) {
	            file->error = this->get_error_message("post_max_size");
	            return false;}
	         */
	        
	        if (!file.name.matches(options.accept_file_types)) {
	            file.error = get_error_message("accept_file_types");
	            return false;
	        }
	        if (uploaded_file && is_uploaded_file(uploaded_file)) {
	            int file_size = get_file_size(uploaded_file);
	        } 
	        else {
	            int file_size = content_length;
	        }
	        if (options.max_file_size && (file_size > options.max_file_size ||
	                file.size > optionsmax_file_size)) {
	            file.error = get_error_message("max_file_size");
	            return false;
	        }
	        if (options.min_file_size && file_size < options.min_file_size) {
	            file.error = get_error_message("min_file_size");
	            return false;
	        }
	        if (count_file_objects() >= options.max_number_of_files) {
	            file.error = get_error_message("max_number_of_files");
	            return false;
	        }
	        BufferedImage img = ImageIOread(new File(uploaded_file));
	        int img_width = img.getWidth();
	        int img_height = img.getHeight();
	        
	        if (this.options["max_width"] && img_width > this.options["max_width"]) {
	            file.error = this.get_error_message("max_width");
	            return false;
	        }
	            if (options.max_height && img_height > options.max_height) {
	                file.error = get_error_message("max_height");
	                return false;
	            }
	            if (options.min_width && img_width < options.min_width) {
	                file.error = get_error_message("min_width");
	                return false;
	            }
	            if (options.min_height && img_height < options.min_height) {
	                file.error = get_error_message("min_height");
	                return false;
	            }
	        
	        return true;
	    }

	    protected String upcount_name_callback(int []matches) { 
	        int index = matches.length < 1 ? matches[1] + 1 : 1;
	        String ext = matches.length < 2 ? Integer.toString(matches[2]) : "";
	        return " (" + index + ")" + ext;
	    }

	    protected String upcount_name(String name) {
	        return preg_replace_callback(
	            //"/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/",
	            array(this, "upcount_name_callback"),
	            name,
	            1
	        );
	    }
	    
	    // type, index and content_range are defaulted to null
	    protected String get_unique_filename(String name,
	            String type, int index, int[] content_range) {
	    	File file = new File(get_upload_path(name));
	        while(file.isDirectory()) {
	            name = upcount_name(name);
	            file = new File(get_upload_path(name));
	        }
	        // Keep an existing filename if this is part of a chunked upload:
	        long uploaded_bytes = fix_integer_overflow(content_range[1]);
	        while(file.isFile()) {
	            if (uploaded_bytes == get_file_size(get_upload_path(name))) {
	                break;
	            }
	            name = upcount_name(name);
	            file = new File(get_upload_path(name));
	        }
	        return name;
	    }
	    
	    protected String basename(String name, String sequence){
	    	String temp [] = name.split("/");
	        name = temp[temp.length - 1];
	        
	        if (name.endsWith(sequence)){
	        	int index = name.lastIndexOf(sequence);
	        	name = name.substring(0, index);
	        }
	        return name;
	    }
	    
	    
	  // type, index and content_range are defaulted to null
	    protected String trim_file_name(String name,
	            String type, int index, int[] content_range) {
	        // Remove path information and dots around the filename, to prevent uploading
	        // into different directories or replacing hidden system files.
	        // Also remove control characters and spaces (\x00..\x20) around the filename:
	        name = name.replaceAll("\\", "");
	        String temp [] = name.split("/");
	        name = temp[temp.length - 1];\
	        for (int i = 0; i < 32; i ++){
	        	basename(name, ("." + Character.toString((char) i)));
	        }
	        name = name.trim();
	        // Use a timestamp for empty filenames:
	        if (!name) {
	            name = name.replace('.', '-');
	        }
	        // Add missing file extension for known image types:
	        type.matches(regex)
	        if (name.indexOf(".")) == -1 && preg_match("/^image\/(gif|jpe?g|png)/", type, matches)) {
	            name += ("." + matches[1]);
	        }
	        return name;
	    }

	    // type, index and content_range are defaulted to null
	    protected String get_file_name(String name,
	            String type, int index, int[] content_range)
	    {
	        return get_unique_filename(
	        	trim_file_name(name, type, index, content_range),
	            type,
	            index,
	            content_range
	        );
	    }

	    protected void handle_form_data(Object file, int index) {
	        // Handle form data, e.g. $_REQUEST['description'][$index]
	    }

	    protected Image imageflip(BufferedImage image,char mode) {
	        int new_width = image.getWidth();
	        int new_height = image.getHeight();
	        BufferedImage new_img = null;
	        new_img.getRGB(new_width, new_height);
	        AffineTransformOp op = null;
	        AffineTransform transform = null;
	        switch (mode) {
	            case '1': // flip on the horizontal axis
	            	transform = AffineTransform.getScaleInstance(1, -1);
	            	transform.translate(-new_width, 0);
	            	break;
	            case '2': // flip on the vertical axis
	            	transform = AffineTransform.getScaleInstance(1, -1);
	            	transform.translate(0, -new_height);
	                break;
	            case '3': // flip on both axes
	            	transform = AffineTransform.getScaleInstance(-1, -1);
	            	transform.translate(-new_width, -new_height);
	                break;
	            default:
	                return image;
	        }
	        op = new AffineTransformOp(transform, AffineTransformOp.TYPE_NEAREST_NEIGHBOR);
        	new_img = op.filter(new_img, null);
	        
	        // Free up memory (imagedestroy does not delete files):
	        image.flush();
	        return new_img;
	    }

	    protected function orient_image(String file_path) {
	        $exif = @exif_read_data($file_path);
	        if ($exif === false) {
	            return false;
	        }
	        $orientation = intval(@$exif['Orientation']);
	        if ($orientation < 2 || $orientation > 8) {
	            return false;
	        }
	        $image = imagecreatefromjpeg($file_path);
	        switch ($orientation) {
	            case 2:
	                $image = $this->imageflip(
	                    $image,
	                    defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2
	                );
	                break;
	            case 3:
	                $image = imagerotate($image, 180, 0);
	                break;
	            case 4:
	                $image = $this->imageflip(
	                    $image,
	                    defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1
	                );
	                break;
	            case 5:
	                $image = $this->imageflip(
	                    $image,
	                    defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1
	                );
	                $image = imagerotate($image, 270, 0);
	                break;
	            case 6:
	                $image = imagerotate($image, 270, 0);
	                break;
	            case 7:
	                $image = $this->imageflip(
	                    $image,
	                    defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2
	                );
	                $image = imagerotate($image, 270, 0);
	                break;
	            case 8:
	                $image = imagerotate($image, 90, 0);
	                break;
	            default:
	                return false;
	        }
	        $success = imagejpeg($image, $file_path);
	        // Free up memory (imagedestroy does not delete files):
	        imagedestroy($image);
	        return $success;
	    }

	    protected void handle_image_file(String file_path, fileParams file) {
	        if (options.orient_image) {
	            orient_image(file_path);
	        }
	       
	        ArrayList<String> failed_versions = new ArrayList<String>;
	        String version;
	        for(int i = 0; i < options.index.length; i++) {
	        	version = options.index[i][0];
	            if (create_scaled_image(file.name, version, options)) {
	                if (!options.index[i][0]) {
	                    file.version.put((version + "URL"), get_download_url(file.name,version));
	                } else {
	                    file.size = get_file_size(file_path, true);
	                }
	            } else {
	                failed_versions.add(version);
	            }
	        }
	        switch (failed_versions.size()) {
	            case 0:
	                break;
	            case 1:
	                file.error = "Failed to create scaled version: "
	                    + failed_versions.get(0);
	                break;
	            default:
	                file.error = "Failed to create scaled versions: " + failed_versions.get(0);
	                for(int i = 1; i < failed_versions.size(); i ++){
	                	file.error.concat(", " + failed_versions.get(i));
	                }
	        }
	    }
	    
	    // index and content_range are defaulted to null
	    protected fileParams handle_file_upload(File uploaded_file, String name, int size, String type, String error,
	            int index, int[] content_range) {
	        fileParams file;
	        file.name = this.get_file_name(name, type, index, content_range);
	        file.size = this.fix_integer_overflow(size);
	        file.type = type;
	        if (this.validate(uploaded_file, file, error, index)) {
	            this.handle_form_data(file, index);
	            File upload_dir = new File(get_upload_path());
	            if (!upload_dir.isDirectory()) {
	                upload_dir.mkdirs();
	            }
	            File file_path = new File(get_upload_path(file.name));
	            boolean append_file = content_range != null && file_path.isFile() &&
	                file.size > get_file_size(get_upload_path(file.name));
	            if (uploaded_file && is_uploaded_file(uploaded_file)) {
	                // multipart/formdata uploads (POST method uploads)
	                if (append_file) {
	                    file_put_contents(
	                        file_path,
	                        fopen(uploaded_file, 'r'),
	                        FILE_APPEND
	                    );
	                } else {
	                    move_uploaded_file(uploaded_file, file_path);
	                }
	            } else {
	                // Non-multipart uploads (PUT method support)
	                file_put_contents(
	                    file_path,
	                    fopen("php://input", 'r'),
	                    append_file ? FILE_APPEND : 0
	                );
	            }
	            file_size = this.get_file_size(file_path, append_file);
	            if (file_size == file.size) {
	                file.url = this.get_download_url(file.name);
	                list(img_width, img_height) = (file_path);
	                if (is_int(img_width) &&
	                        preg_match(this.options["inline_file_types"], file.name)) {
	                    this.handle_image_file(file_path, file);
	                }
	                
	                idPrefix = this.options["image_id_prefix"];
	                colorModel = this.options["color_model"];
	                fileName = basename(file_path);
	                
	                
					result = Settings.resourceOpProcessUserUploadedImage(idPrefix . fileName, colorModel);
					if(result.errorCode == ResourceOpResult.CODE_OK)
					{
						file.rid = result.data[0]; 
					}
					else
					{
						file.rid = -1;
					}

					
	            } else {
	                file.size = file_size;
	                if (!content_range && this.options["discard_aborted_uploads"]) {
	                    unlink(file_path);
	                    file.error = "abort";
	                }
	            }
	            this.set_additional_file_properties(file);
	        }
	        return file;
	    }

	    protected String readfile(String file_path) {
	        long file_size = get_file_size(file_path);
	        int chunk_size = options.readfile_chunk_size;
	        if (chunk_size && file_size > chunk_size) {
	            handle = fopen(file_path, "rb"); 
	            while (!feof($handle)) { 
	                System.out.print(fread($handle, $chunk_size)); 
	                ob_flush(); 
	                flush(); 
	            } 
	            fclose(handle); 
	            return file_size;
	        }
	        return readfile(file_path);
	    }

	    protected void body(String str) {
	        System.out.print(str);
	    }
	    
	    protected String header(String str) {
	         
	    }

	    protected String get_server_var(String id) {
	    	ServletContext.getContextPath();
	        return _SERVER[id] != null ? _SERVER[id] : "";
	    }

	    
	    //print_response default true
	    protected Object generate_response(Object content, String print_response) {
	        if (print_response != null) {
	            json = json_encode(content);
	            redirect = isset(_REQUEST["redirect"]) ?
	                stripslashes(_REQUEST["redirect"]) : null;
	            if ($redirect) {
	                header("Location: ".sprintf($redirect, rawurlencode($json)));
	                return;
	            }
	            head();
	            if (get_server_var('HTTP_CONTENT_RANGE')) {
	                files = isset($content[$this->options['param_name']]) ?
	                    $content[$this->options['param_name']] : null;
	                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
	                    $this->header('Range: 0-'.(
	                        $this->fix_integer_overflow(intval($files[0]->size)) - 1
	                    ));
	                }
	            }
	            $this->body($json);
	        }
	        return content;
	    }

	    protected function get_version_param() {
	        return isset($_GET['version']) ? basename(stripslashes($_GET['version'])) : null;
	    }

	    protected function get_file_name_param() {
	        return isset($_GET['file']) ? basename(stripslashes($_GET['file'])) : null;
	    }

	    protected function get_file_type(String file_path) {
	        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
	            case 'jpeg':
	            case 'jpg':
	                return 'image/jpeg';
	            case 'png':
	                return 'image/png';
	            case 'gif':
	                return 'image/gif';
	            default:
	                return '';
	        }
	    }

	    protected function download() {
	        switch ($this->options['download_via_php']) {
	            case 1:
	                $redirect_header = null;
	                break;
	            case 2:
	                $redirect_header = 'X-Sendfile';
	                break;
	            case 3:
	                $redirect_header = 'X-Accel-Redirect';
	                break;
	            default:
	                return $this->header('HTTP/1.1 403 Forbidden');
	        }
	        $file_name = $this->get_file_name_param();
	        if (!$this->is_valid_file_object($file_name)) {
	            return $this->header('HTTP/1.1 404 Not Found');
	        }
	        if ($redirect_header) {
	            return $this->header(
	                $redirect_header.': '.$this->get_download_url(
	                    $file_name,
	                    $this->get_version_param(),
	                    true
	                )
	            );
	        }
	        $file_path = $this->get_upload_path($file_name, $this->get_version_param());
	        // Prevent browsers from MIME-sniffing the content-type:
	        $this->header('X-Content-Type-Options: nosniff');
	        if (!preg_match($this->options['inline_file_types'], $file_name)) {
	            $this->header('Content-Type: application/octet-stream');
	            $this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
	        } else {
	            $this->header('Content-Type: '.$this->get_file_type($file_path));
	            $this->header('Content-Disposition: inline; filename="'.$file_name.'"');
	        }
	        $this->header('Content-Length: '.$this->get_file_size($file_path));
	        $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
	        $this->readfile($file_path);
	    }

	    protected function send_content_type_header() {
	        $this->header('Vary: Accept');
	        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
	            $this->header('Content-type: application/json');
	        } else {
	            $this->header('Content-type: text/plain');
	        }
	    }

	    protected function send_access_control_headers() {
	        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
	        $this->header('Access-Control-Allow-Credentials: '
	            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
	        $this->header('Access-Control-Allow-Methods: '
	            .implode(', ', $this->options['access_control_allow_methods']));
	        $this->header('Access-Control-Allow-Headers: '
	            .implode(', ', $this->options['access_control_allow_headers']));
	    }

	    public function head() {
	        $this->header('Pragma: no-cache');
	        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
	        $this->header('Content-Disposition: inline; filename="files.json"');
	        // Prevent Internet Explorer from MIME-sniffing the content-type:
	        $this->header('X-Content-Type-Options: nosniff');
	        if ($this->options['access_control_allow_origin']) {
	            $this->send_access_control_headers();
	        }
	        $this->send_content_type_header();
	    }
	    
	    
	    // print_response default true
	    public function get(boolean print_response) {
	        if ($print_response && isset($_GET['download'])) {
	            return $this->download();
	        }
	        $file_name = $this->get_file_name_param();
	        if ($file_name) {
	            $response = array(
	                substr($this->options['param_name'], 0, -1) => $this->get_file_object($file_name)
	            );
	        } else {
	            $response = array(
	                $this->options['param_name'] => $this->get_file_objects()
	            );
	        }
	        return $this->generate_response($response, $print_response);
	    }

	    // print_response default true
	    public function post(boolean print_response) {
	        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
	            return $this->delete($print_response);
	        }
	        $upload = isset($_FILES[$this->options['param_name']]) ?
	            $_FILES[$this->options['param_name']] : null;
	        // Parse the Content-Disposition header, if available:
	        $file_name = $this->get_server_var('HTTP_CONTENT_DISPOSITION') ?
	            rawurldecode(preg_replace(
	                '/(^[^"]+")|("$)/',
	                '',
	                $this->get_server_var('HTTP_CONTENT_DISPOSITION')
	            )) : null;
	        // Parse the Content-Range header, which has the following form:
	        // Content-Range: bytes 0-524287/2000000
	        $content_range = $this->get_server_var('HTTP_CONTENT_RANGE') ?
	            preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;
	        $size =  $content_range ? $content_range[3] : null;
	        $files = array();
	        if ($upload && is_array($upload['tmp_name'])) {
	            // param_name is an array identifier like "files[]",
	            // $_FILES is a multi-dimensional array:
	            foreach ($upload['tmp_name'] as $index => $value) {
	                $files[] = $this->handle_file_upload(
	                    $upload['tmp_name'][$index],
	                    $file_name ? $file_name : $upload['name'][$index],
	                    $size ? $size : $upload['size'][$index],
	                    $upload['type'][$index],
	                    $upload['error'][$index],
	                    $index,
	                    $content_range
	                );
	            }
	        } else {
	            // param_name is a single object identifier like "file",
	            // $_FILES is a one-dimensional array:
	            $files[] = $this->handle_file_upload(
	                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
	                $file_name ? $file_name : (isset($upload['name']) ?
	                        $upload['name'] : null),
	                $size ? $size : (isset($upload['size']) ?
	                        $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
	                isset($upload['type']) ?
	                        $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
	                isset($upload['error']) ? $upload['error'] : null,
	                null,
	                $content_range
	            );
	        }
	        return $this->generate_response(
	            array($this->options['param_name'] => $files),
	            $print_response
	        );
	    }

	    // print_response default true
	    public function delete(boolean print_response) {
	        $file_name = $this->get_file_name_param();
	        $file_path = $this->get_upload_path($file_name);
	        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
	        if ($success) {
	            foreach($this->options['image_versions'] as $version => $options) {
	                if (!empty($version)) {
	                    $file = $this->get_upload_path($file_name, $version);
	                    if (is_file($file)) {
	                        unlink($file);
	                    }
	                }
	            }
	        }
	        return $this->generate_response(array('success' => $success), $print_response);
	    }
}
