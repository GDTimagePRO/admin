<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$id = $_GET['id'];
$json =$db->getTemplateJSON($id);
$json = str_replace(",]","]",$json);
$json = str_replace("undefined","1",$json); 
echo $json;
?>
