<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$id = $_GET['id'];
$user = $_GET['user'];
$images = $db->getTemplatesFromCategory($id,$user);
echo json_encode($images);
?>