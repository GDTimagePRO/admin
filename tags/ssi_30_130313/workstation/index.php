<?php
include "_common.php";


function getTable($items,$type){
	$table = '<table id="showGridTable" cellspacing="0" cellpadding="2">
			<tr>
			<th>Select <input type="checkbox" onClick="selectAll(this)"></th>
		     <th>Submit Time </th>			   	
		     <th>Order #</th>
		     <th>Design #</th>
		     <th>Product</th>
		     <th>Material</th>
		     <th>Image</th></tr>';
	foreach ($items as $item){
		$table .='<tr>
				<td><input name="selectedProduct'.$type.'" value="'.$item->order_id.'" type="checkbox" id="selectedProduct'.$type.'"></td>
				<td>'.$item->submit_time.'</td>
				<td>'.$item->order_id.'</td>
				<td>'.$item->design_id.'</td>
				<td>'.$item->product_name.'</td>
				<td>'.$item->material.'</td>
				<td><img src="../design_part/get_image.php?id='.$item->image_id.'" width="100px" /></td>
				</tr>';
				
	}
	$table.="</table>";
	return $table;
}
?>
<html>
<head>
  <!--include the jquery and jqueryui library. also all scripts are in external file called myscript.js-->
  <script src="js/jquery-1.9.1.js" type="text/javascript"></script>
  <script type="text/javascript" src="js/jquery-ui-1.10.1.custom.js"></script>
  <script type="text/javascript" src="js/jquery.ui.menubar.js"></script>
  <link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui-1.10.1.custom.css">
  <link rel="stylesheet" type="text/css" media="all" href="css/menubar.css">
  <link rel="stylesheet" type="text/css" media="all" href="css/workstation.css">
</head>
<body>
    <header>
        <div align="center"><h1>SMARTypeset Manufacturing Workstation</h1></div>
    </header>
        <ul id="menu">
            <li>
            <a href="#">Tools</a>
            <ul style="background: rgb(223, 239, 252);">
            <li><a onClick="printVellum()" style=" color: rgb(46, 110, 158);">Print Vellum</a></li>
            <li><a href="#" style=" color: rgb(46, 110, 158);">Print Index Cards</a></li>
            <li><a href="#" style=" color: rgb(46, 110, 158);">Print Run Cards</a></li>
            <li><a onClick="printDustCover()" style=" color: rgb(46, 110, 158);">Print Dust Cover</a></li>
            </ul>
            </li>
            <li><a href="#">Settings</a></li>
            <li><a href="#">Help</a></li>
        </ul>
        <?php 
        $items = $_workstation_db->getItemsByMaterial(3,9999);
        $categories = array();
        $per_category = array();
        for($i=0;$i<sizeof($items);$i++){
			$material = $items[$i]->material;
        	if(!in_array($material,$categories)){
        		$categories[] = $material;
        	}
        }
        foreach($categories as $category){
			$per_category[$category] = $_workstation_db->getItemsByMaterial(3,9999,$category);
		}
        ?>
    <div id="tabs">
        <ul>
            <li><a href="#All">All</a></li>
            <?php 
            	foreach($categories as $category){
					echo '<li><a href="#'.$category.'">'.$category.'</a></li>';
				}
            ?>
            
        </ul>
        
        <div id="All"><?php 
        echo getTable($items,"All");
        ?></div>
        <?php 
            	foreach($categories as $category){
					echo '<div id="'.$category.'">';
  					//var_dump($per_category[$category]);
  					echo getTable($per_category[$category],$category);
  					echo '</div>';
				}
            ?>
    </div>
  <!--progressbar will be used by jqueryui to display the progressbar-->
    <div id="dialog" title="Progress">
    <div id="progressbar" width="300px"></div>

    <!--lets use a message div to show a message when the server completes the execution of script. this will display 'complete'. check the javascript.-->
    <div id="message"></div>
    </div>
<script type="text/javascript" src="js/myscript.js"></script>
<div id="userID" style="visibility: hidden">0</div>
</body>
</html>
