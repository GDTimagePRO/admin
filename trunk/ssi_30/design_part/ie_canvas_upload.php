<?php
include_once "_common.php";

$image_id = $_POST['id'];
$dataURL = $_POST['data_url'];	
$data = base64_decode(substr($dataURL, strpos($dataURL, ",")+1));	
echo json_encode($_image_db->setImageData($image_id, $_user_id, $data));
?>