function TextElement()
{   
    
    var me = this;
    var currentScene = null;    
    var drawable = new PatternMapDrawable(null, null, 20, 1.0, null);
	var highlightA = new PatternMapDrawable(null, new PatternHighlight(0,0), drawable.size, 1.0, "$highlight");
	var highlightB = new PatternMapDrawable(null, null, drawable.size, 1.0, "$highlight");

	var isSelected = false;
    
    var text = "Some text mapped as a border .......... ^_^";
    var font = "Verdana";
    var inverted = false;   
    var bold = false;
    var italic = false;

    var textType = TextElement.TYPE_ROUND;
    var widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    
    widget.visible = false;
	highlightA.visible = false;
	highlightB.visible = false;

    widget.onChange = function(sender)
    {
        me.setPosition(
            sender.x1, 
            sender.y1, 
            sender.x2,
            sender.y2,
            sender.angle
        );
    };  
    
    widget.hitTest = function(params)
    {
        if(textType == TextElement.TYPE_ROUND)
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
        else
        {
            if( (params.x >= Math.min(widget.x1, widget.x2)) && 
                (params.x <= Math.max(widget.x1, widget.x2)) )
            {
                var cy = (widget.y1 + widget.y2) / 2;
                if( Math.abs(params.y - cy) <  drawable.size / 2 + 5) return true;
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

    this.getText = function(){ return text; };
    this.setText = function(newText, newFont, newInverted)
    {
        if(newText !== undefined) text = newText;
        if(newFont) font = newFont;
        if(newInverted !== undefined) inverted = newInverted;
                
        drawable.pattern = new TextPattern( text, font, inverted, bold, italic);
		highlightB.pattern = drawable.pattern;
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
    
    this.getInverted = function() { return inverted; };
    this.setInverted = function(newInverted)
    {       
        this.setText(text, font, newInverted); 
    };

    this.getType = function() { return textType; }
    this.setType = function(newType)
    {
        if(textType == newType) return;
        textType = newType
        if(drawable.map != null)
        {
            this.setPosition(widget.x1, widget.y1, widget.x2, widget.y2);   
        }       
    }

    this.setPosition = function(x1, y1, x2, y2, angle)
    {
        widget.x1 = x1;
        widget.y1 = y1;
        widget.x2 = x2;
        widget.y2 = y2;
        
        if(textType == TextElement.TYPE_ROUND)
        {           
            if(angle) widget.angle = angle;
            
            var width = Math.abs(x1 - x2);
            var height = Math.abs(y1 - y2);
            
            if(width == height)
            {
                drawable.map = new CircleMap( 
                    (x1 + x2) / 2, 
                    (y1 + y2) / 2, 
                    widget.angle,
                    widget.angle + Math.PI * 2, 
                    (width < height ? width : height) / 2 - drawable.size, 
                    0, 
                    1.0
                );
            }
            else
            {
                drawable.map = new EllipseMap(
                    Math.min(x1,x2) + drawable.size,
                    Math.min(y1,y2) + drawable.size,
                    width - drawable.size * 2,
                    height - drawable.size * 2,
                    widget.angle,
                    1.0
                );
            }
        }
        else
        {           
            drawable.map = new LineMap(
                Math.min(x1,x2), (y1 + y2) / 2 + drawable.size / 2,
                Math.max(x1,x2), (y1 + y2) / 2 + drawable.size / 2,
                0
            );
        }
		
		highlightA.map = drawable.map;
        highlightB.map = drawable.map;
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
            if(value > 100) value = 100;
            if(drawable.size == value) return;
            
            drawable.size = value;
            highlightA.size = drawable.size;
            highlightB.size = drawable.size;
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
    
	var uiControlGroup = null;
    this.getUIControlGroup = function() { return uiControlGroup; };
    

    this.createUI = function()
    {
        uiControlGroup = new UIControlGroup({
        	type:"Text",
        	element: this        	
        });

        uiControlGroup.addControl(
            "text", 
            UIControl.TYPE_TEXT,
            {
                onGet : function() { return text; },
                onSet : function(value)
                { 
                    me.setText(value); 
                    if(currentScene)currentScene.redraw();
                }
            }
        );
                
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
            "shape", 
            UIControl.TYPE_LIST,
            {
                items : [{name:"Line"}, {name:"Round"}],
                onGet : function() { return textType },
                onSet : function(index, item)
                { 
                    me.setType(index);
                    if(currentScene)currentScene.redraw();
                }
            }
        );          
    }
    
    this.setText();
    this.setPosition(0,0, 100, 100, Math.PI / 2);
    this.createUI();  
}

TextElement.TYPE_LINE = 0; 
TextElement.TYPE_ROUND = 1;

TextElement.FONTS = [
    {name: 'Verdana'},  
    {name: 'Arial'},    
    {name: 'Courier New'}
];

