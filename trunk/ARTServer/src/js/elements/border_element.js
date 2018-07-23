function BorderElement()
{   
	this.common_init();
	this.className = "BorderElement";
	
	var me = this;
    var size = 15.0;
    var radius = 0.0;
    var currentScene = null;
    var drawable = new PatternMapDrawable(null, null, size, 1.0, null);
	var highlightA = new PatternMapDrawable(null, new PatternHighlight(6,6), drawable.size, 1.0, "$highlight");
	var highlightB = new PatternMapDrawable(null, null, drawable.size, 1.0, "$highlight", "$background");

    var borderType = BorderElement.TYPE_BOX;    
    var selectedBorder = null;

	var isSelected = false;

    var widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    
    widget.visible = false;
    widget.setAngleVisible(false);
	highlightA.visible = false;
	highlightB.visible = false;

    highlightA.displayGroup = Scene.DISPLAY_GROUP_UI;
    highlightB.displayGroup = Scene.DISPLAY_GROUP_UI;
	
    var title;
    var showMore;
    
    var updateMap = function(x1, y1, x2, y2)
    {
        if(selectedBorder == null) return;
        if(x1 > x2) { var tmp = x1; x1 = x2; x2 = tmp; }        
        if(y1 > y2) { var tmp = y1; y1 = y2; y2 = tmp; }
        
        if(borderType == BorderElement.TYPE_ELLIPSE)
        {
            var width = x2 - x1;
            var height = y2 - y1;
            
            if(width == height)
            {
                drawable.map = new CircleMap( 
                    (x1 + x2) / 2, 
                    (y1 + y2) / 2, 
                    widget.angle,
                    widget.angle + Math.PI * 2, 
                    (width < height ? width : height) / 2 - drawable.size, 
                    0, 
                    selectedBorder.srcScale
                );
            }
            else
            {
                drawable.map = new EllipseMap(
                    x1 + drawable.size,
                    y1 + drawable.size,
                    width - drawable.size * 2,
                    height - drawable.size * 2,
                    widget.angle,
                    widget.angle + Math.PI * 2,
                    selectedBorder.srcScale
                );          
            }
        }
        else if(borderType == BorderElement.TYPE_CIRCLE)
        {
            var width = x2 - x1;
            var height = y2 - y1;
            
            drawable.map = new CircleMap( 
                (x1 + x2) / 2, 
                (y1 + y2) / 2, 
                widget.angle,
                widget.angle + Math.PI * 2, 
                (width < height ? width : height) / 2 - drawable.size, 
                0, 
                selectedBorder.srcScale
            );
        }
        else
        {
            drawable.map = roundedRectangleMap(
                x1 + drawable.size, 
                y1 + drawable.size, 
                x2 - drawable.size, 
                y2 - drawable.size, 
                radius, 
                selectedBorder.srcScale
            );          
        }
        highlightA.map = drawable.map;
        highlightB.map = drawable.map;
    };
    
    widget.onChange = function(sender)
    {
        updateMap(sender.x1, sender.y1, sender.x2, sender.y2);
                
    };
    
    
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
     
    widget.hitTest = function(params)
    {
        if(borderType == BorderElement.TYPE_BOX)
        {
            var bx = Math.min(widget.x1, widget.x2) + drawable.size;
            var by = Math.min(widget.y1, widget.y2) + drawable.size;            
            var lx = params.x - bx;
            var ly = params.y - by;         
            var width = Math.abs(widget.x2 - widget.x1) - drawable.size * 2;
            var height = Math.abs(widget.y2 - widget.y1) - drawable.size * 2;
            var padding = 5;
            var locRadius = radius;
            if(locRadius < 0) locRadius = 0;
            if(locRadius > width / 2) locRadius = width / 2;
            if(locRadius > height / 2) locRadius = height / 2;
            
            if(lx <= locRadius)
            {
                if(ly <= locRadius) //top left
                {
                    var r = Math.sqrt(Math.pow(lx - locRadius, 2) + Math.pow(ly - locRadius, 2));
                    if((r >= locRadius - padding) && (r <= locRadius + drawable.size + padding)) return true;
                }
                else if(ly >= height - locRadius) //bottom left
                {
                    var r = Math.sqrt(Math.pow(lx - locRadius, 2) + Math.pow(ly - (height - locRadius), 2));
                    if((r >= locRadius - padding) && (r <= locRadius + drawable.size + padding)) return true;                   
                }
                //middle left
                else if((lx <= padding) && (lx >= -drawable.size - padding)) return true;
            } 
            else if(lx >= width - locRadius)
            {
                if(ly <= locRadius) //top right
                {
                    var r = Math.sqrt(Math.pow(lx - (width - locRadius), 2) + Math.pow(ly - locRadius, 2));
                    if((r >= locRadius - padding) && (r <= locRadius + drawable.size + padding)) return true;
                }
                else if(ly >= height - locRadius) //bottom right
                {
                    var r = Math.sqrt(Math.pow(lx - (width - locRadius), 2) + Math.pow(ly - (height - locRadius), 2));
                    if((r >= locRadius - padding) && (r <= locRadius + drawable.size + padding)) return true;                   
                }
                else if((lx >= width - padding) && (lx <= width + drawable.size + padding)) return true;
            }
            else
            {
                //middle top
                if((ly <= padding) && (ly >= -drawable.size - padding)) return true;
                else if((ly >= height - padding) && (ly <= height + drawable.size + padding)) return true;
            }
        }
        else
        {
            var lx = params.x - Math.min(widget.x1, widget.x2);
            var ly = params.y - Math.min(widget.y1, widget.y2);
            var width = Math.abs(widget.x2 - widget.x1);
            var height = Math.abs(widget.y2 - widget.y1);
            
            if((width > 0) && (height > 0))
            {
                var centerXY; 
                 
                if(width > height)
                {
                    centerXY = height / 2;
                    lx *= height / width; 
                }
                else
                {
                    centerXY = width / 2;
                    ly *= width / height; 
                }
                
                var r = Math.sqrt(Math.pow(lx - centerXY, 2) + Math.pow(ly - centerXY, 2));
                if((r <= centerXY + 5) && (r >= centerXY - drawable.size - 5)) return true;
            }
        }
        
        return false;
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

    this.getEdgeRadius = function() { return radius; };
    this.setEdgeRadius = function(value) 
    {       
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            if(value < 0) value = 0;
            if(radius == value) return;
            
            radius = value;
            updateMap(widget.x1, widget.y1, widget.x2, widget.y2);
        }
    };

    this.getType = function() { return borderType; };
    this.setType = function(newType)
    {
        if(borderType == newType) return;
        borderType = newType;
        if(drawable.map != null)
        {
            updateMap(widget.x1, widget.y1, widget.x2, widget.y2);  
        } 
    };

    
    this.getBorder = function(){ return selectedBorder; };
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
    
    this.getFGColor = function() { return drawable.getFGColor(); };
    this.setFGColor = function(value) { drawable.setFGColor(value); };

    this.getVisible = function() { return drawable.visible; };
    this.setVisible = function(value) { drawable.visible = value; };

    this.setPosition = function(x1, y1, x2, y2)
    {
        widget.x1 = x1;
        widget.y1 = y1;
        widget.x2 = x2;
        widget.y2 = y2;

        updateMap(x1, y1, x2, y2);       	
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

    
    
  
    
    this.setState = function(state)
    {
    	this.common_setState(state);
    	
    	this.setBorder(BorderElement.getBorderFromDescriptor(state.border));		
		
    	this.setEditAllowMove(state.editAllowMove);
		this.setType(state.type);    	
    	this.setSize(state.size);
    	this.setEdgeRadius(state.edgeRadius);
    	this.setFGColor(state.fgColor);
    	this.setVisible(state.visible === undefined ? true : state.visible);
    	
    	this.setPosition(
    		state.position.x1, state.position.y1,
    		state.position.x2, state.position.y2
    	);
    	
    	showMore = state.showMore;
    	title = state.title;    	
    };
        

    this.setPosition(0,0, 100, 100);
    this.setBorder(BorderElement.BORDERS[1]);
}

BorderElement.prototype = _prototypeElement;

BorderElement.TYPE_BOX = 0;
BorderElement.TYPE_ELLIPSE = 1;
BorderElement.TYPE_CIRCLE = 2;

BorderElement.getBorderFromDescriptor = function(desc)
{
	if(desc)
	{
		if(desc.id)
		{
			for(var i in BorderElement.BORDERS)
			{
				if(BorderElement.BORDERS[i].id == desc.id)
				{
					return BorderElement.BORDERS[i];
				}
			}
		}
		
	}	
	return null;
};

BorderElement.BORDERS = [
	{
		id: "solid",
		name: "Solid",
		srcScale: 1.0, 
		make: function()
		{
			return new PatternLines([{distance: 0, size: 1, corner:'edge'}]);
		},
		descriptor : { id: "solid" }
	},
	{
		id: "fill",
		name: "Fill",
		srcScale: 1.0, 
		make: function()
		{
			return new PatternFill();
		},
		descriptor : { id: "fill" }
	},
	{
        id: "stars",
        name: "Stars",
        srcScale: 0.8, 
        make: function() { return new PatternStars(); },
        descriptor : { id: "stars" }
    },
    {
        id: "stripes",
        name: "Stripes",
        srcScale: 0.8, 
        make: function() { return new PatternStripes(); }, 
        descriptor : { id: "stripes" }
    },
    {
        id: "stripes2",
        name: "Stripes 2",
        srcScale: 1.0, 
        make: function() { return new PatternStripes2(); }, 
        descriptor : { id: "stripes2" }
    },
    {
        id: "rope",
        name: "Rope",
        srcScale : 1.0, 
        make: function() { return new PatternRope(); }, 
        descriptor : { id: "rope" }
    },
    {
        id: "dotted",
        name: "Dotted",
        srcScale: 1.0, 
        make: function() { return new PatternDotted(); }, 
        descriptor : { id: "dotted" }
    },
    {
        id: "hash",
        name: "Hash",
        srcScale: 1.0, 
        make: function() { return new PatternHash(); }, 
        descriptor : { id: "hash" }
    },
    {
        id: "lines1",
        name: "Lines 1",
        srcScale: 1.0, 
        make: function()
        {
            return new PatternLines([
                {distance: 0, size: 2, corner:'edge'},
                {distance: 4, size: 1, corner:'edge'},
                {distance: 6, size: 1, corner:'edge'}
            ]);
        },
        descriptor : { id: "lines1" }
    },
    {
        id: "lines2",
        name: "Lines 2",
        srcScale: 1.0, 
        make: function()
        {
            return new PatternLines([
                {distance: 0, size: 2, corner:'edge'},
                {distance: 4, size: 1, corner:'indent', radius: 3},
                {distance: 6, size: 1, corner:'indent', radius: 3},
                {distance: 14, size: 1, corner:'indent', radius: 10}
            ]);
        },
        descriptor : { id: "lines2" }
    }
];

