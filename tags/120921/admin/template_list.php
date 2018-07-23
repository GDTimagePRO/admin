<?php 
	include_once "_common.php";
	
	if(isset($_GET['delete_id']))
	{
		$template = $_design_db->getDesignTemplateById($_GET['delete_id']);
		if(!is_null($template))
		{
			if($template->previewImageId >= 0)
			{
				$_image_db->deleteImage($template->previewImageId);
			}
			$_design_db->deleteDesignTemplate($template->id);
		}
	}
	
	function writeTemplate($template)
	{
		echo "<tr><td>";
		echo $template->id;
		echo "</td><td>";
		echo htmlspecialchars($template->name);
		echo "</td><td>";
		echo $template->categoryId;
		echo "</td><td>";
		echo $template->previewImageId;
		echo "</td><td>";
		echo $template->productTypeId;
		echo "</td><td>";
		echo "<a href='template_edit.php?id=".$template->id."'>Edit</a>";		
		echo "</td><td>";
		echo "<a href='template_list.php?delete_id=".$template->id."' onclick='return deletePrompt(".$template->id.");'>Delete</a>";				
		echo "</td><tr>";
	}
	
	function writeTemplateList()
	{	
		global $_design_db;
		
		$templateList = $_design_db->getDesignTemplates();
		echo "<table border='1'>";

		echo "<tr>";
		echo "<td> id </td>";
		echo "<td> name </td>";
		echo "<td> Category </td>";
		echo "<td> Image Id </td>";
		echo "<td> Type </td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";
		
		for($i=0; $i<count($templateList); $i++)
		{
			writeTemplate($templateList[$i]);
		}
		echo "</table>";
	}
	
	include_once 'preamble.php';
?>
<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete template #' + id + ' from the database ?');
	}
</script>
<h1>Template ( List )</h1>
<?php writeTemplateList(); ?>
<br><br>
<a href="template_edit.php?id=-1">New Template</a>
<?php include_once 'postamble.php';?>
