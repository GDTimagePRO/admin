<?php
	include_once "_common.php";
	
	
	$test = new User();
	$test2 = new User();
	$test->email = "a@a.a";
	$test->password="a";
	$test->name = "test";
	$test->contactName = "test";
	$test->department = "a";
	$test->street = "d";
	$test->city = "a";
	$test->state = "a";
	$test->country = "a";
	$test->postalCode = "N2P 1Z6";
	$test->phone = "123-123-1234";
	$test->fax = "123-123-1234";
	//echo $test->email;
	
	$_user_db->createUser($test);
	echo "here is user email ".$test->email;
	
	$test2 = $_user_db->getUserByEmail($test->email);
	echo $test2->email;
?>