<?php

class ImageDB
{
	const DEBUG = TRUE;		
	const IMAGE_PUBLIC_USER_ID = -1;
	const CATEGORY_USER_UPLOADED = 1; 
	
	
	private $connection = NULL;		

	function __construct($connection)
	{
		$this->connection = $connection;
	}
	
	public function getImageCategoryList()
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
	
	public function getImageList($category_id, $user_id)	
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
	
	public function getImageData($image_id, $user_id)
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
	
	public function setImageData($image_id, $user_id, $data)
	{
		$query = sprintf("UPDATE images SET data='%s' WHERE id=%d AND (user_id=%d OR user_id=%d)", 
			mysql_real_escape_string($json) , $image_id, $user_id, ImageDB::IMAGE_PUBLIC_USER_ID
		);	
		return mysql_query($query,$this->connection);		
	}

	public function createImage($category_id, $user_id, $name, $data)
	{
		$query = sprintf("INSERT INTO images(category_id,name,data,user_id) VALUES(%d,'%s','%s',%d)", 
			$category_id, mysql_real_escape_string($name), mysql_real_escape_string($data), $user_id 
		);
		if(!mysql_query($query,$this->connection))
		{
			if(ImageDB::DEBUG) echo mysql_error();
			return -1; 		
		}
		return mysql_insert_id($this->connection);
	}
}
?>