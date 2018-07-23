function PatternMapDrawable(map, pattern, size, spacingScale, fgColor, bgColor)
{
    this.displayGroup = Scene.DISPLAY_GROUP_ANY;
    this.map = map;
    this.pattern = pattern;
    this.visible = true;
    this.size = size;
    this.spacingScale = spacingScale;

    var paletteFgColor = null;
	if(fgColor)
	{
		if(fgColor.charAt(0) == '$')
		{
			paletteFgColor = fgColor.substring(1);
		}
	}
	else paletteFgColor = "ink";

    var paletteBgColor = null;
	if(bgColor)
	{
		if(bgColor.charAt(0) == '$')
		{
			paletteBgColor = bgColor.substring(1);
		}
	}
	else paletteBgColor = "paper";
	
    
    this.onDraw = function(params)
    {        
        if(!this.visible) return;       

		var fg = (paletteFgColor) ? params.palette[paletteFgColor] : fgColor;
		var bg = (paletteBgColor) ? params.palette[paletteBgColor] : bgColor;

        if(this.map && this.pattern)
        {
            if(this.map instanceof MapCollection)
            {               
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
            }
            else
            {
                this.pattern.border(
                    this.map, 
                    params.context,
                    this.size,
                    this.spacingScale,
					fg,
                    bg                    
                );
            }           
        }
    };  
}




function ImageDrawable(image, x, y, width, height)
{
    this.displayGroup = Scene.DISPLAY_GROUP_ANY;
    this.image = image;
    this.visible = true;
    this.x = x;
    this.y = y;
    this.width = width;
    this.height = height;   
    
    this.onBeforeDraw = null;
    
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




