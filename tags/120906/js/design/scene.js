function RectangularClipMask(x1, y1, x2, y2)
{
    this.x1 = x1;
    this.y1 = y1;
    this.x2 = x2;
    this.y2 = y2;

    this.onDraw = function(params)
    {
        ctx.beginPath();
        //ctx.arc(0,0,60,0,Math.PI*2,true);
        ctx.clip();
    };
}

function CircularClipMask(x, y, radius)
{
    this.x = x;
    this.y = y;
    this.radius = radius;

    this.onDraw = function(params)
    {
        var ctx = params.context;
        ctx.beginPath();
        ctx.arc(
            this.x, this.y,
            this.radius,
            0,Math.PI*2,false
        );
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
            drawables[i].onDraw(params);
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


function Scene(canvasId, width, height)
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

    this.canvasIs = canvasId;
    this.scale = 1.0;
    this.offsetX = width / 2;
    this.offsetY = height / 2;
    this.width = width;
    this.height = height;
    
    this.addLayer = function(name)
    {
        var newLayer = new SceneLayer(this); 
        zorder.push(newLayer);
        layers[name] = newLayer;
        return  newLayer;
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

    this.redraw = function()
    {
        var canvas = document.getElementById(this.canvasIs);        
        var params = this.getCommonParams();
        
        params.context = canvas.getContext("2d");
        params.context.clearRect( 0 , 0 , canvas.width , canvas.height );        
        params.context.save();
		params.context.translate(this.offsetX, this.offsetY);		        
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