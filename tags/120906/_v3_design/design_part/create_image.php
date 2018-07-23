<?php
include_once "_common.php";

if($_user_id)
{
	$category_id = ImageDB::CATEGORY_USER_UPLOADED;
	if(isset($_GET['category_id'])) $category_id = $_GET['category_id']; 

	$file = $_FILES['file'];
	$name = $file['name'];
	$data = file_get_contents($file['tmp_name']);
	if(!$data) exit();
	
	echo json_encode($_image_db->createImage($category_id, $_user_id, $name, $data));
}
?>