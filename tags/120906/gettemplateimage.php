<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$id = $_GET['id'];
$data = $db->getTemplateData($id);
$data = explode(",",$data);
//echo $data;
//$data = preg_replace('/\s+/', '', $data[1]);
$data = str_replace(' ','+',$data[1]);
//echo $data;
$image = imagecreatefromstring(base64_decode($data));
header('Content-Type: image/jpeg');
imagejpeg($image);

?>

