//TODO: Make the hit text just use a collision box so that it does not have size worth of paddint at the sides

function TextElement()
{ 
	this.common_init();
	this.className = "TextElement";
	
	/*this.scriptContainer.addBinding("onFilter");
	this.scriptContainer.addBinding("onSet");
	this.scriptContainer.addBinding("onGet");
	this.scriptContainer.addBinding("onInit");
	this.scriptContainer.addBinding("onCustomUI");*/
	
	
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
    var minSize = 15.0;
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

	var title;
	var showMore;
        
    
    var updateControlTemplate = function()
    {
    	var type = me.getType();
    	
    	/*if(type == TextElement.TYPE_LINE)
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
    	}    	*/
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
    
    this.setTextColor = function(value)
    {
    	drawable.setFGColor(value);
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
    
    this.getFGColor = function() { return drawable.getFGColor(); };
    this.setFGColor = function(value) { drawable.setFGColor(value); };

    this.getVisible = function() { return drawable.visible; };
    this.setVisible = function(value) { drawable.visible = value; };
    
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
            else if(textFormat == TextElement.FORMAT_LOWER_CASE)
            {
            	formattedText = text.toLowerCase();
            }
            else
            {
            	formattedText = text; 
            };            
        	
            if(this.scriptContainer.onFilter)
            {
            	formattedText = this.scriptContainer.onFilter(formattedText);
            }
            
        	drawable.pattern = new TextPattern( formattedText, font, scaleToFit,	 inverted, bold, italic, minSize, alignment, verticalAlignment);
        	drawable.displayGroup = Scene.DISPLAY_GROUP_ANY;
    	}
        else
        {
        	drawable.pattern = new TextPattern( title ? title : "New text ...", font, scaleToFit, inverted, bold, italic, minSize, alignment, verticalAlignment);
        	drawable.displayGroup = Scene.DISPLAY_GROUP_ANY;
        	//drawable.displayGroup = Scene.DISPLAY_GROUP_UI;
        }
		highlightB.pattern = drawable.pattern;
		
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
    
    this.getMinSize = function() { return minSize; };    
    this.setMinSize = function(value)
    { 
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            if(value < 8) value = 8;
            if(value > 9000) value = 9000;
            if(minSize == value) return;
            
            minSize = value;
            this.setText();
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
    		minSize			: this.getMinSize(),
    		angle			: this.getAngle(),
    		fanAngle		: this.getFanAngle(),
    		position		: this.getPosition(),
    		srcScale		: this.getSrcScale(),
    		fgColor			: this.getFGColor(),
    		visible			: this.getVisible(),
    		
    		showMore		: showMore,    		
    		title			: title
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
    	this.setMinSize(state.minSize);    	
    	this.setAngle(state.angle);
    	this.setFanAngle(state.fanAngle);
    	this.setSrcScale(state.srcScale);
    	this.setPosition(
    		state.position.x1, state.position.y1,
    		state.position.x2, state.position.y2
    	);    	
    	this.setFGColor(state.fgColor);
    	this.setVisible(state.visible === undefined ? true : state.visible);
    	
    	showMore = state.showMore;
    	title = state.title;
        
    	if(this.scriptContainer.onInit)
    	{
    		this.scriptContainer.onInit();
    	}
    	
    	this.setText();
    };
    
 
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
TextElement.FORMAT_LOWER_CASE = 2;

TextElement.FONTS = [];