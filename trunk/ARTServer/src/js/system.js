
function htmlEncode(text)
{
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function ResourceId(group, path, type)
{
	this.group = group;
	this.path = path;
	this.type = type;
}

ResourceId.parse = function(id)
{
	
};


ResourceId.PARAM_PREFIX = "_@@(";
ResourceId.PARAM_SUFFIX = ")";
ResourceId.PARAM_GRADIENT = "GR1_";
ResourceId.PARAM_LINEAR_TINT = "LTNT_";
ResourceId.PARAM_MIRROR_HORIZONTAL = "MIRH";
ResourceId.PARAM_MIRROR_VERTICAL = "MIRV";
ResourceId.PARAM_MONOCHROME = "MONOC";

ResourceId.getParamBounds = function(path, inner)
{
	if(!path) return null;
	var iStart = path.lastIndexOf(ResourceId.PARAM_PREFIX);
	if(iStart < 0) return null;
	iEnd = path.indexOf(ResourceId.PARAM_SUFFIX, iStart + ResourceId.PARAM_PREFIX.length);
	if(iEnd < 0) return null;
	if(inner)
	{
		return [iStart + ResourceId.PARAM_PREFIX.length, iEnd];
	}
	else
	{
		return [iStart, iEnd + ResourceId.PARAM_SUFFIX.length];
	}
};

ResourceId.getParams = function(path)
{
	var bounds = ResourceId.getParamBounds(path, true);
	if(!bounds) return null;
	return path.substring(bounds[0], bounds[1]).split(",");
};

ResourceId.getPathWithoutParams = function(path)
{	
	var bounds = ResourceId.getParamBounds(path, false);
	if(!bounds) return path;
	return path.substring(0, bounds[0]) + path.substring(bounds[1]);
};

ResourceId.setParams = function(path, params)
{
	var paramString = ResourceId.PARAM_PREFIX;
	var paramCount = 0;
	for(var i=0; i<params.length; i++)
	{
		if(params[i] == null) continue;
		if(paramCount > 0) paramString+= ',';
		paramString += params[i];
		paramCount++;
	}	
	paramString += ResourceId.PARAM_SUFFIX;
	
	path = ResourceId.getPathWithoutParams(path);
	if(paramCount < 1) return path;
	
	var iPos = path.lastIndexOf('.');	
	if(iPos < 0) return path + paramString;
	
	return path.substring(0,iPos) + paramString + path.substring(iPos);
};


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
	this.pageViewWidth = null;
	this.pageViewHeight = null;
	
	this.getPageType = function() { return pageParams.type; };
	this.setPageType = function(value) { pageParams.type = value; };

	

	var reset = function()
	{
		if(system.ui)system.ui.clear();
		system.elements = [];	
		if(system.scene) system.scene.setDrawingEnabled(false);
		
		var newScene = new Scene(null, width, height);
		if(system.scene)
		{
			newScene.setRenderRequestEnabled(system.scene.getRenderRequestEnabled());		
			newScene.colorModel = system.scene.colorModel;
			newScene.setLayer(Scene.LAYER_OVERLAY, system.scene.getLayer(Scene.LAYER_OVERLAY).clone());
		}
		system.scene = newScene; 
	};	
	
	
	this.getProductName = function()
	{ 
		return "TODO: Add this!!"; 
	};

	
	
	
	this.getImageElementSrcURL = function(id, color)
	{
		if(System.ENABLE_HD_IMAGES)
		{
			return System.IMAGE_SERVICE + "?id=" + encodeURIComponent(id);
		}
		else
		{			
			if(color)
			{
				return System.IMAGE_SERVICE + "?id=" + encodeURIComponent("web_" + color.toUpperCase() + "." + id);
			}
			else
			{
				return System.IMAGE_SERVICE + "?id=" + encodeURIComponent("web." + id);
			}
		}
	};

	this.getPaletteColor = function(colorId)
	{
		if(!(colorId in this.scene.colors)) return null;		
		var color = this.scene.colors[colorId];
		return {name: color.name, value:color.value};
	};
	
	this.setPaletteColour = function(id, name, value, saveState)
	{
		if((id in this.scene.colors) && (this.scene.colors[id].value == value) && (this.scene.colors[id].name == name))
		{
			return;
		}
		this.scene.colors[id] = {name:name, value:value};
		_system.scene.redraw();			
		if(saveState) this.saveState(true);
	};
	

		

	var SAVED_STATE_MAX = 40;
	var savedStateData = [];
	var savedStateSelected = -1;
	this.clearStateHistory = function()
	{
		savedStateData = [];
		savedStateSelected = -1;
	};
	
	
	


	this.getPageParams = function() { return pageParams; };
	this.setPageParams = function(params)
	{
		pageParams = params;
		
		if(System.ASPECT_PAGE_TYPE != pageParams.type)
		{
			if(pageParams.type == System.PAGE_TYPE_CIRCLE)
			{
				pageParams.height = pageParams.width = Math.min(pageParams.width, pageParams.height);
			}
			else
			{
				var rad = Math.sqrt(Math.pow(pageParams.width / 2, 2) + Math.pow(pageParams.height / 2, 2));
				pageParams.height = pageParams.width = rad * 2;
			}
			
			pageParams.type = System.ASPECT_PAGE_TYPE; 
		}

		if(System.ASPECT_RATIO)
		{
			var ar = pageParams.height / pageParams.width;
			
			if(Math.abs(ar - System.ASPECT_RATIO) > 0.001)
			{
				if(System.ASPECT_RATIO < ar)
				{
					pageParams.width = pageParams.height / System.ASPECT_RATIO; 
				}
				else if(System.ASPECT_RATIO > ar)
				{
					pageParams.height = pageParams.width * System.ASPECT_RATIO; 
				}
			}
		}

		var width = pageParams.width;
		var height = pageParams.height;


		var fgLayer = system.scene.getLayer(Scene.LAYER_FOREGROUND); 
		if((pageParams.type == System.PAGE_TYPE_CIRCLE) && ((width / height) == 1))
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
    	};
    };
    
    this.setState = function(state)
    {
    	//TODO: This function should notify the UIPanel that it should not rebuild the entire UI every time a new control is added while doing this
    	var defaultScale = this.scene.scale;    	
    	reset();
    	this.scene.setDrawingEnabled(false);
    	
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
    	
    	this.scene.setDrawingEnabled(true);
    	this.scene.redraw();

    	//this.changeInkColour(this.scene.colors.ink.name, this.scene.colors.ink.value);
    	
    	//this.onSetState.raiseLater();
    	
    };
    
    
    
    

    reset();
    this.setPageParams(pageParams);
    //this.scene.redraw();
};

System.PAGE_TYPE_BOX = 1;
System.PAGE_TYPE_CIRCLE = 2;
System.ASPECT_RATIO = null;
System.ASPECT_PAGE_TYPE	= System.PAGE_TYPE_BOX;
System.ENABLE_SAVE_STATE = true;
System.ENABLE_HD_IMAGES = false;
System.IMAGE_SERVICE = "";
System.ACTIVE_DESIGN_INDEX = -1;


var _system = {
    onInit:function(canvasId, propContainerId, listContainerId, width, height)
    {
        //if($("#" + propContainerId).length == 0) propContainerId = null;
        //if($("#" + listContainerId).length == 0) listContainerId = null;

        _system = new System(canvasId, propContainerId, listContainerId, width, height); 
    }
};

