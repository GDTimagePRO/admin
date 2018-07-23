<?php
	require_once '_common.php';
	$result = new stdClass();
	
	if(!is_null(Common::$session))
	{
		$result->attachment = Common::$session->attachment;  
	}
	
	echo json_encode($result)
?>