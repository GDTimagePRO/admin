<?php
include_once "_common.php";

if($_user_id)
{
	echo $_image_db->setImageData(
		Design::sclImageId($_design_id), 
		$_POST['scl']
	);
	
	$designChanges = new Design();
	$designChanges->id = $_design_id;
	$designChanges->state = Design::STATE_PENDING_CONFIRMATION;
	$_design_db->updateDesign($designChanges);
}
?>