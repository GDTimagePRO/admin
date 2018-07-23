<?php 
	include_once "_common.php";
	
	function writeTableHeader($text, $sortIndex, $selectedSortIndex)
	{
		if($sortIndex == abs($selectedSortIndex))
		{
			if($selectedSortIndex > 0)
			{
				echo '<td> <a href="barcode_list.php?orderBy='.(-$selectedSortIndex).'">'.htmlspecialchars($text).' (V)</a> </td>';
			}
			else
			{
				echo '<td> <a href="barcode_list.php?orderBy='.(-$selectedSortIndex).'">'.htmlspecialchars($text).' (^)</a> </td>';
			}
		}
		else
		{
			$sortIndex = -$sortIndex;
			echo '<td> <a href="barcode_list.php?orderBy='.abs($sortIndex).'">'.htmlspecialchars($text).'</a> </td>';
		}
	}
	
	function writeBarcodeList()
	{
		global $_system;
	
		$result = mysql_query("SELECT COUNT(*) as count FROM barcodes",$_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		$row = mysql_fetch_assoc($result);
		$totalEntries = $row['count'];
		$index = 0;

		$pageSize = 20;
		$pageCount = intval($totalEntries / $pageSize);
		$pageIndex = isset($_GET['page']) ? intval($_GET['page']) : 0;
		if($pageCount < $pageIndex) $pageIndex = $pageCount-1;
		if($pageIndex < 0) $pageIndex = 0;
		$pageStart = $pageIndex * $pageSize;
	
			
		if($totalEntries % $pageSize > 0) $pageCount++;

		$query =	'SELECT * FROM barcodes ';
		
// 		$query =	'SELECT barcode, product_id, date_created, master, date_used, template_category_id, template_id, p.code as pcode, dt.name as dtn, dtc.name as dtcn '.
// 					'FROM barcodes '.
// 					'LEFT JOIN products p ON product_id = p.id '.
// 					'LEFT JOIN design_template_categories dtc ON template_category_id = dtc.id '.
// 					'LEFT JOIN design_templates dt ON template_id = dt.id ';
	
		$orderBy = isset($_GET['orderBy']) ? intval($_GET['orderBy']) : 1;
		$orderType = $orderBy < 0 ? 'DESC ' : 'ASC ';
	
		switch(abs($orderBy))
		{
			default:
				$orderBy = 1;
			case 1:
				$query .= 'ORDER BY barcode '.$orderType;
				break; 
				
			case 2:
				$query .= 'ORDER BY master '.$orderType.', date_created ASC, barcode ASC ';
				break;
					
			case 3:
				$query .= 'ORDER BY date_created '.$orderType.', barcode ASC ';
				break;
			
			case 4:
				$query .= 'ORDER BY date_used '.$orderType.', barcode ASC ';
				break;
				
			case 5:
				$query .= 'ORDER BY config_json '.$orderType.', date_created ASC, barcode ASC ';
				break;
		}
	
		$query .= 'LIMIT '.$pageStart.', '.$pageSize;
	
		$result = mysql_query($query,$_system->db->connection);
	
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		
		echo "total ".$totalEntries." results <br /><br>";
		//echo "displaying ".$pageSize." barcodes per page<br>";
		
		if($pageCount > 0)
		{
			if($pageIndex > 0)
			{
				echo ' <a href="barcode_list.php?orderBy='.$orderBy.'&page='.($pageIndex - 1).'">&lt;&lt;&lt;</a> ';
			}
			else
			{
				echo ' &lt;&lt;&lt; ';
			}


			for($i=0; $i<$pageCount; $i++)
			{
				if($i != $pageIndex)
				{
					echo ' <a href="barcode_list.php?orderBy='.$orderBy.'&page='.$i.'">'.$i.'</a> ';
				}
				else
				{
					echo ' ['.$i.'] ';
				}
			}


			if($pageIndex < $pageCount - 1)
			{
				echo '<a href="barcode_list.php?orderBy='.$orderBy.'&page='.($pageIndex + 1).'"> &gt;&gt;&gt; </a>';
			}
			else
			{
				echo '&gt;&gt;&gt;';
			}
		}
		
	
		echo '<table border="1">';
		echo '<tr>';
		echo '<td>Index</td>';
		writeTableHeader('Barcode', 1, $orderBy);
		writeTableHeader('Master', 2, $orderBy);
		writeTableHeader('Date Created', 3, $orderBy);
		writeTableHeader('Date Used', 4, $orderBy);
		writeTableHeader('JSON', 5, $orderBy);
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';
		
		while($row = mysql_fetch_assoc($result))
		{
			$id = $pageIndex*$pageSize+$index++;
			
//			$productCode = $row['pcode'];
//			if(is_null($productCode)) $productCode = "Undefined";			
// 			if($row['template_id'] == -1)
// 			{
// 				$template = "[ Master ]";
// 			}
// 			else
// 			{
// 				$template = $row['template_id'];
// 				if(is_null($row['dtn']))
// 				{
// 					$template .= ": Undefined";
// 				}
// 				else
// 				{
// 					$template .= ": ".$row['dtn'];
// 				}
// 			}
			
// 			if($row['template_category_id'] == -1)
// 			{
// 				$category = "[ Master ]";
// 			}
// 			else
// 			{
// 				$category = $row['template_category_id'];
// 				if(is_null($row['dtn']))
// 				{
// 					$category .= ": Undefined";
// 				}
// 				else
// 				{
// 					$category .= ": ".$row['dtcn'];
// 				}
// 			}

			echo '<tr><td>';
			echo $id;
			echo '</td><td>';
			echo htmlspecialchars($row['barcode']);
			echo '</td><td>';
			echo htmlspecialchars($row['master']);
			echo '</td><td>';
			echo htmlspecialchars($row['date_created']);
			echo '</td><td>';
			echo htmlspecialchars($row['date_used']);
			echo '</td><td>';
			echo htmlspecialchars($row['config_json']);
			echo '</td><td>';
			echo "<a href='barcode_edit.php?barcode=".$row['barcode']."'>Edit</a>";
			echo '</td><td>';
			echo "<a href='barcode_list.php?delete_barcode=".$row['barcode']."' onclick='deletePrompt();'>Delete</a>";
			echo '</td><tr>';
		}
		echo '</table>';
		
		
		if($pageCount > 0)
		{
			if($pageIndex > 0)
			{
				echo ' <a href="barcode_list.php?orderBy='.$orderBy.'&page='.($pageIndex - 1).'">&lt;&lt;&lt;</a> ';
			}
			else
			{
				echo ' &lt;&lt;&lt; ';
			}
	
	
			for($i=0; $i<$pageCount; $i++)
			{
				if($i != $pageIndex)
				{
					echo ' <a href="barcode_list.php?orderBy='.$orderBy.'&page='.$i.'">'.$i.'</a> ';
				}
				else
				{
					echo ' ['.$i.'] ';
				}
			}
		
				
			if($pageIndex < $pageCount - 1)
			{
				echo '<a href="barcode_list.php?orderBy='.$orderBy.'&page='.($pageIndex + 1).'"> &gt;&gt;&gt; </a>';
			}
			else
			{
				echo '&gt;&gt;&gt;';
			}
		}
		
		$export = false;
		if(isset($_POST['indexStart']) && isset($_POST['indexEnd']) && is_numeric($_POST['indexStart']) && is_numeric($_POST['indexEnd']))
		{
			if($_POST['indexStart'] >= 0 && $_POST['indexEnd'] < $totalEntries) {
				$export = true;
				$out = '';
			}
		}
		
		if($export)
		{
			$query =	'SELECT barcode, product_id, date_created, master, date_used, template_category_id, template_id, p.code as pcode, dt.name as dtn, dtc.name as dtcn '.
						'FROM barcodes '.
						'LEFT JOIN products p ON product_id = p.id '.
						'LEFT JOIN design_template_categories dtc ON template_category_id = dtc.id '.
						'LEFT JOIN design_templates dt ON template_id = dt.id ';
			
			$orderBy = isset($_GET['orderBy']) ? intval($_GET['orderBy']) : 1;
			$orderType = $orderBy < 0 ? 'DESC ' : 'ASC ';
			
			switch(abs($orderBy))
			{
				case 2:
					$query .= 'ORDER BY master '.$orderType.', barcode, product_id, date_created ASC ';
					break;
						
				case 3:
					$query .= 'ORDER BY product_id '.$orderType.', barcode, date_created ASC ';
					break;
		
				case 4:
					$query .= 'ORDER BY template_id '.$orderType.', product_id, barcode, date_created ASC ';
					break;
	
				case 5:
					$query .= 'ORDER BY dtcn '.$orderType.', product_id, barcode, date_created ASC ';
					break;
					
				case 6:
					$query .= 'ORDER BY date_created '.$orderType.', product_id, barcode, date_created ASC ';
					break;
				
				case 7:
					$query .= 'ORDER BY date_used '.$orderType.', product_id, barcode, date_created ASC ';
					break;
					
				default:
					$orderBy = 1;
				case 1:
					$query .= 'ORDER BY barcode '.$orderType.', date_created '.$orderType;
			}
			
			$result = mysql_query($query,$_system->db->connection);
			
			if(!$result)
			{
				if(DesignDB::DEBUG) echo mysql_error();
				return false;
			}
			
			$newCounter = 0;
			while($row = mysql_fetch_assoc($result))
			{
				if ($newCounter >= $_POST['indexStart'] && $newCounter <= $_POST['indexEnd'])
				{
					$productCode = $row['pcode'];
					if(is_null($productCode)) $productCode = "Undefined";
					$product = $row['product_id']."| ".$productCode;
					echo $product;
					if($row['template_id'] == -1)
					{
						$template = "[ Master ]";
					}
					else
					{
						$template = $row['template_id'];
						if(is_null($row['dtn']))
						{
							$template .= ": Undefined";
						}
						else
						{
							$template .= ": ".$row['dtn'];
						}
					}
						
					if($row['template_category_id'] == -1)
					{
						$category = "[ Master ]";
					}
					else
					{
						$category = $row['template_category_id'];
						if(is_null($row['dtn']))
						{
							$category .= ": Undefined";
						}
						else
						{
							$category .= ": ".$row['dtcn'];
						}
					}
					
					
					
					$out[] = Array(
							$row['barcode'],
							$row['master'],
							$product,
							$template,
							$category,
							$row['date_created'],
							$row['date_used']
					);
				}
				$newCounter++;
			}
			
			$data = 'Barcode, Master, Product, Template, Category, Date Created, Date Used'."\n";
				
			foreach($out as $line)
			{
				foreach($line as $item)
				{
					$cleandata[] .= escape_csv_value($item);
				}
				$data .= join(',', $cleandata)."\n";
				$cleandata = "";
			}
				
			ob_end_clean();
			$fname = 'barcode_'.date("ymd").'.csv';
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename='.$fname);
			header('Pragma: no-cache');
			header("Expires: 0");
			echo $data;
			exit();
		}
		
		return true;
	}
	
	if(isset($_GET['delete_barcode']))
	{
		$barDelete = $_order_db->getBarcodeByBarcode($_GET['delete_barcode']);
		if(!is_null($barDelete))
		{
			$_order_db->deleteBarcode($barDelete->barcode);
		}
	}
	
	function escape_csv_value($value) {
		$value = str_replace('"', '""', $value); // First off escape all " and make them ""
		if(preg_match('/,/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value)) { // Check if I have any commas or new lines
			return '"'.$value.'"'; // If I have new lines or commas escape them
		} else {
			return $value; // If no new lines or commas just return the value
		}
	}
	
	include_once 'preamble.php';
?>

<script language="JavaScript">
	function deletePrompt()
	{
		return confirm('Are you sure that you wish to delete barcode from the database ?');
	}
</script>

<h1> Barcodes ( List )</h1>

<form method="post">
Index<br />
<input type="text" name="indexStart">
to <input type="text" name="indexEnd">
export to excel <input type="submit" name="csv" value="Export">
</form>


<?php writeBarcodeList(); ?>
<br><br>

<a href='barcode_edit.php'>New Barcode</a>


<?php include_once 'postamble.php';?>