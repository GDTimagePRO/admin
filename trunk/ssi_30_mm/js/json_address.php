<?php
/*
 * This file will get the address of the user from the latitude and longitude
 * retrieved by GeoLocation.
 * This is needed as a workaround because of the inability to do cross-site
 * calls using javascript.
 */
 if(isset($_GET['lat'])){
 	$lat = $_GET['lat'];
 	$long = $_GET['lng'];
	$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$long."&sensor=true";
	$contents = file_get_contents($url);
	
	echo $contents;
 }
 else{
 	
	echo "INVALID USE!";
 }

?>