<?php
include_once "../Backend/startup.php";
$startup = Startup::getInstance("../");
$db = $startup->db;
if(!isset($_POST['name'])){
	?>
<form method="post" enctype="multipart/form-data" action="addgraphic.php">
	<label for="category">Category:</label><select name="category">
		<?php
		$categories = $db->getGraphicCategories();
		foreach ($categories as $category){
			echo '<option value="'.$category['id'].'">'.$category['category'].'</option>';	
		}
		?>
	</select>
	<br />
	<label for="name">Name:</label>
	<input type="text" name="name" /><br />
	<input type="file" name="file"><br />
	<input type="submit" value="add">
</form>
<?php
}
else{
	$file = $_FILES['file'];
	$category = $_POST['category'];
	$name = $_POST['name'];
	$contents =file_get_contents($file['tmp_name']);
	$contents = addslashes($contents);
	$db->newLibraryGraphic($name,$category,$contents);
	echo 'Image added.';
}
