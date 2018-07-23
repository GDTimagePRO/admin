<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor(DesignTemplateCategory $templateCategory)
	{		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>Id:</td><td>";
		echo '<input name="id_old" type="hidden" value="'.htmlspecialchars($templateCategory->id).'">';
		echo '<input name="id_new" type="text" value="'.htmlspecialchars($templateCategory->id).'">';
		echo "</td></tr><tr><td>Name:</td><td>";
		echo '<input name="name" type="text" value="'.htmlspecialchars($templateCategory->name).'">';
		echo "</td></tr>";
		echo "</table>";
		echo '<input name="action_save" type="submit" value="Save">';
		echo '<input type="submit" value="Refresh">';
		echo "</form>";
	}
	
	if(isset($_POST['id_old']))
	{
		$id = $_POST['id_old'];
	}
	else if(isset($_GET['id']))
	{
		$id = $_GET['id'];
		if ($id != -1) $actionLog = $actionLog."Loading template category #".$id."<br>";
	}
	else
	{
		$id = -1;
	}
	

	
	if($id != -1)
	{
		$templateCategory = $_design_db->getDesignTemplateCategoryById($id);
	}
	else
	{
		$templateCategory = new DesignTemplateCategory();
		$actionLog = $actionLog."Starting new category<br>";
	}
	
	
	$templateCategory->id = $oldId = $id;
	 
	if(isset($_POST['id_new'])) $templateCategory->id = $_POST['id_new'];
	if(isset($_POST['name'])) $templateCategory->name = $_POST['name'];
	
	if(isset($_POST['action_save']))
	{
		if($oldId != $templateCategory->id)
		{
			if($templateCategory->id < 0)
			{
				$actionLog = $actionLog."Negative ids are now allowed.<br>";
				$templateCategory->id = $oldId;
			}
			else
			{
				if($_design_db->createDesignTemplateCategory($templateCategory))
				{
					if($oldId > -1)
					{
						$_design_db->deleteDesignTemplateCategory($oldId);
						$actionLog = $actionLog."Update successful.<br>";
					}
					else
					{
						$actionLog = $actionLog."New category has been created.<br>";						
					}
				}
				else
				{
					$actionLog = $actionLog."Unable to change category id. The new id is probably already in use.<br>";
					$templateCategory->id = $oldId;
				}
			}
		}
		else if($templateCategory->id > -1)
		{
			if($_design_db->updateDesignTemplateCategory($templateCategory))
			{
				$actionLog = $actionLog."Update successful.<br>";				
			}
			else
			{
				$actionLog = $actionLog."Update failed.<br>";
			}
		}
		else
		{
			if($_design_db->createDesignTemplateCategory($templateCategory))
			{
				$actionLog = $actionLog."New category has been created.<br>";
			}
			else
			{
				$actionLog = $actionLog."Error creating category.<br>";
			}			
		}
	}
		
	include_once 'preamble.php';
?>
<h1>Template Category ( Edit )</h1>
<h3><?php echo $actionLog; ?></h3>
<?php writeEditor($templateCategory); ?>
<br><br>
<a href="template_category_edit.php?id=-1">New Category</a>
<?php include_once 'postamble.php';?>
