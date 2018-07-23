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
			n: "CCM",
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

function SceneLayer(scene, name, d)
{
	this.drawables = [];
    if(d) this.drawables = d;    
    
    this.scene = scene;
    this.name = name;    
    this.visible = true;
    this.clipMask = null;
    
    this.clone = function()
    {
    	var newLayer = new SceneLayer(this.scene, this.name, this.drawables.slice(0));
    	newLayer.visible = this.visible;
    	newLayer.clipMask = this.clipMask;
    	return newLayer;
    };
    
    this.clear = function()
    {
    	this.drawables = [];
    };
    
	this.getState = function(filter)
	{
		if(!filter) filter = Scene.DISPLAY_GROUP_ANY;
		
		var ds = [];
        for(var i=0; i<this.drawables.length; i++)
        {
            if((filter & this.drawables[i].displayGroup) && (this.drawables[i].visible))
            {
            	ds.push(this.drawables[i].getState());
            }
        }
			
		return {
			n: this.name,
			cm: (this.clipMask) ? this.clipMask.getState() : null ,
			d: ds
		};
	};
    
    this.add = function(drawable)
    {
        this.drawables.push(drawable);
        return true;            
    };
    
    this.remove = function(drawable)
    {
        for(var i in this.drawables)
        {
            if(this.drawables[i] === drawable)
            {                
                if(this.drawables.onRelease)
                {
                	this.drawables.onRelease(this.scene.getCommonParams());
                }
                this.drawables.splice(i,1);
                return true;
            }           
        }
        return false;           
    };
    
    this.onDraw = function(params)
    {
        params.context.save();
        //if(this.clipMask != null) this.clipMask.onDraw(params);

        for(var i=0; i<this.drawables.length; i++)
        {
            //if(params.filter & this.drawables[i].displayGroup)
        	if(this.drawables[i].isWidget)
            {
            	this.drawables[i].onDraw(params);            	
            }
        }
                
        params.context.restore();
    };  

    this.onPress = function(params)
    {
        for(var i = this.drawables.length-1; i>=0; i--) 
        {
            if(this.drawables[i].onPress)
            {
                var capture = this.drawables[i].onPress(params); 
                if(capture) return capture;             
            }
        }
        return null;
    };

    this.onRelease = function(params)
    {
        for(var i in this.drawables)
        {
            if(this.drawables[i].onRelease)
            {
                var capture = this.drawables[i].onRelease(params); 
                if(capture) return capture;             
            }
        }
        return null;
    };

    this.onMove = function(params)
    {
        for(var i in this.drawables)
        {
            if(this.drawables[i].onMove)
            {
                var capture = this.drawables[i].onMove(params); 
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
    
    this.getWidth = function() { return this.width; };
    this.getHeight = function() { return this.height; };
    
	this.colors = { ink : { name : 'Black', value : '000000' }, highlight : { name : 'Blue', value : 'BFB4F0FF' }, paper : { name : 'White', value : 'FFFFFF' }, background : { name : 'White', value : 'FFFFFF' } };
    this.colorModel = '1_BIT'; 
        

	
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
			w: this.width,
			h: this.height,
			colors : this.colors,
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
            	ink: '#' + this.colors.ink.value,
            	paper: "#" + this.colors.paper.value,
            	background: "#" + this.colors.background.value,
            	highlight: this.colors.highlight.value
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
    this.backgroundColor = Scene.DEFAULT_BACKGROUND_COLOR;
	this.drawGrid = false;
    
    var isDrawing = false; 
    this.setDrawingEnabled = function(value)
    {
    	isDrawing = !value;
    };
    
    this.getState = function()
    {
    	return {
    		colors : this.colors,
    		backgroundColor : this.backgroundColor
    	};
    };

    this.setState = function(state)
    {
    	if(state)
    	{
            if (!state.colors) {
                state.colors = [];
             }
            if (!state.colors.ink) {
                state.colors.ink = { name : 'Black', value : '000000' };
            }
            if (!state.colors.highlight) {
                state.colors.highlight = { name : 'Blue', value : 'BFB4F0FF' };
            }
            if (!state.colors.background) {
                state.colors.background = { name : 'White', value : 'FFFFFF' };
            }
            if (!state.colors.paper) {
                state.colors.paper = { name : 'White', value : 'FFFFFF' };
            }
    		this.colors = state.colors;
    	}
    	else
    	{
    		this.colors = { ink : { name : 'Black', value : '000000' }, highlight : { name : 'Blue', value : 'BFB4F0FF' }, paper : { name : 'White', value : 'FFFFFF' }, background : { name : 'White', value : 'FFFFFF' } };
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
		if (x) {
			params.x = (x - this.offsetX) / this.scale;
		}
		if (y) {
			params.y = (y - this.offsetY) / this.scale;
		}
        
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
	    	var tmp = serverRenderedImage;
	    	serverRenderedImage = serverRenderedImageBackBuffer;
	    	serverRenderedImageBackBuffer = tmp;    	
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
    	serverRenderRequested = false;
    	serverRenderPending = false;
    	me.sendRenderRequest();    		
    };
    
    this.getRenderServiceQuery = function(imageWidth, imageHeight, imgDomain, inkColor, scale, filter, destId, frameWidth, frameHeight, fillColor, clearCache)
    {
    	var oldInkColor = this.colors.ink;
    	var oldScale = this.scale; 
    	
    	try
    	{
        	if(inkColor) this.colors.ink = inkColor;
        	if(scale) this.scale = scale;
    		var src = Scene.RENDER_SERVICE_URL;
            src += "?imgWidth=" + encodeURIComponent(imageWidth);
            src += "&imgHeight=" + encodeURIComponent(imageHeight);
            src += "&imgDomain=" + encodeURIComponent(imgDomain);
            if(clearCache) src += "&clearCache=true";
            if(destId) src += "&destId=" + encodeURIComponent(destId);
            if(frameWidth) src += "&imgFrameWidth=" + encodeURIComponent(frameWidth);
            if(frameHeight) src += "&imgFrameHeight=" + encodeURIComponent(frameHeight);
            
            if(fillColor)
            {	
            	if(fillColor != "null")
            	{
                	src += "&fillColor=" + encodeURIComponent(fillColor);
            	}
            }
            else if(this.backgroundColor)
            {
            	src += "&fillColor=" + encodeURIComponent(this.backgroundColor);
            }
			
			if (this.drawGrid) {
				src+= "&drawGrid=" + encodeURIComponent(this.drawGrid);
			}
            
            src += "&sceneJSON=" + encodeURIComponent($.toJSON(this.getSSRDO(filter)));
            return src; 
    	}
    	finally
    	{
    		this.colors.ink = oldInkColor;
    		this.scale = oldScale; 
    	}
    };
    
    this.sendRenderRequest = function(isUrgent)
    {
    	if(serverRenderRequested)
    	{
    		serverRenderPending = true;	
    	}
    	else
    	{    		
    		serverRenderPending = false;
    		serverRenderRequested = true;    		
    		
    		setTimeout(function() { 
    			serverRenderPending = false;
    			serverRenderRequestScale = me.scale;
        		if(me.colorModel == '1_BIT')
        		{
        			src = me.getRenderServiceQuery(me.width, me.height, "web_" + me.colors.ink.value);        			
        		}
        		else
        		{
        			src = me.getRenderServiceQuery(me.width, me.height, "web");        			
        		}
        		serverRenderedImageBackBuffer = new Image();
        		serverRenderedImageBackBuffer.onload = onRenderSuccess;
        		serverRenderedImageBackBuffer.onerror = onRenderFailure;
        		serverRenderedImageBackBuffer.onabort = onRenderFailure;
        		serverRenderedImageBackBuffer.src = src;
    		}, isUrgent ? 1 : 60);
    	}
    };

    var renderRequestEnabled = true;
    this.getRenderRequestEnabled = function() { return renderRequestEnabled; }
    this.setRenderRequestEnabled = function(value)
    {
    	serverRenderRequested = !value;
    	renderRequestEnabled = value;
    	if(!serverRenderRequested && serverRenderPending)
    	{
    		this.sendRenderRequest(true);
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
			params.context.canvas.width = this.width;
			params.context.canvas.height = this.height;
	        
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

    this.addLayer(Scene.LAYER_BACKGROUND);
    this.addLayer(Scene.LAYER_FOREGROUND);
    this.addLayer(Scene.LAYER_OVERLAY);
    this.addLayer(Scene.LAYER_WIDGETS);
}

Scene.DEFAULT_BACKGROUND_COLOR = "#FFFFFF";

Scene.LAYER_BACKGROUND  = "b";
Scene.LAYER_FOREGROUND  = "f";
Scene.LAYER_OVERLAY     = "o";
Scene.LAYER_WIDGETS     = "w";

Scene.RENDER_SERVICE_URL = "";

Scene.DISPLAY_GROUP_CONTENT		= 1;
Scene.DISPLAY_GROUP_UI			= 2;
Scene.DISPLAY_GROUP_ANY			= 3;