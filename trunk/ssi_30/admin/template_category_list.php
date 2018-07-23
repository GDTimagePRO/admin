<?php 
	include_once "_common.php";
	
	if(isset($_GET['delete_id']))
	{
		$_design_db->deleteDesignTemplateCategory($_GET['delete_id']);
	}
	
	function writeCategory($category)
	{
		echo "<tr><td>";
		echo $category->id;
		echo "</td><td>";
		echo htmlspecialchars($category->name);
		echo "</td><td>";
		echo "<a href='template_category_edit.php?id=".$category->id."'>Edit</a>";
		echo "</td><td>";
		echo "<a href='template_category_list.php?delete_id=".$category->id."' onclick='return deletePrompt(".$category->id.");'>Delete</a>";
		echo "</td><tr>";
	}
	
	function writeCategoryList()
	{	
		global $_design_db;
				
		echo "<table border='1'>";
		echo "<tr>";
		echo "<td> id </td>";
		echo "<td> name </td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";
		
		$categories = $_design_db->getDesignTemplateCategories();
		for($i=0; $i<count($categories); $i++)
		{
			writeCategory($categories[$i]);
		}
		echo "</table>";
	}
	
	include_once 'preamble.php';
?>
<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete product #' + id + ' from the database ?');
	}
</script>
<h1>Template Category ( List )</h1>
<?php writeCategoryList(); ?>
<br><br>
<a href="template_category_edit.php?id=-1">New Category</a>
<?php include_once 'postamble.php';?>
