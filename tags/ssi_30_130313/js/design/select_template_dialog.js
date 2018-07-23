var _selectTemplateDialog = {
	selectedTemplate : -1,
	changeTemplate : function()
	{
		if(_selectTemplateDialog.selectedTemplate != -1)
		{
			$.get(
				'design_part/get_template_json.php?template_id=' + _selectTemplateDialog.selectedTemplate,
				function(data)
				{
					_system.saveState();
					_system.setState(jQuery.parseJSON(data));
					_system.scene.redraw();
					closePopup('#selecttemplatepopup');
				}
			);
		}				
	},	
		
	selectTemplate : function selectTemplate(element,template_id)
	{
		$('#selecttemplateblock img').removeClass('imagelistselected');
		$(element).addClass('imagelistselected');
		_selectTemplateDialog.selectedTemplate = template_id;
	},
	
	changeTemplateCategory : function(categoryId)
	{
		$.get('design_part/get_template_list.php?category_id=' + categoryId, function(data) {
			var s = "<legend></legend>";
			var previewSrc = "design_part/get_image.php?thumbnail=true&id=";					
			var object = jQuery.parseJSON(data);
			for(var i=0;i<object.length;i++)
			{
				s += '<img onClick="_selectTemplateDialog.selectTemplate(this,' + object[i].id +')" src="' + previewSrc + object[i].preview_image_id + '"/>&nbsp;&nbsp;';
			}
			$('#selecttemplateblock').html(s);
		});
	}
};
