<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor($image)
	{		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>Image:</td><td>";
		echo "<img src='../design_part/get_image.php?thumbnail=true&id=".$image->id."'>";
		
		echo "</td></tr><tr><td>id:</td><td>";
		echo $image->id;
		echo '<input name="id" type="hidden" value="'.$image->id.'">';
		
		echo "</td></tr><tr><td>category id:</td><td>";
		echo '<input name="categoryId" type="text" value="'.htmlspecialchars($image->categoryId).'">';
		
		echo "</td></tr><tr><td>name: </td><td>";
		echo '<input name="name" type="text" value="'.htmlspecialchars($image->name).'">';
		
		echo "</td></tr><tr><td>data: </td><td>";
		echo '<input name="data" type="file" value="change">';
		
		echo "</td></tr><tr><td>user id: </td><td>";
		echo '<input name="userId" type="text" value="'.htmlspecialchars($image->userId).'">';
		
		echo "</td></tr><tr><td>date changed:</td><td>";
		echo htmlspecialchars($image->dateChanged);
		
		echo "</td></tr>";
		echo "</table>";
		echo '<input name="action_save" type="submit" value="Save">';
		echo "</form>";
	}
	
	if(isset($_POST['id']))
	{
		$id = $_POST['id'];
	}
	else if(isset($_GET['id']))
	{
		$id = $_GET['id'];
		$actionLog = $actionLog."Loading image ".$id."<br>";
	}
	else
	{
		$id = -1;
	}
	
	if($id != -1)
	{
		$image = $_image_db->getImageById($id, false);
	}
	else
	{
		$image = new Image();
		$image->categoryId = isset($_GET['categoryId']) ? $_GET['categoryId'] : 1;
		$image->userId = ImageDB::IMAGE_PUBLIC_USER_ID;
		$image->data = "";
		$actionLog = $actionLog."Starting new image<br>";
	}
	
	
	$image->id = $id;

	
	//public $id = -1;
	//public $categoryId = NULL;
	//public $name = NULL;
	//public $data = NULL;
	//public $userId = NULL;
	//public $dateChanged = NULL;
	
	
	
	if(isset($_POST['name'])) $template->name = $_POST['name']; 

	
	function createTemplate($template)
	{
		global $_image_db;
		global $_design_db;
		global $actionLog;
		
		$template->previewImageId = $_image_db->createImageInline(
				ImageDB::CATEGORY_TEMPLATE_IMAGE,
				ImageDB::IMAGE_PUBLIC_USER_ID,
				"TemplateImage",
				""
		);
			
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
			$template->json = $design->json; 
			$template->productTypeId = $design->productTypeId;
				
			updateTemplate($template);
			
			$srcImage = $_image_db->getImageById($design->imageId);
			if(!is_null($srcImage))
			{
				$destImage = new Image();
				$destImage->id = $template->previewImageId;
				$destImage->data = $srcImage->data;
				$_image_db->updateImage($destImage); 
			}			
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
