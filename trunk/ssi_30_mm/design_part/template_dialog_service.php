<?php	
	require_once '../backend/resource_manager.php';
	require_once '../backend/settings.php';
	require_once '../backend/startup.php';
	
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	
	function writeTemplate(DesignTemplate $template)
	{
		
		$rid = ResourceId::fromId($template->getPreviewImageId());
		$rid->type = ResourceManager::TYPE_THUMBNAIL;
		
		echo sprintf(
				'<td class="image_cell" templateId="%s"  ondblclick="TI.templateSelectDialog.setSelected(\'%s\',true)" onclick="TI.templateSelectDialog.setSelected(\'%s\')">'.
				'<img src="%s"/><br>%s<br></td>',
				$template->id,
				$template->id,
				$template->id,
				Settings::getImageUrl($rid->getId()),
				htmlentities($template->name)
			);
	}
		
	if(isset($_GET['tabTemplateCategoryId']))
	{
		$system = Startup::getInstance();
		$list = $system->db->design->getDesignTemplates($_GET['tabTemplateCategoryId']);

		echo '<table cellpadding="0" cellspacing="2"><tr>';
		$col = 0;
		
		foreach($list as $item)
		{
			if($col == 5)
			{
				echo '</tr><tr>';
				$col = 0;
			}
		
			writeTemplate($item);
			$col++;
		}
		for(; $col<5; $col++) echo '<td></td>';
		
		echo '</tr></table>';
		exit();
	}
	else if(isset($_GET['jsonTemplateId']))
	{
		$system = Startup::getInstance();
		$row = $system->db->design->getTemplateJSON($_GET['jsonTemplateId']);
		if ($row[0] == null || $row[0] == "") {
			echo '{"config":"", "design":' . $row[1] . '}';
		} else {
			echo '{"config":' . $row[0] . ', "design":' . $row[1] . '}';
		}
		
		exit();
	}
		
?>