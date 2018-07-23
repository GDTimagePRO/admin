function RectangularClipMask(width, height)
{
	this.getState = function()
	{
		return {
			n: "RCM",
			p: [width, height]
		};
	};
	
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

	this.getState = function()
	{
		return {
			n: "RCM",
			p: [width, height]
		};
	};
	
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

function SceneLayer(scene, name)
{
    var drawables = [];
    
    
    this.visible = true;
    this.clipMask = null;
    
    
	this.getState = function(filter)
	{
		if(!filter) filter = Scene.DISPLAY_GROUP_ANY;
		
		var ds = [];
        for(var i=0; i<drawables.length; i++)
        {
            if((filter & drawables[i].displayGroup) && (drawables[i].visible))
            {
            	ds.push(drawables[i].getState());
            }
        }
			
		return {
			n: name,
			cm: (this.clipMask) ? this.clipMask.getState() : null ,
			d: ds
		};
	};
    
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
        //if(this.clipMask != null) this.clipMask.onDraw(params);

        for(var i=0; i<drawables.length; i++)
        {
            //if(params.filter & drawables[i].displayGroup)
        	if(drawables[i].isWidget)
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

var COLOR_CODES = [];
COLOR_CODES['black']			=	'#000000';
COLOR_CODES['firebrick']		=	'#B22222';
COLOR_CODES['royalblue']		=	'#4169E1';
COLOR_CODES['crimson']			=	'#DC143C';
COLOR_CODES['palevioletred']	=	'#DB7093';
COLOR_CODES['limegreen']		=	'#32CD32';
COLOR_CODES['dodgerblue']		=	'#1E90FF';
COLOR_CODES['sienna']			=	'#A0522D';
COLOR_CODES['slateblue']		=	'#6A5ACD';

function getColorCode(color)
{
	if(color.charAt(0) == '#') return color; 
	var c = COLOR_CODES[color.toLocaleLowerCase()];
	if(c) return c;
	return color;
}

function Scene(canvas, width, height)
{
	var me = this;
	var layers = {};
    var zorder = [];
    var capture = null;
    var context = null;
    
    this.getWidth = function() { return width; };
    this.getHeight = function() { return height; };
    
	this.inkColor = "black";//"#000000";
    
	this.getSSRDO = function(filter)
	{
		if(!filter) filter = Scene.DISPLAY_GROUP_ANY;
		
		var layerParams = [];
		for(var i=0; i < zorder.length; i++ )
		{
			layerParams.push(zorder[i].getState(filter));
		}
		return {
			s: this.scale,
			w: width,
			h: height,
			ink: getColorCode(this.inkColor),
			l: layerParams,
		};
	};

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
    };

    this.canvas = canvas;
    this.scale = 1.0;
    this.offsetX = width / 2;
    this.offsetY = height / 2;
    this.width = width;
    this.height = height;
    this.backgroundColor = "#808080";
    
    var isDrawing = false; 
    this.setDrawingEnabled = function(value)
    {
    	isDrawing = !value;
    };
    
    this.getState = function()
    {
    	return {
    		inkColor : this.inkColor,
    		backgroundColor : this.backgroundColor     		
    	};
    };

    this.setState = function(state)
    {
    	if(state)
    	{
    		this.inkColor = state.inkColor;
    		//this.backgroundColor = state.backgroundColor; 
    	}
    	else
    	{
    		this.inkColor = "black";
    		//this.backgroundColor = null; 
    	}
    };
    
    this.addLayer = function(name)
    {
        var newLayer = new SceneLayer(this, name); 
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

    var serverRenderedImageBackBuffer = null;
    var serverRenderedImage = null;
    var serverRenderRequested = false;
    var serverRenderPending = false;
    var serverRenderScale = 1.0;
    var serverRenderRequestScale = 1.0;
    
    var updateTimerPendingUpdates = 0;
    
    window.setInterval( function() {
    	if(updateTimerPendingUpdates < 1) return;
    	updateTimerPendingUpdates--;
    	me.redraw(true);
    }, 20);
    
    var onRenderSuccess = function()
    {
		setTimeout(function() {
	    	serverRenderedImage = serverRenderedImageBackBuffer;
	    	serverRenderedImageBackBuffer = null;    	
	    	serverRenderScale = serverRenderRequestScale;
	    	me.redraw(true);
	    	updateTimerPendingUpdates = 40;
	    	
	    	serverRenderRequested = false;
	    	if(serverRenderPending)
	    	{
	    		me.sendRenderRequest(true);    		
	    	}
		}, 1);
    };
    
    var onRenderFailure = function()
    {
    	serverRenderedImageBackBuffer = null;
    	serverRenderRequested = false;
    	serverRenderPending = false;
    	me.sendRenderRequest();
    };
    
    this.getRenderServiceQuery = function(imageWidth, imageHeight, imgDomain, inkColor, scale, filter, dest, frameWidth, frameHeight, fillColor, clearCache)
    {
    	var oldInkColor = this.inkColor;
    	var oldScale = this.scale; 
    	
    	try
    	{
        	if(inkColor) this.inkColor = inkColor;
        	if(scale) this.scale = scale;
    		var src = Scene.RENDER_SERVICE_URL;
            src += "?imgWidth=" + imageWidth;
            src += "&imgHeight=" + imageHeight;
            src += "&imgDomain=" + imgDomain;
            if(clearCache) src += "&clearCache=true";
            if(dest) src += "&dest=" + dest;
            if(frameWidth) src += "&imgFrameWidth=" + frameWidth;
            if(frameHeight) src += "&imgFrameHeight=" + frameHeight;
            
            if(fillColor)
            {	
            	src += "&fillColor=" + encodeURIComponent(fillColor);
            }
            else if(this.backgroundColor)
            {
            	src += "&fillColor=" + encodeURIComponent(this.backgroundColor);
            }
            
            src += "&sceneJSON=" + encodeURIComponent($.toJSON(this.getSSRDO(filter)));
            return src; 
    	}
    	finally
    	{
    		this.inkColor = oldInkColor;
    		this.scale = oldScale; 
    	}
    };

    var sendRenderRequest_timeout = null;
    this.sendRenderRequest = function(isUrgent)
    {
    	if(serverRenderedImageBackBuffer)
    	{
        	var elapsedTime = (new Date()).getTime() - serverRenderedImageBackBuffer.time; 
    		if(elapsedTime > 1000)
        	{
            	serverRenderedImageBackBuffer.onload = null;
        		serverRenderedImageBackBuffer.onerror = null;
        		serverRenderedImageBackBuffer.onabort = null;
        		serverRenderedImageBackBuffer = null;
            	serverRenderRequested = false;
            	serverRenderPending = false;        		
        	}
    	}
    		
    	if(serverRenderRequested)
    	{
    		serverRenderPending = true;
    	}
    	else
    	{    		
    		serverRenderPending = false;
    		serverRenderRequested = true;
    		serverRenderedImageBackBuffer = new Image();
    		serverRenderedImageBackBuffer.time = (new Date()).getTime();    		
    		
    		clearTimeout(sendRenderRequest_timeout);
    		sendRenderRequest_timeout = setTimeout(function() { 

    			serverRenderPending = false;
    			serverRenderRequestScale = me.scale;
        		src = me.getRenderServiceQuery(width, height, "web_" + me.inkColor + "."); 	        
        		serverRenderedImageBackBuffer.onload = onRenderSuccess;
        		serverRenderedImageBackBuffer.onerror = onRenderFailure;
        		serverRenderedImageBackBuffer.onabort = onRenderFailure;
        		serverRenderedImageBackBuffer.src = src;
        		
    		}, isUrgent ? 1 : 60);
    	}
    };
    
    this.redraw = function(suppressUpdate)
    {
    	if(isDrawing) return;
    	
    	try
    	{
        	isDrawing = true;
    		
        	if(suppressUpdate != true) this.sendRenderRequest();
        	
	    	var canvas = this.canvas;        
	        var params = this.getCommonParams();
	        
			//if(filter) params.filter = filter;
					        
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

	        if(serverRenderedImage)
	        {
				params.context.save();			
		        var scaleDelta =  this.scale / serverRenderScale;
		        params.context.scale(scaleDelta,scaleDelta);			
				params.context.drawImage(
						serverRenderedImage,
						-serverRenderedImage.width / 2,
						-serverRenderedImage.height / 2,
						serverRenderedImage.width,
						serverRenderedImage.height
						);
				
	        	params.context.restore();
	        }
			
			params.context.scale(this.scale,this.scale);			

	        for(var i=0; i<zorder.length; i++)
	        {
	            if(zorder[i].visible)
	            {
	                zorder[i].onDraw(params);
	            }
	        }
    	}
    	finally
    	{
        	params.context.restore();
        	isDrawing = false;
    	}
    };

    this.drawTo = function(canvas, width, height, scale, filter, inkColor, backgroundColor, trace)
    {
    	var old_inkColor = this.inkColor;
    	var old_canvas = this.canvas;
    	var old_scale = this.scale;
    	var old_offsetX = this.offsetX;
    	var old_offsetY = this.offsetY;
    	var old_width = this.width;
    	var old_height = this.height;
    	var old_backgroundColor = this.backgroundColor;
    	
    	try
    	{
    		this.inkColor = inkColor;
    		this.canvas = canvas;
    		this.scale = scale;
    		this.offsetX = width / 2;
    		this.offsetY = height / 2;
    		this.width = width;
    		this.height = height;
    		this.backgroundColor = backgroundColor;
    		
    		return this.redraw(filter, trace);

    	}
    	finally
    	{
    		this.inkColor = old_inkColor;
    		this.canvas = old_canvas;
    		this.scale = old_scale;
    		this.offsetX = old_offsetX;
    		this.offsetY = old_offsetY;
    		this.width = old_width;
    		this.height = old_height;
    		this.backgroundColor = old_backgroundColor;
    	}
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

Scene.RENDER_SERVICE_URL = "";

Scene.DISPLAY_GROUP_CONTENT		= 1;
Scene.DISPLAY_GROUP_UI			= 2;
Scene.DISPLAY_GROUP_ANY			= 3;