<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor($template)
	{		
		global $_design_db;
		
		$categoryList = $_design_db->getTemplateCategoryList();
		$designList = $_design_db->getDefualtDesignTemplateList();
		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>Image :</td><td>".htmlspecialchars($template->getPreviewImageId()).'<br>';
		echo "<img src='../design_part/get_image.php?id=".ImageDB::TYPE_THUMBNAIL.'.'.$template->getPreviewImageId()."'>";
		echo "</td></tr><tr><td>id:</td><td>";
		echo $template->id;
		echo '<input name="id" type="hidden" value="'.$template->id.'">';
		echo "</td></tr><tr><td>name:</td><td>";
		echo '<input name="name" type="text" value="'.htmlspecialchars($template->name).'">';
		echo "</td></tr><tr><td>category:</td><td>";
		
		if (count($categoryList)) 
		{
			echo '<select name="categoryId">';
			foreach($categoryList as $categoryItem) {
				$selected = ($categoryItem['id'] == $template->categoryId) ? $selected="selected" : $selected="";
						
      			echo '<option value="' . $categoryItem['id'] . '"' . $selected . '>' . $categoryItem['id'] . ': ' .$categoryItem['name'] . '</option>';
			}
		}
		echo '</select>';
		
		
		echo "</td></tr><tr><td>product type:</td><td>";
		if (count($designList))
		{
			echo '<select name="productTypeId">';
			foreach($designList as $designItem) {
				$selected = ($designItem['product_type_id'] == $template->productTypeId) ? $selected="selected" : $selected="";
		
				echo '<option value="' . $designItem['product_type_id'] . '"' . $selected . '>' . $designItem['product_type_id'] . ': ' . $designItem['description'] . '</option>';
			}
		}
		echo '</select>';
		/*
		echo '<input name="productTypeId" type="text" value="'.htmlspecialchars($template->productTypeId).'">';
		echo "</td></tr><tr><td>image:</td><td>";
		echo '<input name="previewImageId" type="hidden" value="'.$template->previewImageId.'">';
		echo $template->previewImageId;
		*/
		echo "</td></tr><tr><td>design JSON:</td><td>";
		echo '<input name="designJSON" type="text" value="'.htmlspecialchars($template->designJSON).'">';
		
		echo "</td></tr><tr><td>config JSON:</td><td>";
		echo '<input name="configJSON" type="text" value="'.htmlspecialchars($template->configJSON).'">';
		
		
		echo "</td></tr>";
		echo "</table>";
		echo '<input name="action_save" type="submit" value="Save">';
		echo '<input name="action_copy" type="submit" value="Copy Selected Design">';
		echo '<input type="submit" value="Refresh">';
		echo "</form>";
	}
	
	if(isset($_POST['id']))
	{
		$id = $_POST['id'];
	}
	else if(isset($_GET['id']))
	{
		$id = $_GET['id'];
		$actionLog = $actionLog."Loading template ".$id."<br>";
	}
	else
	{
		$id = -1;
	}
	
	if($id != -1)
	{
		$template = $_design_db->getDesignTemplateById($id);
	}
	else
	{
		$template = new DesignTemplate();
		$actionLog = $actionLog."Starting new template<br>";
	}
	
	
	$template->id = $id;
	
	
	if(isset($_POST['name'])) $template->name = $_POST['name']; 
	if(isset($_POST['categoryId'])) $template->categoryId = $_POST['categoryId'];
	if(isset($_POST['previewImageId'])) $template->previewImageId = $_POST['previewImageId'];
	if(isset($_POST['configJSON'])) $template->configJSON = $_POST['configJSON'];
	if(isset($_POST['designJSON'])) $template->designJSON = $_POST['designJSON'];
	if(isset($_POST['productTypeId'])) $template->productTypeId = $_POST['productTypeId'];

	
	function createTemplate($template)
	{
		global $_image_db;
		global $_design_db;
		global $actionLog;
		
		if($_design_db->createDesignTemplate($template))
		{
			$actionLog = $actionLog."Adding<br>";
		}
		else
		{
			$actionLog = $actionLog."Error Adding.<br>";
		}
	}
	
	function updateTemplate($template)
	{
		global $_design_db;
		global $actionLog;
		
		if($_design_db->updateDesignTemplate($template))
		{
			$actionLog = $actionLog."Saved<br>";
		}
		else
		{
			$actionLog = $actionLog."Error saving.<br>";
		}
	}
	
	
	if(isset($_POST['action_copy']))
	{
		if($_design_id != "")
		{
			if($template->id < 0)
			{
				createTemplate($template);
			}
				
			$actionLog = $actionLog."Loading design ".$_design_id."<br>";
			
			$design = $_design_db->getDesignById($_design_id);
			$template->designJSON = $design->designJSON; 
			$template->productTypeId = $design->productTypeId;
			
			$imageData = $_image_db->getImageData($design->getPreviewImageId());
			if(!is_null($imageData))
			{
				$_image_db->setImageData($template->getPreviewImageId(), $imageData);
			}
			
			updateTemplate($template);
		}
		else
		{
			$actionLog = $actionLog."No design selected<br>";
		}
	}
	else if(isset($_POST['action_save']))
	{
		if($template->id < 0)
		{
			createTemplate($template);
		}
		else
		{
			updateTemplate($template);
		}			
	}
		
	include_once 'preamble.php';
?>
<h1>Template ( Edit )</h1>
<h3><?php echo $actionLog; ?></h3>
<?php writeEditor($template); ?>
<br><br>
<a href="template_edit.php?id=-1">New Template</a>
<?php include_once 'postamble.php';?>
