//TODO: Make the hit text just use a collision box so that it does not have size worth of paddint at the sides

function TextElement()
{ 
	this.common_init();
	this.className = "TextElement";
	
	this.scriptContainer.addBinding("onFilter");
	this.scriptContainer.addBinding("onSet");
	this.scriptContainer.addBinding("onGet");
	
	
    var me = this;
    var currentScene = null;    
    var drawable = new PatternMapDrawable(null, null, 20, 1.0, null);
	var highlightA = new PatternMapDrawable(null, new PatternHighlight(0,0), drawable.size, 1.0, "$highlight");
	var highlightB = new PatternMapDrawable(null, null, drawable.size, 1.0, "$highlight");
	
	var isSelected = false;
    
    var text = "";
    var font = "Verdana";
    var alignment = TextElement.ALIGN_CENTRE;
    var scaleToFit = false;
    var inverted = false;   
    var bold = false;
    var italic = false;
    var fanAngle = Math.PI * 2;
    var srcScale = 1.0;
    var verticalAlignment = 1;
    
    var textFormat = TextElement.FORMAT_NONE; 

    
    var textType = TextElement.TYPE_ELLIPSE;
    var widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    widget.angle = 0;
    
    widget.visible = false;
    widget.setAngleVisible(true);
	highlightA.visible = false;
	highlightB.visible = false;

    highlightA.displayGroup = Scene.DISPLAY_GROUP_UI;
    highlightB.displayGroup = Scene.DISPLAY_GROUP_UI;

	var uiControlGroup = null;
    this.getUIControlGroup = function() { return uiControlGroup; };
    

    var updateControlTemplate = function()
    {
    	var type = me.getType();
    	
    	if(type == TextElement.TYPE_LINE)
    	{
        	me.getUIControlGroup().template = UIPanel.TEMPLATE_TEXT_LINE_1;
    	}
    	else if(type == TextElement.TYPE_CIRCLE)
    	{
        	me.getUIControlGroup().template = UIPanel.TEMPLATE_TEXT_CIRCLE_1;    		
    	}
    	else if(type == TextElement.TYPE_ELLIPSE)
    	{
        	me.getUIControlGroup().template = UIPanel.TEMPLATE_TEXT_ELLIPSE_1;    		
    	}    	
    };
    

    var oldAngle = 0;
    var oldPosition = null;
    widget.onRelease = function(sender)
    {    	
		var newPosition = me.getPosition();
		if(	(oldPosition == null) ||
			(oldPosition.x1 != newPosition.x1) ||
			(oldPosition.y1 != newPosition.y1) ||
			(oldPosition.x2 != newPosition.x2) ||
			(oldPosition.y2 != newPosition.y2) ||
			(widget.angle != oldAngle))
		{
			oldPosition = newPosition;
			oldAngle = widget.angle; 
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
            sender.angle,
            true         
        );
        
        uiControlGroup.updateControl("angle");
        uiControlGroup.updateControl("centerX");
        uiControlGroup.updateControl("centerY");
        uiControlGroup.updateControl("width");
        uiControlGroup.updateControl("height");
        uiControlGroup.updateControl("radius");
        uiControlGroup.updateControl("length");        
    };  
    
    widget.hitTest = function(params)
    {
        if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
        {
            var lx = params.x - Math.min(widget.x1, widget.x2);
            var ly = params.y - Math.min(widget.y1, widget.y2);
            var width = Math.abs(widget.x2 - widget.x1);
            var height = Math.abs(widget.y2 - widget.y1);
                     
            if((width > 0) && (height > 0))
            {
                var centerX = width / 2;
                var centerY = height / 2;
                var maxR = Math.min(centerX, centerY);
                var mx = 1;
                var my = 1;
                
                if(textType != TextElement.TYPE_CIRCLE)
                {
                    if(width > height) mx = height / width;
                    else my = width / height;
                }
                
                var r = Math.sqrt(Math.pow((lx - centerX) * mx, 2) + Math.pow((ly - centerY) * my, 2));
                if((r <= maxR + 5) && (r >= maxR - drawable.size - 5)) return true;
            }
        }
        else
        {
            var size = drawable.size;
            var topLeft = widget.getTopLeft();
            if((params.x < topLeft.x - size) || (params.y < topLeft.y - size)) return false;
            
            var bottomRight = widget.getBottomRight();
            if((params.x > bottomRight.x + size) || (params.y > bottomRight.y + size)) return false;
            
            var w1 = params.x-widget.x1;
            var h1 = params.y-widget.y1;

            var a = Math.atan2(widget.y2-widget.y1, widget.x2-widget.x1) - Math.atan2(h1, w1);      
            var h = Math.sqrt(w1*w1 + h1*h1);
            var d = Math.sin(a) * h;
            
            return (d >=0) && (d < size);
        }
        
        return false;
    };

    widget.onSelect = function()
    {
        _system.setSelected(me);
    };
    
    this.getAngle = function()
    { 
    	varAngle = widget.angle + Math.PI;
    	if(varAngle > Math.PI * 2) varAngle -= Math.PI * 2;
    	return varAngle; 
    };
    this.setAngle = function(angle)
    {
    	this.setPosition(widget.x1, widget.y1, widget.x2, widget.y2, angle + Math.PI);
    };
    
    this.getFanAngle = function() { return fanAngle; };
    this.setFanAngle = function(angle)
    {
    	if(!angle) angle = Math.PI * 2;    	
    	if(angle < 0.5) angle =  0.5;
    	if(angle > Math.PI * 2) angle = Math.PI * 2;
    	fanAngle = angle;
    	this.setPosition(widget.x1, widget.y1, widget.x2, widget.y2);
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

    this.getText = function(){ return text; };
    this.setText = function(newText, newFont, newInverted)
    {
        if(newText !== undefined) text = newText;
        if(newFont) font = newFont;
        if(newInverted !== undefined) inverted = newInverted;
        
        if (text)
    	{
        	var formattedText;
            if(textFormat == TextElement.FORMAT_UPPER_CASE)
            {
            	formattedText = text.toUpperCase();
            }
            else
            {
            	formattedText = text; 
            };            
        	
            if(this.scriptContainer.onFilter)
            {
            	formattedText = this.scriptContainer.onFilter(formattedText);
            }
            
        	drawable.pattern = new TextPattern( formattedText, font, scaleToFit,	 inverted, bold, italic, null, alignment, verticalAlignment);
        	drawable.displayGroup = Scene.DISPLAY_GROUP_ANY;
    	}
        else
        {
        	drawable.pattern = new TextPattern( uiControlGroup.title ? uiControlGroup.title : "New text ...", font, scaleToFit, inverted, bold, italic, null, alignment, verticalAlignment);
        	drawable.displayGroup = Scene.DISPLAY_GROUP_ANY;
        	//drawable.displayGroup = Scene.DISPLAY_GROUP_UI;
        }
		highlightB.pattern = drawable.pattern;
		uiControlGroup.updateControl("text");
    };
    
    this.getTextFormat = function(){ return textFormat; };
    this.setTextFormat = function(value)
    {
        value = parseInt(value);
        if(!isNaN(value) && isFinite(value))
        {
        	textFormat = value;
        	this.setText();
        }
    };
    
    
    this.getBold = function(){ return bold; };
    this.setBold = function(newBold)
    {
        bold = newBold;
        this.setText(); 
    };

    this.getItalic = function(){ return italic; };
    this.setItalic = function(newItalic)
    {
        italic = newItalic;
        this.setText(); 
    };

    this.getFont = function(){ return font; };
    this.setFont = function(newFont)
    {
        this.setText(text, newFont); 
    };
    
    this.getAlignment = function() { return alignment; };
    this.setAlignment = function(newAlignment)
    {
    	alignment = newAlignment;
    	this.setText();
    };
    
    this.getVAlignment = function() { return verticalAlignment; };
    this.setVAlignment = function(value)
    {
        value = parseInt(value);
        if(!isNaN(value) && isFinite(value))
        {
        	verticalAlignment = value;
        	this.setText();
        }
    };
    
    this.getScaleToFit = function(){ return scaleToFit; };
    this.setScaleToFit = function(newScaleToFit)
    {
        scaleToFit = newScaleToFit;
        this.setText(); 
    };    
    
    this.getInverted = function() { return inverted; };
    this.setInverted = function(newInverted)
    {       
        this.setText(text, font, newInverted); 
    };

    this.getType = function() { return textType; };
    this.setType = function(newType)
    {
    	if((newType == TextElement.TYPE_ELLIPSE) || (newType == TextElement.TYPE_CIRCLE))
		{
    		widget.setAngleVisible(true);
    	    
    		widget.setPointAllowVisible("topMiddle", true);
    	    widget.setPointAllowVisible("topRight", true);
    	    widget.setPointAllowVisible("middleLeft", true);
    	    widget.setPointAllowVisible("middleMiddle", true);
    	    widget.setPointAllowVisible("middleRight", true);
    	    widget.setPointAllowVisible("bottomMiddle", true);
    	    widget.setPointAllowVisible("bottomLeft", true);
    	    widget.setPointAllowVisible("angle", true);
		}
    	else 
    	{
    		widget.setAngleVisible(false);

    		widget.setPointAllowVisible("topMiddle", false);
    	    widget.setPointAllowVisible("topRight", false);
    	    widget.setPointAllowVisible("middleLeft", false);
    	    widget.setPointAllowVisible("middleMiddle", false);
    	    widget.setPointAllowVisible("middleRight", false);
    	    widget.setPointAllowVisible("bottomMiddle", false);
    	    widget.setPointAllowVisible("bottomLeft", false);
    	    widget.setPointAllowVisible("angle", false);
    	}
    	
        if(textType == newType) return;
        textType = newType;
        updateControlTemplate();
        
        if(drawable.map != null)
        {
            this.setPosition(widget.x1, widget.y1, widget.x2, widget.y2);   
        }       
    };
    

    this.setPosition = function(x1, y1, x2, y2, angle, fromWidget)
    {
        widget.x1 = x1;
        widget.y1 = y1;
        widget.x2 = x2;
        widget.y2 = y2;
        
        if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
        {           
            if(angle) widget.angle = angle;
            
            var width = Math.abs(x1 - x2);
            var height = Math.abs(y1 - y2);
            
            if((textType == TextElement.TYPE_CIRCLE) || (width == height))
            {
                drawable.map = new CircleMap( 
                    (x1 + x2) / 2, 
                    (y1 + y2) / 2, 
                    widget.angle - fanAngle / 2,
                    widget.angle + fanAngle / 2,
                    (width < height ? width : height) / 2 - drawable.size, 
                    0, 
                    srcScale
                );
            }
            else
            {
                drawable.map = new EllipseMap(
                    Math.min(x1,x2) + drawable.size,
                    Math.min(y1,y2) + drawable.size,
                    width - drawable.size * 2,
                    height - drawable.size * 2,
                    widget.angle - fanAngle / 2,
                    widget.angle + fanAngle / 2,
                    srcScale
                );
            }
        }
        else
        {           
            drawable.map = new LineMap(
                x1, y1, x2, y2,
                0, srcScale
            );
        }
		
		highlightA.map = drawable.map;
        highlightB.map = drawable.map;
        
        if(!fromWidget)
        {
        	oldAngle = angle;
        	oldPosition = this.getPosition();
        }
    };
    
    this.getPosition = function()
    {
        return {
            x1: widget.x1,
            y1: widget.y1,
            x2: widget.x2,
            y2: widget.y2,
            angle : widget.angle
        };
    };
    
    this.getSize = function() { return drawable.size; };    
    this.setSize = function(value)
    { 
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            if(value < 8) value = 8;
            if(value > 9000) value = 9000;
            if(drawable.size == value) return;
            
            drawable.size = value;
            highlightA.size = drawable.size;
            highlightB.size = drawable.size;
            this.setPosition(widget.x1, widget.y1, widget.x2, widget.y2);
        }
    };  
    
    this.getSrcScale = function() { return srcScale; };    
    this.setSrcScale = function(value)
    {
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
        	srcScale = value;
        	if(srcScale < 0.2) srcScale = 0.2;
        	this.setPosition(widget.x1, widget.y1, widget.x2, widget.y2);        	
        }
    };

    
    
    this.setScene = function(scene)
    {
        if(currentScene)
        {
            currentScene.getLayer(Scene.LAYER_WIDGETS).remove(widget);             
            //currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(highlightB);
            currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(highlightA);
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(highlightA);            
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(drawable);
            currentScene = null;
        }
        
        if(scene)
        {
            scene.getLayer(Scene.LAYER_WIDGETS).add(widget);
            //scene.getLayer(Scene.LAYER_BACKGROUND).add(highlightB);
            scene.getLayer(Scene.LAYER_BACKGROUND).add(highlightA);
            scene.getLayer(Scene.LAYER_FOREGROUND).add(highlightA);           
            scene.getLayer(Scene.LAYER_FOREGROUND).add(drawable);           

            currentScene = scene;
        }
    };  
    

    this.createUI = function()
    {
        uiControlGroup = new UIControlGroup({
        	type:"Text",
        	element: this        	
        });
        
        uiControlGroup.title = null;

        updateControlTemplate();
        
        uiControlGroup.addControl(
            "text", 
            UIControl.TYPE_TEXT,
            {
                onGet : function()
                {
                	if(me.scriptContainer.onGet)
                	{
                		return me.scriptContainer.onGet();
                	}
                	else return text;
                	
                },
                onSet : function(value)
                { 
                	if(me.scriptContainer.onSet)
                	{
                		me.scriptContainer.onSet(value);
                	}
                	else me.setText(value);
                	
                    if(currentScene)currentScene.redraw();
                }
            }
        );
                
        uiControlGroup.addControl(
            "size", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : 10,
            	maxValue : 200,            	
                onGet : function() { return me.getSize(); },
                onSet : function(value)
                { 
                    me.setSize(value); 
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "scale to fit", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return scaleToFit ? 0 : 1; },
                onSet : function(index, item)
                { 
                    me.setScaleToFit(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "inverted", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return inverted ? 0 : 1; },
                onSet : function(index, item)
                { 
                    me.setInverted(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );          

        uiControlGroup.addControl(
            "bold", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return bold ? 0 : 1; },
                onSet : function(index, item)
                { 
                    me.setBold(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );          

        uiControlGroup.addControl(
            "italic", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return italic ? 0 : 1; },
                onSet : function(index, item)
                { 
                    me.setItalic(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );          

        uiControlGroup.addControl(
            "font", 
            UIControl.TYPE_LIST,
            {
                items : TextElement.FONTS,
                onGet : function() { 
                    for(var i in TextElement.FONTS)
                    {
                        if(TextElement.FONTS[i].name == font) return i;
                    }                   
                    return 0; 
                },
                onSet : function(index, item)
                { 
                    me.setFont(item.name);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
        
        uiControlGroup.addControl(
            "alignment", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Centre"}, {name:"Left"}, {name:"Right"}],
                onGet : function() { return alignment; },
                onSet : function(index, item)
                {
                    me.setAlignment(index);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
                "textFormat", 
                UIControl.TYPE_LIST,
                {
                    items : [{name:"None"}, {name:"Upper case"}],
                    onGet : function() { return me.getTextFormat(); },
                    onSet : function(index, item)
                    {
                        me.setTextFormat(index);
                        if(currentScene)currentScene.redraw();
                    }
                }
            );

        uiControlGroup.addControl(
                "valignment",
                UIControl.TYPE_LIST,
                {
                    items : [{name:"Top"}, {name:"Middle"}, {name:"Bottom"}],
                    onGet : function() { return verticalAlignment + 1; },
                    onSet : function(index, item)
                    {
                        me.setVAlignment(index-1);
                        if(currentScene)currentScene.redraw();
                    }
                }
            );

        uiControlGroup.addControl(
            "shape", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Line"}, {name:"Ellipse"}, {name:"Circle"}],
                onGet : function() { return textType; },
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
            "angle", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : 0,
            	maxValue : 360,            	
                onGet : function() 
                { 
                	if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
            		{
                    	var a = 360 - Math.round(me.getAngle() * 180/Math.PI) - 180;                    	
                    	while(a < 0) a += 360;
                    	return a % 360; 
            		}
                	else
                	{
                		var p = me.getPosition();
                		var a = Math.atan2(p.y2 - p.y1, p.x2 - p.x1); 
                    	a = Math.round(a * 180/Math.PI);
                    	if(a < 0) a += 360;
                    	return a % 360;
                	}
                },
                onSet : function(value)
                {
                	if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
            		{
	                	me.setAngle(-value * Math.PI / 180 + Math.PI);
	                	if(currentScene)currentScene.redraw();
            		}
                	else                		
                	{
                		var a = value * Math.PI / 180;
                		var p = me.getPosition();
                		var cx = (p.x1 + 	p.x2) / 2;
                		var cy = (p.y1 + p.y2) / 2;
                		var dx = p.x2 - p.x1;
                		var dy = p.y2 - p.y1;
                		var d = Math.sqrt(dx*dx + dy*dy) / 2;
                		dx = Math.cos(a) * d;
                		dy = Math.sin(a) * d;                		
                		me.setPosition(
                				cx - dx, cy - dy,
                				cx + dx, cy + dy                			
                		);
	                	if(currentScene)currentScene.redraw();
                	}
                }
            }
        );
        
        uiControlGroup.addControl(
            "fan angle", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : 50,
            	maxValue : 360,            	
                onGet : function() 
                { 
                	var a = Math.round(me.getFanAngle() * 180/Math.PI);
                	if(a < 0) a += 360;
                	return a; 
                },
                onSet : function(value)
                {
                	me.setFanAngle((value == 360) ? Math.PI * 2 : (value * Math.PI / 180));
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
            "length", 
            UIControl.TYPE_NUMBER,
            {
            	minValue : 40,
            	maxValue : 400,            	
                onGet : function()
                {
                	var p = me.getPosition();                	
                	var dx = p.x2 - p.x1;
                	var dy = p.y2 - p.y1;                	

                	return Math.round(Math.sqrt(dx*dx + dy*dy));                 	
                },
                onSet : function(value)
                {
                	var p = me.getPosition();                	
                	var cx = (p.x1 +  p.x2) / 2;
                	var cy = (p.y1 +  p.y2) / 2;
                	var dx = p.x2 - p.x1;
                	var dy = p.y2 - p.y1;                	
                	var a = Math.atan2(dy, dx);
                	var ox = (value / 2) * Math.cos(a);
                	var oy = (value / 2) * Math.sin(a);
                	
                	me.setPosition(cx - ox, cy - oy, cx + ox, cy + oy);

                	if(currentScene)currentScene.redraw();                	
                }
            }
        );
        
        uiControlGroup.addControl(
                "srcScale", 
                UIControl.TYPE_NUMBER,
                {
                	minValue : -60,
                	maxValue : 140,            	
                    onGet : function()
                    {
                    	if(srcScale >= 1)
                    	{
                        	return Math.max(Math.round(srcScale * 40) - 40, 0);
                    	}
                    	else
                    	{
                    		return Math.round(-1 / srcScale * 40 + 40);
                    	}
                    },
                    onSet : function(value)
                    {
                        value = parseFloat(value);
                        if(!isNaN(value) && isFinite(value))
                        {
                        	value = Math.round(value);
                        	if(value >= 0)
                        	{
                        		me.setSrcScale(Math.max(1, value + 40) / 40);
                        	}
                        	else
                        	{
                        		me.setSrcScale(-1 / Math.min(-1,((value - 40) / 40)));
                        	}
                        	if(currentScene)currentScene.redraw();
                        }                    	
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
                onSet : function(value)
                {
                	uiControlGroup.title = value;
                    me.setText(); 
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
    		text			: this.getText(),
    		bold			: this.getBold(),
    		italic			: this.getItalic(),
    		font			: this.getFont(),
    		textFormat		: this.getTextFormat(),
    		alignment		: this.getAlignment(),
    		valignment		: this.getVAlignment(),
    		scaleToFit		: this.getScaleToFit(),
    		inverted		: this.getInverted(), 
    		size			: this.getSize(),
    		angle			: this.getAngle(),
    		fanAngle		: this.getFanAngle(),
    		position		: this.getPosition(),
    		srcScale		: this.getSrcScale(),
    		
    		showMore		: uiControlGroup.showMore,    		
    		title			: uiControlGroup.title
    	});
    };
    
    this.setState = function(state)
    {
    	this.common_setState(state);
    	
    	this.setEditAllowMove(state.editAllowMove);    	
    	this.setType(state.type);
    	this.setText(state.text);
    	this.setBold(state.bold);
    	this.setItalic(state.italic);
    	this.setFont(state.font);
    	this.setTextFormat(state.textFormat);
    	//if (typeof state.alignment === "undefined");    		
		this.setAlignment(state.alignment);
		this.setVAlignment(state.valignment);
    	this.setScaleToFit(state.scaleToFit);
    	this.setInverted(state.inverted);
    	this.setSize(state.size);    	
    	this.setAngle(state.angle);
    	this.setFanAngle(state.fanAngle);
    	this.setSrcScale(state.srcScale);
    	this.setPosition(
    		state.position.x1, state.position.y1,
    		state.position.x2, state.position.y2
    	);    	
    	
    	uiControlGroup.showMore = state.showMore;
    	uiControlGroup.title = state.title;
        this.setText();
    };
    
    
    this.createUI();  
    this.setText();
    this.setPosition(0,0, 100, 100, Math.PI / 2);
}

TextElement.prototype = _prototypeElement;

TextElement.ALIGN_CENTRE = 0;
TextElement.ALIGN_LEFT = 1;
TextElement.ALIGN_RIGHT = 2;

TextElement.VALIGN_MIDDLE = 0;
TextElement.VALIGN_BOTTOM = 1;
TextElement.VALIGN_TOP = -1;

TextElement.TYPE_LINE = 0; 
TextElement.TYPE_ELLIPSE = 1;
TextElement.TYPE_CIRCLE = 2;

TextElement.FORMAT_NONE = 0;
TextElement.FORMAT_UPPER_CASE = 1;

TextElement.FONTS = [
 	{name: 'Arial'},    
 	{name: 'Boulder'},    
 	{name: 'Brush Script BT'}, 	
 	{name: 'Candy Script'},
 	{name: 'Cooper Black BT'},
 	{name: 'Courier New'},
 	{name: 'DiskusDMed'},
 	{name: 'Great Vibes'},
 	{name: 'Helvetica'},
 	{name: 'Helvetica_Bold'},
 	{name: 'Helvetica Ext'},
 	{name: 'Impact'},
 	{name: 'Ribbon 131 BT'},
 	{name: 'Segoe UI'},
 	{name: 'Souvenir Light BT'},
 	{name: 'Swiss 721 Black Condensed BT'},
 	{name: 'Swiss 721 Black Outline BT'},
 	{name: 'Times New Roman'},
 	{name: 'Nadia Serif'},
 	{name: 'Respective'},
 	{name: 'Respective Slanted'},
 	{name: 'Respective Swashed'},
 	{name: 'Respective Swashed Slanted'},
 	{name: 'Gotham Black'},
 	{name: 'Gotham Black Italic'},
 	{name: 'Gotham Thin'},
 	{name: 'Gotham Thin Italic'},
 	{name: 'Gotham Bold'},
 	{name: 'Gotham Bold Italic'},
 	{name: 'Gotham Book'},
 	{name: 'Gotham Book Italic'},
 	{name: 'Gotham XLight'},
 	{name: 'Gotham XLight Italic'},
 	{name: 'Gotham Light'},
 	{name: 'Gotham Light Italic'},
 	{name: 'Gotham Medium'},
 	{name: 'Gotham Medium Italic'},
 	{name: 'Gotham Ultra'},
 	{name: 'Gotham Ultra Italic'},
 	{name: 'AGaramond-Italic-2'},
 	{name: 'AGaramond-Italic'},
 	{name: 'AGaramond-Regular-2'},
 	{name: 'AGaramond-Regular'},
 	{name: 'AGaramond-Semibold-2'},
 	{name: 'AGaramond-Semibold'},
 	{name: 'AGaramond-SemiboldItalic-2'},
 	{name: 'AGaramond-SemiboldItalic'},
 	{name: 'AGaramond-Titling'},
 	{name: 'AGaramondAlt-Italic'},
 	{name: 'AGaramondAlt-Regular'},
 	{name: 'AGaramondExp-Bold'},
 	{name: 'AGaramondExp-BoldItalic'},
 	{name: 'AGaramondExp-Italic'},
 	{name: 'AGaramondExp-Regular'},
 	{name: 'AGaramondExp-Semibold'},
 	{name: 'AGaramondExp-SemiboldItalic'},
 	{name: 'FuturBlaBTReg'},
 	{name: 'FuturBTBol'},
 	{name: 'FuturBTBolCon'},
 	{name: 'FuturBTBolConIta'},
 	{name: 'FuturBTBolIta'},
 	{name: 'FuturBTBoo'},
 	{name: 'FuturBTBooIta'},
 	{name: 'FuturBTExtBla'},
 	{name: 'FuturBTExtBlaCon'},
 	{name: 'FuturBTExtBlaConIta'},
 	{name: 'FuturBTExtBlaIta'},
 	{name: 'FuturBTHea'},
 	{name: 'FuturBTHeaIta'},
 	{name: 'FuturBTLig'},
 	{name: 'FuturBTLigCon'},
 	{name: 'FuturBTLigIta'},
 	{name: 'FuturBTMed'},
 	{name: 'FuturBTMedCon'},
 	{name: 'FuturBTMedIta'},
 	{name: 'FuturLtCnBTIta'},
 	{name: 'FuturMdCnBTIta'},
 	{name: 'Futur'},
 	{name: 'FuturBoo'},
 	{name: 'FuturBooObl'},
 	{name: 'FuturCon'},
 	{name: 'FuturConBol'},
 	{name: 'FuturConBolObl'},
 	{name: 'FuturConExtBol'},
 	{name: 'FuturConExtBolObl'},
 	{name: 'FuturConLig'},
 	{name: 'FuturConLigObl'},
 	{name: 'FuturConObl'},
 	{name: 'FuturExtBol'},
 	{name: 'FuturExtBolObl'},
 	{name: 'FuturHea'},
 	{name: 'FuturHeaObl'},
 	{name: 'FuturLig'},
 	{name: 'FuturLigObl'},
 	{name: 'FuturObl'},
 	{name: 'RSFut'},
 	{name: 'Granj'},
 	{name: 'GranjItaOsF'},
 	{name: 'Granjon_Small_Caps_Old_Style_Figures'},
 	{name: 'GranjSC'},
 	{name: 'MrsEavesRoman_Regular'},
 	{name: 'MrsEavesBold'},
 	{name: 'MrsEavesItalic'},
 	{name: 'MrsEavesJustLigItalic'},
 	{name: 'WeissBTBol'},
 	{name: 'WeissBTExtBol'},
 	{name: 'WeissBTIta'},
 	{name: 'WeissBTRom'},
 	{name: 'Bodoni'},
 	{name: 'BodonBTBol'},
 	{name: 'BodonBTBolCon'},
 	{name: 'BodonBTBolIta'},
 	{name: 'BodonBTBoo'},
 	{name: 'BodonBTBooIta'},
 	{name: 'BodonBTIta'},
 	{name: 'BodonBTRom'},
 	{name: 'Unive'},
 	{name: 'UniveBla'},
 	{name: 'UniveBlaExt'},
 	{name: 'UniveBlaExtObl'},
 	{name: 'UniveBlaObl'},
 	{name: 'UniveBol'},
 	{name: 'UniveBolExt'},
 	{name: 'UniveBolExtObl'},
 	{name: 'UniveBolObl'},
 	{name: 'UniveCon'},
 	{name: 'UniveConBol'},
 	{name: 'UniveConBolObl'},
 	{name: 'UniveConLig'},
 	{name: 'UniveConLigObl'},
 	{name: 'UniveConObl'},
 	{name: 'UniveExt'},
 	{name: 'UniveExtBla'},
 	{name: 'UniveExtBlaExt'},
 	{name: 'UniveExtBlaExtObl'},
 	{name: 'UniveExtBlaObl'},
 	{name: 'UniveExtObl'},
 	{name: 'UniveLig'},
 	{name: 'UniveLigObl'},
 	{name: 'UniveObl'},
 	{name: 'UniveThiUltCon'},
 	{name: 'UniveUltCon'},
 	{name: 'Shardee'},
 	{name: 'MSGOTHIC'},
 	{name: 'Old_Claude_LP'},
 	{name: 'bickham-script-one'},
 	{name: 'BickhamScriptPro-Bold'},
 	{name: 'BickhamScriptPro-Regular'},
 	{name: 'BickhamScriptPro-Semibold'},
 	{name: 'angelina'},
 	{name: 'BEBAS'},
 	{name: 'TrajanPro-Bold'},
 	{name: 'TrajanPro-Bold_1'},
 	{name: 'TrajanPro-Regular'},
 	{name: 'TrajanPro-Regular_1'},
 	{name: 'Novecentowide-Bold'},
 	{name: 'Novecentowide-Book'},
 	{name: 'Novecentowide-DemiBold'},
 	{name: 'Novecentowide-Light'},
 	{name: 'Novecentowide-Medium'},
 	{name: 'Novecentowide-Normal'},
 	{name: 'Sreda'},
 	{name: 'CaviarDreams'},
 	{name: 'CaviarDreams_Bold'},
 	{name: 'CaviarDreams_BoldItalic'},
 	{name: 'CaviarDreams_Italic'},
 	{name: 'Rockwell-Bold-Italic'},
 	{name: 'Rockwell-Bold'},
 	{name: 'Rockwell-Extra-Bold'},
 	{name: 'Rockwell'},
 	{name: 'Shelley-VolanteScript'},
 	{name: 'Champignon'},
 	{name: 'lmroman10-bold'},	
 	{name: 'lmroman10-bolditalic'},
 	{name: 'lmroman10-italic'},
 	{name: 'lmroman10-regular'},
 	{name: 'lmroman10-regular-custom'},
 	{name: 'lmromancaps10-oblique'},
 	{name: 'lmromancaps10-regular'},
 	{name: 'lmromandemi10-oblique'},
 	{name: 'lmromandemi10-regular'},
 	{name: 'lmromandunh10-oblique'},
 	{name: 'lmromandunh10-regular'},
 	{name: 'lmromanslant10-bold'},
 	{name: 'lmromanslant10-regular'},
 	{name: 'Lobster_1.3'},
 	{name: 'Lobster_1_4'},
 	{name: 'Arvil_Sans'},
 	{name: 'champignonaltswash'},
 	{name: 'Bordeaux-Roman-Bold-LET-Plain1.0'},
 	{name: 'Georgia'},
 	{name: 'MONOSCR'},
 	{name: 'PTC55F'},
 	{name: 'PTC75F'},
 	{name: 'PTN57F'},
 	{name: 'PTN77F'},
 	{name: 'PTS55F'},
 	{name: 'PTS56F'},
 	{name: 'PTS75F'},
 	{name: 'PTS76F'},
 	{name: 'MON20IBT'},
 	{name: 'MODN20BT'},
 	{name: 'MODERN20'},
 	{name: 'English_'},
 	{name: 'Bernard_MT_Condensed'},
 	{name: 'Satan_Minion'},
 	{name: 'AmeriTypBol'},
 	{name: 'AmeriTypBolA'},
 	{name: 'AmeriTypBolCon'},
 	{name: 'AmeriTypBolConA'},
 	{name: 'AmeriTypCon'},
 	{name: 'AmeriTypConA'},
 	{name: 'AmeriTypLig'},
 	{name: 'AmeriTypLigA'},
 	{name: 'AmeriTypLigCon'},
 	{name: 'AmeriTypLigConA'},
 	{name: 'AmeriTypMed'},
 	{name: 'AmeriTypMedA'},
 	{name: 'AmerTypITCbyBTBol'},
 	{name: 'AmerTypITCbyBTMed'},
 	{name: 'didot'},
 	{name: 'didot-bold'},
 	{name: 'didot-italic'},
 	{name: 'FrankGotBTExtCon'},
 	{name: 'FrankGotBTIta'},
 	{name: 'FrankGotBTRom'},
 	{name: 'FrankGotBTRomCon'},
 	{name: 'FrankGotITCbyBTBoo'},
 	{name: 'FrankGotITCbyBTBooIta'},
 	{name: 'FrankGotITCbyBTDem'},
 	{name: 'FrankGotITCbyBTDemIta'},
 	{name: 'FrankGotITCbyBTHea'},
 	{name: 'FrankGotITCbyBTHeaIta'},
 	{name: 'FrankGotITCbyBTMed'},
 	{name: 'FrankGotITCbyBTMedIta'},
 	{name: 'FrutigerLight'},
 	{name: 'FrutigerNeueLTPro-UltLt'},
 	{name: 'FrutigerLTCom65BoldLinotype'},
 	{name: 'Arvo-Regular'},
	{name: 'Arvo-Italic'},
	{name: 'Dalle_Typeface'},
	{name: 'DISCO___'},
	{name: 'Elega_Bold'},
	{name: 'GearedSlab-Bold'},
	{name: 'GearedSlab-Extrabold'},
	{name: 'GearedSlab-Light'},
	{name: 'heftyregular'},
	{name: '83'},
	{name: 'LaurenScript'},
	{name: 'Lavanderia-Regular'},
	{name: 'leaguegothic-regular'},
	{name: 'MinionPro-Regular'},
	{name: 'MinionPro-It'},
	{name: 'MinionPro-Semibold'},
	{name: 'monogram_kk_sc'},
	{name: 'NASHVILL'},
	{name: 'Oranienbaum'},
	{name: 'ostrich-rounded'},
	{name: 'raleway_thin'},
	{name: 'Ranger'},
	{name: 'Silverfake'},
	{name: 'steelfish-rg'},
	{name: 'steelfish-bd'},
	{name: 'Tommaso'},
	{name: 'WisdomScript'},
	{name: 'Alternate_Gothic_No2_BT'},
	{name: 'Arvil_Sans'},
	{name: 'BALLPARK_WEINER'},
	{name: 'CAC_Champagne'},
	{name: 'Carton_Slab'},
	{name: 'DinerFatt'},
	{name: 'EdwardianScriptITC'},
	{name: 'Garamond-Bold'},
	{name: 'Garamond-BoldItalic'},
	{name: 'Garamond-Italic'},
	{name: 'Garamond-Light'},
	{name: 'Garamond-LightItalic'},
	{name: 'Garamond'},
	{name: 'gothic_ultra'},
	{name: 'House-A-Rama-League-Night'},
	{name: 'ITC_Cheltenham_Std_bold_italic'},
	{name: 'MFC_Manoir_Mngm250'},
	{name: 'MFC_Manoir_Mngm_Basic250'},
	{name: 'MFC_Manoir_Mngm_Flourish250'},
	{name: 'POSTOFFICE'},
	{name: 'trade-gothic-lt-condensed-no-18-oblique'},
	{name: 'Fanwood-Italic'},
	{name: 'Fanwood'},
	{name: 'Quicksand_Bold'},
	{name: 'Quicksand_Bold_Oblique'},
	{name: 'Quicksand_Book'},
	{name: 'Quicksand_Book_Oblique'},
	{name: 'Quicksand_Dash'},
	{name: 'Quicksand_Light'},
	{name: 'Quicksand_Light_Oblique'}


];