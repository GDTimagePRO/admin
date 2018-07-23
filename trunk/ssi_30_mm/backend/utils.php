<?php
	function getPost($id, $defualt = "")
	{		
		if(isset($_POST[$id])) return $_POST[$id];
		return  $defualt;
	}

	function startsWith($haystack, $needle)
	{
		return !strncmp($haystack, $needle, strlen($needle));
	}
	
	function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
	
		return (substr($haystack, -$length) === $needle);
	}
?>