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
				<td><input name="selectedProduct'.$type.'" value="'.$item->image_id.'+'.$item->order_id.'+'.$item->color.'" type="checkbox" id="selectedProduct'.$type.'"></td>
				<td>'.$item->submit_time.'</td>
				<td>'.$item->order_id.'</td>
				<td>'.$item->design_id.'</td>
				<td>'.$item->product_name.'</td>
				<td>'.$item->material.'</td>
				<td><a href="../design_part/get_image.php?id='.$item->image_id.'" target="_blank">Show Image</a></td>
				</tr>';
				
	}
	$table.="</table>";
	return $table;
}

if(isset($_GET['status'])){
	switch ($_GET['status']){
		case 0:
			$status = ProcessingStage::STAGE_READY;
			break;
		case 1:
			$status = ProcessingStage::STAGE_ARCHIVED;
			break;
	}
}
else{
	$status = ProcessingStage::STAGE_READY;
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
        <div align="center"><h1>SM-ART Manufacturing Workstation by In-Stamp Solutions</h1></div>
    </header>
    <!--progressbar will be used by jqueryui to display the progressbar-->
    <div id="dialog" title="Progress">
    <div id="progressbar" width="300px"></div>

    <!--lets use a message div to show a message when the server completes the execution of script. this will display 'complete'. check the javascript.-->
    <div id="message">Creating PDF...</div>
    </div>
        <ul id="menu">
            <li>
            <a href="#">Tools</a>
            <ul style="background: rgb(223, 239, 252);">
            <li><a href="#" style=" color: rgb(46, 110, 158);">Print</a>
            <ul>
            <li><a onClick="print(0)" style=" color: rgb(46, 110, 158); cursor: pointer;">Polymer</a></li>
            <li><a onClick="print(1)" style=" color: rgb(46, 110, 158); cursor: pointer;">Trio</a></li>
            <li><a onClick="print(2)" style=" color: rgb(46, 110, 158); cursor: pointer;">Trio Index Cards</a></li>
            <li><a onClick="print(3)" style=" color: rgb(46, 110, 158); cursor: pointer;">Embossers</a></li>
            <li><a onClick="print(4)" style=" color: rgb(46, 110, 158); cursor: pointer;">Embossers Index Cards</a></li>
            <li><a onClick="print(5)" style=" color: rgb(46, 110, 158); cursor: pointer;">Laser</a></li>
            <li><a onClick="print(6)" style=" color: rgb(46, 110, 158); cursor: pointer;">Laser Layout Sheet</a></li>
            </ul>
            </li>
            <li><a href="#" style=" color: rgb(46, 110, 158);">Show Status</a>
            <ul>
            <li><a href="index.php?status=0" style=" color: rgb(46, 110, 158); cursor: pointer;">Ready for Manufacturing</a></li>
            <li><a href="index.php?status=1" style=" color: rgb(46, 110, 158); cursor: pointer;">Archived</a></li>
            </ul>
            </li>
            <li><a href="#" style=" color: rgb(46, 110, 158);">Change Status</a>
            <ul>
            <li><a onClick="changeStatus(0)" style=" color: rgb(46, 110, 158); cursor: pointer;">Ready for Manufacturing</a></li>
            <li><a onClick="changeStatus(1)" style=" color: rgb(46, 110, 158); cursor: pointer;">Archived</a></li>
            </ul>
            </li>
			</ul>           
            </li>
            <li><a href="#">Settings</a></li>
            <li><a href="#">Help</a></li>
        </ul>
        <?php 
        $items = $_workstation_db->getItemsByMaterial(3,$status);
        $categories = array();
        $per_category = array();
        for($i=0;$i<sizeof($items);$i++){
			$material = $items[$i]->material;
        	if(!in_array($material,$categories)){
        		$categories[] = $material;
        	}
        }
        foreach($categories as $category){
			$per_category[$category] = $_workstation_db->getItemsByMaterial(3,$status,$category);
		}
		//echo "=================================".ProcessingStage::STAGE_READY;
		
        ?>
        <h3 style="text-align: center">Status - 
        <?php 
        switch($status){
			case ProcessingStage::STAGE_READY:
				echo "Ready To Manufacture";
				break;
			case ProcessingStage::STAGE_ARCHIVED:
				echo "Archived";
				break; 
		}
        ?></h3>
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
  
<script type="text/javascript" src="js/myscript.js"></script>
<div id="userID" style="visibility: hidden">0</div>
</body>
</html>
