<?php
include_once "_common.php";

if($_user_id)
{
	echo $_design_db->getTemplateJSON($_GET['template_id']);
}
?>