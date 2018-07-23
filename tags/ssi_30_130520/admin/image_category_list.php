<?php 
	include_once "_common.php";
	
	function writeTableHeader($text, $sortIndex, $selectedSortIndex)
	{
		if($sortIndex == abs($selectedSortIndex))
		{
			if($selectedSortIndex > 0)
			{
				echo '<td> <a href="image_category_list.php?orderBy='.(-$selectedSortIndex).'">'.htmlspecialchars($text).' (V)</a> </td>';
			}
			else
			{
				echo '<td> <a href="image_category_list.php?orderBy='.(-$selectedSortIndex).'">'.htmlspecialchars($text).' (^)</a> </td>';
			}
		}
		else
		{
			$sortIndex = -$sortIndex;
			echo '<td> <a href="image_category_list.php?orderBy='.abs($sortIndex).'">'.htmlspecialchars($text).'</a> </td>';
		}
	}
	
	function writeCategoryList()
	{
		global $_system;
	
		$result = mysql_query("SELECT COUNT(*) as count FROM image_categories",$_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		$row = mysql_fetch_assoc($result);
		$totalEntries = $row['count'];
			
		$pageSize = 25;
		$pageCount = intval($totalEntries / $pageSize);
	
		if($totalEntries % $pageSize > 0) $pageCount++;
			
		$pageIndex = isset($_GET['page']) ? intval($_GET['page']) : 0;
		if($pageCount < $pageIndex) $pageIndex = $pageCount-1;
		if($pageIndex < 0) $pageIndex = 0;
		$pageStart = $pageIndex * $pageSize;
	
		$query =	'SELECT * from image_categories ';
	
		$orderBy = isset($_GET['orderBy']) ? intval($_GET['orderBy']) : 1;
		$orderType = $orderBy < 0 ? 'DESC ' : 'ASC ';
	
		switch(abs($orderBy))
		{
			case 2:
				$query .= 'ORDER BY name '.$orderType.', id ASC ';
				break;
	
			case 3:
				$query .= 'ORDER BY type '.$orderType.', id ASC ';
				break;
			
			default:
				$orderBy = 1;
			case 1:
				$query .= 'ORDER BY id '.$orderType;
		}
			
		$query .= 'LIMIT '.$pageStart.', '.$pageSize;
		
		$result = mysql_query($query,$_system->db->connection);
	
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
	
	
		echo '<table border="1">';
	
		echo '<tr>';
		writeTableHeader('Id', 1, $orderBy);
		writeTableHeader('Name', 2, $orderBy);
		writeTableHeader('Type', 3, $orderBy);
	
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';
	
		while($row = mysql_fetch_assoc($result))
		{
			$id = $row['id'];
	
			echo '<tr><td>';
			echo $id;
			echo '</td><td>';
			echo htmlspecialchars($row['name']);
			echo '</td><td>';
			echo htmlspecialchars($row['type']);
			echo '</td><td>';
			echo '<a href="image_category_edit.php?id='.$id.'">Edit</a>';
			echo '</td><td>';
			echo '<a href="image_category_list.php?page='.$pageIndex.'&delete_id='.$id.'" onclick="return deletePrompt('.$id.');">Delete</a>';
			echo '</td><tr>';
		}
		echo '</table>';
	
		if($pageCount > 0)
		{
			if($pageIndex > 0)
			{
				echo ' <a href="image_category_list.php?orderBy='.$orderBy.'&page='.($pageIndex - 1).'">&lt;&lt;&lt;</a> ';
			}
			else
			{
				echo ' &lt;&lt;&lt; ';
			}
				
				
			for($i=0; $i<$pageCount; $i++)
			{
				if($i != $pageIndex)
				{
					echo ' <a href="image_category_list.php?orderBy='.$orderBy.'&page='.$i.'">'.$i.'</a> ';
				}
				else
				{
					echo ' ['.$i.'] ';
				}
			}
	
			$pageIndex = isset($_GET['page']) ? $_GET['page'] : 0;
	
			if($pageIndex < $pageCount - 1)
			{
				echo '<a href="image_category_list.php?orderBy='.$orderBy.'&page='.($pageIndex + 1).'"> &gt;&gt;&gt; </a>';
			}
			else
			{
	 			echo '&gt;&gt;&gt;';
			}
		}
	
		return true;
	}
	
	if(isset($_GET['delete_id']))
	{
		$_image_db->deleteImageCategory($_GET['delete_id']);
	}
	/*
	function writeCategory($category)
	{
		echo "<tr><td>";
		echo $category->id;
		echo "</td><td>";
		echo htmlspecialchars($category->name);
		echo "</td><td>";
		echo "<a href='template_category_edit.php?id=".$category->id."'>Edit</a>";
		echo "</td><td>";
		echo "<a href='template_category_list.php?delete_id=".$category->id."' onclick='return deletePrompt(".$category->id.");'>Delete</a>";
		echo "</td><tr>";
	}
	
	function writeCategoryList()
	{	
		global $_image_db;
				
		echo "<table border='1'>";
		echo "<tr>";
		echo "<td> id </td>";
		echo "<td> name </td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";
		
		$categories = $_image_db->getDesignTemplateCategories();
		for($i=0; $i<count($categories); $i++)
		{
			writeCategory($categories[$i]);
		}
		echo "</table>";
	}
	*/
	include_once 'preamble.php';
?>
<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete product #' + id + ' from the database ?');
	}
</script>
<h1>Image Category ( List )</h1>
<?php writeCategoryList(); ?>
<br><br>
<a href="image_category_edit.php?id=-1">New Image Category</a>
<?php include_once 'postamble.php';?>
