var _addGraphicsDialog = {
	
	//imageCategories : [<?php echo $imageCategorySJArray ?>],			
	selectedImage : 0,
				
	showTab : function(id)
	{
		//console.log("Showing tab "+id);
		$('#selectgraphictab').css('display','none');
		$('#symboltab').css('display','none');
		$('#uploadgraphictab').css('display','none');
		if(id=="#selectgraphictab"){
			$('#uploadGraphicTabButton').removeClass('tab_button_selected');
			$('#selectSymbloTabButton').removeClass('tab_button_selected');
			$('#selectGraphicTabButton').removeClass('tab_button');
			$('#uploadGraphicTabButton').addClass('tab_button');
			$('#selectSymbolTabButton').addClass('tab_button');
			$('#selectGraphicTabButton').addClass('tab_button_selected');	
		}
		else if(id=="#uploadgraphictab"){
			$('#selectGraphicTabButton').removeClass('tab_button_selected');
			$('#selectSymbolTabButton').removeClass('tab_button_selected');
			$('#uploadGraphicTabButton').removeClass('tab_button');
			$('#selectGraphicTabButton').addClass('tab_button');
			$('#selectSymbolTabButton').addClass('tab_button');		
			$('#uploadGraphicTabButton').addClass('tab_button_selected');
		}
		$(id).css('display','block');
	},
	
	changeImageCategory : function(categoryId)
	{
		$.get('design_part/get_image_list.php?category_id=' + categoryId, function(data) {
			var s = "<legend>Select the stock graphic to use for your " + _system.getProductName() + "</legend>";
			var object = jQuery.parseJSON(data);
			for(var i=0;i<object.length;i++)
			{
				s += '<img  class="librarygraphic" onClick="_addGraphicsDialog.selectImage(this,'+ object[i].id + ')" src="design_part/get_image.php?thumbnail=true&id='+object[i].id+'&color=black" />';
			}
			$('#selectimageblock').html(s);
		});
	},
	
	selectImage : function (element,image_id)
	{
		$('#selectimageblock img').removeClass('imagelistselected');
		$(element).addClass('imagelistselected');
		_addGraphicsDialog.selectedImage = image_id;
	},

	uploadGraphicCheck : function(element)
	{
		var fileElement = element;
		var file = element.files[0];
		if(file)
		{
			$('#filename').html("File Name: "+file.name+"<br />File Size: "+(file.size/1024).toFixed(2)+"KB");
		}
	},
	
	uploadProgress : function(e,data){ },
	
	uploadGraphicComplete : function (e,data)
	{
		_addGraphicsDialog.changeImageCategory(1);
		_addGraphicsDialog.showTab('#selectgraphictab');
	},
				
	uploadGraphicSubmit : function ()
	{
		var file = $('#uploadFile')[0].files[0];		

		$("#uploadFileResult").html('<div id="uploadFileProgressBar"></div>');				
		$("#uploadFileProgressBar").progressbar( "value" , 0);

		
		_system.uploadImageFiles(
			file, 
			1, //ImageDB::CATEGORY_USER_UPLOADED
			
			function()
			{
				$("#uploadFileResult").html("");
				_addGraphicsDialog.changeImageCategory(_addGraphicsDialog.imageCategories[0][0]);
				_addGraphicsDialog.showTab('#selectgraphictab');
			},
			
			function(value)
			{
				$("#uploadFileProgressBar").progressbar( "value" , Math.floor(value * 100.0));				
			}
		);
	}
};
