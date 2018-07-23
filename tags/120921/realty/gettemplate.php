<?php

$id = $_GET['id'];

$url = "http://www.cameronmcguinness.com/ssi/gettemplate.php?id=".$id;
$contents = file_get_contents($url);

echo $contents;

?>
