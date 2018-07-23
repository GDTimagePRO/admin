<?php
	include_once "_common.php";
	$searchResult = "";
	
	if (!isset($searched))
		$searched = "";
		
	function search($searched) {
	
		echo "<form method='POST'>";
		echo '<input name="searchTxt" type="text" value="'.$searched.'">';
		echo "<button>search</button>";
		echo "</form>";	
	}
	
	
	if(isset($_POST['searchTxt']))
	{
		$searched = $_POST['searchTxt'];
		$searchResult = "Search Result for \"".$_POST['searchTxt']."\"<br />";
		$result = $_image_db->searchKeyword($_POST['searchTxt']);
		
		if (!$result)
		{
			$searchResult = $searchResult."Not found, please try again";
		}
		else
		{
			foreach ($result as $item)
			{
				foreach ($item as $image_id)
				{
					$searchResult = $searchResult.$image_id;
					$searchResult = $searchResult."<br />";
				}
			}
		}
			
		
	}
?>
<html>
<head>
</head>
<body>
<?php search($searched)?>
<?php echo $searchResult; ?>
</body>
</html>