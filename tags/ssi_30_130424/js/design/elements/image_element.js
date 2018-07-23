//TODO: If no image is loaded then draw a filler graphic
//TODO: Images should just be stored as a set of parameters like an ID

function ImageSrc(type, id)
{
	this.type = type;
	this.id = id;
	this.color = "";
}

ImageSrc.TYPE_ID = 1;
ImageSrc.toDescriptor = function(o)
{
	return o.type + " " + o.id + " " + o.color;   
};


function ImageElement()
{
	this.common_init();
	this.className = "ImageElement";
	
    var me = this;    
    
    var highlightAPadding = 6;
    var imageSrc = null;
    var imageColor = "black";
    var isReadyEvents = 0;
    
    this.isReady = function(){ return isReadyEvents == 0; };
    
	var updateSizeOnLoad = false;    
    this.updateSizeOnLoad = function() { return updateSizeOnLoad; };
	
    var currentScene = null;
    var drawable = new ImageDrawable(null, 0, 0, 0, 0);
    drawable.onBeforeDraw = function(params)
    {
    	if(params.palette.ink != imageColor)
    	{
    		imageColor = params.palette.ink;
    		me.loadImage(imageSrc, updateSizeOnLoad);
    	}
    };
    
	var highlightA = new RectDrawable(0, 0, 0, 0, "$highlight");
	var highlightB = new RectDrawable(0, 0, 0, 0, "$highlight");
	
	var isSelected = false;
	
	var maintainAspectRatio = false;	
	var imageWidth = 0;
	var imageHeight = 0;
	
	var uiControlGroup = null;
    this.getUIControlGroup = function() { return uiControlGroup; };   

    var updatImageRect = function(x1, y1, x2, y2)
    {
    	if(x1 > x2) { var tmp = x1; x1 = x2; x2 = tmp; }        
        if(y1 > y2) { var tmp = y1; y1 = y2; y2 = tmp; }

        var width = x2 - x1; 
        var height = y2 - y1; 
        
        highlightA.x = x1 - highlightAPadding;
        highlightA.y = y1 - highlightAPadding;
        highlightA.width = width + highlightAPadding * 2;
        highlightA.height = height + highlightAPadding* 2;

        highlightB.x = x1;
        highlightB.y = y1;
        highlightB.width = width;
        highlightB.height = height;
        
        if (maintainAspectRatio)
        {
	        var imgDisplayWidth = 0;
	        var imgDisplayHeight = 0;
	        
	        if(imageWidth != 0 && imageHeight != 0)
        	{
		        if((height/width) > (imageHeight/imageWidth))
		        {
		        	imgDisplayWidth = width;
		        	imgDisplayHeight = imageHeight * width/imageWidth;
		        }
		        else
		        {
		        	imgDisplayWidth = imageWidth * height/imageHeight;
		        	imgDisplayHeight = height;
		        }
        	}
	        
	        drawable.x = x1 + (width - imgDisplayWidth) / 2;
	        drawable.y = y1 + (height - imgDisplayHeight) / 2;	        
	        drawable.width = imgDisplayWidth;
	        drawable.height = imgDisplayHeight;
        }
        else
    	{
            drawable.x = x1;
            drawable.y = y1;            
        	drawable.width = width;
            drawable.height = height;
    	}
    };
    
    var widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);

    widget.visible = false;
    widget.setAngleVisible(false);
    highlightA.visible = false;
    highlightB.visible = false;

    highlightA.displayGroup = Scene.DISPLAY_GROUP_UI;
    highlightB.displayGroup = Scene.DISPLAY_GROUP_UI;
    
    var oldPosition = null;
    widget.onRelease = function(sender)
    {    	
		var newPosition = me.getPosition();
		if(	(oldPosition == null) ||
			(oldPosition.x1 != newPosition.x1) ||
			(oldPosition.y1 != newPosition.y1) ||
			(oldPosition.x2 != newPosition.x2) ||
			(oldPosition.y2 != newPosition.y2))
		{
			oldPosition = newPosition;
			_system.setStateDirty(); 	
		}
    };     
    
	widget.onChange = function(sender)
    {
        updatImageRect(sender.x1, sender.y1, sender.x2, sender.y2);
        uiControlGroup.updateControl("centerX");
        uiControlGroup.updateControl("centerY");
        uiControlGroup.updateControl("width");
        uiControlGroup.updateControl("height");
    };
    
    widget.hitTest = function(params)
    {
        var topLeft = widget.getTopLeft();
        var bottomRight = widget.getBottomRight();
        return ((topLeft.x <= params.x) && (bottomRight.x >= params.x) &&
                (topLeft.y <= params.y) && (bottomRight.y >= params.y));
    };

    
    widget.onSelect = function()
    {
        _system.setSelected(me);
    };
    
    this.getSelected = function(){ return isSelected; };
    this.setSelected = function(value)
    {
        isSelected = value;                
        widget.visible = value;
        highlightA.visible = value;
        highlightB.visible = value;
    };

    this.getEditAllowMove = function() { return widget.getEditAllowMove(); };
    this.setEditAllowMove = function(value)
    { 
    	widget.setEditAllowMove(value);
    	if(currentScene)currentScene.redraw();
    };

    this.loadImage = function(src, useImageSize)
    {
    	if((src != null) && src.substring)
    	{
    		var iStart = src.indexOf("id=");  
    		if(iStart > -1)
    		{
        		var iEnd = src.indexOf("&", iStart);
        		if(iEnd < 0) iEnd = src.length;
        		src = {
        			type: 1,
        			id: parseInt(src.substring(iStart+3,iEnd))
        		};
    		}
    		else src = null; 
    	}
    	
    	if(src == null)
    	{
    		isReadyEvents = 0;
    		drawable.image = null;
        	imageSrc = null;
    		if(currentScene) currentScene.redraw();
    		return;
    	}
    	
    	src.color = imageColor; 
    	imageSrc = src;
    	var imageColorCopy = imageColor; 

    	if(imageColor != "black")
    	{
    		isReadyEvents = 2;
    		ImageElement.loadThroughCache(_system.getImageElementSrcURL(imageSrc.id, "black"), function(image) {
    			if((imageColorCopy != imageColor) || (src !== imageSrc)) return; 
                isReadyEvents = Math.max(isReadyEvents - 1, 0);    			
    		});
    	}
    	else isReadyEvents = 1;
    		
    	
    	updateSizeOnLoad = useImageSize;
		ImageElement.loadThroughCache(_system.getImageElementSrcURL(imageSrc.id, imageColor), function(image, cacheHit) {
            
			//handle potential race condition
			if((imageColorCopy != imageColor) || (src !== imageSrc)) return; 
			
            isReadyEvents = Math.max(isReadyEvents - 1, 0);

            drawable.image = image;
            image.descriptor = ImageSrc.toDescriptor(imageSrc);
            imageWidth = image.width;
            imageHeight = image.height;
            
            if(updateSizeOnLoad)
            {            	
            	var x = (widget.x1 + widget.x2 - image.width) / 2; 
            	var y = (widget.y1 + widget.y2 - image.height) / 2; 
	            me.setPosition(
	                x, y, 
					x + image.width, y + image.height
	            );
            }
            else
            {
                var pos = me.getPosition();
                updatImageRect(pos.x1, pos.y1, pos.x2, pos.y2);                    
            }
            if(currentScene) currentScene.redraw();
		});
    };
    
	this.getLoadedImageSrc = function() { return imageSrc; };
    
    this.setPosition = function(x1, y1, x2, y2)
    {
    	updateSizeOnLoad = false;
    	
        if( (widget.x1 == x1) &&
            (widget.y1 == y1) &&
            (widget.x2 == x2) &&
            (widget.y2 == y2) )
        {
            return;
        }

        widget.x1 = x1;
        widget.y1 = y1;
        widget.x2 = x2;
        widget.y2 = y2;

        updatImageRect(x1, y1, x2, y2);
       	oldPosition = this.getPosition();        
    };
    
    this.getPosition = function()
    {
        return {
            x1: widget.x1,
            y1: widget.y1,
            x2: widget.x2,
            y2: widget.y2
        };
    };
    
    this.setScene = function(scene)
    {
        if(currentScene)
        {
            currentScene.getLayer(Scene.LAYER_WIDGETS).remove(widget);                         
            currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(highlightB);            
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(highlightA);            
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(drawable);            
            currentScene = null;
        }
        
        if(scene)
        {
            scene.getLayer(Scene.LAYER_WIDGETS).add(widget);
            scene.getLayer(Scene.LAYER_BACKGROUND).add(highlightB);            
            scene.getLayer(Scene.LAYER_FOREGROUND).add(highlightA);            
            scene.getLayer(Scene.LAYER_FOREGROUND).add(drawable);           
            currentScene = scene;
        }
    };
    
    this.getState = function()
    {
	    return this.common_getState({
    		className		: this.className,    		
    		editAllowMove	: this.getEditAllowMove(),
    		position		: this.getPosition(),
    		updateSizeOnLoad: updateSizeOnLoad,
    		maintainAspectRatio		: maintainAspectRatio,
    		imageSrc		: this.getLoadedImageSrc(),
    		
    		showMore		: uiControlGroup.showMore,    		
    		title			: uiControlGroup.title
    	});
    };
    
    this.setState = function(state)
    {
    	this.common_setState(state);
    	
    	this.setEditAllowMove(state.editAllowMove);
    	this.setPosition(
    		state.position.x1, state.position.y1,
    		state.position.x2, state.position.y2
    	);
    	
    	this.setMaintainAspectRatio(state.maintainAspectRatio);
    	
    	uiControlGroup.title = state.title;    	
    	this.loadImage(state.imageSrc, state.updateSizeOnLoad);
    };
    
    this.getMaintainAspectRatio = function(){ return maintainAspectRatio; };
    this.setMaintainAspectRatio = function(newMaintainAspectRatio)
    {
        maintainAspectRatio = newMaintainAspectRatio;
    };

    this.createUI = function()
    {
        uiControlGroup = new UIControlGroup({
        	type: "Image",
        	element: this       	
        });
        
        uiControlGroup.template = UIPanel.TEMPLATE_IMAGE_1;
        
        
        uiControlGroup.addControl(
            "width", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : 40,
            	maxValue : 400,            	
                onGet : function() { return Math.round(me.getWidth()); },
                onSet : function(value)
                {
                	me.setWidth(value);
                	if(currentScene)currentScene.redraw();                	
                }
            }
        );
        
        uiControlGroup.addControl(
            "height", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : 40,
            	maxValue : 400,            	
                onGet : function() { return Math.round(me.getHeight()); },
                onSet : function(value)
                {
                	me.setHeight(value);
                	if(currentScene)currentScene.redraw();
                }
            }
        );
            

        uiControlGroup.addControl(
            "maintainAspectRatio", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return maintainAspectRatio ? 0 : 1; },
                onSet : function(index, item)
                { 
                    me.setMaintainAspectRatio(index == 0);
                    var pos = me.getPosition();
                    updatImageRect(pos.x1, pos.y1, pos.x2, pos.y2);                    
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        
        uiControlGroup.addControl(
            "centerX", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : -200,
            	maxValue : 200,            	
                onGet : function() { return Math.round(me.getCenterX()); },
                onSet : function(value)
                {
                	me.setCenterX(value);
                	if(currentScene)currentScene.redraw();
                }
            }
        );
            
        
        uiControlGroup.addControl(
            "centerY", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : -175,
            	maxValue : 175,            	
                onGet : function() { return -Math.round(me.getCenterY()); },
                onSet : function(value)
                {
                	me.setCenterY(-value);
                	if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
        	"change image", 
            UIControl.TYPE_BUTTON,
            {
        		onGet : function() 
        		{ 
        			return "Change Image"; 
        		},
                onSet : function(value) 
                { 
                	var oldId = imageSrc ? imageSrc.id : -1;
                	_imageSelectDialog.show(oldId ,function(id) {    					
    					me.loadImage(new ImageSrc(ImageSrc.TYPE_ID, id), false);
    					_system.saveState();    					
    				});
                	
                	return false;
                }
            }
        );        
        
        uiControlGroup.addControl(
        	"title", 
            UIControl.TYPE_TEXT,
            {
        		onGet : function() { return uiControlGroup.title; },
                onSet : function(value) { uiControlGroup.title = value; }
            }
        );        
    };
    
    this.setPosition(100,100, 100, 100);
    this.createUI();
    
    //this.loadImage("images/_delete_me_.png", true);
}

ImageElement.prototype = _prototypeElement;

ImageElement.imageCache = [];

ImageElement.addCachedImage = function(src)
{
	var cachedImage = {
		src:src,
		onLoad:[],
		image:new Image(),
	};

	cachedImage.image.onload = function()
	{
		cachedImage.loaded = true;
		for(var i in cachedImage.onLoad)
		{
			cachedImage.onLoad[i](cachedImage.image, false);
		}
		delete cachedImage.onLoad;
	};
	
	cachedImage.image.src = src;

	ImageElement.imageCache.push(cachedImage);
	return cachedImage;
};

ImageElement.getCachedImage = function(src, addIfNotFound)
{
	for(var i in ImageElement.imageCache)
	{
		if(ImageElement.imageCache[i].src == src)
		{
			return ImageElement.imageCache[i];
		}
	}
	
	return addIfNotFound ? ImageElement.addCachedImage(src) : null;
};


ImageElement.loadThroughCache = function(src, onload)
{
	var cachedImage = ImageElement.getCachedImage(src, true);
	if(cachedImage.onLoad)
	{
		cachedImage.onLoad.push(onload);
	}
	else
	{
		onload(cachedImage.image, true);
	}
	return cachedImage.image;
};