<html>
<body>
<?php 
	//OrderId=".$designImageId
	if(isset($_GET['OrderId']))
	{
		echo '<img src="design_part/get_image.php?nocache=true&id='.$_GET['OrderId'].'">';
	}
	
?>
	<a href="SetUp.php?code=WOOT&emailUs=ValtchanV@GMail.com&sName=test_site&url=_nop_sim.php">Go</a><br>
	<a href="SetUp.php?emailUrl=cs@stampsignsbadges.com&code=AD-1023&emailUs=cs@stampsignsbadges.com&sName=Mason%20Row&url=http://masonrow.in-stamp.com/address-stamps-10">Go</a>
</body>
</html>