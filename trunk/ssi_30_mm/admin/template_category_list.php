<?php 
	include_once "_common.php";
	
	if(isset($_GET['delete_id']))
	{
		$_design_db->deleteDesignTemplateCategory($_GET['delete_id']);
	}
	
	function writeCategoryList()
	{	
		global $_system;
				
		echo "<table border='1'>";
		echo "<tr>";
		echo "<td> id </td>";
		echo "<td> Customer </td>";
		echo "<td> name </td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";

		
		$query = 
			"SELECT dtc.id AS id, dtc.name AS name, c.description AS customer_description ".
			"FROM design_template_categories AS dtc " . 
			"LEFT JOIN customers AS c ON dtc.customer_id = c.id " .
			"ORDER BY c.id, dtc.id";
					
		$result = mysql_query($query,$_system->db->connection);
	
 		if(!$result)
 		{
 			if(DesignDB::DEBUG) echo mysql_error();
 			return false;
 		}
 		
 		while($row = mysql_fetch_assoc($result))
 		{
 			echo "<tr><td>";
			echo $row['id'];
			echo "</td><td>";
			echo htmlspecialchars($row['customer_description']);
			echo "</td><td>";
			echo htmlspecialchars($row['name']);
			echo "</td><td>";
			echo "<a href='template_category_edit.php?id=".$row['id']."'>Edit</a>";
			echo "</td><td>";
			echo "<a href='template_category_list.php?delete_id=".$row['id']."' onclick='return deletePrompt(".$row['id'].");'>Delete</a>";
			echo "</td><tr>";
		}
		echo "</table>";
	}
	
	include_once 'preamble.php';
?>
<script language="JavaScript">
	function deletePrompt(id)
	{
		return confirm('Are you sure that you wish to delete product #' + id + ' from the database ?');
	}
</script>
<h1>Template Category ( List )</h1>
<?php writeCategoryList(); ?>
<br><br>
<a href="template_category_edit.php?id=-1">New Category</a>
<?php include_once 'postamble.php';?>
