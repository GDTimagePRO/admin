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
	return o.type + "\n" + o.id + "\n" + o.color;   
};


function ImageElement()
{
	this.common_init();
	this.className = "ImageElement";
	this.sizeToBox = true;
    var me = this;
    
    var highlightAPadding = 7;
    var imageSrc = null;
    var imageColor = "000000";
    var isReadyEvents = 0;
    
    this.isReady = function(){ return isReadyEvents == 0; };
    
	var updateSizeOnLoad = false;    
    this.updateSizeOnLoad = function() { return updateSizeOnLoad; };
	
    var currentScene = null;
    this.drawable = new ImageDrawable(null, 0, 0, 0, 0);
//    this.drawable.onBeforeDraw = function(params)
//    {
//    	if(params.palette.ink != imageColor)
//    	{
//    		imageColor = params.palette.ink;
//    		me.loadImage(imageSrc, updateSizeOnLoad);
//    	}
//    };
    
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
        
        
        
        if (maintainAspectRatio)
        {
	        var imgDisplayWidth = 0;
	        var imgDisplayHeight = 0;
	        
	        if(imageWidth != 0 && imageHeight != 0)
        	{
				if (me.sizeToBox) {
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
					me.drawable.x = x1 + (width - imgDisplayWidth) / 2;
					me.drawable.y = y1 + (height - imgDisplayHeight) / 2;	        
					me.drawable.width = imgDisplayWidth;
					me.drawable.height = imgDisplayHeight;
				} else {
					var oldHeight = me.drawable.height;
					var oldWidth = me.drawable.width;
					if (oldHeight != height) {
						imgDisplayWidth = height * (imageWidth/imageHeight);
						imgDisplayHeight = height;
						var increase = (imgDisplayWidth - width) / 2;
						me.widget.x1 = me.widget.x1 - increase;
						me.widget.x2 = me.widget.x2 + increase;
					}else if (oldWidth != width) {
						imgDisplayWidth = width;
						imgDisplayHeight = width * (imageHeight/imageWidth);

						var increase = (imgDisplayHeight - height) / 2;
						me.widget.y1 = me.widget.y1 - increase;
						me.widget.y2 = me.widget.y2 + increase;
					} else if ((height/width) != (imageHeight/imageWidth)) {
						imgDisplayWidth = height * (imageWidth/imageHeight);
						imgDisplayHeight = height;
						var increase = (imgDisplayWidth - width) / 2;
						me.widget.x1 = me.widget.x1 - increase;
						me.widget.x2 = me.widget.x2 + increase;
					}
					if (imgDisplayWidth > 0 || imgDisplayHeight > 0) {
						me.drawable.x = x1 + (width - imgDisplayWidth) / 2;
						me.drawable.width = imgDisplayWidth;
						me.drawable.y = y1 + (height - imgDisplayHeight) / 2;
						me.drawable.height = imgDisplayHeight;
					} else {
						me.drawable.x = x1;
						me.drawable.y = y1;            
						me.drawable.width = width;
						me.drawable.height = height;
					}
					
				}
			}
        }
        else
    	{
            me.drawable.x = x1;
            me.drawable.y = y1;            
        	me.drawable.width = width;
            me.drawable.height = height;
    	}
		highlightA.x = me.drawable.x - highlightAPadding;
        highlightA.y = me.drawable.y - highlightAPadding;
        highlightA.width = me.drawable.width + highlightAPadding * 2;
        highlightA.height = me.drawable.height + highlightAPadding * 2;

        highlightB.x = x1;
        highlightB.y = y1;
        highlightB.width = width;
        highlightB.height = height;
    };
    
    this.widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    
    var oldAngle = 0;
    this.getAngle = function() { return this.drawable.angle; };
    this.setAngle = function(value)
    {
		if (value === undefined || value == null) value = 0;
    	this.widget.angle = value;
    	this.drawable.angle = value;
    	oldAngle = value;
    };
    
    this.setAngle(0);
    
    this.widget.visible = false;
    this.widget.setAngleVisible(true);
    highlightA.visible = false;
    highlightB.visible = false;

    highlightA.displayGroup = Scene.DISPLAY_GROUP_UI;
    highlightB.displayGroup = Scene.DISPLAY_GROUP_UI;
    
    var oldPosition = null;
    this.widget.onRelease = function(sender)
    {    	
		var newPosition = me.getPosition();
		if(	(oldPosition == null) ||
			(oldPosition.x1 != newPosition.x1) ||
			(oldPosition.y1 != newPosition.y1) ||
			(oldPosition.x2 != newPosition.x2) ||
			(oldPosition.y2 != newPosition.y2) ||
			(this.angle != oldAngle))
		{
			oldPosition = newPosition;
			me.setAngle(sender.angle);
			_system.setStateDirty();			
		}
    };
    
	this.widget.onChange = function(sender)
    {
        updatImageRect(sender.x1, sender.y1, sender.x2, sender.y2);
        me.setAngle(sender.angle);
        
        uiControlGroup.updateControl("centerX");
        uiControlGroup.updateControl("centerY");
        uiControlGroup.updateControl("width");
        uiControlGroup.updateControl("height");
        uiControlGroup.updateControl("size");
        uiControlGroup.updateControl("angle");
    };
    
    this.widget.hitTest = function(params)
    {
        var topLeft = this.getTopLeft();
        var bottomRight = this.getBottomRight();
        return ((topLeft.x <= params.x) && (bottomRight.x >= params.x) &&
                (topLeft.y <= params.y) && (bottomRight.y >= params.y));
    };

    
    this.widget.onSelect = function()
    {
        if (me.id && me.id != "" && me.id != "background_image") _system.setSelected(me);
    };
    
    this.getSelected = function(){ return isSelected; };
    this.setSelected = function(value)
    {
        isSelected = value;                
        this.widget.visible = value;
        highlightA.visible = value;
        highlightB.visible = value;
    };

    this.getEditAllowMove = function() { return this.widget.getEditAllowMove(); };
    this.setEditAllowMove = function(value)
    { 
    	if(this.widget.getEditAllowMove() == value) return;    	
    	this.widget.setEditAllowMove(value);
    	
    	if(currentScene)
    	{
        	if(this.widget.getEditAllowMove())
        	{
        		currentScene.getLayer(Scene.LAYER_WIDGETS).add(this.widget);
        		//currentScene.getLayer(Scene.LAYER_BACKGROUND).add(highlightB);            
        		currentScene.getLayer(Scene.LAYER_FOREGROUND).add(highlightA);            
        	}
        	else
        	{
                currentScene.getLayer(Scene.LAYER_WIDGETS).remove(this.widget);
                //currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(highlightB);
                currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(highlightA);
        	}
        	currentScene.redraw();
    	}
    };

    this.setImageId = function(id)
    {
    	this.loadImage(new ImageSrc(ImageSrc.TYPE_ID, id), false);	
    };
    
    this.getImageId = function(id)
    {
    	return this.getLoadedImageSrc().id;
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
    		this.drawable.image = null;
        	imageSrc = null;
    		if(currentScene) currentScene.redraw();
    		return;
    	}
    	
    	src.color = imageColor; 
    	imageSrc = src;
    	var imageColorCopy = imageColor; 

    	updateSizeOnLoad = useImageSize;
    	isReadyEvents = 1;
    	var defaultColor = (TI.colorModel == '1_BIT') ? "000000" : null;
    	ImageElement.loadThroughCache(_system.getImageElementSrcURL(imageSrc.id, defaultColor), function(image, cacheHit) {
            
			//handle potential race condition
			if((imageColorCopy != imageColor) || (src !== imageSrc)) return; 
			
            isReadyEvents = Math.max(isReadyEvents - 1, 0);

            me.drawable.image = image;
            image.descriptor = ImageSrc.toDescriptor(imageSrc);
            imageWidth = image.width;
            imageHeight = image.height;
            
            if(updateSizeOnLoad)
            {            	
            	var x = (this.widget.x1 + this.widget.x2 - image.width) / 2; 
            	var y = (this.widget.y1 + this.widget.y2 - image.height) / 2; 
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
    	
        if( (this.widget.x1 == x1) &&
            (this.widget.y1 == y1) &&
            (this.widget.x2 == x2) &&
            (this.widget.y2 == y2) )
        {
            return;
        }

        this.widget.x1 = x1;
        this.widget.y1 = y1;
        this.widget.x2 = x2;
        this.widget.y2 = y2;

        updatImageRect(x1, y1, x2, y2);
       	oldPosition = this.getPosition();
    };
    
    this.getPosition = function()
    {
        return {
            x1: this.widget.x1,
            y1: this.widget.y1,
            x2: this.widget.x2,
            y2: this.widget.y2
        };
    };
    
    this.getVisible = function() { return this.drawable.visible; };
    this.setVisible = function(value) { this.drawable.visible = value; };
    
    this.setScene = function(scene)
    {
        if(currentScene)
        {
            currentScene.getLayer(Scene.LAYER_WIDGETS).remove(this.widget);                         
            //currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(highlightB);            
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(highlightA);            
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(this.drawable);            
            currentScene = null;
        }
        
        if(scene)
        {
			scene.getLayer(Scene.LAYER_WIDGETS).add(this.widget);
			//scene.getLayer(Scene.LAYER_BACKGROUND).add(highlightB);            
			scene.getLayer(Scene.LAYER_FOREGROUND).add(highlightA);        		
            scene.getLayer(Scene.LAYER_FOREGROUND).add(this.drawable);           
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
    		visible			: this.getVisible(),
    		angle			: this.getAngle(),
    		
    		showMore		: uiControlGroup.showMore,    		
    		title			: uiControlGroup.title,

    		displayGroup	: this.drawable.displayGroup     		
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
    	this.setVisible(state.visible === undefined ? true : state.visible);
    	this.setAngle(state.angle === undefined ? 0 : state.angle);
    	
    	uiControlGroup.title = state.title;    	
    	this.loadImage(state.imageSrc, state.updateSizeOnLoad);
    	
    	this.drawable.displayGroup = state.displayGroup ? state.displayGroup : Scene.DISPLAY_GROUP_ANY;
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
                	if(isNaN(value)) return;
                	me.setWidth(value);
                	uiControlGroup.updateControl("size");
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
                	if(isNaN(value)) return;
                	me.setHeight(value);
                	uiControlGroup.updateControl("size");
                	if(currentScene)currentScene.redraw();
                }
            }
        );
            
        uiControlGroup.addControl(
                "size", 
                UIControl.TYPE_NUMBER,
                {
                	minValue : 50,
                	maxValue : 300,
                    onGet : function()
                    { 
                    	var size = me.getSize();

                    	if(_system)
                    	{
                        	var pw = _system.getPageWidth() / 2.0;
                        	var ph = _system.getPageHeight() / 2.0;
                        	var pageSize = Math.sqrt(ph * ph + pw * pw) / 2.0;
                        	size = (size / pageSize) * 100; 
                    	}
                    	
                    	return Math.round(size);
                    },
                    onSet : function(value)
                    {
                    	if(isNaN(value)) return;

                    	
                    	if(_system)
                    	{
                        	var pw = _system.getPageWidth() / 2.0;
                        	var ph = _system.getPageHeight() / 2.0;
                        	var pageSize = Math.sqrt(ph * ph + pw * pw) / 2.0;
                        	value = (value / 100) * pageSize; 
                    	}

                    	
                    	me.setSize(value);
                        uiControlGroup.updateControl("width");
                        uiControlGroup.updateControl("height");
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
            	minValue : -400,
            	maxValue : 400,            	
                onGet : function() { return Math.round(me.getCenterX()); },
                onSet : function(value)
                {
                	if(isNaN(value)) return;
                	me.setCenterX(value);
                	if(currentScene)currentScene.redraw();
                }
            }
        );
            
        
        uiControlGroup.addControl(
            "centerY", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : -375,
            	maxValue : 375,            	
                onGet : function() { return -Math.round(me.getCenterY()); },
                onSet : function(value)
                {
                	if(isNaN(value)) return;
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
                	TI.imageSelectDialog.show(oldId ,function(id) {    					
    					me.loadImage(new ImageSrc(ImageSrc.TYPE_ID, id), false);
    					_system.saveState();    					
    				});
                	
                	return false;
                }
            }
        );

		uiControlGroup.addControl(
        	"image", 
            UIControl.TYPE_TEXT,
            {
        		onGet : function() { if (imageSrc) {
					return imageSrc.id;
				} else {
					return "";
				}}
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

        uiControlGroup.addControl(
            "visibility", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Always"}, {name:"Design Only"}],
                onGet : function() { return me.drawable.displayGroup == Scene.DISPLAY_GROUP_ANY ? 0 : 1; },
                onSet : function(index, item)
                { 
                    if(index == 0)
                    {
                    	me.drawable.displayGroup = Scene.DISPLAY_GROUP_ANY;
                    }
                    else
                    {
                    	me.drawable.displayGroup = Scene.DISPLAY_GROUP_UI;
                    }
                }
            }
        );
        
        uiControlGroup.addControl(
            "visible", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Visible"}, {name:"Hidden"}],
                onGet : function() { return me.getVisible() ? 0 : 1; },
                onSet : function(index, item)
                {
                	me.setVisible(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "angle", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : -180,
            	maxValue : 180,            	
                onGet : function() 
                { 
                	var a = 360 - Math.round(me.getAngle() * 180/Math.PI);                    	
                	while(a <= -180) a += 360;
                	while(a > 180) a -= 360;
                	return a; 
                },
                onSet : function(value)
                {
                	if(isNaN(value)) return;
                	me.setAngle(-value * Math.PI / 180);
                	if(currentScene)currentScene.redraw();
                }
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

	cachedImage.image.onerror = function()
	{
		alert("Error loading image " + src);
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