
function LineElement()
{
	this.common_init();
	this.className = "LineElement";
	
    var me = this;
    var size = 2.0;    
    var currentScene = null;
    var drawable = new PatternMapDrawable(null, null, size, 1.0, null);
	var highlightA = new PatternMapDrawable(null, new PatternHighlight(6,6), drawable.size, 1.0, "$highlight");
	var highlightB = new PatternMapDrawable(null, null, drawable.size, 1.0, "$highlight", "$background");

	var isSelected = false;
    
    var widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    var selectedBorder = null;

    widget.visible = false;
    widget.setAngleVisible(false);
	
    widget.setPointAllowVisible("topMiddle", false);
    widget.setPointAllowVisible("topRight", false);
    widget.setPointAllowVisible("middleLeft", false);
    widget.setPointAllowVisible("middleMiddle", false);
    widget.setPointAllowVisible("middleRight", false);
    widget.setPointAllowVisible("bottomMiddle", false);
    widget.setPointAllowVisible("bottomLeft", false);
    widget.setPointAllowVisible("angle", false);

    
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
		me.setPosition(
			sender.x1,
			sender.y1,
			sender.x2, 
			sender.y2, 
			true
		);
			
    }; 

    var updateMap = function(x1, y1, x2, y2)
    {
    	var a = Math.atan2(y2-y1,x2-x1) + Math.PI / 2;
    	var ox = Math.cos(a) * drawable.size / 2;
    	var oy = Math.sin(a) * drawable.size / 2;
    	
    	drawable.map = new LineMap( x1 + ox, y1 + oy, x2 + ox, y2 + oy, 0 ); 
        highlightA.map = drawable.map;
        highlightB.map = drawable.map;
    };
    
    
    widget.hitTest = function(params)
    {
        var size = drawable.size / 2 + 5;
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

    this.setPosition = function(x1, y1, x2, y2, fromWidget)
    {
        widget.x1 = x1;
        widget.y1 = y1;
        widget.x2 = x2;
        widget.y2 = y2;
        updateMap(x1, y1, x2, y2);
    	
        if(!fromWidget)	oldPosition = this.getPosition();
        
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
    
    this.getFGColor = function() { return drawable.getFGColor(); };
    this.setFGColor = function(value) { drawable.setFGColor(value); };
    
    this.getVisible = function() { return drawable.visible; };
    this.setVisible = function(value) { drawable.visible = value; };
    
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

    var title;
    var showMore;
    
    this.getBorder = function(){ return border; };
    this.setBorder = function(border)
    { 
        drawable.pattern = border.make();
        highlightB.pattern = drawable.pattern; 
        selectedBorder = border;
        if(drawable.map == null)
        {
            updateMap(widget.x1, widget.y1, widget.x2, widget.y2);  
        }
    };

    this.getSize = function() { return drawable.size; };    
    this.setSize = function(value)
    { 
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            if(value < 1) value = 1;
            if(value > 100) value = 100;
            if(drawable.size == value) return;
            
            drawable.size = value;
			highlightA.size = drawable.size;      
			highlightB.size = drawable.size;      

            updateMap(widget.x1, widget.y1, widget.x2, widget.y2);
        }
    }; 
    
    this.getState = function()
    {
	    return this.common_getState({
    		className		: this.className,    		
    		editAllowMove	: this.getEditAllowMove(),
    		position		: this.getPosition(),
    		size			: this.getSize(),
    		border			: (selectedBorder ? selectedBorder.descriptor : null),
    		fgColor			: this.getFGColor(),
    		visible			: this.getVisible(),

    		showMore		: showMore,    		
    		title			: title    		
    	});
    };
    
    
    this.setState = function(state)
    {
    	this.common_setState(state);    	
    	
		var newBorder = BorderElement.getBorderFromDescriptor(state.border);
		if(!newBorder) newBorder = BorderElement.BORDERS[1];
    	this.setBorder(newBorder);		
    	this.setSize(state.size ? state.size : 2.0);
    	this.setFGColor(state.fgColor);
    	this.setVisible(state.visible === undefined ? true : state.visible);
    	
    	this.setEditAllowMove(state.editAllowMove);
    	this.setPosition(
    		state.position.x1, state.position.y1,
    		state.position.x2, state.position.y2
    	);
    	
    	showMore = state.showMore;
    	title = state.title;    	
    };
    
    this.setPosition(100,100, 200, 200);
    this.setBorder(BorderElement.BORDERS[0]);
}

LineElement.prototype = _prototypeElement;

