<?php
	require_once 'backend/startup.php';
	$system = Startup::getInstance();	
	echo mysql_query("UPDATE redemption_codes SET date_used = 0 WHERE code='ABC'",$system->db->connection);
	
?>