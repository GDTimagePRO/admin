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
				s += '<img  class="librarygraphic" onClick="_addGraphicsDialog.selectImage(this,'+ object[i].id + ')" src="design_part/get_image.php?id='+object[i].id+'&color=black" />';
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
		// name = file.name;
		// size = file.size;
		// type = file.type;
   		
		if (navigator.appName!="Microsoft Internet Explorer")
		{// code for IE7+, Firefox, Chrome, Opera, Safari   		
			var formData = new FormData();
			formData.append("file", file);
	   		
			$("#uploadFileResult").html("Uploading");
			$.ajax({
	    		
				url: 'design_part/create_image.php',
				type: 'POST',
				data: formData,        	
				cache: false,
				contentType: false,
				processData: false
	        	
			}).done( function(data) {
				
				$("#uploadFileResult").html("Done: <br>" + data);
				    		
	    	}).fail( function(jqXHR, textStatus) {
	    		
	    		$("#uploadFileResult").html("Error: <br>" + textStatus);
	    		
	    	});		
		}
		else
  		{// code for IE6, IE5
		  	var form = document.getElementById('uploadGraphicForm');
		  	var iframe = document.createElement("iframe");
		    iframe.setAttribute("id", "upload_iframe");
		    iframe.setAttribute("name", "upload_iframe");
		    iframe.setAttribute("width", "0");
		    iframe.setAttribute("height", "0");
		    iframe.setAttribute("border", "0");
		    iframe.setAttribute("style", "width: 0; height: 0; border: none;");
 
			// Add to document...
			form.parentNode.appendChild(iframe);
			window.frames['upload_iframe'].name = "upload_iframe";
 
    		iframeId = document.getElementById("upload_iframe");
 
    		// Add event...
    		var eventHandler = function ()
    		{
    			try
    			{
    				$("#uploadFileResult").html("Done: <br>" + window.frames['upload_iframe'].window.document.body.innerHTML);    				
    			}
    			catch(e){};
    			
            	if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
            	else iframeId.removeEventListener("load", eventHandler, false);
            	_addGraphicsDialog.uploadGraphicComplete(null,null);
 
            	// Del the iframe...
            	setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
        	}
			if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
	    	if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);
	 
			// Set properties of form...
	    	form.setAttribute("target", "upload_iframe");
	    	form.setAttribute("action", "design_part/create_image.php");
	    	form.setAttribute("method", "post");
	    	form.setAttribute("enctype", "multipart/form-data");
	    	form.setAttribute("encoding", "multipart/form-data");
	    	
	    	// Submit the form...
	    	form.submit();
		}
	}
};
