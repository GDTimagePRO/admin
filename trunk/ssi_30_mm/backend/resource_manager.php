<?php
	require_once 'settings.php';

	/********************************************************************************************************
	 ********************************************************************************************************
	 * ResourceId
	 ********************************************************************************************************
	 ********************************************************************************************************/

	class ResourceId
	{
		const PARAM_PREFIX = '_@@(';
		const PARAM_SUFFIX = ')';
		const PARAM_GRADIENT = 'GR1_';
		const PARAM_LINEAR_TINT = 'LTNT_';
		const PARAM_MIRROR_HORIZONTAL = 'MIRH';
		const PARAM_MIRROR_VERTICAL = 'MIRV';
		const PARAM_MONOCHROME = 'MONOC';

		public $type;
		public $group;
		public $path;

		function __construct($group = null, $path = null, $type = ResourceManager::TYPE_ORIGINAL)
		{
			$this->group	= $group;
			$this->type		= $type;
			$this->path		= $path;
		}

		public static function getParams($path)
		{
			if(is_null($path)) return null;
			$iStart = strrpos($path, ResourceId::PARAM_PREFIX);
			if(!$iStart) return null;
			$iStart += strlen(ResourceId::PARAM_PREFIX);
			$iEnd = strpos($path,ResourceId::PARAM_SUFFIX, $iStart);
			if(!$iEnd) return null;

			$value = substr($path, $iStart, $iEnd - $iStart);
			return explode(',', $value);
		}

		public static function isParamRoot($path)
		{
			if(is_null($path)) return true;
			$iStart = strrpos($path, ResourceId::PARAM_PREFIX);
			if(!$iStart) return true;
			$iStart += strlen(ResourceId::PARAM_PREFIX);
			$iEnd = strpos($path,ResourceId::PARAM_SUFFIX, $iStart);
			if(!$iEnd) return true;
			return false;
		}

		public static function getPathWithoutParams($path)
		{
			if(is_null($path)) return $path;
			$iStart = strrpos($path, ResourceId::PARAM_PREFIX);
			if(!$iStart) return $path;
			$iEnd = strpos($path,ResourceId::PARAM_SUFFIX, $iStart);
			if(!$iEnd) return $path;

			return substr($path, 0, $iStart) . substr($path, $iEnd + strlen(ResourceId::PARAM_SUFFIX));
		}

		public static function setParams($path, $params)
		{
			$paramString = ResourceId::PARAM_PREFIX;
			$paramCount = 0;
			foreach($params as $p)
			{
				if(!is_null($p))
				{
					if($paramCount > 0) $paramString .= ',';
					$paramString .= $p;
					$paramCount++;
				}
			}
			$paramString .= ResourceId::PARAM_SUFFIX;

			$path = ResourceId::getPathWithoutParams($path);
			if($paramCount > 0)
			{
				$iPos = strrpos($path,'.');
				if(!$iPos)
				{
					$path .= $paramString;
				}
				else
				{
					$path = substr($path, 0, $iPos) . $paramString . substr($path, $iPos);
				}
			}
			return $path;
		}

		public function getPath($createDir = false)
		{
			$path = ResourceManager::getPath(
					$this->group,
					null,
					$this->type
				);

			if($createDir && !is_dir($path)) mkdir($path, 0777, true);
			return $path . '/' . $this->path;
		}

		public function getOriginalPath()
		{
			return ResourceManager::getPath(
					$this->group,
					$this->path,
					ResourceManager::TYPE_ORIGINAL
			);
		}

		private static function getFileList_Internal($idPrefix, $path, &$appendTo, $recursive)
		{
			if($handle = @opendir($path) )
			{
				while( false !== ($file = readdir($handle)) )
				{
					if ($file != "." && $file != "..")
					{
						if(filetype($path . $file) == 'file')
						{
							if(ResourceId::isParamRoot($file))
							{
								$appendTo[] = $idPrefix . $file;
							}
						}
						else if($recursive)
						{
							ResourceId::getFileList_Internal(
									$idPrefix . $file . '/',
									$path . $file . '/',
									$appendTo,
									TRUE
								);
						}
					}
				}
				closedir($handle);
			}
		}

		/**
		 * @param boolean $recursive
		 * @return array
		 */
		function getFileList($recursive = FALSE, &$appendTo = NULL)
		{
			if(is_null($appendTo))
			{
				$result = array();
			}
			else
			{
				$result = &$appendTo;
			}

			$path = ResourceManager::getPath(
					$this->group,
					$this->path,
					$this->type
				);

			if($path == null) return $result;

			ResourceId::getFileList_Internal(
					$this->getId() . '/',
					$path . '/',
					$result,
					$recursive
				);

			return $result;
		}

		public function getId()
		{
			return ResourceManager::getId(
					$this->group,
					$this->path,
					$this->type
			);
		}

		function getDateChanged()
		{
			$path = $this->getPath();
			if(!is_file($path)) return 0;
			return @filemtime($path);
		}

		public function isDirty()
		{
			if(!$this->exists()) return true;
			if($this->type == ResourceManager::TYPE_ORIGINAL) return false;

			$pathOriginal = $this->getOriginalPath();
			if(!is_file($pathOriginal)) return true;

			$pathThis = $this->getPath();
			return @filemtime($pathOriginal) > @filemtime($pathThis);
		}

		public function exists()
		{
			return !is_file($this->getPath());
		}

		function delete()
		{
			return unlink($this->getPath());
		}

		function update()
		{
			return ResourceManager::update(
					$this->group,
					$this->path,
					$this->type
			);
		}

		function setData($data)
		{
			$path = $this->getPath(true);
			return @file_put_contents($path, $data) !== FALSE;
		}

		function getData($updateIfDirty = false)
		{
			if($updateIfDirty && $this->isDirty())
			{
				if(!ResourceManager::update(
						$this->group,
						$this->path,
						$this->type
				))
				{
					return null;
				}
			}

			$data = @file_get_contents($this->getPath());
			if($data === FALSE) return null;
			return $data;
		}

		public static function fromId($id)
		{
			return ResourceManager::parseId($id);
		}
	}

	/********************************************************************************************************
	 ********************************************************************************************************
	 * ResourceManager
	 ********************************************************************************************************
	 ********************************************************************************************************/

	class ResourceManager
	{

		const GROUP_SESSION				= 'session';
		const GROUP_DESIGNES			= 'designs';			//Images for designs table
		const GROUP_DESIGNE_TEMPLATES	= 'design_templates';	//Images for design_templates table
		const GROUP_ORDER_ITEMS			= 'order_items';		//Images for order_items table
		const GROUP_CUSTOMER			= 'customer';
		const GROUP_LEGACY_IMAGES		= "old_db";
		const GROUP_LEGACY_FONTS		= "fonts";

		const TYPE_ORIGINAL				= 'original';
		const TYPE_THUMBNAIL			= 'thumbs';
		const TYPE_THUMBNAIL_COLOR		= 'thumbs_';
		const TYPE_WEB					= 'web';
		const TYPE_WEB_COLOR			= 'web_';

		const DIR_CUSTOMER_IMAGES		= "images";
		const DIR_CUSTOMER_ART			= "art_library";
		const DIR_CUSTOMER_FONTS		= "fonts";
		const DIR_CUSTOMER_THEMES		= "themes";


		private static $GROUP_TO_PATH_MAP = array(
				ResourceManager::GROUP_SESSION				=> 'system/session',
				ResourceManager::GROUP_DESIGNES				=> 'system/designs',
				ResourceManager::GROUP_DESIGNE_TEMPLATES	=> 'system/design_templ ates',
				ResourceManager::GROUP_ORDER_ITEMS			=> 'system/order_items',
				ResourceManager::GROUP_CUSTOMER				=> 'customer',
				ResourceManager::GROUP_LEGACY_IMAGES		=> "legacy/images",
				"masonrow"									=> "legacy/images",
				ResourceManager::GROUP_LEGACY_FONTS			=> "legacy/fonts",
			);

		static function getPath($group, $relPath = null, $type = ResourceManager::TYPE_ORIGINAL)
		{
			$path =
				Settings::DIR_DATA_ROOT . '/' .
				$type . '/' .
				ResourceManager::$GROUP_TO_PATH_MAP[$group];

			if(!is_null($relPath)) $path .= '/' . $relPath;
			return $path;
		}

		static function getThemeDirectory($customerId, $themeName)
		{
			$path =
				Settings::DIR_DATA_ROOT . '/' .
				ResourceManager::TYPE_ORIGINAL . '/' .
				ResourceManager::$GROUP_TO_PATH_MAP[ResourceManager::GROUP_CUSTOMER] . '/' .
				$customerId . '/' .
				DIR_CUSTOMER_THEMES . '/' .
				$themeName;
		}

		static function update($group, $relPath, $type)
		{
			return false;
		}


		static function getId($group, $relPath = null, $type = ResourceManager::TYPE_ORIGINAL)
		{

			if($type == ResourceManager::TYPE_ORIGINAL)
			{
				$id = $group;
			}
			else
			{
				$id = $type . '.' . $group;
			}

			if(!is_null($relPath)) $id .= '/' . $relPath;

			return $id;
		}

		public static function parseId($id)
		{
			$bigParts = explode('/', $id, 2);
			$smallParts = explode('.', $bigParts[0], 3);

			$result = new ResourceId();

			if(count($bigParts) > 1) $result->path = $bigParts[1];


			if(count($smallParts) == 1)
			{
				$result->type = ResourceManager::TYPE_ORIGINAL;
				$result->group = $smallParts[0];
			}
			else if(count($smallParts) == 2)
			{
				$result->type = $smallParts[0];
				$result->group = $smallParts[1];
			}
			else return null;

			return $result;
		}

		static function idToPath($id)
		{
			$rid = ResourceManager::parseId($id);
			if(is_null($rid)) return null;
			return $rid->getPath();
		}

	}
?>
