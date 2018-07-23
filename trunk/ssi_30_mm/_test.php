<html>
<body>
<?php 
	require_once "./backend/resource_manager.php";
	$rid = new ResourceId(ResourceManager::GROUP_SESSION,'9ec1c2c5dd5b87f7347c04730407f97c149cccbb/1379206404506_@@(LTNT#70421440DA,MIRV).png', ResourceManager::TYPE_ORIGINAL);
	//$rid = new ResourceId(ResourceManager::GROUP_SESSION,'9ec1c2c5dd5b87f7347c04730407f97c149cccbb/1379206404506.jpg', ResourceManager::TYPE_ORIGINAL);
	
	//print_r(ResourceId::getParams($rid->path));
	//print_r(ResourceId::getPathWithoutParams($rid->path));
	//print_r(ResourceId::setParams($rid->path, array(null)));
	
	echo substr(hash('ripemd160', uniqid('', true)), 0, 12);
?>
</body>
</html>
