<?php
/**
 * This is the stuff that goes before the content on the pages
 * 
 */

?>
<!DOCTYPE html>
<html>
<head>
<title>SMARTypeset Solutions Inc. Design Your Own</title>
<link rel=StyleSheet href="style.css" type="text/css" />
<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<script src="register.js"></script>

</head>	
<body>
	<div id="container">
	<div >
		<img src="images/In-stamp Logo June 2012 Image.jpg" />		
	</div>
	<div id="blank">
		
<?php
if($s->getUserId()!=""){
	$user = new User();
	$user->loadFromDB($s->getUserId());
	echo 'Welcome back '.$user->getName().' | <a href="#" Title="Coming soon">Profile</a> | <a href="#" Title="Coming soon">Order Info</a> | <a href="logout.php">Logout</a>';
	
}
else
{
	echo '&nbsp;';
}
?></div>
