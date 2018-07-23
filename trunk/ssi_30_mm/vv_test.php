<!DOCTYPE html>
<html>
<head>
<script src="js/lib/jquery-1.8.0.min.js"></script>
</head>
<body>

<?php if(isset($_GET["frame"])) { ?>

This is the frame
<script type="text/javascript">
	var TI = {
		showMessage : function(msg)
		{
			alert("The message is : " + msg);
		}
	};

</script>


<?php } else { ?>

<script type="text/javascript">
	function doTest()
	{
		$("#frame")[0].contentWindow.TI.showMessage("Please work");
	}
</script>

This is the head<br>
<iframe id="frame" src="vv_test.php?frame=true"></iframe>
<button onclick="doTest()">Hello</button>

<?php }?>

</body>
</html>
