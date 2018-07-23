<?php 
	include_once "_common.php";
	
	if(isset($_GET['delete_id']))
	{
		$product = $_order_db->getProductById($_GET['delete_id']);
		if(!is_null($product))
		{
			$_order_db->deleteProduct($product->id);
		}
	}
	
	function writeProduct($product)
	{
		echo "<tr><td>";
		echo $product->id;
		echo "</td><td>";
		echo htmlspecialchars($product->code);
		echo "</td><td>";
		echo $product->width;
		echo "</td><td>";
		echo $product->frameWidth;
		echo "</td><td>";
		echo $product->height;
		echo "</td><td>";
		echo $product->frameHeight;
		echo "</td><td>";
		echo htmlspecialchars($product->longName);
		echo "</td><td>";
		echo $product->categoryId;
		echo "</td><td>";
		echo $product->allowGraphics;
		echo "</td><td>";
		echo htmlspecialchars($product->shapeId);
		echo "</td><td>";
		echo $product->productTypeId;
		echo "</td><td>";
		echo "<a href='product_edit.php?id=".$product->id."'>Edit</a>";		
		echo "</td><td>";
		echo "<a href='product_list.php?delete_id=".$product->id."' onclick='return deletePrompt(".$product->id.");'>Delete</a>";				
		echo "</td><tr>";
	}
	
	function writeProductList()
	{	
		global $_order_db;
		
		$productList = $_order_db->getProducts();
		echo "<table border='1'>";

		echo "<tr>";
		echo "<td> id </td>";
		echo "<td> Code </td>";
		echo "<td> Width </td>";
		echo "<td> frameWidth </td>";
		echo "<td> Height </td>";
		echo "<td> frameHeight </td>";
		echo "<td> Long Name </td>";
		echo "<td> Category Id </td>";
		echo "<td> Graphics </td>";
		echo "<td> Shape Id </td>";
		echo "<td> productTypeId </td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";
		
		for($i=0; $i<count($productList); $i++)
		{
			writeProduct($productList[$i]);
		}
		echo "</table>";
	}
	
	include_once 'preamble.php';
?>
<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete product #' + id + ' from the database ?');
	}
</script>
<h1>Products ( List )</h1>
<?php writeProductList(); ?>
<br><br>
<a href="product_edit.php?id=-1">New Product</a>
<?php include_once 'postamble.php';?>
