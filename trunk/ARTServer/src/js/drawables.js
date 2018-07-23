function PatternMapDrawable(map, pattern, size, spacingScale, fgColor, bgColor)
{
    this.displayGroup = Scene.DISPLAY_GROUP_ANY;
    this.map = map;
    this.pattern = pattern;
    this.visible = true;
    this.size = size;
    this.spacingScale = spacingScale;

    var paletteFgColor = null;
    this.setFGColor = function(value)
    {
    	fgColor = value;
    	if(fgColor)
    	{
    		if(fgColor.charAt(0) == '$')
    		{
    			paletteFgColor = fgColor.substring(1);
    		}
    	}
    	else paletteFgColor = "ink";    	
    };
    this.getFGColor = function()
    {
    	if(fgColor) return fgColor;
    	return '$' + paletteFgColor;
    };    
    this.setFGColor(fgColor);
    
    

    var paletteBgColor = null;
    this.setBGColor = function(value)
    {
    	bgColor = value;
    	if(bgColor)
    	{
    		if(bgColor.charAt(0) == '$')
    		{
    			paletteBgColor = bgColor.substring(1);
    		}
    	}
    	else paletteBgColor = "paper";
    };
    this.setBGColor(bgColor);
	
    
	this.getState = function()
	{
		return {
			n: "P",
			p: [this.map.state, this.pattern.state, this.size, this.spacingScale, fgColor, bgColor]
		};
	};
	
    this.onDraw = function(params)
    {        
        if(!this.visible) return;       

		var fg = (paletteFgColor) ? params.palette[paletteFgColor] : fgColor;
		var bg = (paletteBgColor) ? params.palette[paletteBgColor] : bgColor;

        if(this.map && this.pattern)
        {
        	if(this.map instanceof MapCollection)
            {               
            	if(this.pattern.begin)
            	{
                    this.pattern.border(
                        this.map.maps[0],
                        params.context,
                        this.size,
                        this.spacingScale,
                        fg,
                        bg
                    );
            	}        		

        		for(var i in this.map.maps)
                {
                    this.pattern.border(
                        this.map.maps[i],
                        params.context,
                        this.size,
                        this.spacingScale,
                        fg,
                        bg
                    );
                }
                
                for(var i in this.map.corners)
                {
                    this.pattern.corner(
                        this.map.corners[i],
                        params.context,
                        this.size,
                        this.spacingScale,
                        fg,
                        bg                        
                    );            
                }
                
            	if(this.pattern.end)
            	{
                    this.pattern.end(
                        this.map.maps[this.map.maps.length - 1],
                        params.context,
                        this.size,
                        this.spacingScale,
                        fg,
                        bg
                    );
            	}        		
            }
            else
            {
            	if(this.pattern.begin)
            	{
            		this.pattern.begin(
                        this.map, 
                        params.context,
                        this.size,
                        this.spacingScale,
    					fg,
                        bg                    
                    );
            	}        		

            	this.pattern.border(
                    this.map, 
                    params.context,
                    this.size,
                    this.spacingScale,
					fg,
                    bg                    
                );

            	if(this.pattern.end)
            	{
            		this.pattern.end(
                        this.map, 
                        params.context,
                        this.size,
                        this.spacingScale,
    					fg,
                        bg                    
                    );
            	}        		
            }           
        }
    };  
}




function ImageDrawable(image, x, y, width, height, src)
{
	this.displayGroup = Scene.DISPLAY_GROUP_ANY;
    this.image = image;
    this.visible = true;
    this.x = x;
    this.src = src;
    this.y = y;
    this.width = width;
    this.height = height;
    this.angle = 0;
    this.type = ImageDrawable.TYPE_STRETCH;
    
    this.onBeforeDraw = null;
    
	this.getState = function()
	{
		if(this.angle === undefined) this.angle = 0;
		return {
			n: "I",
			p: [((this.image) ? this.image.descriptor : this.src), this.x, this.y, this.width, this.height, this.angle, this.type]
		};
	};
	
    this.onDraw = function(params)  
    {
        if(this.onBeforeDraw) this.onBeforeDraw(params);
    	
    	if(!this.visible) return;       
        if(!this.image) return;
        
        params.context.drawImage(
            this.image, 
            this.x, this.y, 
            this.width, this.height
            );
    };  
}
ImageDrawable.TYPE_STRETCH = 0;
ImageDrawable.TYPE_CENTER = 1;


function RectDrawable(x, y, width, height, fgColor)
{
    this.displayGroup = Scene.DISPLAY_GROUP_ANY;
    this.visible = true;
    this.x = x;
    this.y = y;
    this.width = width;
    this.height = height;   

	var paletteFgColor = null;
	if(fgColor)
	{
		if(fgColor.charAt(0) == '$')
		{
			paletteFgColor = fgColor.substring(1);
		}
	}
	else paletteFgColor = "ink";	

	this.getState = function()
	{
		return {
			n: "R",
			p: [this.x, this.y, this.width, this.height, fgColor]
		};
	};
	
    
    this.onDraw = function(params)  
    {
        if(!this.visible) return;       
		params.context.fillStyle = (paletteFgColor) ? params.palette[paletteFgColor] : fgColor;   
        params.context.fillRect(
            this.x, this.y, 
            this.width, this.height
            );
    };  
}

function LineDrawable(x1, y1, x2, y2, lineWidth, fgColor)
{
    this.displayGroup = Scene.DISPLAY_GROUP_ANY;
    this.visible = true;
    this.x1 = x1;
    this.y1 = y1;
    this.x2 = x2;
    this.y2 = y2;
	this.lineWidth = lineWidth
	
	var paletteFgColor = null;
	if(fgColor)
	{
		if(fgColor.charAt(0) == '$')
		{
			paletteFgColor = fgColor.substring(1);
		}
	}
	else paletteFgColor = "ink";	

	this.getState = function()
	{
		return {
			n: "L",
			p: [this.x1, this.y1, this.x2, this.y2, this.lineWidth, fgColor]
		};
	};
	
    this.onDraw = function(params)  
    {
        if(!this.visible) return;       

		params.context.lineWidth = this.lineWidth;
		params.context.strokeStyle = (paletteFgColor) ? params.palette[paletteFgColor] : fgColor;

        params.context.beginPath();
        params.context.moveTo(this.x1,this.y1);
        params.context.lineTo(this.x2,this.y2);
        params.context.closePath();
        params.context.stroke();
    };  
}




