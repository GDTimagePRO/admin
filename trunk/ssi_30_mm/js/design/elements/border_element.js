function BorderElement()
{   
	this.common_init();
	this.className = "BorderElement";
	
	var me = this;
    var size = 15.0;
    var radius = 0.0;
    var currentScene = null;
    this.drawable = new PatternMapDrawable(null, null, size, 1.0, null);
	this.highlightA = new PatternMapDrawable(null, new PatternHighlight(6,6), this.drawable.size, 1.0, "$highlight");
	var highlightB = new PatternMapDrawable(null, null, this.drawable.size, 1.0, "$highlight", "$background");

    var borderType = BorderElement.TYPE_BOX;    
    var selectedBorder = null;

	var isSelected = false;

    this.widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    
    this.widget.visible = false;
    this.widget.setAngleVisible(false);
	this.highlightA.visible = false;
	highlightB.visible = false;

    this.highlightA.displayGroup = Scene.DISPLAY_GROUP_UI;
    highlightB.displayGroup = Scene.DISPLAY_GROUP_UI;
	
    var uiControlGroup = null;
    this.getUIControlGroup = function() { return uiControlGroup; };
    
    
    var updateControlTemplate = function()
    {
    	var borderType = me.getType();
    	
    	if(borderType == BorderElement.TYPE_BOX)
    	{
        	me.getUIControlGroup().template = UIPanel.TEMPLATE_BORDER_RECTANGLE_1;
    	}
    	else if(borderType == BorderElement.TYPE_ELLIPSE)
    	{
        	me.getUIControlGroup().template = UIPanel.TEMPLATE_BORDER_ELLIPSE_1;    		
    	}
    	else if(borderType == BorderElement.TYPE_CIRCLE)
    	{
        	me.getUIControlGroup().template = UIPanel.TEMPLATE_BORDER_CIRCLE_1;    		
    	}    	
    };
    
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
                me.drawable.map = new CircleMap( 
                    (x1 + x2) / 2, 
                    (y1 + y2) / 2, 
                    me.widget.angle,
                    me.widget.angle + Math.PI * 2, 
                    (width < height ? width : height) / 2 - me.drawable.size, 
                    0, 
                    selectedBorder.srcScale
                );
            }
            else
            {
                me.drawable.map = new EllipseMap(
                    x1 + me.drawable.size,
                    y1 + me.drawable.size,
                    width - me.drawable.size * 2,
                    height - me.drawable.size * 2,
                    me.widget.angle,
                    me.widget.angle + Math.PI * 2,
                    selectedBorder.srcScale
                );          
            }
        }
        else if(borderType == BorderElement.TYPE_CIRCLE)
        {
            var width = x2 - x1;
            var height = y2 - y1;
            
            me.drawable.map = new CircleMap( 
                (x1 + x2) / 2, 
                (y1 + y2) / 2, 
                me.widget.angle,
                me.widget.angle + Math.PI * 2, 
                (width < height ? width : height) / 2 - me.drawable.size, 
                0, 
                selectedBorder.srcScale
            );
        }
        else
        {
            me.drawable.map = roundedRectangleMap(
                x1 + me.drawable.size, 
                y1 + me.drawable.size, 
                x2 - me.drawable.size, 
                y2 - me.drawable.size, 
                radius, 
                selectedBorder.srcScale
            );          
        }
        me.highlightA.map = me.drawable.map;
        highlightB.map = me.drawable.map;
    };
    
    this.widget.onChange = function(sender)
    {
        updateMap(sender.x1, sender.y1, sender.x2, sender.y2);
        
        uiControlGroup.updateControl("angle");
        uiControlGroup.updateControl("centerX");
        uiControlGroup.updateControl("centerY");
        uiControlGroup.updateControl("width");
        uiControlGroup.updateControl("height");
        uiControlGroup.updateControl("radius");        
    };
    
    
    var oldPosition = null;
    this.widget.onRelease = function(sender)
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
     
    this.widget.hitTest = function(params)
    {
        if(borderType == BorderElement.TYPE_BOX)
        {
            var bx = Math.min(this.x1, this.x2) + me.drawable.size;
            var by = Math.min(this.y1, this.y2) + me.drawable.size;            
            var lx = params.x - bx;
            var ly = params.y - by;         
            var width = Math.abs(this.x2 - this.x1) - me.drawable.size * 2;
            var height = Math.abs(this.y2 - this.y1) - me.drawable.size * 2;
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
                    if((r >= locRadius - padding) && (r <= locRadius + me.drawable.size + padding)) return true;
                }
                else if(ly >= height - locRadius) //bottom left
                {
                    var r = Math.sqrt(Math.pow(lx - locRadius, 2) + Math.pow(ly - (height - locRadius), 2));
                    if((r >= locRadius - padding) && (r <= locRadius + me.drawable.size + padding)) return true;                   
                }
                //middle left
                else if((lx <= padding) && (lx >= -me.drawable.size - padding)) return true;
            } 
            else if(lx >= width - locRadius)
            {
                if(ly <= locRadius) //top right
                {
                    var r = Math.sqrt(Math.pow(lx - (width - locRadius), 2) + Math.pow(ly - locRadius, 2));
                    if((r >= locRadius - padding) && (r <= locRadius + me.drawable.size + padding)) return true;
                }
                else if(ly >= height - locRadius) //bottom right
                {
                    var r = Math.sqrt(Math.pow(lx - (width - locRadius), 2) + Math.pow(ly - (height - locRadius), 2));
                    if((r >= locRadius - padding) && (r <= locRadius + me.drawable.size + padding)) return true;                   
                }
                else if((lx >= width - padding) && (lx <= width + me.drawable.size + padding)) return true;
            }
            else
            {
                //middle top
                if((ly <= padding) && (ly >= -me.drawable.size - padding)) return true;
                else if((ly >= height - padding) && (ly <= height + me.drawable.size + padding)) return true;
            }
        }
        else
        {
            var lx = params.x - Math.min(this.x1, this.x2);
            var ly = params.y - Math.min(this.y1, this.y2);
            var width = Math.abs(this.x2 - this.x1);
            var height = Math.abs(this.y2 - this.y1);
            
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
                if((r <= centerXY + 5) && (r >= centerXY - me.drawable.size - 5)) return true;
            }
        }
        
        return false;
    };

    this.widget.onSelect = function()
    {
        _system.setSelected(me);
    };
    
    this.getSelected = function(){ return isSelected; };
	this.setSelected = function(value)
    {
        isSelected = value;                
        this.widget.visible = value;
        this.highlightA.visible = value;
        highlightB.visible = value;      
    };
	
    this.getEditAllowMove = function() { return this.widget.getEditAllowMove(); };
    this.setEditAllowMove = function(value)
    { 
    	this.widget.setEditAllowMove(value);
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
            updateMap(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);
        }
    };

    this.getType = function() { return borderType; };
    this.setType = function(newType)
    {
        if(borderType == newType) return;
        borderType = newType;
        updateControlTemplate();
        if(this.drawable.map != null)
        {
            updateMap(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);  
        } 
    };

    
    this.getBorder = function(){ return selectedBorder; };
    this.setBorder = function(border)
    { 
        this.drawable.pattern = border.make();
        highlightB.pattern = this.drawable.pattern; 
        selectedBorder = border;
        if(this.drawable.map == null)
        {
            updateMap(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);  
        }       
    };
    
    this.getFGColor = function() { return this.drawable.getFGColor(); };
    this.setFGColor = function(value) { this.drawable.setFGColor(value); };

    this.getVisible = function() { return this.drawable.visible; };
    this.setVisible = function(value) { this.drawable.visible = value; };

    this.setPosition = function(x1, y1, x2, y2)
    {
        this.widget.x1 = x1;
        this.widget.y1 = y1;
        this.widget.x2 = x2;
        this.widget.y2 = y2;

        updateMap(x1, y1, x2, y2);       	
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

    this.getSize = function() { return this.drawable.size; };    
    this.setSize = function(value)
    { 
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            if(value < 1) value = 1;
            if(value > 100) value = 100;
            if(this.drawable.size == value) return;
            
            this.drawable.size = value;
			this.highlightA.size = this.drawable.size;      
			highlightB.size = this.drawable.size;      

            updateMap(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);
        }
    };  
    
    this.setScene = function(scene)
    {
        if(currentScene)
        {
            currentScene.getLayer(Scene.LAYER_WIDGETS).remove(this.widget);             
            currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(highlightB);
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(this.highlightA);
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(this.drawable);
            currentScene = null;
        }
        
        if(scene)
        {
            scene.getLayer(Scene.LAYER_WIDGETS).add(this.widget);
            scene.getLayer(Scene.LAYER_BACKGROUND).add(highlightB);
            scene.getLayer(Scene.LAYER_FOREGROUND).add(this.highlightA);
            scene.getLayer(Scene.LAYER_FOREGROUND).add(this.drawable);           
            currentScene = scene;
        }
    };

    
    
    this.createUI = function()
    {
        uiControlGroup = new UIControlGroup({
        	type: "Border",
        	element: this
        });        
        
        updateControlTemplate();
        
        uiControlGroup.addControl(
            "size", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : 1,
            	maxValue : 50,
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
            	minValue : 0,
            	maxValue : 75,
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
                items : [{name:"Box"}, {name:"Ellipse"}, {name:"Circle"}],
                onGet : function() { return borderType; },
                onSet : function(index, item)
                { 
                    me.setType(index);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "radius",            
            UIControl.TYPE_NUMBER,
            {
            	minValue : 20,
            	maxValue : 200,
                onGet : function() { return me.getRadius(); },
                onSet : function(value)
                {                   
                	if(value <= me.getSize() + 5) value = me.getSize() + 5;
                	me.setRadius(value); 
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
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
            "title", 
            UIControl.TYPE_TEXT,
            {
                onGet : function() { return uiControlGroup.title; },
                onSet : function(value) { uiControlGroup.title = value; }
            }
        );        
        
        uiControlGroup.addControl(
            "colorFG", 
            UIControl.TYPE_TEXT,
            {
                onGet : function()
                {
                	return me.getFGColor();
                },
                onSet : function(value)
                { 
                	me.setFGColor(value);
                    if(currentScene)currentScene.redraw();
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
    };
    
    this.getState = function()
    {
	    return this.common_getState({
    		className		: this.className,    		
    		editAllowMove	: this.getEditAllowMove(),
    		type			: this.getType(),
    		size			: this.getSize(),
    		edgeRadius		: this.getEdgeRadius(),
    		position		: this.getPosition(),
    		border			: (selectedBorder ? selectedBorder.descriptor : null),
    		fgColor			: this.getFGColor(),
    		visible			: this.getVisible(),
    	    		
    		showMore		: uiControlGroup.showMore,    		
    		title			: uiControlGroup.title
    	});
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
    	
    	uiControlGroup.showMore = state.showMore;
    	uiControlGroup.title = state.title;    	
    };
        

    this.setPosition(0,0, 100, 100);
    this.createUI();
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

