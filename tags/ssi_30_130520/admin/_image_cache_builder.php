<html>
<body>
<?php 
	include_once "_common.php";

	$types = array();
	
	$types[] = ImageDB::TYPE_WEB_COLOR . 'black';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'red';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'green';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'blue';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'yellow';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'grey';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'silver';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'violet';
	$types[] = ImageDB::TYPE_WEB_COLOR . 'purple';
	
	function writeImage($id)
	{
		echo "<img src='../design_part/get_image.php?id=".$id."'><br>";		
	}
	
	function facheGroup($group)
	{
		global $_image_db;
		global $types;
		
		$list = $_image_db->getImageList($group);
		
		foreach($list as $item)
		{
			foreach($types as $t)
			{
				writeImage($t.'.'.$item);
			}
		}
	}
	
	facheGroup(ImageDB::GROUP_OLD_DB);
?>
</body>
</html>