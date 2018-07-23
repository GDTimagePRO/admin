//TODO: save ink color
//TODO: Text goes into an endless loop when the circle is made too small


function htmlEncode(text)
{
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function System(canvasId, propContainerId, listContainerId, width, height)
{
	var system = this;	
	var isStateDirty = false; 
	this.setStateDirty = function() { isStateDirty = true; };

	var pageParams = {
		type : 2,
		width: Math.min(width, height) - 30,
		height: Math.min(width, height) - 30
	};
	this.getPageWidth = function() { return pageParams.width; };
	this.getPageHeight = function() { return pageParams.height; };
	this.getPageType = function() { return pageParams.type; };
	
	var timer = setInterval( function() {
		if(isStateDirty) system.saveState();
	}, 1);

	var reset = function()
	{
		system.ui.clear();
		system.elements = [];	
		system.scene = new Scene($("#canvas")[0], width, height);
	};	
	
	$("#" + canvasId).mousedown(function(e) {
		var offset = $(this).offset();
		var x = e.pageX - offset.left;
        var y = e.pageY - offset.top;          
        if(system.scene.onPress(x, y) == null)
        {
        	system.clearSelection();
        	system.scene.redraw();
        }
	});
           
	$("#" + canvasId).mousemove(function(e) {
		var offset = $(this).offset();
		var x = e.pageX - offset.left;
		var y = e.pageY - offset.top;          
		system.scene.onMove(x, y);
	});
	
	$("#" + canvasId).mouseup(function(e) {
        var offset = $(this).offset();
        var x = e.pageX - offset.left;
        var y = e.pageY - offset.top;        
        system.scene.onRelease(x, y);
	});

	this.getProductName = function()
	{ 
		return "TODO: Add this!!"; 
	};

	this.setDesignJSON = function(json)
	{
		$.post(
			'design_part/set_design_json.php', 
			{ json:  json }
		);		
	};
	

	var SAVED_STATE_MAX = 30;
	var savedStateData = [];
	var savedStateSelected = -1;
	this.clearStateHistory = function()
	{
		savedStateData = [];
		savedStateSelected = -1;
	};
	
	this.saveState = function(dontSendToServer)
	{
		if((savedStateSelected >= 0) && (savedStateSelected < savedStateData.length -1))
		{
			savedStateData = savedStateData.slice(0,savedStateSelected + 1);
		}
		savedStateData.push($.toJSON(this.getState()));
		if(savedStateData.length > SAVED_STATE_MAX) savedStateData = savedStateData.slice(1);
		savedStateSelected = savedStateData.length - 1;
		isStateDirty = false;
		
		if(!dontSendToServer) this.setDesignJSON(savedStateData[savedStateData.length-1]);
		
		return true;
	};
	
	this.getStateJSON = function()
	{
		return $.toJSON(this.getState());		
	};
	
	this.undo = function()
	{
		if(savedStateSelected == 0) return false;
		if(savedStateSelected == savedStateData.length -1)
		{
			savedStateData[savedStateSelected] = $.toJSON(this.getState());	
		}
		savedStateSelected--;
		this.setState(
			jQuery.parseJSON(savedStateData[savedStateSelected])
		);
		
		this.setDesignJSON(savedStateData[savedStateSelected]);
				
		return true;		
	};
	
	this.redo = function()
	{
		if(savedStateSelected == savedStateData.length -1) return false;
		savedStateSelected++;
		this.setState(
			jQuery.parseJSON(savedStateData[savedStateSelected])
		);

		this.setDesignJSON(savedStateData[savedStateSelected]);
						
		return true;
	};

	this.getPageParams = function() { return pageParams; };
	this.setPageParams = function(params)
	{		
		pageParams = params;
		var fgLayer = system.scene.getLayer(Scene.LAYER_FOREGROUND); 
		if(pageParams.type == System.PAGE_TYPE_CIRCLE)
		{			
			fgLayer.clipMask = new RectangularClipMask(params.width, params.height);
		}
		else if(pageParams.type == System.PAGE_TYPE_BOX)
		{			
			fgLayer.clipMask = new CircularClipMask(params.width, params.height);
		}
	};
	
	
	this.ui =  new UIPanel(propContainerId,listContainerId);	

	this.addElement = function(element)
	{
        this.elements.push(element);
        this.ui.addGroup(element.getUIControlGroup());
        element.setScene(this.scene);        
        this.scene.redraw();
        isStateDirty = true;
        return element;
    };

	this.removeElement = function(element)
	{
		for(i in this.elements)
		{
			if(this.elements[i] === element)
			{
				this.ui.removeGroup(element.getUIControlGroup());
				element.setScene(null);
				this.elements.splice(i,1);
        		this.scene.redraw();
		        isStateDirty = true;
        		return true;
			}
		}
		return false;
	};

    var selectedElement = null;
    this.getSelected = function() { return selectedElement; };
    
    this.clearSelection = function()
    {
        for(var i in this.elements) this.elements[i].setSelected(false);
        this.ui.selectGroup(null);
        //isStateDirty = true;
        selectedElement = true;
    };  
    
    
    this.setSelected = function(element)
    {        
        for(var i in this.elements) this.elements[i].setSelected(false);
        element.setSelected(true);
        selectedElement = element;
        this.ui.selectGroup(element.getUIControlGroup());
        this.scene.redraw();        
        //isStateDirty = true;
    }; 

    
    
    this.getState = function()
    {
    	var selectedElement = -1;
    	var elementStates = [];
    	for(var i in this.elements)
    	{
    		var ele = this.elements[i]; 
    		if(ele.getSelected()) selectedElement = i; 
    		elementStates.push(ele.getState());
    	}
    	    	
	    return {
    		selected		: selectedElement,
    		elements		: elementStates,
    		pageParams		: this.getPageParams()
    		//scale			: this.scene.scale 
    	};
    };
    
    this.setState = function(state)
    {
    	var defaultScale = this.scene.scale;    	
    	reset();    	
    	this.setPageParams(state.pageParams);
    	this.scene.scale = state.scale ? state.scale : defaultScale;
    	
    	for(var i in state.elements)
    	{
    		var eleState = state.elements[i];
    		var ele = eval("new " + eleState.className + "()");
    		ele.setState(eleState);
    		this.addElement(ele);    		
    	}
    	
    	if((state.selected >= 0) && (state.selected < this.elements.length))
    	{
    		this.setSelected(this.elements[state.selected]);
    	}
    	
    	isStateDirty = false;
    	this.scene.redraw();
    };
    
    this.saveCanvasAsImage = function(canvasElement, imageId, categoryId, onDone, onProgress)
    {        
    	var fileUploadWidget = $('<input type="file" name="files[]" data-url="file_upload_php/index.php?_image_id=' +  imageId + '&_category_id='+ categoryId +'">');
    	if(canvasElement.toBlob)
    	{
    		canvasElement.toBlob(function (blob) {
    			
            	fileUploadWidget.fileupload({
        		    dataType: 'json',
        	        done: function (e, data)
        	        { 
        	        	//options.result
        	        	if(onDone) { onDone(); } 
        	        },
        		    progressall: function (e, data) { if(onProgress) { onProgress(data.loaded / data.total); }}
        	    });
        	
            	fileUploadWidget.fileupload('send', {files: [blob]});    			
    		});
    	}
        else
        {
        	alert("File upload is not supported by your browser yet");
        	if(onDone) onDone();
        }
    };
    
    this.uploadImageFiles = function(file, categoryId, onDone, onProgress)
    {        
    	var fileUploadWidget = $('<input type="file" name="files[]" data-url="file_upload_php/index.php?_image_id=-1&_category_id='+ categoryId +'">');
    	fileUploadWidget.fileupload({
		    dataType: 'json',
	        done: function (e, data)
	        { 
	        	if(onDone) { onDone(); } 
	        },
		    progressall: function (e, data) { if(onProgress) { onProgress(data.loaded / data.total); }}
	    });
	
    	fileUploadWidget.fileupload('send', {files: [file]});    			
    };

    reset();
    this.setPageParams(pageParams);
    this.scene.redraw();
};

System.PAGE_TYPE_CIRCLE = 1;
System.PAGE_TYPE_BOX = 2;


var _system = {
    onInit:function(canvasId, propContainerId, listContainerId, width, height)
    {
        _system = new System(canvasId, propContainerId, listContainerId, width, height); 
    }
};

