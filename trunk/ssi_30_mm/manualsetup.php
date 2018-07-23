<?php
$barcode = 'TESTBLANK';
if (isset($_GET['barcode'])) {
	$barcode = $_GET['barcode'];
}
$theme = '';
if (isset($_GET['theme'])) {
	$theme = $_GET['theme'];
	$html = @file_get_contents("http://test.genesysdesigntechnologies.com/SetUp.php?code=" . urlencode($barcode) . "&sName=" . urlencode('RTN ImagePRO') . "&url=&return_url=&system_name=test&redirect=false&theme=" . $theme);
} else {
	$html = @file_get_contents("http://test.genesysdesigntechnologies.com/SetUp.php?code=" . urlencode($barcode) . "&sName=" . urlencode('RTN ImagePRO') . "&url=&return_url=&system_name=test&redirect=false");
}

$json = json_decode($html);

if (!$json->error || $json->error == "") {
	header('Location: '. $json->url);
}
die();
?>