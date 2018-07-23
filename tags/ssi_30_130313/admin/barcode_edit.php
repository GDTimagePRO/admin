<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor($barcode)
	{		
		global $_design_db;
		global $_order_db;
		
		$productList = $_order_db->getProductList();
		$categoryList = $_design_db->getTemplateCategoryList();
		$templateList = $_design_db->getTemplateList();
		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>barcode:</td><td>";
		echo '<input name="barcode" type="text" value="'.htmlspecialchars($barcode->barcode).'">';
		
		echo "</td></tr><tr><td>master:</td><td>";
		echo '<select name="master">';
		if($barcode->master == "Y")
		{
			echo '<option value="Y" selected>Y</option>';
			echo '<option value="N">N</option>';
		}
		else
		{
			echo '<option value="Y">Y</option>';
			echo '<option value="N" selected>N</option>';
		}
		echo '</select>';
		
		echo "</td></tr><tr><td>Product:</td><td>";
		if (count($productList))
		{
			echo '<select name="productId">';
			foreach($productList as $productItem) {
				$selected = ($productItem['id'] == $barcode->productId) ? $selected="selected" : $selected="";
				echo '<option value="' . $productItem['id'] . '"' . $selected . '>' . $productItem['id'] . ': ' .$productItem['code'] . '</option>';
			}
		}
		echo '</select>';
		
		echo "</td></tr><tr><td>Template:</td><td>";
		if (count($templateList))
		{
			echo '<select name="templateId">';
			if ($barcode->templateId == -1)
			{
				echo '<option value="' . $barcode->templateId . '" selected>[ MASTER ]</option>';
			}
			else
			{
				echo '<option value="-1">[ MASTER ]</option>';
			}
			foreach($templateList as $templateItem) {
				$selected = ($templateItem['id'] == $barcode->templateId) ? $selected="selected" : $selected="";
				echo '<option value="' . $templateItem['id'] . '"' . $selected . '>' . $templateItem['id'] . ': ' .$templateItem['name'] . '</option>';
			}
		}
		echo '</select>';

		echo "</td></tr><tr><td>Category</td><td>";
		if (count($categoryList))
		{
			echo '<select name="templateCategoryId">';
			if ($barcode->templateCategoryId == -1)
			{
				echo '<option value="' . $barcode->templateCategoryId . '" selected>[ MASTER ]</option>';
			}
			else
			{
				echo '<option value="-1">[ MASTER ]</option>';
			}
			foreach($categoryList as $categoryItem) {
				$selected = ($categoryItem['id'] == $barcode->templateCategoryId) ? $selected="selected" : $selected="";
				echo '<option value="' . $categoryItem['id'] . '"' . $selected . '>' . $categoryItem['id'] . ': ' .$categoryItem['name'] . '</option>';
			}
		}
		echo '</select>';
		
		//echo '<input name="master" type="text" value="'.htmlspecialchars($barcode->master).'">';		
		//echo '<input name="productId" type="text" value="'.htmlspecialchars($barcode->productId).'">';
		//echo '<input name="templateId" type="text" value="'.htmlspecialchars($barcode->templateId).'">';
		//echo '<input name="templateCategoryId" type="text" value="'.htmlspecialchars($barcode->templateCategoryId).'">';		
		echo "</td></tr><tr><td>date created:</td><td>";
		echo '<input name="dateCreated" type="text" value="'.date("Y-m-d H:i:s",$barcode->dateCreate).'">';
		echo "</td></tr>";
		echo "</td></tr><tr><td>date used:</td><td>";
		echo '<input name="dateUsed" type="text" value="'.htmlspecialchars($barcode->dateUsed).'">';
		echo "</td></tr>";
		echo "</table>";
		echo '<input name="action_save" type="submit" value="Save">';
		echo '<input type="submit" value="Refresh">';
		echo "</form>";
	}
	
	if(isset($_POST['barcode']))
	{
		$bar = $_POST['barcode'];
	}
	else if(isset($_GET['barcode']))
	{
		$bar = $_GET['barcode'];
		if($bar != "" ) $actionLog = $actionLog."Loading product #".$bar."<br>";
	}
	else
	{
		$bar = "";
	}

	
	$barcode = NULL;
	if($bar != "")
	{
		$barcode = $_order_db->getBarcodeByBarcode($bar);
	}
	
	if(is_null($barcode))
	{
		$barcode = new Barcode();
		$barcode->barcode = $bar;
		$barcode->dateCreated = date("Y-m-d H:i:s", time());
		$actionLog = $actionLog."Starting new barcode<br>";		
	}
	
	if(isset($_POST['barcode'])) $barcode->barcode = $_POST['barcode'];
	if(isset($_POST['productId'])) $barcode->productId = $_POST['productId'];
	if(isset($_POST['master'])) $barcode->master = $_POST['master'];
	if(isset($_POST['templateCategoryId'])) $barcode->templateCategoryId = $_POST['templateCategoryId'];
	if(isset($_POST['templateId'])) $barcode->templateId = $_POST['templateId'];
	if(isset($_POST['dateCreated'])) $barcode->dateCreated = strtotime($_POST['dateCreated']);
	if(isset($_POST['dateUsed'])) $barcode->dateUsed = $_POST['dateUsed'];
	
	function createBarcode($barcode)
	{
		global $_order_db;
		global $actionLog;
		
			
		if($_order_db->createBarcode($barcode))
		{
			$actionLog = $actionLog."Adding<br>";
		}
		else
		{
			$actionLog = $actionLog."Error Adding.<br>";
		}
	}
	
	function updateBarcode($barcode)
	{
		global $_order_db;
		global $actionLog;
		
		if($_order_db->updateBarcode($barcode))
		{
			$actionLog = $actionLog."Saved<br>";
		}
		else
		{
			$actionLog = $actionLog."Error saving.<br>";
		}
	}
	
	
	if(isset($_POST['action_save']))
	{
		if(is_null($_order_db->getBarcodeByBarcode($barcode->barcode)))
		{
			createBarcode($barcode);
		}
		else
		{
			updateBarcode($barcode);
		}			
	}
		
	include_once 'preamble.php';
?>
<h1>Barcode ( Edit )</h1>
<h3><?php echo $actionLog; ?></h3>
<?php writeEditor($barcode); ?>
<br><br>
<a href='barcode_edit.php'>New Barcode</a>
<?php include_once 'postamble.php';?>
