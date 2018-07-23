function BorderElement()
{   
    var me = this;
    var size = 15.0;
    var radius = 30.0;
    var currentScene = null;
    var drawable = new PatternMapDrawable(null, null, size, 1.0, null);
	var highlightA = new PatternMapDrawable(null, new PatternHighlight(6,6), drawable.size, 1.0, "$highlight");
	var highlightB = new PatternMapDrawable(null, null, drawable.size, 1.0, "$highlight", "$background");

    var borderType = BorderElement.TYPE_BOX;    
    var selectedBorder = null;

	var isSelected = false;

    var widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    widget.visible = false;
	highlightA.visible = false;
	highlightB.visible = false;

    
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
                    selectedBorder.srcScale
                );          
            }
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

    this.getEdgeRadius = function() { return radius; }
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
    }

    this.getType = function() { return borderType; }
    this.setType = function(newType)
    {
        if(borderType == newType) return;
        borderType = newType
        if(drawable.map != null)
        {
            updateMap(widget.x1, widget.y1, widget.x2, widget.y2);  
        }       
    }

    
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

    this.setPosition = function(x1, y1, x2, y2)
    {
        widget.x1 = x1;
        widget.y1 = y1;
        widget.x2 = x2;
        widget.y2 = y2;

        updateMap(x1, y1, x2, y2);
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
            if(value < 8) value = 8;
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

    var uiControlGroup = null;
    this.getUIControlGroup = function() { return uiControlGroup; };
    
    this.createUI = function()
    {
        uiControlGroup = new UIControlGroup({
        	type: "Border",
        	element: this
        });        
                
        uiControlGroup.addControl(
            "size", 
            UIControl.TYPE_NUMBER,
            {
                onGet : function() { return me.getSize(); },
                onSet : function(value)
                { 
                    me.setSize(value); 
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "edge radius", 
            UIControl.TYPE_NUMBER,
            {
                onGet : function() { return radius; },
                onSet : function(value)
                {                   
                    me.setEdgeRadius(value); 
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "pattern", 
            UIControl.TYPE_LIST,
            {
                items : BorderElement.BORDERS,
                onGet : function() { 
                    for(var i in BorderElement.BORDERS)
                    {
                        if(BorderElement.BORDERS[i] === selectedBorder) return i;
                    }
                    return 0; 
                },
                onSet : function(index, item)
                { 
                    me.setBorder(item);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "shape", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Box"}, {name:"Round"}],
                onGet : function() { return borderType },
                onSet : function(index, item)
                { 
                    me.setType(index);
                    if(currentScene)currentScene.redraw();
                }
            }
        );          
    }
    

    this.setPosition(0,0, 100, 100);
    this.createUI();
    this.setBorder(BorderElement.BORDERS[0]);
}

BorderElement.TYPE_BOX = 0;
BorderElement.TYPE_ELLIPSE = 1;

BorderElement.BORDERS = [
    {
        id: "stars",
        name: "Stars",
        srcScale: 0.8, 
        make: function() { return new PatternStars(); }
    },
    {
        id: "stripes",
        name: "Stripes",
        srcScale: 0.8, 
        make: function() { return new PatternStripes(); } 
    },
    {
        id: "stripes2",
        name: "Stripes 2",
        srcScale: 1.0, 
        make: function() { return new PatternStripes2(); } 
    },
    {
        id: "rope",
        name: "Rope",
        srcScale : 1.0, 
        make: function() { return new PatternRope(); } 
    },
    {
        id: "dotted",
        name: "Dotted",
        srcScale: 1.0, 
        make: function() { return new PatternDotted(); } 
    },
    {
        id: "hash",
        name: "Hash",
        srcScale: 1.0, 
        make: function() { return new PatternHash(); } 
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
        } 
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
        } 
    },
];

