<?php 
	include_once "_common.php";
	$selected = "";
	
	if (isset($_GET['dateSelected']))
	{
		$selected = $_GET['dateSelected'];
	}
	
	function writeBarcodeList()
	{
		global $_system;
		global $selected;
		
		
		$query = sprintf('SELECT * FROM barcodes where date_created="%s"',mysql_escape_string($selected));
		
		$result = mysql_query($query,$_system->db->connection);
		if(!$result)
		{
			if(DesignDB::DEBUG) echo mysql_error();
			return false;
		}
		echo "<table border='1'>";
		
		echo "<tr>";
		echo "<td> Barcode </td>";
		echo "<td> Master </td>";
		echo "<td> Product </td>";
		echo "<td> Template </td>";
		echo "<td> Category </td>";
		echo "<td> Date Created </td>";
		echo "<td> Date Used </td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";
		
		while($row = mysql_fetch_assoc($result))
		{
			echo "<tr><td>";
			echo htmlspecialchars($row['barcode']);
			echo "</td><td>";
			echo htmlspecialchars($row['master']);
			echo "</td><td>";
			echo $row['product_id'];
			echo "</td><td>";
			echo $row['template_id'];
			echo "</td><td>";
			echo $row['template_category_id'];
			echo "</td><td>";
			echo $row['date_created'];
			echo "</td><td>";
			echo $row['date_used'];
			echo "</td><td>";
			echo "<a href='barcode_edit.php?barcode=".$row['barcode']."'>Edit</a>";
			echo '</td><td>';
			echo "<a href='barcode_select.php?delete_barcode=".$row['barcode']."&dateSelected=".$selected."' onclick='deletePrompt();'>Delete</a>";
			echo '</td><tr>';
		}
		
		$export = false;
		if(isset($_GET['export']))
		{
			$export = true;
			$out = '';
		}
		
		if ($export)
		{
			$query = sprintf(
					'SELECT barcode, master, product_id, template_id, template_category_id, '.
					'date_created, date_used FROM barcodes where date_created="%s"',
					mysql_escape_string($selected)
				);
			
			$result = mysql_query($query,$_system->db->connection);
			if(!$result)
			{
				if(DesignDB::DEBUG) echo mysql_error();
				return false;
			}
			
			while($row = mysql_fetch_assoc($result))
			{
				$out[] = $row;
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

	}
	
	function escape_csv_value($value) 
	{
		$value = str_replace('"', '""', $value);
		if(preg_match('/,/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value)) 
		{
			return '"'.$value.'"';
		} 
		else 
		{
			return $value; 
		}
	}
	
	global $_order_db;
	
	if(isset($_GET['delete_barcode']))
	{
		$barDelete = $_order_db->getBarcodeByBarcode($_GET['delete_barcode']);
		if(!is_null($barDelete))
		{
			$_order_db->deleteBarcode($barDelete->barcode);
		}
	}
	
	
	include_once 'preamble.php';
?>
<script language="JavaScript">
	function deletePrompt()
	{
		return confirm('Are you sure that you wish to delete barcode from the database ?');
	}
	function updateSelect()
	{
		document.getElementById("select").submit();
	}
</script>
<h1> Barcodes ( Select )</h1>

<form id="select">
<select name="dateSelected" onChange="updateSelect()">
<?php 
	global $_system;
		
	
	$query = 'SELECT DISTINCT date_created FROM barcodes';
		
	$result = mysql_query($query,$_system->db->connection);
	
	if(!$result)
	{
		if(DesignDB::DEBUG) echo mysql_error();
		return false;
	}
	
	
	while($row = mysql_fetch_assoc($result))
	{
		$barcodeList[] = $row;
	}
	
	if (empty($selected))
	{
		echo '<option value="default" selected>---------------</option>';
	}
	foreach($barcodeList as $barcodeItem) {
		echo '<option value="' . $barcodeItem['date_created'] . '" ';
		if($barcodeItem['date_created']==$selected)
		{
			echo 'selected';
		}
	    echo '> Date Created: ' . $barcodeItem['date_created'] . '</option>';
	}
?>
<input type="submit" name="export" value="Export"></input>
</select>
</form>
<br><br>
<?php
	writeBarcodeList($selected);	
?>


<?php include_once 'postamble.php';?>