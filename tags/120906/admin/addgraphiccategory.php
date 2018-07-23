<?php
if(!isset($_POST['categoryName']))
{
?>

<form method="post" action="addgraphiccategory.php">
	<label for="categoryName">Name:</label>
	<input type="text" name="categoryName"><br />
	<input type="submit" value="Add">
</form>
<?php
}
else{
	include_once "../Backend/startup.php";
	$startup = Startup::getInstance("../");
	$db = $startup->db;
	$db->newGraphicCategory($_POST['categoryName']);
	echo $_POST['categoryName']." added.";
}
?>
