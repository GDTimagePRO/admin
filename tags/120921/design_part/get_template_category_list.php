<?php
include_once "_common.php";

if($_user_id)
{
	echo json_encode($_design_db->getTemplateCategoryList());
}
?>