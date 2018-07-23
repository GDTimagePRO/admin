<!DOCTYPE html>
<html>
<head>
<title>SMARTypeset Solutions Inc. Design Your Own</title>
<link rel=StyleSheet href="css/common_style.css" type="text/css" />
<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<script src="js/browser_check.js"></script>	
	


</head>	
<body>
	<div id="container">
		<div>
			<img src="images/In-stamp Logo June 2012 Image.jpg" />		
		</div>
		<div>
			<?php
				if($_user_id!="")
				{
					echo '<a href="logout.php">Logout</a>';
				}	
				else
				{
					echo '&nbsp;';
				}
			?>
		</div>