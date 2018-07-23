<?php 
	include_once "_common.php";
	
	function writeTableHeader($text, $sortIndex, $selectedSortIndex)
	{
		if($sortIndex == abs($selectedSortIndex))
		{
			if($selectedSortIndex > 0)
			{
				echo '<td> <a href="template_list.php?orderBy='.(-$selectedSortIndex).'">'.htmlspecialchars($text).' (V)</a> </td>';
			}
			else
			{
				echo '<td> <a href="template_list.php?orderBy='.(-$selectedSortIndex).'">'.htmlspecialchars($text).' (^)</a> </td>';
			}
		}
		else
		{
			$sortIndex = -$sortIndex;
			echo '<td> <a href="template_list.php?orderBy='.abs($sortIndex).'">'.htmlspecialchars($text).'</a> </td>';
		}
	}
	
 	function writeTemplateList() 	
 	{
 		global $_system;
 		 		
 		$result = mysql_query("SELECT COUNT(*) as count FROM design_templates",$_system->db->connection);
 		if(!$result)
 		{
 			if(DesignDB::DEBUG) echo mysql_error();
 			return false;
 		}
 		$row = mysql_fetch_assoc($result);
 		$totalEntries = $row['count'];
 		
 		$pageSize = 6;
 		$pageCount = intval($totalEntries / $pageSize);

 		if($totalEntries % $pageSize > 0) $pageCount++;
 		
 		$pageIndex = isset($_GET['page']) ? intval($_GET['page']) : 0; 		
 		if($pageCount < $pageIndex) $pageIndex = $pageCount-1;		
 		if($pageIndex < 0) $pageIndex = 0;
 		$pageStart = $pageIndex * $pageSize;
 			
 		$query =	'SELECT dt.id, dt.name, dt.category_id, dtc.name as category_name, dt.product_type_id, ddt.description as product_type_name '.
 					'FROM design_templates AS dt '.
 					'LEFT JOIN (design_template_categories as dtc, default_design_templates as ddt) ON '.
 					'(dt.category_id = dtc.id) AND (dt.product_type_id = ddt.product_type_id) ';

 		$orderBy = isset($_GET['orderBy']) ? intval($_GET['orderBy']) : 1;
 		$orderType = $orderBy < 0 ? 'DESC ' : 'ASC ';
 		 
 		switch(abs($orderBy))
 		{
 			case 2:
 				$query .= 'ORDER BY dt.name '.$orderType.', dt.id ASC ';
 				break;
 				 						
 			case 3:
 				$query .= 'ORDER BY dtc.name '.$orderType.', dt.id ASC ';
 				break;
 								
 			case 4:
 				$query .= 'ORDER BY ddt.description '.$orderType.', dt.id ASC ';
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

 		if($pageCount > 0)
 		{
 			if($pageIndex > 0)
 			{
 				echo ' <a href="template_list.php?orderBy='.$orderBy.'&page='.($pageIndex - 1).'">&lt;&lt;&lt;</a> ';
 			}
 			else
 			{
 				echo ' &lt;&lt;&lt; ';
 			}
 				
 				
 			for($i=0; $i<$pageCount; $i++)
 			{
 				if($i != $pageIndex)
 				{
 					echo ' <a href="template_list.php?orderBy='.$orderBy.'&page='.$i.'">'.$i.'</a> ';
 				}
 				else
 				{
 					echo ' ['.$i.'] ';
 				}
 			}
 				
 			$pageIndex = isset($_GET['page']) ? $_GET['page'] : 0;
 			$pageStart = 0;
 			$pageSize = 5;
 				
 			if($pageIndex < $pageCount - 1)
 			{
 				echo '<a href="template_list.php?orderBy='.$orderBy.'&page='.($pageIndex + 1).'"> &gt;&gt;&gt; </a>';
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
		writeTableHeader('Type', 4, $orderBy);
		
		echo '<td> Image </td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';
		
		while($row = mysql_fetch_assoc($result))
 		{
 			$id = $row['id'];

 			$category_name = $row['category_name'];
			if(is_null($category_name)) $category_name = "Undefined : ".$row['category_id'];
			 
			$product_type_name = $row['product_type_name'];
			if(is_null($product_type_name)) $product_type_name = "Undefined : ".$row['product_type_id'];
			
			$preview_image_id = DesignTemplate::previewImageId($id);

			echo '<tr><td>';
			echo $id;
			echo '</td><td>';
			echo htmlspecialchars($row['name']);
			echo '</td><td>';
			echo htmlspecialchars($category_name);
			echo '</td><td>';
			echo htmlspecialchars($product_type_name);
			echo '</td><td>';
			echo '<img style="margin:2px; border-width:1px;border-style:solid;" src="../design_part/get_image.php?id='.ImageDB::TYPE_THUMBNAIL.'.'.$preview_image_id.'">';		
			echo '</td><td>';
			echo '<a href="template_edit.php?id='.$id.'">Edit</a>';		
			echo '</td><td>';
			echo '<a href="template_list.php?page='.$pageIndex.'&delete_id='.$id.'" onclick="return deletePrompt('.$id.');">Delete</a>';				
			echo '</td><tr>';
		}		
		echo '</table>';		
		
		if($pageCount > 0)
		{
			if($pageIndex > 0)
			{
				echo ' <a href="template_list.php?orderBy='.$orderBy.'&page='.($pageIndex - 1).'">&lt;&lt;&lt;</a> ';
			}
			else
			{
				echo ' &lt;&lt;&lt; ';
			}
			
			
	 		for($i=0; $i<$pageCount; $i++)
	 		{
	 			if($i != $pageIndex)
	 			{
	 				echo ' <a href="template_list.php?orderBy='.$orderBy.'&page='.$i.'">'.$i.'</a> ';
	 			}
	 			else
	 			{
	 				echo ' ['.$i.'] ';
	 			}
	 		}
	 		
			$pageIndex = isset($_GET['page']) ? $_GET['page'] : 0; 		
	 		$pageStart = 0;
	 		$pageSize = 5;
	 		
	 		if($pageIndex < $pageCount - 1)
	 		{
	 			echo '<a href="template_list.php?orderBy='.$orderBy.'&page='.($pageIndex + 1).'"> &gt;&gt;&gt; </a>';
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
		$template = $_design_db->getDesignTemplateById($_GET['delete_id']);
		if(!is_null($template))
		{
			if($template->previewImageId >= 0)
			{
				$_image_db->deleteImage($template->previewImageId);
			}
			$_design_db->deleteDesignTemplate($template->id);
		}
	}
	
	include_once 'preamble.php';
?>
<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete template #' + id + ' from the database ?');
	}
</script>
<h1>Template ( List )</h1>
<?php writeTemplateList(); ?>
<br><br>
<a href="template_edit.php?id=-1">New Template</a>
<?php include_once 'postamble.php';?>
