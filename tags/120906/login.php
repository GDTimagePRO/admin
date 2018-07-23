<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$s = $startup->session;
if($s->getUserId()!=""){
	if($s->getCurrentItem()==""){
		Header("location: http://".$startup->settings['url']."code.php");
	}
	else{
		Header("location: http://".$startup->settings['url']."design.php");
	}
}
function printForm(){
	?>
		<form method="post" action="login.php">
	<table id="login_table" width="100">
	<tr>
		<td width="30%">Email:</td>
		<td width="70%"><input type="email" name="email" placeholder="email address" required="required"/></td>
	</tr>	
	<tr>
		<td width="30%">Password:</td>
		<td width="70%"><input type="password" name="password" placeholder="password" required="required"/></td>
	</tr>
	<tr>
		<td colspan="2"><input class="submit_button" type="submit" value="Login" /></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: center;"><a href="#" Title="Coming soon">Forgot your password?</a> | <a href="register.php">Register</a></td>
	</tr>
	</table>
	</form>
	
<?php
	
	
}
if(!isset($_POST['email'])){
	include "preamble.php";
	printForm();
	include "postamble.php";
}
else{
	$code =  $db->login($_POST['email'],$_POST['password']);
	
	if($code==Database::LOGIN_OK){ //their login information was correct.
		if($s->getCurrentItem()==""){
			Header("location: http://".$startup->settings['url']."code.php");
		}
		else{
			Header("location: http://".$startup->settings['url']."login2.php");
		}
		
	}
	elseif($code == Database::EMAIL_FAIL){
		$error = 'That email is not recognised. Perhaps you need to <a class="error_link" href="register.php">Register</a>?';
		
	}
	elseif($code==Database::PASSWORD_FAIL){
		$error = 'That password is incorrect. Perhaps you <a class="error_link" href="forgot.php">Forgot Your Password</a>?';
		
	}
	include "preamble.php";
	echo '<div id="error">'.$error.'</div>';
	echo '<div id="blank">&nbsp;</div>';
	printForm();
	include "postamble.php";
}
?>