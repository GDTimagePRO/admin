<?php	
	include_once "_common.php";	
	include_once "./backend/user_logic.php";
	
	$errorHTML = "";
	
	$email		= getPost('email');
	$password	= getPost('password');
	
	setcookie("redirect", "");
	
	if(isset($_POST['submit']))
	{
		if(loginUserByCredentials($email, $password, false) == null)
		{
			$errorHTML = 'The email or password you entered is incorrect.<br>Perhaps you need to <a class="error_link" href="register.php">register</a>?';
		}
	}
	
	if($_session->getActiveUserId() != "")
	{
		Header("location: http://".$_url."enter_barcode.php");
		exit();
	}
	
	include "preamble.php";
	if($errorHTML != "")
	{
		echo '<div id="error">'.$errorHTML.'</div>';
		echo '<div id="blank">&nbsp;</div>';
	}
?>

	<form method="post" action="login.php">
		<table id="login_table">
		<tr>
			<td width="30%">Email:</td>
			<td width="70%"><input type="email" name="email" placeholder="email address" required="required" value="<?php echo htmlspecialchars($email)?>" /></td>
		</tr><tr>
			<td width="30%">Password:</td>
			<td width="70%"><input type="password" name="password" placeholder="password" required="required"/></td>
		</tr><tr>
			<td colspan="2"><input type="submit" name="submit" class="submit_button" value="Login" /></td>
		</tr><tr>
			<td colspan="2" style="text-align: center;"><a href="#" Title="Coming soon">Forgot your password?</a> | <a href="register.php">Register</a></td>
		</tr>
		</table>
	</form>

<?php include "postamble.php"; ?>
