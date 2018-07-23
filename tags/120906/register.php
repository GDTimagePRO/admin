<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$s = $startup->session;
function printform($vars=null){
	?>
	<form method="post" action="register.php">
	<table id="register_table" width="100">
	<tr>
		<td width="30%">Email:</td>
		<td width="70%"><input type="email" name="email" placeholder="email address" value="<?php echo $vars['email']; ?>" required="TRUE"/></td>
	</tr>	
	<tr>
		<td width="30%">Password:</td>
		<td width="70%"><input type="password" name="password" placeholder="password" required="true"/></td>
	</tr>
	<tr>
		<td width="30%">Re-type Password:</td>
		<td width="70%"><input type="password" name="password2" placeholder="password" required="true"/></td>
	</tr>
	<tr>
		<td width="30%">Name:</td>
		<td width="70%"><input type="text" name="name" placeholder="Name" value="<?php echo $vars['name']; ?>" required="TRUE"/></td>
	</tr>
	<tr>
		<td width="30%">Contact Name:</td>
		<td width="70%"><input type="text" name="contactname" value="<?php echo $vars['contactname']; ?>" placeholder="contact name (if necessary)"/></td>
	</tr>
	<tr>
		<td width="30%">Department:</td>
		<td width="70%"><input type="text" name="department" value="<?php echo $vars['department']; ?>" placeholder="Department (if necessary)"/></td>
	</tr>
	<tr>
		<td width="30%">Street:</td>
		<td width="70%"><input id="street" type="text" name="street" value="<?php echo $vars['street']; ?>" placeholder="Street" required="TRUE"/></td>
	</tr>
	<tr>
		<td width="30%">City:</td>
		<td width="70%"><input id="city" type="text" name="city" value="<?php echo $vars['city']; ?>" placeholder="City" required="TRUE"/></td>
	</tr>
	<tr>
		<td width="30%">State:</td>
		<td width="70%"><input id="state" type="text" name="statecode" value="<?php echo $vars['statecode']; ?>" placeholder="State" required="TRUE"/></td>
	</tr>
	<tr>
		<td width="30%">Country:</td>
		<td width="70%"><input id="country" type="text" name="countrycode" value="<?php echo $vars['countrycode']; ?>" placeholder="Country" required="TRUE"/></td>
	</tr>
	<tr>
		<td width="30%">Postal Code:</td>
		<td width="70%"><input id="zip" type="text" name="postalcode" value="<?php echo $vars['postalcode']; ?>" placeholder="Postal Code" required="TRUE"/></td>
	</tr>
	<tr>
		<td width="30%">Phone:</td>
		<td width="70%"><input type="text" name="phone" value="<?php echo $vars['phone']; ?>" placeholder="Ex. (555) 555-5555" required="TRUE"/></td>
	</tr>
	<tr>
		<td width="30%">Fax:</td>
		<td width="70%"><input type="text" name="fax" value="<?php echo $vars['fax']; ?>" placeholder="Ex. (555) 555-5555"/></td>
	</tr>
	<tr>
		<td colspan="2"><input class="submit_button" type="submit" value="Register" /></td>
	</tr>
	</table>
	</form>
	<div id="blank">&nbsp;</div>
	<?php
	
	
}

?>




<?php
//$email = $_POST['email'];
if(!isset($_POST['email'])){
	include "preamble.php";
	printform();
	include "postamble.php";
?>
	
<?php
}
else{
	$email = $_POST['email'];
	$password = $_POST['password'];
	$password2 = $_POST['password2'];
	$name = $_POST['name'];
	$contact_name  = $_POST['contactname'];
	$department = $_POST['department'];
	$street = $_POST['street'];
	$state = $_POST['statecode'];
	$country = $_POST['countrycode'];
	$postalcode = $_POST['postalcode'];
	$phone = $_POST['phone'];
	$fax = $_POST['fax'];
	$error = array();
 	//$error[] = "Email address already in use";
	if($password!=$password2){
		$error[] = "Passwords do not match";
	}
	if(sizeof($error)>0){
		include "preamble.php";
		echo '<div id="error">';
		for($i=0;$i<sizeof($error);$i++){
			echo $error[$i].'<br />';
		}
		echo '</div>';
		echo '<div id="blank">&nbsp;</div>';
		printform($_POST);
		include "postamble.php";	
	}
	else{
		$user = new User();
		$user->setValues($_POST);
		$db->register($user);
		Header("Location: code.php");
	}
	
}
?>


