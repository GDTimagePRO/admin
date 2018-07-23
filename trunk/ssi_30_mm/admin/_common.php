<?php
	require_once "../backend/startup.php";
	
	$_startup	= Startup::getInstance();
	$_system	= $_startup; 
	$_order_db	= $_startup->db->order; 
	$_design_db	= $_startup->db->design; 
	
	$isAdmin	= false;
	
	if(!isset($_COOKIE['is_admin_enabled']))
	{
		if(isset($_POST['admin_name']) && isset($_POST['admin_password']))
		{
			if((strtoupper($_POST['admin_name']) == 'SECURE') && (strtoupper($_POST['admin_password']) == 'WOOT'))
			{
				setcookie('is_admin_enabled', 'true');
				$isAdmin = true;
			}
		}
	}
	else $isAdmin = true;
	
	if(!$isAdmin)
	{
		?>
			<html>
			<body>
			<form method="post">
			<table>
				<tr><td>Loggin: </td><td><input type="text" name="admin_name"></td></tr>
				<tr><td>Password: </td><td><input type="text" name="admin_password"></td></tr>
				<tr><td></td><td><input type="submit" value="login"></td></tr>
			</table>
			</form>
			</body>
			</html>
		<?php
		exit();
	}
?>