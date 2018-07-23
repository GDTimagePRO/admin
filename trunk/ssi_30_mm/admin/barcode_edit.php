<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor(Barcode $barcode)
	{		
		global $_system;
		global $_design_db;
		global $_order_db;
		
		$productList = $_order_db->getProductList();
		$categoryList = $_design_db->getTemplateCategoryList();
		$templateList = $_design_db->getTemplateList();
		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr>";
		
		echo "<td>barcode:</td>";
		echo "<td>";
		echo '<input name="oldBarcode" type="hidden" value="'.htmlspecialchars($barcode->barcode).'">';
		echo '<input name="barcode" type="text" value="'.htmlspecialchars($barcode->barcode).'">';		
		echo '</td>';

		echo "</tr><tr>";
		
		echo "<td>Customer:</td>";
		echo "<td>";
		echo '<input name="oldCustomerId" type="hidden" value="'.htmlspecialchars($barcode->customerId).'">';
		echo '<select name="customerId">';
		
		$result = mysql_query("SELECT id, description FROM Customers ORDER BY id" , $_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
			
		while($row = mysql_fetch_assoc($result))
		{
			echo sprintf(
					'<option value="%d" %s >%s</option>',
					$row['id'],
					($row['id'] == $barcode->customerId) ? 'selected' : '',
					$row['description']
			);
		}
		
		echo '</select>';
		echo '</td>';

		echo "</tr><tr>";
		
		echo "<td>master:</td>";
		echo "<td>";
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
		echo '</td>';

		echo "</tr><tr>";
		
		echo "<td>date created:</td>";
		echo "<td>";
		echo '<input name="dateCreated" type="text" value="'.date("Y-m-d H:i:s",$barcode->dateCreated).'">';		
		echo '</td>';
		
		echo "</tr><tr>";
		
		echo "<td>date used:</td>";
		echo "<td>";
		echo '<input name="dateUsed" type="text" value="'.htmlspecialchars($barcode->dateUsed).'">';
		echo '</td>';
		
		echo "</tr><tr>";
		
		echo "<td>json:</td>";
		echo "<td>";
		echo '<input name="json" type="text" value="'.htmlspecialchars($barcode->configJSON).'">';
		echo '<input name="jsonOld" type="hidden" value="'.htmlspecialchars($barcode->configJSON).'">';
		echo '</td>';
		
		echo "</tr><tr>";
		
		$config = Config::fromJSON($barcode->configJSON);
		echo '<td colspan="2">options: -------------------------</td>';		

		echo "</tr><tr>";
		
		echo "<td>gui type:</td>";
		echo "<td>";
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
		echo '</td>';

		echo "</tr><tr>";
		
		echo "<td>theme:</td>";
		echo "<td>";
		echo '<input name="theme" type="text" value="'.htmlspecialchars($config->theme).'">';
		echo '</td>';
		
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
				

				// ------ Template category ---------------
				
				echo '<td><input type="text" name="templateCategoryId_'.$i.'" value="' . htmlspecialchars($item->templateCategoryId) . '"><select name="templateCategoryId_'.$i.'_select">';
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
		$oldBar= $_POST['oldBarcode'];
		$oldCustomerId = $_POST['oldCustomerId'];
		$bar = $_POST['barcode'];
		$customerId = $_POST['customerId'];
	}
	else if(isset($_GET['barcode']) && isset($_GET['customerId']))
	{
		$oldBar = $bar = $_GET['barcode'];
		$oldCustomerId = $customerId = $_GET['customerId'];
		if($bar != "" ) $actionLog = $actionLog."Loading barcode #".$bar." for customer #" . $customerId . "<br><br>";
	}
	else
	{
		$oldBar = $bar = "";
		$oldCustomerId = $customerId = -1;
	}

	$barcode = NULL;
	if(($oldBar != "") && ($oldCustomerId != ""))
	{
		$barcode = $_order_db->getBarcodeByBarcode($oldCustomerId, $oldBar);
	}
	
	if(is_null($barcode))
	{
		$barcode = new Barcode();
		$barcode->barcode = $bar;
		$barcode->customerId = $oldCustomerId; 
		$barcode->dateCreated = date("Y-m-d H:i:s", time());
		$actionLog = $actionLog."Starting new barcode<br>";		
	}
	
	if(!isset($_POST['action_reset']))
	{
		if(isset($_POST['barcode'])) $barcode->barcode = $_POST['barcode'];
		if(isset($_POST['master'])) $barcode->master = $_POST['master'];
		if(isset($_POST['customerId'])) $barcode->customerId = $_POST['customerId'];
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
				$config->theme = $_POST['theme'];
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
							
							$item->templateCategoryId = trim($_POST['templateCategoryId_'.$i]);
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
		if(
				is_null($_order_db->getBarcodeByBarcode($barcode->customerId, $barcode->barcode)) ||
				($oldBar != $bar) ||
				($oldCustomerId != $customerId) )
		{
			$_order_db->deleteBarcode($oldCustomerId, $oldBar);
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
