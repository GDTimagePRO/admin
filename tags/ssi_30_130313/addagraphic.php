<?php
	//NOT FINISHED
	include_once "_common.php";	
	$_system->forceLogin();
	if($_design_id == "") $_system->loginRedirect();
	
	/*if(isset($_GET['select']))
	{
		$_session->setSelectedTemplateId($_GET['select']);
		exit();
	}
	*/
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");	
	
	//$selectedTemplateId = $_session->getSelectedTemplateId();
	/*
	$design = $_design_db->getDesignById($_design_id);
	$defaultTemplateId = $_design_db->getDefualtDesignTemplateId($design->productTypeId);
	
	if($selectedTemplateId == "")
	{
		$selectedTemplateId = $defaultTemplateId;
		$_session->setSelectedTemplateId($selectedTemplateId);
	}
	
	if(isset($_POST['next']))
	{
		$template = $_design_db->getDesignTemplateById($selectedTemplateId);
		$_design_db->setDesignJSON($_design_id, $template->json);
		Header("location: http://".$_settings[Startup::SETTING_HOME_URL]."design_customize.php");
		exit();
	}
	
	function writeTemplateImage($template, $selectedTemplateId)
	{
		//$item->name
		$cssClass = ($selectedTemplateId == $template->id) ? "template_image_selected" : "template_image";
		echo sprintf('<img id="template_image_%d" onclick="selectTemplate(%d)" class="%s" src="design_part/get_image.php?thumbnail=true&id=%d"/>',
				$template->id,
				$template->id,
				$cssClass,
				$template->previewImageId
		);
	}

	
	*/
	
	function writeImage($image)
	{
		$cssClass = "template_image";
		echo sprintf('<img id="image_%d" onclick="selectImage(%d)" class="%s" src="design_part/get_image.php?thumbnail=true&id=%d"/>',
				$image->id,
				$image->id,
				$cssClass,
				$image->id
		);
	}
	
	if(isset($_GET['tab']))
	{
		/*$defaultTemplate = $_design_db->getDesignTemplateById($defaultTemplateId); 
		writeTemplateImage($defaultTemplate, $selectedTemplateId);
				
		$list = $_design_db->getDesignTemplates($_GET['tab']);		
		foreach($list as $item)
		{
			writeTemplateImage($item, $selectedTemplateId);
		}
		*/
		if ($_GET['tab'] == 1)
		{
			
			echo "<p><fieldset id=\"selectimageblock\"></fieldset></p>";
			
			echo "<div>";
			echo "<label for=\"uploadGraphic\">File:</label>";
			echo "<input type=\"file\" name=\"file\" id=\"uploadFile\" onChange=\"uploadGraphicCheck(this)\"/>";
			echo "<div id=\"filename\"></div>";
			echo "<button id=\"uploadGraphic\" onClick=\"uploadGraphicSubmit()\">Upload</button>";
			echo "<div id=\"uploadFileResult\"></div>";
			echo "<div id=\"imageField\"></div>";
			echo "<fieldset id=\"imageField\"></fieldset>";
			echo "<div>";
		}
		else if ($_GET['tab'] == 2)
		{
			
			echo "office";
			echo $_user_id;
			$list = $_image_db->getImagesByCategoryId($_GET['tab'], $_user_id);
			echo count($list);
			foreach($list as $item)
			{
				writeImage($item);
			}
		}
		else if ($_GET['tab'] == 3)
		{
			echo "sport";
		}
		exit();
	}
	
	
	
	
	function writeCategories()
	{
		/*global $_design_db;
		$list = $_design_db->getTemplateCategoryList();
		foreach($list as $item)
		{
			echo sprintf("<li><a href='design_template_select.php?tab=%d'>%s</a></li>",
					$item['id'],
					htmlspecialchars($item['name'])
				); 
		}*/
		echo "<li><a href='addagraphic.php?tab=1'>user uploaded</a></li>";
		echo "<li><a href='addagraphic.php?tab=2'>office</a></li>";
		echo "<li><a href='addagraphic.php?tab=3'>sports</a></li>";
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/cupertino/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
	<link type="text/css" href="css/design.css" rel="StyleSheet"/>	
	
	<style>
		*.unselectable
		{
		   -moz-user-select: -moz-none;
		   -khtml-user-select: none;
		   -webkit-user-select: none;
		   cursor:default;		
		   -ms-user-select: none;
		   user-select: none;
		}
				
		.wizard_frame
		{
			margin: 0 auto;
			padding: 0px 0px 0px 0px;			
			displa: block;
			width: 800px;
			height: 600px;
		}
		
		.wizard_header
		{
			margin: 0px 0px 0px 0px;
			padding: 0px 0px 0px 0px;			
			displa: block;
			width: 100%;
			height: 120px;
			color:black;
			background-color:white;
		}
		
		.wizard_header_text
		{
			margin: 0px 0px 0px 0px;
			padding: 1.8em 1em 1em 1.8em;			
			displa: block;
			height: 120px;
			color:black;
			font-weight: bold;
			font-size: 18px;
			line-height: 22px;
			font-family: 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
		}			
		
		.wizard_body
		{
			margin: 0px 0px 0px 0px;
			padding: 0px 0px 0px 0px;			
			displa: block;
			width: 100%;
			height: 430px;
		}			
		
		.wizard_footer
		{
			margin: 0px 0px 0px 0px;
			padding: 0px 0px 0px 0px;			
			displa: block;
			width: 100%;
			height: 50px;
		}
		
		.next_button
		{
			float:right;
			margin-top:5px;
			margin-right:10px;
		}
		
		.template_image
		{
			width:125px;
			height:125px;
			margin: 9px 9px 9px 9px;
			padding: 0px 0px 0px 0px;
			
			border-color:silver;
			border-style:solid;
			border-width:1px;	
			
			cursor: pointer;
					
		}
		
		.template_image:hover
		{
			width:125px;
			height:125px;
			border-style:solid;
			
			border-color:black;
			border-width:2px;		
			margin: 8px 8px 8px 8px;
			padding: 0px 0px 0px 0px;			
		}
		
		.template_image_selected
		{
			width:125px;
			height:125px;
			border-style:solid;
			
			border-color:blue;
			border-width:3px;		
			margin: 7px 7px 7px 7px;
			padding: 0px 0px 0px 0px;			
		}
	}
		
		
		
	</style>
	
	<script src="js/lib/jquery-1.8.0.min.js"></script>
	<script src="js/lib/jquery-ui-1.8.23.custom.min.js"></script>
	<script src="js/lib/jquery.json-2.3.min.js"></script>	
	
	<script src="js/design/add_graphics_dialog.js"></script>
	
	
	<script type="text/javascript">

		function uploadGraphicCheck(file)
		{
			//alert("hello");
			_addGraphicsDialog.uploadGraphicCheck(file);


			/*echo "<div>";
			echo "<label for=\"uploadGraphic\">File:</label>";
			echo "<input type=\"file\" name=\"file\" id=\"uploadFile\" onChange=\"_addGraphicsDialog.uploadGraphicCheck(this)\"/>";
			echo "<div id=\"filename\"></div>";
			echo "<button id=\"uploadGraphic\" onClick=\"_addGraphicsDialog.uploadGraphicSubmit()\">Upload</button>";
			echo "<div id=\"uploadFileResult\"></div>";
			echo "<div>";*/
		}	 

		function uploadGraphicSubmit()
		{
			_addGraphicsDialog.uploadGraphicSubmit();
		}

		function changeImageCategory(value)
		{
			alert("hi");
			_addGraphicsDialog.changeImageCategory(value);
		}
		
		function selectImage(id)
		{
			$( "#tabs" ).find(".template_image_selected").attr("class", "template_image");
			$("#template_image_" + id).attr("class", "template_image_selected");
			$.ajax({ url: "addagraphic.php", data: {select : id } });
		}
		
		$(document).ready(function() {
			$( "#tabs" ).tabs();
			$( "#nextButton" ).button();
			
			$( "#dostuffandthings" ).button().click(function() {				
				$( "#dialog_confirm_previous" ).dialog({
					resizable: false,
					height:600,
					width:600,
					modal: true,
					buttons: {
						"Cancel": function() {
							$( this ).dialog( "close" );
						}
					}
				});
			});
		});
		
	</script> 	  
</head>	

<body unselectable="on" class="unselectable">
	<button id="dostuffandthings">Hello</button>
	<div class="wizard_frame">
		<div class="wizard_header">
			<div class="wizard_header_text">			
				Please select a template for your design and click "next"<br>
				Any template can be completely customized until it meets your needs.<br>
			</div>
		</div>
		<div id="dialog_confirm_previous" title="Revert to template" class="hidden">						
			<div id="tabs" class="wizard_body">
				<ul>
					<?php writeCategories(); ?>
				</ul>
			</div>
		</div>		
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<form method="POST" action="">
				<button type="submit" id="nextButton" name="next" class="next_button" value="next">Next</button>
			</form>
		</div>
	</div>
</body>
</html>