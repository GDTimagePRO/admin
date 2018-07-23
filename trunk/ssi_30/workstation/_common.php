<?php
	include_once "../backend/utils.php";
	include_once "../backend/startup.php";
	include_once "../backend/workstation_interface.php";
	$_version	= "130205_01";	
	$_startup	= Startup::getInstance("..");
	$_system	= $_startup; 
	$_settings	= $_startup->settings; 
	$_user_db	= $_startup->db->user; 
	$_order_db	= $_startup->db->order; 
	$_image_db	= $_startup->db->image; 
	$_design_db	= $_startup->db->design; 
	$_workstation_db = new WorkstationDB($_startup->db->connection);
	$_session	= $_startup->session; 
	$_user_id	= $_session->getActiveUserId();
	$_design_id	= $_session->getActiveDesignId();
	$_url = $_startup->settings[Startup::SETTING_HOME_URL]."workstation/";
	
?>