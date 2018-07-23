<?php
	function getPost($id, $defualt = "")
	{		
		if(isset($_POST[$id])) return $_POST[$id];
		return  $defualt;
	}	
?>