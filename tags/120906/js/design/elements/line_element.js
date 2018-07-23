
function LineElement()
{   
    var me = this;
    var currentScene = null;
    var drawable = new LineDrawable( 0, 0, 0, 0, 2);
	var highlightA = new LineDrawable( 0, 0, 0, 0, drawable.lineWidth + 12, "$highlight"); 
	var highlightB = new LineDrawable( 0, 0, 0, 0, drawable.lineWidth, "$highlight"); 

	var isSelected = false;
    
    var onChange = function(sender)
    {
		me.setPosition(
			sender.x1,
			sender.y1,
			sender.x2, 
			sender.y2
		);
    };
    
    var widget = new RectangleWidget(0, 0, 0, 0, 0, onChange, null);
    widget.visible = false;
	highlightA.visible = false;
	highlightB.visible = false;

    widget.hitTest = function(params)
    {
        var size = 10;
        var topLeft = widget.getTopLeft();
        if((params.x < topLeft.x - size) || (params.y < topLeft.y - size)) return false;
        
        var bottomRight = widget.getBottomRight();
        if((params.x > bottomRight.x + size) || (params.y > bottomRight.y + size)) return false;
        
        var w1 = params.x-widget.x1;
        var h1 = params.y-widget.y1;

        var a = Math.atan2(widget.y2-widget.y1, widget.x2-widget.x1) - Math.atan2(h1, w1);      
        var h = Math.sqrt(w1*w1 + h1*h1);
        var d = Math.abs(Math.sin(a) * h);
        
        //console.log("a: " + Math.round(a * 180 / Math.PI) + "   h: " + h + "   d: " + d);
        return d < size;
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

    this.setPosition = function(x1, y1, x2, y2)
    {
        drawable.x1 = x1;
        drawable.y1 = y1;
        drawable.x2 = x2;
        drawable.y2 = y2;
        
        widget.x1 = x1;
        widget.y1 = y1;
        widget.x2 = x2;
        widget.y2 = y2;

		highlightA.x1 = drawable.x1; 
        highlightA.y1 = drawable.y1; 
        highlightA.x2 = drawable.x2; 
        highlightA.y2 = drawable.y2; 

		highlightB.x1 = drawable.x1; 
        highlightB.y1 = drawable.y1; 
        highlightB.x2 = drawable.x2; 
        highlightB.y2 = drawable.y2; 
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
	    	type:"Line",
        	element: this        	
        });
	}
    
    this.setPosition(100,100, 200, 200);
    this.createUI();
}

