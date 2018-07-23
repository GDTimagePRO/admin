<html>
<body>
<?php 
	require_once 'backend/settings.php';
	require_once 'backend/db_order.php';
	//OrderId=".$designImageId
	if(isset($_GET['orderDetails']))
	{
		echo 'Order Details = ' . htmlentities($_GET['orderDetails']) . '<br><br>'; 
	}
	
	if(isset($_GET['OrderId']))
	{
		echo 'Preview Image Id = ' . $_GET['OrderId'] . '<br>'; 
		echo '<img src="' . Settings::getImageUrl($_GET['OrderId'], true) . '"><br>';
	}	
?>
	<a href="SetUp.php?sName=<?php echo urlencode(Customer::KEY_INTERNAL); ?>&url=_nop_sim.php&code=WOOT">Go (Local : WOOT)</a><br>
	<a href="SetUp.php?sName=<?php echo urlencode(Customer::KEY_INTERNAL); ?>&url=_nop_sim.php&code=TR-1001">Go (Local : TR-1001)</a><br>
	<a href="SetUp.php?sName=<?php echo urlencode(Customer::KEY_INTERNAL); ?>&url=_nop_sim.php&code=TR-1002">Go (Local : TR-1002)</a><br>
	<br><br><br>
</body>
</html>