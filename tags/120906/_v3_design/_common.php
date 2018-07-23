<?php
	include_once "./Backend/startup.php";
	$_startup	= Startup::getInstance(".");
	$_settings	= $_startup->settings; 
	$_image_db	= $_startup->db->image; 
	$_design_db	= $_startup->db->design; 
	$_session	= $_startup->session; 
	$_user_id	= $_session->getUserId();
	$_design_id	= $_session->getDesignId();
?>