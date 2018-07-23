<?php 
	include_once "_common.php";
	
	
	$list = Array('All', 'User', 'Design', 'Template', 'Public');
	
	if(!empty($_POST['select']))
	{
		$selected = $_POST['select'];
	}
	else if (isset($_GET['select']))
	{
		$selected = $_GET['select'];
	}
	else
	{
		$selected = 5;
	}
	
	
	function writeTableHeader($text, $sortIndex, $selectedSortIndex)
	{
		global $selected;
		if($sortIndex == abs($selectedSortIndex))
		{
			if($selectedSortIndex > 0)
			{
				echo '<td> <a href="image_list.php?orderBy='.(-$selectedSortIndex).'&select='.$selected.'">'.htmlspecialchars($text).' (V)</a> </td>';
			}
			else
			{
				echo '<td> <a href="image_list.php?orderBy='.(-$selectedSortIndex).'&select='.$selected.'">'.htmlspecialchars($text).' (^)</a> </td>';
			}
		}
		else
		{
			$sortIndex = -$sortIndex;
			echo '<td> <a href="image_list.php?orderBy='.abs($sortIndex).'&select='.$selected.'">'.htmlspecialchars($text).'</a> </td>';
		}
	}
	
	function writeImageList($selected)
	{
		global $_system;
	
		switch($selected)
		{
			case 1: $filter="";
					break;
			case 2: $filter="where img.category_id = 1 ";
					break;
			case 3: $filter="where img.category_id = 2 ";
					break;
			case 4: $filter="where img.category_id = 3 ";
					break;
			case 5: $filter="where img.category_id >= 100 ";
					break;
		}
		
		$result = mysql_query("SELECT COUNT(*) as count FROM images AS img ".$filter,$_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		$row = mysql_fetch_assoc($result);
		$totalEntries = $row['count'];
			
		$pageSize = 6;
		$pageCount = intval($totalEntries / $pageSize);
	
		$pageIndex = isset($_GET['page']) ? intval($_GET['page']) : 0;
		if($pageCount < $pageIndex) $pageIndex = $pageCount-1;
		if($pageIndex < 0) $pageIndex = 0;
		$pageStart = $pageIndex * $pageSize;
		
	
			
		if($totalEntries % $pageSize > 0) $pageCount++;
	
		$query =	'SELECT img.id, img.category_id, img.name, img.user_id, ic.name as cname, users.name as uname, users.id as uid '.
 					'FROM images AS img '.
					'LEFT JOIN image_categories as ic ON img.category_id = ic.id '.
					'LEFT JOIN users ON img.user_id = users.id '.$filter;
					
				
		$orderBy = isset($_GET['orderBy']) ? intval($_GET['orderBy']) : 1;
		$orderType = $orderBy < 0 ? 'DESC ' : 'ASC ';
	
		switch(abs($orderBy))
		{
			case 2:
				$query .= 'ORDER BY img.name '.$orderType.', id ASC ';
				break;
	
			case 3:
				$query .= 'ORDER BY cname '.$orderType.', img.category_id, id ASC ';
				break;
					
			case 4:
				$query .= 'ORDER BY uname '.$orderType.', user_id, id ASC ';
				break;

				
			default:
				$orderBy = 1;
			case 1:
				$query .= 'ORDER BY id '.$orderType.', img.category_id, user_id ASC ';
		}
			
		$query .= 'LIMIT '.$pageStart.', '.$pageSize;
	
		$result = mysql_query($query,$_system->db->connection);
	
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}

		echo $totalEntries." results<br><br>";
		
		if($pageCount > 0)
		{
			if($pageIndex > 0)
			{
				echo ' <a href="image_list.php?orderBy='.$orderBy.'&page='.($pageIndex - 1).'&select='.$selected.'">&lt;&lt;&lt;</a> ';
			}
			else
			{
				echo ' &lt;&lt;&lt; ';
			}


			for($i=0; $i<$pageCount; $i++)
			{
				if($i != $pageIndex)
				{
					echo ' <a href="image_list.php?orderBy='.$orderBy.'&page='.$i.'&select='.$selected.'">'.$i.'</a> ';
				}
				else
				{
					echo ' ['.$i.'] ';
				}
			}

			//$pageIndex = isset($_GET['page']) ? $_GET['page'] : 0;
			//$pageStart = 0;

			if($pageIndex < $pageCount - 1)
			{
				echo '<a href="image_list.php?orderBy='.$orderBy.'&page='.($pageIndex + 1).'&select='.$selected.'"> &gt;&gt;&gt; </a>';
			}
			else
			{
				echo '&gt;&gt;&gt;';
			}
		}

		echo '<table border="1">';
	
		echo '<tr>';
		writeTableHeader('Id', 1, $orderBy);
		writeTableHeader('Name', 2, $orderBy);
		writeTableHeader('Category', 3, $orderBy);
		writeTableHeader('User', 4, $orderBy);
	
		echo '<td> Image </td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';
	
		while($row = mysql_fetch_assoc($result))
		{
			$id = $row['id'];
			
			if($row['category_id'] == 1)
			{
				$categoryName = "[ User ]";
			}
			else if($row['category_id'] == 2)
			{
				$categoryName = "[ Design ]";
			}
			else if($row['category_id'] == 3)
			{
				$categoryName = "[ Template ]";
			}
			else
			{
				$categoryName = $row['cname'];
				if(is_null($categoryName)) $categoryName = "[Undefined : ".$row['category_id']."]";
			}			
			
	
			$userId = $row['user_id'];
						
			$userName = $row['uname'];
			if(is_null($userName) && $userId = -1)
			{
				$userName = "[Public]";
			}

			echo '<tr><td>';
			echo $id;
			echo '</td><td>';
			echo htmlspecialchars($row['name']);
			echo '</td><td>';
			echo htmlspecialchars($categoryName);
			echo '</td><td>';
			if ($userId != -1)
			{
				echo "[" . $userId . "] " . htmlspecialchars($userName);
			}
			else
			{
				echo htmlspecialchars($userName);
			} 
			echo '</td><td>';
			echo '<a href="get_image.php?id='.$id.'"><img style="margin:2px; border-width:1px;border-style:solid;" src="get_image.php?thumbnail=true&id='.$id.'"></a>';
			echo '</td><td>';
			echo '<a href="image_edit.php?id='.$id.'">Edit</a>';
			echo '</td><td>';
			echo '<a href="image_list.php?page='.$pageIndex.'&delete_id='.$id.'&select='.$selected.'" onclick="return deletePrompt('.$id.');">Delete</a>';
			echo '</td><tr>';
		}
		echo '</table>';
	
		if($pageCount > 0)
		{
			if($pageIndex > 0)
			{
				echo ' <a href="image_list.php?orderBy='.$orderBy.'&page='.($pageIndex - 1).'&select='.$selected.'">&lt;&lt;&lt;</a> ';
			}
			else
			{
				echo ' &lt;&lt;&lt; ';
			}
				
				
			for($i=0; $i<$pageCount; $i++)
			{
				if($i != $pageIndex)
				{
					echo ' <a href="image_list.php?orderBy='.$orderBy.'&page='.$i.'&select='.$selected.'">'.$i.'</a> ';
				}
				else
				{
					echo ' ['.$i.'] ';
				}
			}
	
			//$pageIndex = isset($_GET['page']) ? $_GET['page'] : 0;
			//$pageStart = 0;
		
			if($pageIndex < $pageCount - 1)
			{
				echo '<a href="image_list.php?orderBy='.$orderBy.'&page='.($pageIndex + 1).'&select='.$selected.'"> &gt;&gt;&gt; </a>';
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
		$_image_db->deleteImage($_GET['delete_id']);
	}
	
	include_once 'preamble.php';
?>


<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete image #' + id + ' from the database ?');
	}
	function updateSelect(index)
	{
		document.getElementById("display").submit();
	}
</script>
<h1>Image ( List )</h1>


<form id="display" action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'> 
	Display: 
	<select name="select" onChange="updateSelect(this.options[selectedIndex].value);">
		<?php 
			$index = 1;
			foreach($list as $item)
			{
				echo $index;
				echo '<option value="'.$index.'" ';
				if ($index == $selected)
				{
					echo 'selected';
				}
				echo '>'.$item.'</option>';
				$index++;
			}
			
		?>
	</select>
</form>
<?php writeImageList($selected); ?>
<br><br>
<a href="image_edit.php?id=-1">New Image</a>
<?php include_once 'postamble.php';?>
