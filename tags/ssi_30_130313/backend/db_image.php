<?php
	class Image
	{
		public $id = -1;
		public $categoryId = NULL;
		public $name = NULL;
		public $data = NULL;
		public $userId = NULL;
		public $dateChanged = NULL;
	}
	

	class ImageCategory
	{
		const TYPE_PUBLIC = 0;
		const TYPE_HIDDEN = 1;

		public $id = -1;
		public $name;
		public $type;
	}
	
	
	class ImageDB
	{
		const DEBUG = TRUE;		
		const IMAGE_PUBLIC_USER_ID = -1;
		
		const CATEGORY_USER_UPLOADED = 1;	
		const CATEGORY_DESIGN_IMAGE = 2;
		const CATEGORY_TEMPLATE_IMAGE = 3;
		

		const IMAGE_FIELDS 			= "id, category_id, name, user_id, date_changed, data";
		const IMAGE_FIELDS_NO_DATA	= "id, category_id, name, user_id, date_changed";
		
		private $connection = NULL;		
	
		
		function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		function getImageCategoryList()
		{
			$query = "SELECT id, name FROM image_categories";
			$result = mysql_query($query,$this->connection);
	
			if(!$result)
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return NULL;
			}
	
			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $row;
			}
			return $return;
		}
		
		function getImageList($category_id, $user_id)	
		{
			$query = sprintf(
				'SELECT id, name, user_id FROM images WHERE category_id=%d AND (user_id=%d OR user_id=%d)',
				$category_id, $user_id, ImageDB::IMAGE_PUBLIC_USER_ID
			);				
			$result = mysql_query($query,$this->connection);
	
			if(!$result)
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return NULL;
			}
	
			$return = array();
			while($row = mysql_fetch_assoc($result))
			{
				$return[] = $row;
			}
			return $return;
		}
		
		function getImageData($image_id, $user_id)
		{
			$query = sprintf(
				'SELECT data FROM images WHERE id=%d AND (user_id=%d OR user_id=%d)',
				$image_id, $user_id, ImageDB::IMAGE_PUBLIC_USER_ID
			);
			$result = mysql_query($query,$this->connection);
			$row = mysql_fetch_assoc($result);
			
			if(!$row) return NULL;		
			return $row['data'];
		}
		
		function setImageData($image_id, $user_id, $data)
		{
			$query = sprintf("UPDATE images SET date_changed=NOW(), data='%s' WHERE id=%d AND (user_id=%d OR user_id=%d)", 
				mysql_real_escape_string($data) , $image_id, $user_id, ImageDB::IMAGE_PUBLIC_USER_ID
			);	
			return mysql_query($query,$this->connection);		
		}
			
		//==============================================================================
		// Image
		//==============================================================================
		
		
		function loadImage($row)
		{
			$result = new Image();

			$result->id = $row['id'];
			$result->categoryId = $row['category_id'];
			$result->name = $row['name'];
			$result->data = isset($row['data']) ? $row['data'] : NULL;
			$result->userId = $row['user_id'];
			$result->dateChanged = strtotime($row['date_changed']);
			return $result;
		}
		
		function createImage(Image $image)
		{
			$query = "INSERT INTO images(category_id,name,data,user_id, date_changed)";
			$query = $query.sprintf(" VALUES(%d,'%s','%s',%d, NOW())",
					$image->categoryId, 
					mysql_real_escape_string($image->name), 
					mysql_real_escape_string($image->data), 
					$image->userId
				);
			
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$image->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$image->id = -1;
				if(ImageDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function deleteImage($id)
		{
			$query = sprintf("DELETE FROM images WHERE id=%d",$id);
			$result = mysql_query($query,$this->connection);
		
			if($result)
			{
				
				return true;
			}
			else
			{
				return false;
			}
				
		}

		function createImageInline($categoryId, $userId, $name, $data)
		{
			$image = new Image();
			$image->categoryId = $categoryId;
			$image->userId = $userId;
			$image->name = $name;
			$image->data = $data;
			$this->createImage($image);
			
			return $image->id;
		}
		
	
		
		function updateImage(Image $image)
		{			
			$query = "UPDATE images SET date_changed = NOW()";
			
			if(!is_null($image->categoryId))
			{
				$query = $query.sprintf(", category_id =%d", $image->categoryId); 
			}
			
			if(!is_null($image->userId))
			{
				$query = $query.sprintf(", user_id =%d", $image->userId);
			}

			if(!is_null($image->name))
			{
				$query = $query.sprintf(", name ='%s'", mysql_escape_string($image->name));
			}			
				
			if(!is_null($image->data))
			{
				$query = $query.sprintf(", data ='%s'", mysql_escape_string($image->data));
			}
				
			$query = $query.sprintf(" WHERE id=%d", $image->id);
			
			if(!mysql_query($query,$this->connection))
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return false;
			}
			return true;
		}
		
		function getImageById($id, $withData = true)
		{
			$fields = $withData ? ImageDB::IMAGE_FIELDS : ImageDB::IMAGE_FIELDS_NO_DATA;
			$query = sprintf("SELECT ".$fields." FROM images WHERE id=%d", $id);		
			
			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return NULL;
			}
			
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
			
			return $this->loadImage($row);					
		}
		
		function getImagesByCategoryId($categoryId, $userId, $withData = false)
		{
			$fields = $withData ? ImageDB::IMAGE_FIELDS : ImageDB::IMAGE_FIELDS_NO_DATA;

			if(isset($userId))
			{
				$query = sprintf(
					'SELECT '.$fields.' FROM images WHERE category_id=%d AND (user_id=%d OR user_id=%d)',
					$categoryId, $userId, ImageDB::IMAGE_PUBLIC_USER_ID
				);
			}
			else
			{
				$query = sprintf('SELECT '.$fields.' FROM images WHERE category_id=%d ORDER BY id', $categoryId);
			}				
			
			$result = mysql_query($query,$this->connection);			
			if(!$result)
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return array();
			}
			
			$images = array();
			while($row = mysql_fetch_assoc($result))
			{
				$images[] = $this->loadImage($row);;
			}
			return $images;
		}
		
		function getImages($withData = false)
		{
			$fields = $withData ? ImageDB::IMAGE_FIELDS : ImageDB::IMAGE_FIELDS_NO_DATA;
			$query = "SELECT ".$fields." FROM images ORDER BY category_id, user_id, id";
		
			$result = mysql_query($query,$this->connection);			
			if(!$result)
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return array();
			}
			
			$images = array();
			while($row = mysql_fetch_assoc($result))
			{
				$images[] = $this->loadImage($row);;
			}
			return $images;
		}
		
		//==============================================================================
		// Image Category
		//==============================================================================
		function loadImageCategory($row)
		{
			$result = new ImageCategory();
			$result->id = $row['id'];
			$result->name = $row['name'];
			$result->type = $row['type'];
				
			return $result;
		}
		
		function createImageCategory(ImageCategory $imageCategory)
		{
			if($imageCategory->id > -1)
			{
				$query = sprintf(
					"INSERT INTO image_categories(id, name, type) ".
					"VALUES( %d, '%s', %d )",
					$imageCategory->id,
					mysql_real_escape_string($imageCategory->name),
					$imageCategory->type
				);
			}
			else
			{
				$query = sprintf(
					"INSERT INTO image_categories(name, type) ".
					"VALUES( '%s', %d )",
					mysql_real_escape_string($imageCategory->name),
					$imageCategory->type
				);
			}
				
			$result = mysql_query($query,$this->connection);
			if($result)
			{
				$imageCategory->id = mysql_insert_id($this->connection);
				return true;
			}
			else
			{
				$imageCategory->id = -1;
				if(ImageDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		function deleteImageCategory($id)
		{
			$query = sprintf("DELETE FROM image_categories WHERE id=%d",$id);
			$result = mysql_query($query,$this->connection);
		
			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		
		}
		
		function updateImageCategory(ImageCategory $imageCategory)
		{
			$query = "UPDATE image_categories SET ";
			$first = true;
		
			$query = $query.sprintf(" name='%s',", mysql_real_escape_string($imageCategory->name));
			$query = $query.sprintf(" type=%d", $imageCategory->type);
			$query = $query.sprintf(" WHERE id=%d", $imageCategory->id);
		
			if(!mysql_query($query,$this->connection))
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return false;
			}
			return true;
		}
		
		function getImageCategoryById($id)
		{
			$query = sprintf("SELECT * FROM image_categories WHERE id=%d", $id);
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadImageCategory($row);
		}
		
		function getImageCategoryByName($name)
		{
			$query = sprintf(
				"SELECT * FROM image_categories WHERE name='%S'", 
				mysql_real_escape_string($name)
			);
			
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
		
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
		
			return $this->loadImageCategory($row);
		}
		
		
		function getImageCategories()
		{
			$query = "SELECT * FROM image_categories ORDER BY id";
		
			$result = mysql_query($query,$this->connection);
			if(!$result)
			{
				if(ImageDB::DEBUG) echo mysql_error();
				return array();
			}
		
			$categories = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$categories[] = $this->loadImageCategory($row);
			}
		
			return $categories;
		}
	}
?>