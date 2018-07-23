<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor($image)
	{		
		global $_image_db;
		global $_user_db;
		
		$categoryList = $_image_db->getImageCategoryList();
		$userList = $_user_db->getUserList();
		
		echo '<form method="post" enctype="multipart/form-data">';
		echo "<table>";
		echo "<tr><td>Image:</td><td>";
		echo "<img src='get_image.php?thumbnail=true&id=".$image->id."'>";
		
		echo "</td></tr><tr><td>id:</td><td>";
		echo $image->id;
		echo '<input name="id" type="hidden" value="'.$image->id.'">';
		
		echo "</td></tr><tr><td>category:</td><td>";
		if (count($categoryList))
		{
			echo '<select name="categoryId">';
			
			$select = false;
			foreach($categoryList as $categoryItem) 
			{
				if($categoryItem['id'] == $image->categoryId)
				{ 
					$selected="selected";
					$select = true;
				}
				else
				{
					$selected="";
				}
				echo '<option value="' . $categoryItem['id'] . '"' . $selected . '>' . $categoryItem['id'] . ': ' .$categoryItem['name'] . '</option>';
			}
			if (!$select)
			{
				echo '<option value="' . $image->categoryId . '" selected>[ Undefined: ' .$image->categoryId . ' ]</option>';
			}
		}
		//echo '<input name="categoryId" type="text" value="'.htmlspecialchars($image->categoryId).'">';
		
		echo "</td></tr><tr><td>name: </td><td>";
		echo '<input name="name" type="text" value="'.htmlspecialchars($image->name).'">';
		
		echo "</td></tr><tr><td>data: </td><td>";
		echo '<input name="newImage" type="file">';
		
		echo "</td></tr><tr><td>user id: </td><td>";
		
		
		array_unshift($userList,array(
			'id' => ImageDB::IMAGE_PUBLIC_USER_ID,
			'name' => '[ Public User ]',
		));
		
		if (count($userList))
		{
			echo '<select name="userId">';
				
			$select = false;
			foreach($userList as $user) {
				if($user['id'] == $image->userId)
				{
					$selected="selected";
					$select = true;
				}
				else
				{
					$selected="";
				}
				echo '<option value="' . $user['id'] . '"' . $selected . '>' . $user['id'] . ': ' .$user['name'] . '</option>';
			}
			if (!$select)
			{
				if ($image->userId == -1)
				{
					echo '<option value="' . $image->userId . '" selected>[ Public ]</option>';
				}
				else
				{
					echo '<option value="' . $image->userId . '" selected>[ Undefined: ' .$image->userId . ' ]</option>';
				}
			}
		}
		//echo '<input name="userId" type="text" value="'.htmlspecialchars($image->userId).'">';
		
		echo "</td></tr><tr><td>date changed:</td><td>";
		echo htmlspecialchars(date("Y-m-d H:i:s", $image->dateChanged));
		
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
	
	
	
	if(isset($_POST['action_save']))
	{
		$image->categoryId = $_POST['categoryId'];
		$image->name = $_POST['name'];
		$image->userId = $_POST['userId'];

		if(isset($_FILES['newImage']))
		{			
			$file = $_FILES['newImage'];
			if($file['name'] != "")
			{
				$image->name = $file['name'];
				$image->data = file_get_contents($file['tmp_name']);
				
				//$image->data = file_get_contents($file['tmp_name']);
				
				//$imageMaybeTrueColor = imagecreatefromstring($image->data);
				//$imageTrueColor = imagecreatetruecolor(imagesx($imageMaybeTrueColor),imagesy($imageMaybeTrueColor));
				//imagecopy($imageTrueColor, $imageMaybeTrueColor, 0, 0, 0, 0, imagesx($imageMaybeTrueColor),imagesy($imageMaybeTrueColor));
				//ob_start();
				//imagepng($imageTrueColor);
				//$image->data = ob_get_contents();
				//ob_end_clean();				
				//imagedestroy($imageMaybeTrueColor);
				//imagedestroy($imageTrueColor);				
			}
		}
		
		if($image->id < 0)
		{
			if($_image_db->createImage($image))
			{
				$actionLog = $actionLog."New image created successfully<br>";
			}
			else
			{
				$actionLog = $actionLog."Error creating image<br>";
			}
		}
		else
		{
			if($_image_db->updateImage($image))
			{
				$actionLog = $actionLog."Image updated successfully<br>";
			}
			else
			{
				$actionLog = $actionLog."Error updating image<br>";
			}
		}
	}
		
	include_once 'preamble.php';
?>
<h1>Image ( Edit )</h1>
<h3><?php echo $actionLog; ?></h3>
<?php writeEditor($image); ?>
<br><br>
<a href="image_edit.php?id=-1">New Image</a>
<?php include_once 'postamble.php';?>
