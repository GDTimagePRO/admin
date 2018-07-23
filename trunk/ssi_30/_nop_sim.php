<html>
<body>
<?php 
	//OrderId=".$designImageId
	if(isset($_GET['OrderId']))
	{
		echo '<img src="design_part/get_image.php?nocache=true&id='.$_GET['OrderId'].'">';
	}
?>
	<a href="SetUp.php?code=WOOT&emailUs=ValtchanV@GMail.com&sName=test_site&url=_nop_sim.php">Go (Local : WOOT VV)</a><br>
	<a href="SetUp.php?code=TR-1001&emailUs=ValtchanV@GMail.com&sName=test_site&url=_nop_sim.php">Go (Local : TR-1001 VV)</a><br>
	<a href="SetUp.php?code=TR-1001&emailUs=Q@G.com&sName=test_site&url=_nop_sim.php">Go (Local : TR-1001 QQ)</a><br>
	
	<br><br><br>
	<a href="SetUp.php?emailUrl=cs@stampsignsbadges.com&code=AD-1023&emailUs=cs@stampsignsbadges.com&sName=Mason%20Row&url=http://masonrow.in-stamp.com/address-stamps-10">Go (MR)</a>
</body>
</html>