<?php
	include_once "_common.php";

	$actionLog = "";
	
	function writeEditor($product)
	{		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>code:</td><td>";
		echo '<input name="code" type="text" value="'.htmlspecialchars($product->code).'">';
		echo "</td></tr><tr><td>width:</td><td>";
		echo '<input name="width" type="text" value="'.$product->width.'">';
		echo "</td></tr><tr><td>height:</td><td>";
		echo '<input name="height" type="text" value="'.$product->height.'">';
		echo "</td></tr><tr><td>long name:</td><td>";
		echo '<input name="longName" type="text" size="50" value="'.htmlspecialchars($product->longName).'">';
		echo "</td></tr><tr><td>Category ID:</td><td>";
		echo '<input name="categoryId" type="text" value="'.$product->categoryId.'">';
		echo "</td></tr><tr><td>Graphics</td><td>";
		echo '<input name="allowGraphics" type="text" value="'.$product->allowGraphics.'">';
		echo "</td></tr><tr><td>Shape ID:</td><td>";
		echo '<input name="shapeId" type="text" value="'.htmlspecialchars($product->shapeId).'">';
		echo "</td></tr><tr><td>frameWidth:</td><td>";
		echo '<input name="frameWidth" type="text" value="'.$product->frameWidth.'">';
		echo "</td></tr><tr><td>frameHeight:</td><td>";
		echo '<input name="frameHeight" type="text" value="'.$product->frameHeight.'">';
		echo "</td></tr><tr><td>productTypeId:</td><td>";
		echo '<input name="productTypeId" type="text" value="'.$product->productTypeId.'">';
		echo "</td></tr><tr><td>colorModel:</td><td>";
		echo '<input name="colorModel" type="text" size="120" value="'. htmlspecialchars($product->colorModel) .'">';
		echo "</td></tr><tr><td>configJSON:</td><td>";
		echo '<input name="configJSON" type="text" size="120" value="'. htmlspecialchars($product->configJSON) .'">';
		echo "</td></tr>";
		echo "</table>";
		echo '<input name="action_save" type="submit" value="Save">';
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
		if ($id != -1)
			$actionLog = $actionLog."Loading product #".$id."<br>";
	}
	else
	{
		$id = -1;
	}
	
	if($id != -1)
	{
		$product = $_order_db->getProductById($id);
	}
	else
	{
		$product = new Product();
		$actionLog = $actionLog."Starting new product<br>";
	}
	
	
	$product->id = $id;
	
	if(isset($_POST['code'])) $product->code = $_POST['code'];
	if(isset($_POST['width'])) $product->width = $_POST['width'];
	if(isset($_POST['height'])) $product->height = $_POST['height'];
	if(isset($_POST['longName'])) $product->longName = $_POST['longName'];
	if(isset($_POST['categoryId'])) $product->categoryId = $_POST['categoryId'];
	if(isset($_POST['allowGraphics'])) $product->allowGraphics = $_POST['allowGraphics'];
	if(isset($_POST['shapeId'])) $product->shapeId = $_POST['shapeId'];
	if(isset($_POST['frameWidth'])) $product->frameWidth = $_POST['frameWidth'];
	if(isset($_POST['frameHeight'])) $product->frameHeight = $_POST['frameHeight'];
	if(isset($_POST['productTypeId'])) $product->productTypeId = $_POST['productTypeId'];
	if(isset($_POST['colorModel'])) $product->colorModel = $_POST['colorModel'];
	if(isset($_POST['configJSON'])) $product->configJSON = $_POST['configJSON'];
	
	function createProduct($product)
	{
		global $_order_db;
		global $actionLog;
		
			
		if($_order_db->createProduct($product))
		{
			$actionLog = $actionLog."Adding<br>";
		}
		else
		{
			$actionLog = $actionLog."Error Adding.<br>";
		}
	}
	
	function updateProduct($product)
	{
		global $_order_db;
		global $actionLog;
		
		if($_order_db->updateProduct($product))
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
		if($product->id < 0)
		{
			createProduct($product);
		}
		else
		{
			updateProduct($product);
		}			
	}
		
	include_once 'preamble.php';
?>
<h1>Product ( Edit )</h1>
<h3><?php echo $actionLog; ?></h3>
<?php writeEditor($product); ?>
<br><br>
<a href="product_edit.php?id=-1">New Product</a>
<?php include_once 'postamble.php';?>
