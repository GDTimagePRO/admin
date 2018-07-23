<?php
	include_once "Backend/startup.php";
	$startup = Startup::getInstance(".");
	$db = $startup->db;
	$s = $startup->session;
	
	if($s->getCurrentItem()==""){
		Header("Location: login.php");
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
  	<meta name="description" content="" />
  	<meta name="author" content="Allan Dickinson, Cameron McGuinness" />
  	<meta name="viewport" content="width=device-width; initial-scale=1.0" />
  	<link rel=StyleSheet href="css/ui-lightness/jquery-ui-1.8.18.custom.css" type="text/css" />
	<link rel=StyleSheet href="css/design.css" type="text/css" />
	<!--<link rel=StyleSheet href="css/tabs.css" type="text/css" />-->
	<script src="js/jquery-1.7.1.min.js"></script>
	<script src="js/jquery-ui-1.8.20.custom.min.js"></script>	
	<script src="_design.js"></script>
	<script src="js/jquery.json-2.3.min.js"></script>	
	<!--<script src="js/json2.js"></script>
	<script src="js/script.js"></script>	
	<script src="js/thumbscroller.js" type="text/javascript"></script>-->
	
	<!--<script type="text/javascript" src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script> 
	<script type="text/javascript">
    $(function(){
        // Fast and dirty
        $('article.tabs section > h3').click(function(){
            $('article.tabs section').removeClass('current');
            $(this)
            .closest('section').addClass('current');
        });
    });
	</script>-->

	<!--	<script src="js/modernizr-2.5.3.js"></script> -->	
	
</head>	

<body>
	<div id="error_checking"></div>
	
	<table width="100%" border="0">
		<tr>
			<td width="14%">
				<div id="help" class="button">HELP</div>				
			</td>
			<td width="80%">
				<div id="title">Create Your Design</div>
			</td>
			<td width="10%" align="right">
				<div id="order" class="button">ORDER INFO</div>
			</td>
		</tr>
		<tr>
			<td>
				<div id="tools">
					<span >Zoom:</span>				
					<div id="zoom">					
					</div>
					<label style="float: left">Smaller</label>
					<label style="float: right">Larger</label>
					<!-- <input type="text" id="currentzoom" value="100%" size="4" onChange="zoom(this.value)"/>-->
				</div>
			</td>
			<td>
				<table width="60%" border="0" align="center">
					<tr>
						<td width="25%">
							<div id="text" class="button" onclick="newTextLine()">Add Text</div>
						</td>
						<td width="25%">
							<div id="border" class="button" onclick="showPopup('#addborderpopup')" >Add Border</div>
						</td>
						<td width="25%">
							<div id="graphic" class="button" onclick="showPopup('#addgraphicpopup')">Add Graphic</div>							
						</td>
						<td width="25%">
							<div id="table" class="button" onclick="showPopup('#addtablepopup')">Add Table</div>							
						</td>
						<td></td>
					</tr>
					<tr>
						<td width="25%">
							<div id="symbols" class="button" onclick="showPopup('#addlinepopup')">Add Line</div>
						</td>
						<td width="25%">
							<div id="colour" class="button" onclick="showPopup('#addcolourpopup')">Ink Colour</div>
						</td>
						<td width="25%">
							<div id="colour" class="button" onclick="showPopup('#addmaterialpopup')">Choose Material</div>							
						</td>
						<td width="25%">
							<div id="template" class="button" onclick="showPopup('#addtemplatepopup')">View Templates</div>
						</td>
						<td width="25%">
							<div id="grid" class="button" onclick="toggleGrid(this)">Grid Lines: Off</div>							
						</td>
					</tr>
				</table>
				<div id="blank">You are currently working on a <span id="productName">
					 <?php
					$barcode = $db->getBarCode($s->getCurrentItem());
					$product_id = $db->getProductId($barcode);
					$product = $db->getProduct($product_id);
					echo $product['longname'];
					?></span>
				</div>
			</td>
			<td></td>
			
		</tr>
		<tr>
			<td class="objectbox">	
				<table id="objects">					
					<th colspan="4" >
						<h3>Object List</h3>
					</th>														
				</table>
				<br/>
				<div id="undo" class="button" onclick="_stateHistory.loadPrevious(); return true;">
					<label onclick="return false">Undo</label>
				</div>
				<div id="redo" class="button" onclick="_stateHistory.loadNext(); return true;">
					<label onclick="return false">Redo</label>
				</div>							
			</td>
			<td align="center">
				<canvas id="surface" width="242" height="107"></canvas>
			</td>
			<td></td>
		</tr>
		<tr class="textlinebox">
			<td colspan="3">
				<div id="textlines">
				<table width="100%" border="0" id="textlineTable">
					<thead>
						<th>#</th>
						<th>Type</th>
						<th>Text</th>
						<th>Font</th>
						<th>Size</th>
						<th>Bold</th>
						<th>Italic</th>
						<th>Align</th>
						<th>Select</th>
						<th>Delete</th>
					</thead>
					<tbody>				
					</tbody>
				</table>
				</div>	
			</td>
		</tr>
		<tr>
			<td colspan="3" align="right">
				<div id="next" class="button">
					<a href="confirm.php">NEXT</a>
				</div>				
			</td>
		</tr>	
	</table>

	<div id="fontselect" class="hidden">
		<select id="font" onchange="changeTextLineFont('#LINENUM',this)">
				<?php
					$fonts = $startup->settings['fonts'];
					foreach($fonts as $font){
						echo '<option>'.$font.'</option>';
					}
				?>	
				</select>	
	</div>

	<div id="fontsizeselect" class="hidden">
		<select id="fontsize" onchange="changeTextLineFontSize('#LINENUM',this)">
			<?php
			$min_size = $startup->settings['min font size'];
			$max_size = $startup->settings['max font size'];
			$step = $startup->settings['font size step'];
			for($i=$min_size;$i<=$max_size;$i+=$step){
				echo '<option>'.round($i,1).'</option>';
			}
			?>
		</select>
	</div>

	<div id="fade"></div>
	
	<div id="addgraphicpopup" class="popup">
		<?php
			include "addgraphic.php";
		?>
	
	</div>
	
	<div id="addtablepopup" class="popup">
		<?php
			include "addtable.php";
		?>
	
	</div>
	
	<div id="addborderpopup" class="popup">
		<?php
			include "addborder.php";
		?>
	
	</div>
	
	<div id="addlinepopup" class="popup">
		<?php
			include "addline.php";
		?>
	
	</div>
	
	<div id="addcolourpopup" class="popup">
		<?php
			include "addcolour.php";
		?>
	
	</div>
	
	<div id="addmaterialpopup" class="popup">
		<?php
			include "addmaterial.php";
		?>
	
	</div>
	
	<div id="addtemplatepopup" class="popup">
		<?php
			include "addtemplate.php";
		?>
	
	</div>
	
	<div id="orderitem_id" class="hidden">
		<?php
		echo $s->getCurrentItem();
		?>
	</div>
	
	<div id="user_id" class="hidden">
		<?php
		echo $s->getUserId();
		?>
	</div>
	<div id="textColor" class="hidden">
		<?php echo $db->getColor($s->getCurrentItem());?>
	</div>
	
	<div id="textlinesdb" class="hidden">
		<?php		
		echo $db->getOrderItemDesign($s->getCurrentItem());
		?>
	</div>
	
</body>
</html>