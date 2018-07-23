//TODO: If no image is loaded then draw a filler graphic

function ImageElement()
{   
    var me = this;

    var highlightAPadding = 6;
    var src = "";
    var currentScene = null;
    var drawable = new ImageDrawable(null, 0, 0, 0, 0);
	var highlightA = new RectDrawable(0, 0, 0, 0, "$highlight");
	var highlightB = new RectDrawable(0, 0, 0, 0, "$highlight");
	
	var updateSizeOnLoad = false;    
    
	var isSelected = false;

    var updatImageRect = function(x1, y1, x2, y2)
    {
        if(x1 > x2) { var tmp = x1; x1 = x2; x2 = tmp; }        
        if(y1 > y2) { var tmp = y1; y1 = y2; y2 = tmp; }
        
        drawable.x = x1;
        drawable.y = y1;
        drawable.width = x2 - x1;
        drawable.height = y2 - y1;
        
        highlightA.x = drawable.x - highlightAPadding;
        highlightA.y = drawable.y - highlightAPadding;
        highlightA.width = drawable.width + highlightAPadding * 2;
        highlightA.height = drawable.height + highlightAPadding* 2;

        highlightB.x = drawable.x;
        highlightB.y = drawable.y;
        highlightB.width = drawable.width;
        highlightB.height = drawable.height;
    };
    
    var onChange = function(sender)
    {
        updatImageRect(sender.x1, sender.y1, sender.x2, sender.y2);
    };
    
    var widget = new RectangleWidget(0, 0, 0, 0, 0, onChange, null);
    widget.visible = false;
    highlightA.visible = false;
    highlightB.visible = false;
    
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
    
    this.getSelected = function(){ return isSelected; }
    this.setSelected = function(value)
    {
        isSelected = value;                
        widget.visible = value;
        highlightA.visible = value;
        highlightB.visible = value;
    }

    this.getEditAllowMove = function() { return widget.getEditAllowMove(); }
    this.setEditAllowMove = function(value)
    { 
    	widget.setEditAllowMove(value);
    	if(currentScene)currentScene.redraw();
    }

    this.loadImage = function(src, useImageSize)
    {
    	updateSizeOnLoad = useImageSize;
        var image = new Image();        
        image.onload = function()
        {
            drawable.image = image;
            if(updateSizeOnLoad)
            {            	
            	var x = (widget.x1 + widget.x2 - image.width) / 2; 
            	var y = (widget.y1 + widget.y2 - image.height) / 2; 
	            me.setPosition(
	                x, y, 
					x + image.width, y + image.height
	            );
            }
            if(currentScene) currentScene.redraw();
        };
        image.src = src;
    };
    
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
    
    var uiControlGroup = null;
    this.getUIControlGroup = function() { return uiControlGroup; };   
    
    this.createUI = function()
    {
        uiControlGroup = new UIControlGroup({
        	type: "Image",
        	element: this        	
        });
    }
    
    this.setPosition(100,100, 100, 100);
    this.createUI();
    
    //this.loadImage("images/_delete_me_.png", true);
}
