<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor(ImageCategory $imageCategory)
	{		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>Id:</td><td>";
		echo '<input name="id_old" type="hidden" value="'.htmlspecialchars($imageCategory->id).'">';
		echo '<input name="id_new" type="text" value="'.htmlspecialchars($imageCategory->id).'">';
		echo "</td></tr><tr><td>Name:</td><td>";
		echo '<input name="name" type="text" value="'.htmlspecialchars($imageCategory->name).'">';
		echo "</td></tr><tr><td>Type:</td><td>";
		echo '<input name="type" type="text" value="'.htmlspecialchars($imageCategory->type).'">';
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
		if ($id != -1) $actionLog = $actionLog."Loading image category #".$id."<br>";
	}
	else
	{
		$id = -1;
	}
	

	
	if($id != -1)
	{
		$imageCategory = $_image_db->getImageCategoryById($id);
	}
	else
	{
		$imageCategory = new ImageCategory();
		$actionLog = $actionLog."Starting new category<br>";
	}
	
	
	$imageCategory->id = $oldId = $id;
	 
	if(isset($_POST['id_new'])) $imageCategory->id = $_POST['id_new'];
	if(isset($_POST['name'])) $imageCategory->name = $_POST['name'];
	if(isset($_POST['type'])) $imageCategory->type = $_POST['type'];
	
	if(isset($_POST['action_save']))
	{
		if($oldId != $imageCategory->id)
		{
			if($imageCategory->id < 0)
			{
				$actionLog = $actionLog."Negative ids are now allowed.<br>";
				$imageCategory->id = $oldId;
			}
			else
			{
				if($_image_db->createImageCategory($imageCategory))
				{
					if($oldId > -1)
					{
						$_image_db->deleteImageCategory($oldId);
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
					$imageCategory->id = $oldId;
				}
			}
		}
		else if($imageCategory->id > -1)
		{
			if($_image_db->updateImageCategory($imageCategory))
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
			if($_image_db->createImageCategory($imageCategory))
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
<h1>Image Category ( Edit )</h1>
<h3><?php echo $actionLog; ?></h3>
<?php writeEditor($imageCategory); ?>
<br><br>
<a href="image_category_edit.php?id=-1">New Image Category</a>
<?php include_once 'postamble.php';?>
