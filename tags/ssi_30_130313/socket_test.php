<?php
	$fp = fsockopen("localhost", 2733, $errno, $errstr, 30);
	if (!$fp)
	{
		echo "$errstr ($errno)<br />\n";
	}
	else
	{
		fwrite($fp, "Hello From PHP\r\n");
		$response = fgets($fp);
		echo $response;
    	//while (!feof($fp)) {
    	//	echo fgets($fp, 128);
    	//}
	}
    fclose($fp);
?>