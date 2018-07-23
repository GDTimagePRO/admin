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
		echo '<input name="barcode" type="text" value="'.htmlspecialchars($barcode->barcode).'"></td></tr>';		
				
		echo "<tr><td>master:</td><td>";
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
		echo '</select></td><td>';		
		
		echo "<tr><td>date created:</td><td>";
		echo '<input name="dateCreated" type="text" value="'.date("Y-m-d H:i:s",$barcode->dateCreate).'"></td></tr>';		
		
		echo "<tr><td>date used:</td><td>";
		echo '<input name="dateUsed" type="text" value="'.htmlspecialchars($barcode->dateUsed).'"></td></tr>';

		echo "<tr><td>json:</td><td>";
		echo '<input name="json" type="text" value="'.htmlspecialchars($barcode->configJSON).'">';
		echo '<input name="jsonOld" type="hidden" value="'.htmlspecialchars($barcode->configJSON).'"></td></tr>';
		
		
		$config = Config::fromJSON($barcode->configJSON);
		echo '<tr><td colspan="2">options: -------------------------</td><td>';		

		echo "<tr><td>gui type:</td><td>";
		echo '<select name="uiMode">';
		if($config->uiMode == Config::UI_MODE_SIMPLE)
		{
			echo '<option value=""> Normal </option>';
			echo '<option value="'.Config::UI_MODE_SIMPLE.'" selected> Simple </option>';
		}
		else
		{
			echo '<option value="" selected> Normal </option>';
			echo '<option value="'.Config::UI_MODE_SIMPLE.'"> Simple </option>';
		}
		echo '</select>';
		echo "</table>";
		
		echo "<table>";
		echo "<tr><td>#</td><td>Product</td><td>Template</td><td>Template Category</td><td></td></tr>";
		if(!is_null($config->items))
		{
			for($i=0; $i<count($config->items); $i++)
			{
				$item = $config->items[$i];
				
				echo '<tr><td>'.($i+1).'</td>';
				
				echo '<td><select name="productId_'.$i.'">';				
				echo '<option value="" '.(is_null($item->productId) ? 'selected' : '').'>[NOT SET]</option>';				
				$found = false;
				if(count($productList))
				{
					foreach($productList as $productItem)
					{
						echo '<option value="'.$productItem['id'].'"';
						if($productItem['id'] == $item->productId)
						{
							echo 'selected';
							$found = true;
						}
						echo '>'.htmlspecialchars($productItem['id']). ': '.htmlspecialchars($productItem['code']).'</option>';
					}
				}
				if(!$found && !is_null($item->productId)) echo '<option selected>'.htmlspecialchars($item->productId).'</option>';
				echo '</select></td>';

				
				echo '<td><select name="templateId_'.$i.'">';
				echo '<option value="" '.(is_null($item->templateId) ? 'selected' : '').'>[NOT SET]</option>';
				$found = false;
				if(count($templateList))
				{
					foreach($templateList as $templateItem)
					{
						echo '<option value="'.$templateItem['id'].'"';
						if($templateItem['id'] == $item->templateId)
						{
							echo 'selected';
							$found = true;
						}
						echo '>'.htmlspecialchars($templateItem['id']). ': '.htmlspecialchars($templateItem['name']).'</option>';
					}
				}
				if(!$found && !is_null($item->templateId)) echo '<option selected>'.htmlspecialchars($item->templateId).'</option>';
				echo '</select></td>';
				
								
				echo '<td><select name="templateCategoryId_'.$i.'">';
				echo '<option value="" '.(is_null($item->templateCategoryId) ? 'selected' : '').'>[NOT SET]</option>';
				$found = false;
				if(count($templateList))
				{
					foreach($categoryList as $categoryItem)
					{
						echo '<option value="'.$categoryItem['id'].'"';
						if($categoryItem['id'] == $item->templateCategoryId)
						{
							echo 'selected';
							$found = true;
						}
						echo '>'.htmlspecialchars($categoryItem['id']). ': '.htmlspecialchars($categoryItem['name']).'</option>';
					}
				}
				if(!$found && !is_null($item->templateCategoryId)) echo '<option selected>'.htmlspecialchars($item->templateCategoryId).'</option>';
				echo '</select></td>';
				
				echo '<td><input name="action_remove_'.$i.'" type="submit" value="Remove"></td></tr>';
				
			}
		}
		echo "</table>";
		echo '<input name="action_add" type="submit" value="Add Products"><br><br>';

		echo '<input name="action_save" type="submit" value="Save">';
		echo '<input name="action_reset" type="submit" value="Reset">';
		echo "</form>";
	}
	
	if(isset($_POST['barcode']))
	{
		$bar = $_POST['barcode'];
	}
	else if(isset($_GET['barcode']))
	{
		$bar = $_GET['barcode'];
		if($bar != "" ) $actionLog = $actionLog."Loading product #".$bar."<br><br>";
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
	
	if(!isset($_POST['action_reset']))
	{
		if(isset($_POST['barcode'])) $barcode->barcode = $_POST['barcode'];
		if(isset($_POST['master'])) $barcode->master = $_POST['master'];
		if(isset($_POST['dateCreated'])) $barcode->dateCreated = strtotime($_POST['dateCreated']);
		if(isset($_POST['dateUsed'])) $barcode->dateUsed = $_POST['dateUsed'];
		if(isset($_POST['json']) && isset($_POST['jsonOld']))
		{
			if($_POST['json'] != $_POST['jsonOld'])
			{
				$barcode->configJSON = $_POST['json'];
			}
			else
			{
				$config = Config::fromJSON($_POST['jsonOld']);			
				$config->uiMode = $_POST['uiMode'];
				if($config->uiMode == '') $config->uiMode = null;
					
				if(!is_null($config->items))
				{
					if(!is_null($config->items))
					{
						for($i=0; $i<count($config->items); $i++)
						{
							$item = $config->items[$i];
	
							$item->productId = $_POST['productId_'.$i];
							if($item->productId == '') $item->productId = null;
							
							$item->templateId = $_POST['templateId_'.$i];
							if($item->templateId == '') $item->templateId = null;
							
							$item->templateCategoryId = $_POST['templateCategoryId_'.$i];
							if($item->templateCategoryId == '') $item->templateCategoryId = null;
						}
					}
				}
				$barcode->setConfig($config);
			}
		}
	}

	
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
	else if(isset($_POST['action_add']))
	{
		$config = $barcode->getConfig();
		if(is_null($config->items)) $config->items = array();
		$config->items[] = new ConfigItem();
		$barcode->setConfig($config);
	}
	
	$config = $barcode->getConfig();
	if(!is_null($config->items))
	{
		for($i=0; $i<count($config->items); $i++)
		{
			if(isset($_POST['action_remove_'.$i]))
			{
				array_splice($config->items, $i, 1);
				if(count($config->items) == 0) $config->items = null;
				$barcode->setConfig($config);
				break;
			}
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
