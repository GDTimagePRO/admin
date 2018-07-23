<?php
	require_once "../backend/startup.php";
	require_once "../backend/utils.php";
	require_once "../backend/workstation_interface.php";

	$_version	= "130205_01";	
	$_startup	= Startup::getInstance();
	$_system	= $_startup; 
	$_order_db	= $_startup->db->order; 
	$_design_db	= $_startup->db->design; 
	
	$_workstation_db = new WorkstationDB($_startup->db->connection);
	$_url = Settings::HOME_URL . "workstation/";
	
?>