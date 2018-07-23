<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;
	$s = $startup->session;
	if($s->getUserId()==""){
		Header("location: http://".$startup->settings['url']."login.php");
	}
	
	function printForm($code = ""){
		echo '<div id="code_box">
		<form method="post" action="code.php">
		<div id="interior_code">Code: &nbsp;&nbsp;<input type="text" name="code" value="'.$code.'" placeholder="Product Code" required="required"/>
		<input class="code_submit_button" type="submit" value="Next" /></div>
		</form>
	</div>';
		
	}
	if(!isset($_POST['code'])){
		include "preamble.php";
		printForm();
		include "postamble.php";		
	}
	else{
			
		//check to see if the code is valid.
		$returncode = $db->checkCode($_POST['code']);
		if($returncode == Database::CODE_OK){
			$id = $db->newOrderItem($_POST['code']);
			$s->setCurrentItem($id);
			Header("Location: http://".$startup->settings['url']."design.php");
		}
		elseif($returncode == Database::CODE_UNKNOWN){
			$error = "That code was not recognised.";
			
		}
		elseif($returncode == Database::CODE_USED){
			$error = "That code has already been used.";
			
		}
		include "preamble.php";
		echo '<div id="error">'.$error.'</div>';
		echo '<div id="blank">&nbsp;</div>';
		printForm();
		include "postamble.php";
		
	}
	
?>