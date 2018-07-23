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
	
	if(System.ENABLE_SAVE_STATE)
	{
		setInterval( function() {
			if(isStateDirty) system.saveState();
		}, 1);
	}

	var reset = function()
	{
		if(system.ui)system.ui.clear();
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
			{ json:  json, inkColor: this.scene.inkColor }
		);		
	};
	
	this.setDesignJSON = function(json)
	{
		$.post(
			'design_part/set_design_json.php', 
			{ json:  json, inkColor: this.scene.inkColor }
		);		
	};
	
	this.setDesignSCL = function(scl, callback)
	{
		var o = $.post(
			'design_part/set_design_scl.php', 
			{ scl:  scl }
		);
		
		if(callback)
		{
			o.done( function(data) {
				callback(true, data);
			});
			
			o.fail( function() {
				callback(false);
			});
		}
	};
	
	
	this.getImageElementSrcURL = function(id, color)
	{
		if(System.ENABLE_HD_IMAGES)
		{
			return "design_part/get_image.php?id=" + id;
		}
		else
		{
			return "design_part/get_image.php?id=web_" + color.toLowerCase() + "." + id;
		}
	};

	this.changeInkColour = function(color)
	{
		if(this.scene.inkColor != color)
		{
			this.scene.inkColor = color;
			this.saveState();
			_system.scene.redraw();			
		}
		
		$("#ink_color_palette").children(".color_selector_box_selected").attr("class","color_selector_box");
		$("#ink_color_palette").children("#" + color).attr("class","color_selector_box_selected");
	};
		

	var SAVED_STATE_MAX = 40;
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
		
		if(!dontSendToServer &&  System.ENABLE_SAVE_STATE) this.setDesignJSON(savedStateData[savedStateData.length-1]);
		
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
		var width = params.width;
		var height = params.height;
		var aspectRatio = Math.round((height / width) * 10000);
		
    	if(System.ASPECT_RATIO && (System.ASPECT_RATIO != aspectRatio))
    	{
    		if(aspectRatio > System.ASPECT_RATIO)
    		{
    			width = Math.round(height / (System.ASPECT_RATIO / 10000));
    		}
    		else
    		{
    			height = Math.round(width * (System.ASPECT_RATIO / 10000));    			
    		}
    	}
    	
    	pageParams.width = width;
    	pageParams.height = height;

		var fgLayer = system.scene.getLayer(Scene.LAYER_FOREGROUND); 
		if((params.type == System.PAGE_TYPE_CIRCLE) && ((width / height) == 1))
		{			
			fgLayer.clipMask = new CircularClipMask(width, height);
		}
		else // if(params.type == System.PAGE_TYPE_BOX)
		{			
			fgLayer.clipMask = new RectangularClipMask(width, height);
		}
	};
	
	this.isReady = function()
	{
		for(i in this.elements)
		{
			if(!this.elements[i].isReady()) return false;
		}
		return true;
	};
	
	this.invokeWhenReady = function(callback)
	{
		if(this.isReady())
		{
			callback();
		}
		else
		{
			var readyTimer = {id:null};
			var me = this;
			readyTimer.id = setInterval( function() {
				if(me.isReady())
				{
					callback();
					clearInterval(readyTimer.id);				
				}
			}, 1);
		}
	};
	
	if((listContainerId != null) && (propContainerId != null))
	{
		this.ui =  new UIPanel(propContainerId,listContainerId);	
	}

	this.addElement = function(element, suppressUpdate)
	{
        this.elements.push(element);
        if(this.ui) this.ui.addGroup(element.getUIControlGroup(), suppressUpdate);
        element.setScene(this.scene);        
        if(!suppressUpdate) this.scene.redraw();
        isStateDirty = true;
        return element;
    };

	this.removeElement = function(element)
	{
		for(i in this.elements)
		{
			if(this.elements[i] === element)
			{
				if(this.ui)this.ui.removeGroup(element.getUIControlGroup());
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
        if(this.ui)this.ui.selectGroup(null);
        //isStateDirty = true;
        selectedElement = true;
    };  
    
    
    this.setSelected = function(element)
    {        
        for(var i in this.elements) this.elements[i].setSelected(false);
        element.setSelected(true);
        selectedElement = element;
        if(this.ui)this.ui.selectGroup(element.getUIControlGroup());
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
    		pageParams		: this.getPageParams(),
    		scene			: this.scene.getState()
    		//scale			: this.scene.scale
    	};
    };
    
    this.setState = function(state)
    {
    	//TODO: This function should notify the UIPanel that it should not rebuild the entire UI every time a new control is added while doing this
    	var defaultScale = this.scene.scale;    	
    	reset();
    	if(state.scene) this.scene.setState(state.scene);
    	this.setPageParams(state.pageParams);
    	this.scene.scale = state.scale ? state.scale : defaultScale;
    	
    	for(var i in state.elements)
    	{
    		var eleState = state.elements[i];
    		var ele = eval("new " + eleState.className + "()");
    		ele.setState(eleState);
    		this.addElement(ele, true);    		
    	}
    	if(this.ui)this.ui.updateHTML();
    	
    	if((state.selected >= 0) && (state.selected < this.elements.length))
    	{
    		this.setSelected(this.elements[state.selected]);
    	}
    	    	
    	isStateDirty = false;
    	this.scene.redraw();
    	
    	this.changeInkColour(this.scene.inkColor);
    };
    
    this.saveCanvasAsImage = function(canvasElement, imageId, categoryId, onDone, onProgress)
    {        
    	if(canvasElement.toBlob)
    	{
        	var fileUploadWidget = $('<input type="file" name="files[]" data-url="file_upload_php/index.php?_image_id=' +  imageId + '&_category_id='+ categoryId +'">');
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
        	$.post(
        		"design_part/ie_canvas_upload.php", 
        		{	
        			id: imageId,
        			data_url: canvasElement.toDataURL()
        		},
        		function(data) {
                	if(onDone) onDone();
        		}
        	);
        }
    };
    
    this.uploadImageFiles = function(file, categoryId, onDone, onProgress)
    {        
    	var fileUploadWidget = $('<input type="file" name="files[]" data-url="file_upload_php/index.php?_image_id=-1&_category_id='+ categoryId +'">');
    	fileUploadWidget.fileupload({
		    dataType: 'json',
	        done: function (e, data)
	        { 
	        	var dbImageId = data.result[0].dbImageId;
	        	if(onDone && (dbImageId >= 0)) { onDone(dbImageId); } 
	        },
		    progressall: function (e, data) { if(onProgress) { onProgress(data.loaded / data.total); }}
	    });
	
    	fileUploadWidget.fileupload('send', {files: [file]});    			
    };

    reset();
    this.setPageParams(pageParams);
    this.scene.redraw();
};

System.PAGE_TYPE_BOX = 1;
System.PAGE_TYPE_CIRCLE = 2;
System.ASPECT_RATIO = null;
System.ENABLE_SAVE_STATE = true;
System.ENABLE_HD_IMAGES = false;

var _system = {
    onInit:function(canvasId, propContainerId, listContainerId, width, height)
    {
        _system = new System(canvasId, propContainerId, listContainerId, width, height); 
    }
};

