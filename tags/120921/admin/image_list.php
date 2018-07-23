<?php 
	include_once "_common.php";

	function writeImage($image)
	{
		echo "<tr><td>";
		echo $image->id;
		echo "</td><td>";
		echo $image->categoryId;
		echo "</td><td>";
		echo htmlspecialchars($image->name);
		echo "</td><td>";
		echo $image->userId;
		echo "</td><td>";
		echo $image->dateChanged;
		echo "</td><td>";
		echo "<a href='image_edit.php?id=".$image->id."'>Edit</a>";
		echo "</td><td>";
		echo "<a href='image_list.php?delete_id=".$image->id."' onclick='return deletePrompt(".$image->id.");'>Delete</a>";
		echo "</td><tr>";
	}
	
	function writeImageList()
	{
		global $_image_db;

		$imageCategoryFilter = isset($_GET['categoryId']) ? $_GET['categoryId'] : ImageDB::IMAGE_PUBLIC_USER_ID;
		$imageList = $_image_db->getImagesByCategoryId($imageCategoryFilter);
		
		echo "<table border='1'>";
	
// 		echo "<tr>";
// 		echo "<td> id </td>";
// 		echo "<td> name </td>";
// 		echo "<td> Category </td>";
// 		echo "<td> Image Id </td>";
// 		echo "<td> Type </td>";
// 		echo "<td></td>";
// 		echo "<td></td>";
// 		echo "</tr>";
	
		for($i=0; $i<count($imageList); $i++)
		{
			writeImage($imageList[$i]);
		}
		echo "</table>";
	}
		
?>

<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete image #' + id + ' from the database ?');
	}
</script>
<h1>Image ( List )</h1>
<?php writeImageList(); ?>
<br><br>
<a href="image_edit.php?id=-1">New Image</a>
<?php include_once 'postamble.php';?>
