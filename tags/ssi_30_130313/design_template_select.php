<?php
	include_once "_common.php";	
	$_system->forceLogin();
	if(!$_session->getEnableTemplateBrowser()) exit;
	
	if($_design_id == "") $_system->loginRedirect();
	
	if(isset($_GET['select']))
	{
		$_session->setSelectedTemplateId($_GET['select']);
		exit();
	}
	
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");	
	
	
	$design = $_design_db->getDesignById($_design_id);
	$defaultTemplateId = $_design_db->getDefualtDesignTemplateId($design->productTypeId);
	$selectedTemplateId = $_session->getSelectedTemplateId();
	if($selectedTemplateId == "")
	{
		$selectedTemplateId = $defaultTemplateId;
		$_session->setSelectedTemplateId($selectedTemplateId);
	}
	
	if(isset($_POST['next']))
	{
		$template = $_design_db->getDesignTemplateById($selectedTemplateId);		
		$_design_db->setDesignJSON($_design_id, $template->json);
		Header("location: http://".$_url."design_customize.php");		
		exit();
	}
	
	function writeTemplateImage($template, $selectedTemplateId)
	{
		$cssClass = ($selectedTemplateId == $template->id) ? "template_cell_selected" : "template_cell";		
		echo sprintf('<td class="%s" id="template_cell_%d" ondblclick="selectTemplate(%d,true)" onclick="selectTemplate(%d)" ><img id="template_image_%d" src="design_part/get_image.php?thumbnail=true&id=%d"/><br>%s</td>',
				$cssClass,
				$template->id,
				$template->id,
				$template->id,
				$template->id,
				$template->previewImageId,
				htmlspecialchars($template->name)
		);
	}

	
	if(isset($_GET['tab']))
	{
		$defaultTemplate = $_design_db->getDesignTemplateById($defaultTemplateId); 

		echo '<table cellpadding="0" cellspacing="2"><tr>';		
		
		if($_session->getDesignMode() != Session::DESIGN_MODE_SIMPLE)
		{
			writeTemplateImage($defaultTemplate, $selectedTemplateId);
		}
		
		$col = 1;
		
		$list = $_design_db->getDesignTemplates($_GET['tab'], $design->productTypeId);		
		foreach($list as $item)
		{
			if($defaultTemplateId != $item->id)
			{
				if($col == 5)
				{
					echo '</tr><tr>';
					$col = 0;
				}
				
				writeTemplateImage($item, $selectedTemplateId);
				$col++;
			}
		}
		for(; $col<5; $col++) echo '<td></td>';
		
		echo '</tr></table>';		
		exit();
	}
	
	
	
	
	function writeCategories()
	{
		global $_design_db;
		global $_session;
		
		$list = $_design_db->getTemplateCategoryListForOrderItem($_session->getActiveOrderItemId());
		foreach($list as $item)
		{
			echo sprintf("<li><a href='design_template_select.php?tab=%d'>%s</a></li>",
					$item['id'],
					htmlspecialchars($item['name'])
				); 
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/cupertino/jquery-ui-1.8.23.custom.css?version=<?php echo $_version ?>" rel="stylesheet" />
	<link type="text/css" href="css/design_wizard.css?version=<?php echo $_version ?>" rel="StyleSheet"/>
	
		
	<style>

		.template_cell, .template_cell:hover, .template_cell_selected 
		{
			width:150px;
			height:165px;
			cursor: pointer;		
			text-align: center;
			border-style:none;
			font-size:12px;
			color:#101010;
}
		
		.template_cell
		{
		}
		
		.template_cell:hover
		{
			background-color:#dbedf8;			
		}
		
		.template_cell_selected
		{			
			background-color:#a0cedb;		
		}
		
		.template_cell img
		{
			border-style:solid;
			border-width:1px;
			border-color:silver;
		}
		
		template_cell:hover img, .template_cell_selected img
		{
			border-style:solid;
			border-width:1px;
			border-color:white;
		}
		.ui-tabs-panel 
		{
			height: 79%;
			overflow-y: auto;
		}
		
	</style>
	
	<script src="js/lib/jquery-1.8.0.min.js"></script>
	<script src="js/lib/jquery-ui-1.8.23.custom.min.js"></script>
	<script src="js/lib/jquery.json-2.3.min.js"></script>	
	<script src="js/browser_check.js"></script>	
	
	<script type="text/javascript">
		forceModernBrowser();
	
		function selectTemplate(id, doSubmit)
		{
			$( "#tabs" ).find(".template_cell_selected").attr("class", "template_cell");
			$("#template_cell_" + id).attr("class", "template_cell_selected");
			$.ajax({ url: "design_template_select.php", data: {select : id } }).done(function(){
				if(doSubmit)
				{
					$("#nextButton").click();
				}
			});
		}
		
		$(document).ready(function() {
			$( "#tabs" ).tabs();
			$( "#nextButton" ).button();			
		});
		
	</script> 	  
</head>	

<body unselectable="on" class="unselectable">
	<div class="wizard_frame">
		<div class="wizard_header">
			<div class="wizard_header_title">Select a template for your design</div>
			<div class="wizard_header_info">Any template you select can be further customized to better meet your needs.</div>
		</div>
		<div id="tabs" class="wizard_body" style="width:795px; height:455px">
			<ul>
				<?php writeCategories(); ?>
			</ul>
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
			<form method="POST" action="" id="nextForm">
				<button type="submit" id="nextButton" name="next" class="next_button" value="next">Next</button>
			</form>
		</div>
	</div>
</body>
</html>



