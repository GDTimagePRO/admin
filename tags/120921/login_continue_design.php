<?php	
	include_once "_common.php";	
	
	$_system->forceLogin();
	
	if(isset($_POST['answer']))
	{
		$answer = $_POST['answer'];
		if($answer == "Yes")
		{
			Header("location: http://".$_url."design.php");
			exit();
		}
		else if($answer == "No")
		{
			Header("location: http://".$_url."enter_barcode.php");
			exit();
		}
	}
	
	include "preamble.php";
?>

	<form method="post" action="">
		<table id="login_table">
		<tr>
			<td colspan="2">You already have a design in progress, would you like to continue with the design?</td>
		</tr>	
		<tr>
			<td width="50%" align="center"><input type="submit" class="submit_button" name="answer" value="Yes"></td>
			<td width="50%" align="center"><input type="submit" class="submit_button" name="answer" value="No"></td>
		</tr>
		</table>
	</form>
	
<?php include "postamble.php"; ?>
	