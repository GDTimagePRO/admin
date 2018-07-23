function RectangularClipMask(width, height)
{
    this.onDraw = function(params)
    {
        var hw = width / 2;
        var hh = height / 2;

        var ctx = params.context;
        
        ctx.beginPath();
		ctx.moveTo(-hw, -hh);
        ctx.lineTo(hw, -hh);
        ctx.lineTo(hw, hh);
        ctx.lineTo(-hw, hh);
		ctx.closePath();
        
        ctx.fillStyle = params.palette.paper;
        ctx.fill();
        ctx.clip();
    };
}

function CircularClipMask(width, height)
{
	var radius = Math.min(width, height) / 2;
    this.onDraw = function(params)
    {
        var ctx = params.context;
        ctx.beginPath();
        ctx.arc(0,0,radius,0,Math.PI*2,false);
        ctx.fillStyle = params.palette.paper;
        ctx.fill();
        ctx.clip();
    };
}

function SceneLayer(scene)
{
    var drawables = [];
    
    
    this.visible = true;
    this.clipMask = null;
    
    this.add = function(drawable)
    {
        drawables.push(drawable);
        return true;            
    };
    
    this.remove = function(drawable)
    {
        for(var i in drawables)
        {
            if(drawables[i] === drawable)
            {                
                if(drawables.onRelease)
                {
                	drawables.onRelease(scene.getCommonParams());
                }
                drawables.splice(i,1);
                return true;
            }           
        }
        return false;           
    };
    
    this.onDraw = function(params)
    {
        params.context.save();
        if(this.clipMask != null) this.clipMask.onDraw(params);

        for(var i=0; i<drawables.length; i++)
        {
            if(params.filter & drawables[i].displayGroup)
            {
            	drawables[i].onDraw(params);            	
            }
        }
                
        params.context.restore();
    };  

    this.onPress = function(params)
    {
        for(var i = drawables.length-1; i>=0; i--) 
        {
            if(drawables[i].onPress)
            {
                var capture = drawables[i].onPress(params); 
                if(capture) return capture;             
            }
        }
        return null;
    };

    this.onRelease = function(params)
    {
        for(var i in drawables)
        {
            if(drawables[i].onRelease)
            {
                var capture = drawables[i].onRelease(params); 
                if(capture) return capture;             
            }
        }
        return null;
    };

    this.onMove = function(params)
    {
        for(var i in drawables)
        {
            if(drawables[i].onMove)
            {
                var capture = drawables[i].onMove(params); 
                if(capture) return capture;             
            }
        }
        return null;
    };
}


function Scene(canvas, width, height)
{
    var layers = {};
    var zorder = [];
    var capture = null;
    var context = null;
    
	this.inkColor = "black";//"#000000";
    
    

    this.getCommonParams = function()
    {
        return {
            dirty : false,
            scale : this.scale,
            filter : Scene.DISPLAY_GROUP_ANY,
            palette : {
            	ink: this.inkColor,
            	paper: "#ffffff",
            	background: "#404040",
            	highlight:"rgba(180,240,255,0.75)"
            },
            viewRect : {
                x1 : -this.offsetX,
                y1 : -this.offsetY,
                x2 : -this.offsetX + this.width / this.scale,               
                y2 : -this.offsetY + this.height / this.scale               
            }
        };
    }

    this.canvas = canvas;
    this.scale = 1.0;
    this.offsetX = width / 2;
    this.offsetY = height / 2;
    this.width = width;
    this.height = height;
    this.backgroundColor = null;
    
    this.addLayer = function(name)
    {
        var newLayer = new SceneLayer(this); 
        zorder.push(newLayer);
        layers[name] = newLayer;
        return  newLayer;
    };
    
    this.setLayer = function(name, newLayer)
    {
		var oldLayer = layers[name]; 
		layers[name] = newLayer;
		for(var i in zorder)
		{
			if(zorder[i] === oldLayer)
			{
				zorder[i] = newLayer;
				break;
			}
		}
    };

    this.getLayer = function(name)
    {
        return layers[name];
    };
    
    this.onPress = function(x, y)
    {
        var params = this.getCommonParams();
        params.x = (x - this.offsetX) / this.scale;
        params.y = (y - this.offsetY) / this.scale;
        
        for(params.pass=0; params.pass<2; params.pass++)
        {
            //for(var i = 0; i < zorder.length; i++) 
            for(var i = zorder.length-1; i>= 0; i--) 
            {
                capture = zorder[i].onPress(params); 
                if(capture) break;              
            }
            if(capture != null) break;
        }

        if(params.dirty) this.redraw();
        return capture; 
    };

    this.onRelease = function(x, y)
    {
        var params = this.getCommonParams();
        params.x = (x - this.offsetX) / this.scale;
        params.y = (y - this.offsetY) / this.scale;
        
        if(capture)
        {
            capture.onRelease(params);
        }
        else
        {
            for(var i = zorder.length-1; i>= 0; i--) 
            {
                if(zorder[i].onRelease(params)) break; 
            }
        }
        capture = null;

        if(params.dirty) this.redraw(); 
    };

    this.onMove = function(x, y)
    {
        var params = this.getCommonParams();
        params.x = (x - this.offsetX) / this.scale;
        params.y = (y - this.offsetY) / this.scale;
        
        if(capture)
        {
            capture.onMove(params);
        }
        else
        {
            for(var i = zorder.length-1; i>= 0; i--) 
            {
                if(zorder[i].onMove(params)) break; 
            }
        }

        if(params.dirty) this.redraw(); 
        return capture; 
    };

    this.redraw = function(filter)
    {
        var canvas = this.canvas;        
        var params = this.getCommonParams();
        
		if(filter) params.filter = filter;
				        
        params.context = canvas.getContext("2d");
        
        if(this.backgroundColor)
        {
        	params.context.fillStyle = this.backgroundColor;
        	params.context.fillRect( 0 , 0 , canvas.width , canvas.height );        	
        }
        else
        {
            params.context.clearRect( 0 , 0 , canvas.width , canvas.height );        	
        }
        
        params.context.save();
		params.context.translate(this.offsetX, this.offsetY);
		params.context.scale(this.scale,this.scale);
		
        for(var i=0; i<zorder.length; i++)
        {
            if(zorder[i].visible)
            {
                zorder[i].onDraw(params);
            }
        }
        params.context.restore();
    };

    this.addLayer(Scene.LAYER_BACKGROUND);
    this.addLayer(Scene.LAYER_FOREGROUND);
    this.addLayer(Scene.LAYER_OVERLAY);
    this.addLayer(Scene.LAYER_WIDGETS);
}

Scene.LAYER_BACKGROUND  = "b";
Scene.LAYER_FOREGROUND  = "f";
Scene.LAYER_OVERLAY     = "o";
Scene.LAYER_WIDGETS     = "w";

Scene.DISPLAY_GROUP_CONTENT		= 1;
Scene.DISPLAY_GROUP_UI			= 2;
Scene.DISPLAY_GROUP_ANY			= 3;