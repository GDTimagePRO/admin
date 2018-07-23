<?php
	class ImageGroup
	{
		public $id; 
		public $name;
		public $hidden;
		public $source;
		
		function __construct($id, $name, $hidden, $source)
		{
			$this->id = $id;
			$this->name = $name;
			$this->hidden= $hidden;
			$this->source= $source;
		}
	}
	
	class ImageDB
	{
		const DEBUG = TRUE;		

		//const IMAGE_DIRECTORY			= 'C:/Users/QuetechDev01/Desktop/img_data';
				
		const GROUP_DESIGNES			= 'designs';			//Images for designs table
		const GROUP_DESIGNE_TEMPLATES	= 'design_templates';	//Images for design_templates table
		const GROUP_ORDER_ITEMS			= 'order_items';		//Images for order_items table
		const GROUP_OLD_DB				= 'old_db';				//Images from the old database
		
		const TYPE_ORIGINAL				= 'original';
		const TYPE_THUMBNAIL			= 'thumbs';
		const TYPE_THUMBNAIL_COLOR		= 'thumbs_';
		const TYPE_WEB					= 'web';
		const TYPE_WEB_COLOR			= 'web_';
		
		private $connection = NULL;
		private $groupList = array();
		function getGroupList() { return $this->groupList; }

		function getGroupDirectory($groupId, $typeId = ImageDB::TYPE_ORIGINAL)
		{
			global $_settings;
			
			foreach ($this->groupList as $group)
			{
				if($group->id == $groupId) return $_settings[Startup::SETTING_IMAGE_DIR] . '/' . $typeId . '/' . $group->source;
			}
			return null;
		}

		function __construct($connection)
		{
			$this->connection = $connection;

			$this->groupList[] = new ImageGroup(	ImageDB::GROUP_OLD_DB,				'Old Database',			false,	'customer/old_db');
			$this->groupList[] = new ImageGroup(	ImageDB::GROUP_DESIGNES,			'Design Images',		true,	'system/designs');
			$this->groupList[] = new ImageGroup(	ImageDB::GROUP_DESIGNES,			'Design Images',		true,	'system/designs');
			$this->groupList[] = new ImageGroup(	ImageDB::GROUP_DESIGNE_TEMPLATES,	'Templates Images',		true,	'system/design_templates');
			$this->groupList[] = new ImageGroup(	ImageDB::GROUP_ORDER_ITEMS,			'Order Item Images',	true,	'system/order_items');
			$this->groupList[] = new ImageGroup(	"masonrow",							'Mason-Row',			false,	'customer/masonrow');
		}

		public static function parseImageId($imageId)
		{
			$bigParts = explode('/', $imageId, 2);
			$smallParts = explode('.', $bigParts[0], 2);
			
			if(count($bigParts) == 1)
			{
				$bigParts[] = end($smallParts) . '.png';
				$smallParts[count($smallParts) - 1] = ImageDB::GROUP_OLD_DB;
			}
			
			if(count($smallParts) == 1)
			{
				$groupId = $smallParts[0];
				$typeId = ImageDB::TYPE_ORIGINAL;	
			}
			else
			{
				$groupId = $smallParts[1];
				$typeId = $smallParts[0];	
			}
			return array(
				'type' => $typeId,
				'group' => $groupId,
				'path' => $bigParts[1]
			);
		}
		
		public static function createImageId($type, $group, $path)
		{
			return 	$type . '.' . $group . '/' . $path;				
		}
		
		function getImageFileName($imageId, $createDir = false )
		{
			$parts = ImageDB::parseImageId($imageId);

			//TODO: also check type against known types
			//TODO: create thumbnails and so on
			$groupDirectory = $this->getGroupDirectory($parts['group'], $parts['type']);
			if($groupDirectory == null) return null;

			if($createDir && !is_dir($groupDirectory)) mkdir($groupDirectory, 0777, true);
			return $groupDirectory . '/' . $parts['path'];
		}
			
		function getImageList($groupId, $typeId=ImageDB::TYPE_ORIGINAL)	
		{
			$result = array();
			
			$groupDirectory = $this->getGroupDirectory($groupId, $typeId);
			if($groupDirectory == null) return $result;
			
			$idPrefix = $groupId . '/';			
			if($typeId != ImageDB::TYPE_ORIGINAL) $idPrefix = $typeId . '.' . $idPrefix;
			
			if( $handle = opendir($groupDirectory . '/') )
			{
    			while( false !== ($file = readdir($handle)) )
    			{
        			if ($file != "." && $file != "..")
        			{
        				$result[] = $idPrefix . $file;
        			}
    			}
    			closedir($handle);
			}

			return $result;
		}
		
		static function loadImage($fileName)
		{
			$type = strtolower(substr($fileName, -4));
			if($type == '.png')
			{
				return @imagecreatefrompng($fileName);
			}
			else if($type == '.jpg')
			{
				return @imagecreatefromjpeg($fileName);
			}
			return false;
		}
		
		static function saveImage($image, $fileName)
		{
			$type = strtolower(substr($fileName, -4));
			if($type == '.png')
			{
				$dir = dirname($fileName);
				if(!is_dir($dir)) mkdir($dir, 0777, true);
				
				return imagepng($image, $fileName);
			}
			else if($type == '.jpg')
			{
				$dir = dirname($fileName);
				if(!is_dir($dir)) mkdir($dir, 0777, true);

				return imagejpeg($image, $fileName);
			}
			return false;
		}
		
		function createCollage($destImageId, $srcImageIds, $width, $height)
		{
			$cells = ceil(sqrt(count($srcImageIds)));
			$firstCell = $cells * $cells - count($srcImageIds);
			
			//TODO: $width != $height bug 
			//TODO: fix the bug where this only really works with 1 and 3 items
			
			$frameWidth = $width / $cells;
			$frameHeight = $height / $cells;
		
			$newImage = imagecreatetruecolor($width, $height);
			$newImageFileName = $this->getImageFileName($destImageId);
			
			//imagecolortransparent($newImage, 0xFEFFFF);
			imagefilledrectangle($newImage, 0, 0, $width, $height,  0xFEFFFF);
			
			for($i=0; $i<count($srcImageIds); $i++)
			{
				$srcFileName = $this->getImageFileName($srcImageIds[$i]);
				$srcImage = ImageDB::loadImage($srcFileName);
				
				if($srcImage)
				{
					if(imagesx($srcImage) > imagesy($srcImage))
					{
						$destWidth = $frameWidth;
						$destHeight = $frameWidth/imagesx($srcImage)*imagesy($srcImage);
					}
					else
					{
						$destHeight = $frameHeight;
						$destWidth = $frameHeight/imagesy($srcImage)*imagesx($srcImage);
					}

					$cell = $firstCell + $i;
					$row = floor($cell / $cells);
					$col = $cell % $cells;
					
 					if($row == 0)
 					{
 						$destX = ($frameWidth - $destWidth) / 2 + $col * $frameWidth - ($firstCell * $frameWidth) / 2;
 						$destY = ($frameHeight - $destHeight) / 2 + $row * $destHeight;
 					}
 					else
					{
						$destX = ($frameWidth - $destWidth) / 2 + $col * $frameWidth; 
						$destY = ($frameHeight - $destHeight) / 2 + $row * $destHeight; 
					}					
						
					imagecopyresampled(
						$newImage, $srcImage, 
						$destX, $destY, 
						0, 0, 
						$frameWidth, $frameHeight, 
						imagesx($srcImage), imagesy($srcImage)
					);
					imagedestroy($srcImage);
				}
			}
				
			$result = true;
			if(!ImageDB::saveImage($newImage, $newImageFileName)) $result = false;		
			imagedestroy($newImage);
			
			return $result;
		}
		
		static function resizeImage($srcFileName, $destFileName, $maxWidth, $maxHeight)
		{
			$image = ImageDB::loadImage($srcFileName);
			if(!$image) return false;
			
			$width = 0;
			$height = 0;
				
			if(imagesx($image) > imagesy($image))
			{
				$width = $maxWidth;
				$height = $maxWidth/imagesx($image)*imagesy($image);
			}
			else
			{
				$height = $maxHeight;
				$width = $maxHeight/imagesy($image)*imagesx($image);
			}
			
			$newImage = imagecreatetruecolor($width,$height);
			imagefilledrectangle($newImage, 0, 0, $width, $height, 0xFFFFFF);
						
			$debugWidth = imagesx($image);
			$debugHeight = imagesy($image);
			
			$debugResult = imagecopyresampled( $newImage, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image) );
			
			$result = true;
			if(!ImageDB::saveImage($newImage, $destFileName)) $result = false;
						
			imagedestroy($image);
			imagedestroy($newImage);
			
			return $result;
		}
		
		static function setImageColor($srcFileName, $destFileName, $fgColor)
		{
			$image = ImageDB::loadImage($srcFileName);
			if(!$image) return false;
			
			$width = imagesx($image);
			$height = imagesy($image);
				
			$newImage = imagecreatetruecolor($width, $height);
			imagecolortransparent($newImage, 0xFFFFFF);
			imagefilledrectangle($newImage, 0, 0, $width, $height, 0xFFFFFF);
			
			//imagesavealpha($newImage, true);
			
			imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
					
			imagedestroy($image);

			for($ix = 0;  $ix < $width; $ix++)
			{
				for($iy = 0;  $iy < $height; $iy++)
				{
					$argb = imagecolorat($newImage, $ix, $iy);
					//$a = ($argb >> 24) & 0x7F;
					$r = ($argb >> 16) & 0xFF;
					$g = ($argb >> 8) & 0xFF;
					$b = $argb & 0xFF;					
					
					if($r + $g + $b < 460)
					{
						imagesetpixel($newImage, $ix, $iy, $fgColor);
					}
					else
					{
						imagesetpixel($newImage, $ix, $iy, 0xFFFFFF);
					}
				}
			}
			
			$result = true;
			if(!ImageDB::saveImage($newImage, $destFileName)) $result = false;
						
			imagedestroy($newImage);
			return $result;
		}
		
		function createImageFromOriginal($imageId)
		{	
			$parts = ImageDB::parseImageId($imageId);
			$type = $parts['type']; 
			if($type == ImageDB::TYPE_ORIGINAL) return false;
						
			$srcFileName = $this->getImageFileName(ImageDB::createImageId(
				ImageDB::TYPE_ORIGINAL, $parts['group'], $parts['path'])
			);
			
			$destFileName = $this->getImageFileName($imageId);
			
			if($type == ImageDB::TYPE_THUMBNAIL)
			{
				return ImageDB::resizeImage($srcFileName, $destFileName, 125, 125);
			}
			else if($type == ImageDB::TYPE_WEB)
			{
				return ImageDB::resizeImage($srcFileName, $destFileName, 600, 600);
			}
			else if(substr($imageId, 0, strlen(ImageDB::TYPE_THUMBNAIL_COLOR)) == ImageDB::TYPE_THUMBNAIL_COLOR)
			{
				$colorName = substr($type, strlen(ImageDB::TYPE_THUMBNAIL_COLOR));				
				$webColorImageId = ImageDB::createImageId(ImageDB::TYPE_WEB_COLOR . $colorName, $parts['group'], $parts['path']);
				
				if(!ImageDB::createImageFromOriginal($webColorImageId)) return false;
				
				return ImageDB::resizeImage(
					$this->getImageFileName($webColorImageId),
					$destFileName, 
					125, 125
				);
			}
			else if(substr($imageId, 0, strlen(ImageDB::TYPE_WEB_COLOR)) == ImageDB::TYPE_WEB_COLOR)
			{
				$colorName =  strtolower(substr($type, strlen(ImageDB::TYPE_WEB_COLOR)));
				$color = 0x000000;
				
				switch($colorName)
				{
					case 'firebrick' :
						$color = 0xB22222;
						break;
							
					case 'royalblue' :
						$color = 0x4169E1;
						break;
							
					case 'crimson' :
						$color = 0xDC143C;
						break;
							
					case 'palevioletred' :
						$color = 0xDB7093;
						break;
							
					case 'limegreen' :
						$color = 0x32CD32;
						break;
							
					case 'dodgerblue' :
						$color = 0x1E90FF;
						break;
							
					case 'sienna' :
						$color = 0xA0522D;
						break;
							
					case 'slateblue':
						$color = 0x6A5ACD;
						break;
						
					case 'black' :
						$color = 0x000000;
						break;
					
					case 'red' :
						$color = 0xFF0000;
						break;
						  
					case 'green' :
						$color = 0x008000;
						break;
						  
					case 'blue' :
						$color = 0x0000FF;
						break;  
						  
					case 'yellow' :
						$color = 0xFFFF00;
						break;  
						  
					case 'grey' :
						$color = 0x808080;
						break;  
						  
					case 'silver' :
						$color = 0xC0C0C0;
						break;  
						  
					case 'violet' :
						$color = 0xEE82EE;
						break;

					case 'purple' :
						$color = 0x622D65;
						break;
					
					default:
						return false;
				}
				
				$webImageId = ImageDB::createImageId(ImageDB::TYPE_WEB, $parts['group'], $parts['path']); 
				$srcWebFileName = $this->getImageFileName($webImageId);

				if((!is_file($srcWebFileName)) || (filemtime($srcFileName) > filemtime($srcWebFileName)))
				{
					if(!$this->createImageFromOriginal($webImageId))
					{
						return false;
					}
				}
				
				return ImageDB::setImageColor($srcWebFileName, $destFileName, $color);
			}
			$x = substr($string_n, 0, strlen(ImageDB::TYPE_WEB_COLOR));
			
			return false;
		}

		function getDateChanged($imageId)
		{			
			$parts = ImageDB::parseImageId($imageId);
			$fileName = $this->getImageFileName(
				ImageDB::createImageId(ImageDB::TYPE_ORIGINAL, $parts['group'], $parts['path'])
			);
				
			if(!is_file($fileName)) return 0;
			return filemtime($fileName);
		}
		
		
		function getImageData($imageId, $createIfMissing = false)
		{
			$parts = ImageDB::parseImageId($imageId);
			$fileName = $this->getImageFileName($imageId);
				
			if($parts['type'] != ImageDB::TYPE_ORIGINAL)
			{
				$originalFileName = $this->getImageFileName(
						ImageDB::createImageId(ImageDB::TYPE_ORIGINAL, $parts['group'], $parts['path'])
				);
				
				if(!is_file($originalFileName)) return null;
				
				if((!is_file($fileName)) || (filemtime($originalFileName) > filemtime($fileName)))
				{
					if($createIfMissing)
					{
						$this->createImageFromOriginal($imageId);
					}
					else
					{
						return null;
					}
				}
			}

			$data = @file_get_contents($fileName);
			if($data === FALSE) return null;
			return $data;
		}

		function setImageData($imageId, $data)
		{
			//TODO: Make sure that the file time is changed
			$fileName = $this->getImageFileName($imageId, true);
			return @file_put_contents($fileName, $data) !== FALSE;
		}
			
		function deleteImage($imageId)
		{
			//TODO: delete thumbnails and so on if any
			$fileName = $this->getImageFileName($imageId);
			return unlink($fileName);
		}
		
		function createImage($groupId, $data, $ext, $typeId=ImageDB::TYPE_ORIGINAL)
		{			
			$groupDirectory = $this->getGroupDirectory($groupId, $typeId);
			if(!is_dir($groupDirectory)) mkdir($groupDirectory, 0777, true);
				
			
			for($i=0; $i<1000; $i++)
			{
				$fileName = hash('md5',time().rand()) . $ext;
				$file = @fopen($groupDirectory . '/' . $fileName, "x");
				
				if($file === FALSE) continue;

				fwrite($file, $data);
				fclose($file);
				
				return $groupId . "/" . $fileName; 
			}
		}
	}
?>