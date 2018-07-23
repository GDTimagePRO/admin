function TouchWidget(x, y, onDrag, onDrop)
{
    var dragOffsetX = 0;
    var dragOffsetY = 0;
    
    this.displayGroup = Scene.DISPLAY_GROUP_UI;
    this.x = x;
    this.y = y;
    this.isActive = false;
    this.onDrag = onDrag;
    this.onDrop = onDrop;   
    this.visible = true;
	this.editAllowMove = true;
	this.tags = {};
	this.drawImage = false;
	
	this.isWidget = true;

	this.getState = function()
	{
		return {
			n: "TW",
			p: [this.x, this.y, this.isActive, this.editAllowMove]
		};
	};
	
    this.onDraw = function(params)
    {       
        if(!this.visible) return;
        var context = params.context;

		if (this.drawImage) {
			context.drawImage(img, this.x, this.y, 10 / params.scale, 10 / params.scale);
		} else {
			context.beginPath();
			if(this.isActive)
			{
				context.arc(this.x,this.y, 6 / params.scale, 0, 2 * Math.PI, false);
				context.fillStyle = "#8ED6FF";
				context.fill();         
				context.lineWidth = 3 / params.scale;
				context.strokeStyle = "black"; 
				context.stroke();
			}
			else
			{
				context.arc(this.x,this.y, 6 / params.scale, 0, 2 * Math.PI, false);            
				//context.fillStyle = "rgba(142,214,255,0.3)";
				context.fillStyle = this.editAllowMove ? "rgba(150,0,0,0.75)" : "rgba(150,0,0,0.25)";
				context.fill();
				context.lineWidth = 3 / params.scale;
				context.strokeStyle = params.palette.paper; 
				context.stroke();
			}
			context.closePath();
		}
        
    };
    
    this.forceCapture = function(params)
    {
        dragOffsetX = params.x - this.x; 
        dragOffsetY = params.y - this.y; 
        this.isActive = true;
        this.onMove(params);
        return this;        
    };
    
    this.onPress = function(params)
    {
        if(!this.visible) return;

        var dx = params.x - this.x;
        var dy = params.y - this.y;
        if(dx*dx + dy*dy < 180 / params.scale)
        {
            dragOffsetX = dx; 
            dragOffsetY = dy; 
            this.isActive = true;
            this.onMove(params);
            return this;            
        }
        return null;
    };

    this.onRelease = function(params)
    {
        if(this.isActive)
        {
			if (params.x && params.y) {
				this.onMove(params);
			}
            this.isActive = false;
            if(this.onDrop) this.onDrop(this);
            return this;
        }
        return null;
    };

    this.onMove = function(params)
    {
        if(!this.isActive) return null;
        if(!this.editAllowMove) return this;
        
        params.dirty = true;
        this.x = params.x - dragOffsetX;
        this.y = params.y - dragOffsetY;        
        if(this.onDrag) this.onDrag(this);          
        return this;
    };
}

function RectangleWidget(x1, y1, x2, y2, angle, onChange, onRelease, hitTest, la)
{   
    var me = this;  
    var editAllowMove = true;    
    var points = {};
    var zorder = [];
	
	var oldX;
	var oldY;
	
	this.lockAspect = la || false;

	this.isWidget = true;

    this.displayGroup = Scene.DISPLAY_GROUP_UI;
    
	this.getState = function()
	{
	    return {
			n: "RW",
			p: [this.x1, this.y1, this.x2, this.y2, this.angle]
		};
	};
	
    var onDrag = function(sender)
    {
		if (me.rotatePoints && sender != points.angle && sender != points.middleMiddle) {
			var xm = (me.x1 + me.x2) / 2.0;
			var ym = (me.y1 + me.y2) / 2.0;
			var cos = Math.cos(-me.angle);
			var sin = Math.sin(-me.angle);
			var oldx = sender.x;
			var oldy = sender.y;
			sender.x = cos * (oldx - xm) - sin * (oldy - ym) + xm;
			sender.y = sin * (oldx - xm) + cos * (oldy - ym) + ym;
		}
        if(sender === points.topLeft)       
        {
			if (me.lockAspect) {
				oldY = oldY || sender.y;
				oldX = oldX || sender.x;
				var diffY = sender.y - oldY;
				var diffX = sender.x - oldX;
				oldY = sender.y;
				oldX = sender.x;
				if (Math.abs(diffY) > Math.abs(diffX)) {
					me.x1 = me.x1 + diffY;
					me.y1 = me.y1 + diffY;
				} else {
					me.x1 = me.x1 + diffX;
					me.y1 = me.y1 + diffX;
				}
			} else {
				me.x1 = sender.x;   
				me.y1 = sender.y;   
			}
        }
        else if(sender === points.topMiddle)        
        {
            me.y1 = sender.y;
        }   
        else if(sender === points.topRight)     
        {
			if (me.lockAspect) {
				oldY = oldY || sender.y;
				oldX = oldX || sender.x;
				var diffY = sender.y - oldY;
				var diffX = sender.x - oldX;
				oldY = sender.y;
				oldX = sender.x;
				if (Math.abs(diffY) > Math.abs(diffX)) {
					me.x2 = me.x2 - diffY;
					me.y1 = me.y1 + diffY;
				} else {
					me.x2 = me.x2 + diffX;
					me.y1 = me.y1 - diffX;
				}
			} else {
				me.x2 = sender.x;
				me.y1 = sender.y;
			}
        }
        else if(sender === points.middleLeft)       
        {
            me.x1 = sender.x;           
        }
        else if(sender === points.middleMiddle)     
        {
            var dx = (me.x2 - me.x1) / 2.0; 
            var dy = (me.y2 - me.y1) / 2.0;
            me.x1 = sender.x - dx;
            me.y1 = sender.y - dy;
            me.x2 = sender.x + dx;
            me.y2 = sender.y + dy;
        }
        else if(sender === points.middleRight)      
        {   
            me.x2 = sender.x;           
        }
        else if(sender === points.bottomLeft)       
        {
			if (me.lockAspect) {
				oldY = oldY || sender.y;
				oldX = oldX || sender.x;
				var diffY = sender.y - oldY;
				var diffX = sender.x - oldX;
				oldY = sender.y;
				oldX = sender.x;
				if (Math.abs(diffY) > Math.abs(diffX)) {
					me.x1 = me.x1 - diffY;
					me.y2 = me.y2 + diffY;
				} else {
					me.x1 = me.x1 + diffX;
					me.y2 = me.y2 - diffX;
				}
			} else {
				me.x1 = sender.x;   
				me.y2 = sender.y;  
			}			
        }
        else if(sender === points.bottomMiddle)     
        {
            me.y2 = sender.y;
        }
        else if(sender === points.bottomRight)      
        {           
			if (me.lockAspect) {
				oldY = oldY || sender.y;
				oldX = oldX || sender.x;
				var diffY = sender.y - oldY;
				var diffX = sender.x - oldX;
				oldY = sender.y;
				oldX = sender.x;
				if (Math.abs(diffY) > Math.abs(diffX)) {
					me.x2 = me.x2 + diffY;
					me.y2 = me.y2 + diffY;
				} else {
					me.x2 = me.x2 + diffX;
					me.y2 = me.y2 + diffX;
				}
			} else {
				me.x2 = sender.x;   
				me.y2 = sender.y; 
			}			
        }   
        else if(sender === points.angle)        
        {
            var xm = (me.x1 + me.x2) / 2;
            var ym = (me.y1 + me.y2) / 2;
            me.angle = Math.atan2( sender.y - ym, sender.x - xm );
        }   

        if(me.onChange)
        {
            me.onChange(me);
        }
    };
        
    var onDrop = function(sender)
    {
		oldX = undefined;
		oldY = undefined;
        if(me.onRelease && !me.lockAspect)
        {
            me.onRelease(me);
        }
    };  
    
    this.visible = true;
    
    points.topLeft      = new TouchWidget(0, 0, onDrag,onDrop);
    points.topMiddle    = new TouchWidget(0, 0, onDrag,onDrop);
    points.topRight     = new TouchWidget(0, 0, onDrag,onDrop);
    points.middleLeft   = new TouchWidget(0, 0, onDrag,onDrop);
    points.middleMiddle = new TouchWidget(0, 0, onDrag,onDrop);
    points.middleRight  = new TouchWidget(0, 0, onDrag,onDrop);
    points.bottomLeft   = new TouchWidget(0, 0, onDrag,onDrop);
    points.bottomMiddle = new TouchWidget(0, 0, onDrag,onDrop);
    points.bottomRight  = new TouchWidget(0, 0, onDrag,onDrop); 
    points.angle        = new TouchWidget(0, 0, onDrag,onDrop);

    points.topLeft.tags.allowVisible		= true;
    points.topMiddle.tags.allowVisible		= true;
    points.topRight.tags.allowVisible		= true;
    points.middleLeft.tags.allowVisible		= true;
    points.middleMiddle.tags.allowVisible	= false;
    points.middleRight.tags.allowVisible	= true;
    points.bottomLeft.tags.allowVisible		= true;
    points.bottomMiddle.tags.allowVisible	= true;
    points.bottomRight.tags.allowVisible	= true; 
    points.angle.tags.allowVisible			= true;
        
    for(var i in points) zorder.push(points[i]);
    
    var updateZOrder = function()
    {
        for(var i=0; i<zorder.length-1; i++)
        {
            if(zorder[i].isActive)
            {
                var active = zorder[i]; 
                zorder.splice(i,1);
                zorder.push(active);
                return;
            }           
        }
    };

    this.x1 = x1;
    this.y1 = y1;
    this.x2 = x2;
    this.y2 = y2;
    this.angle = angle;
    this.onChange = onChange;
    this.onRelease = onRelease;
    this.hitTest = hitTest;
    this.onSelect = null; 
	this.rotatePoints = false;
    
	var boxLineWidth = 3.0;
	var boxSpacingScale = 1.2;
    var box = new PatternMapDrawable(null, new PatternDotted(), boxLineWidth, boxSpacingScale, "rgba(0,0,0,0.25)");
    var boxParams = {x1:0,y1:0,x2:0,y2:0};      
    var angleVisible = false;
    
    var updatePointVisibility = function()
    {
    	for(var i in points)
		{    	
    		points[i].visible = editAllowMove && points[i].tags.allowVisible; 
		}    		
    	points.angle.visible = editAllowMove && angleVisible;
        box.map = null;
    };
    
    
    this.getAngleVisible = function() { return angleVisible; };
    this.setAngleVisible = function(value)
    {
    	angleVisible = value;
    	points.angle.visible = value;
    };
    
    this.setPointAllowVisible = function(name, value)
    {
    	points[name].tags.allowVisible = value;
		points[name].visible = editAllowMove && points[name].tags.allowVisible;
		points.angle.visible = editAllowMove && angleVisible;
        box.map = null;		
    };        
    
    this.getEditAllowMove = function() { return editAllowMove; };
    this.setEditAllowMove = function(value)
    {
    	editAllowMove = value;  	
    	for(var i in points) points[i].editAllowMove = value;
    	points.angle.editAllowMove = editAllowMove && angleVisible;    	
    	updatePointVisibility();
    };


    this.getTopLeft = function()
    {
        return {
            x : Math.min(this.x1, this.x2),  
            y : Math.min(this.y1, this.y2)  
        };
    };

    this.setTopLeft = function(point)
    {
        if(this.x1 <= this.x2) this.x1 = point.x;
        else this.x2 = point.x;
        
        if(this.y1 <= this.y2) this.y1 = point.y;
        else this.y2 = point.y;
    };

    this.getBottomRight = function()
    {
        return {
            x : Math.max(this.x1, this.x2),  
            y : Math.max(this.y1, this.y2)  
        };
    };

    this.setTopRight = function(point)
    {
        if(this.x1 > this.x2) this.x1 = point.x;
        else this.x2 = point.x;
        
        if(this.y1 > this.y2) this.y1 = point.y;
        else this.y2 = point.y;
    };
    
    this.onDraw = function(params)
    {
        if(!this.visible) return;
        
        var s_x1 = this.x1 < this.x2 ? this.x1 : this.x2;
        var s_y1 = this.y1 < this.y2 ? this.y1 : this.y2;
        var s_x2 = this.x1 > this.x2 ? this.x1 : this.x2;
        var s_y2 = this.y1 > this.y2 ? this.y1 : this.y2;


        if(box.map)
        {
            if( (boxParams.x1 != s_x1) ||
                (boxParams.y1 != s_y1) ||
                (boxParams.x2 != s_x2) ||
                (boxParams.y2 != s_y2) )
            {
                box.map = null;
            }
        }
        
        if((box.map == null) && 
        	editAllowMove &&
        	points.topLeft.visible && 
        	points.topRight.visible &&
    		points.bottomLeft.visible && 
    		points.bottomRight.visible)
        {
            boxParams.x1 = s_x1;
            boxParams.y1 = s_y1;
            boxParams.x2 = s_x2;
            boxParams.y2 = s_y2;
            
            var bo = 1.5;  
            
            box.map = new MapCollection();
            box.map.addMap(new LineMap( s_x1 + bo, s_y1 + bo, s_x2 - bo, s_y1 + bo, 0));
            box.map.addCorner();
            box.map.addMap(new LineMap( s_x2 - bo, s_y1 + bo, s_x2 - bo, s_y2 - bo, 0));
            box.map.addCorner();
            box.map.addMap(new LineMap( s_x2 - bo, s_y2 - bo, s_x1 + bo, s_y2 - bo, 0));
            box.map.addCorner();
            box.map.addMap(new LineMap( s_x1 + bo, s_y2 - bo, s_x1 + bo, s_y1 + bo, 0));
            box.map.addCorner();
        }
    

        
        var xm = (this.x1 + this.x2) / 2.0;
        var ym = (this.y1 + this.y2) / 2.0;

		var cos = Math.cos(this.angle);
		var sin = Math.sin(this.angle);
		
        if(points.topLeft)
        {
			if (me.rotatePoints) {
				points.topLeft.x = (this.x1 - xm) * cos - (this.y1 - ym) * sin + xm;
				points.topLeft.y = (this.x1 - xm) * sin + (this.y1 - ym) * cos + ym;
			} else {
				points.topLeft.x = this.x1;
				points.topLeft.y = this.y1;
			}
        }

        if(points.topMiddle)
        {
			if (me.rotatePoints) {
				points.topMiddle.x = (xm - xm) * cos - (this.y1 - ym) * sin + xm;
				points.topMiddle.y = (xm - xm) * sin + (this.y1 - ym) * cos + ym;
			} else {
				points.topMiddle.x = xm;
				points.topMiddle.y = this.y1;
			}
        }

        if(points.topRight)
        {
			if (me.rotatePoints) {
				points.topRight.x = (this.x2 - xm) * cos - (this.y1 - ym) * sin + xm;
				points.topRight.y = (this.x2 - xm) * sin + (this.y1 - ym) * cos + ym;
			} else {
				points.topRight.x = this.x2;
				points.topRight.y = this.y1;
			}			
        }

        if(points.middleLeft)
        {
			if (me.rotatePoints) {
				points.middleLeft.x = (this.x1 - xm) * cos + xm;
				points.middleLeft.y = (this.x1 - xm) * sin + ym;
			} else {
				points.middleLeft.x = this.x1;
				points.middleLeft.y = ym;
			}			
        }

        if(points.middleMiddle)
        {
			points.middleMiddle.x = xm;
			points.middleMiddle.y = ym;
		}

        if(points.middleRight)
        {
			if (me.rotatePoints) {
				points.middleRight.x = (this.x2 - xm) * cos + xm;
				points.middleRight.y = (this.x2 - xm) * sin + ym;
			} else {
				points.middleRight.x = this.x2;
				points.middleRight.y = ym;
			}			
        }

        if(points.bottomLeft)
        {
			if (me.rotatePoints) {
				points.bottomLeft.x = (this.x1 - xm) * cos - (this.y2 - ym) * sin + xm;
				points.bottomLeft.y = (this.x1 - xm) * sin + (this.y2 - ym) * cos + ym;
			} else {
				points.bottomLeft.x = this.x1;
				points.bottomLeft.y = this.y2;
			}			
        }

        if(points.bottomMiddle)
        {
			if (me.rotatePoints) {
				points.bottomMiddle.x = 0 - (this.y2 - ym) * sin + xm;
				points.bottomMiddle.y = (this.y2 - ym) * cos + ym;
			} else {
				points.bottomMiddle.x = xm;
				points.bottomMiddle.y = this.y2;
			}			
        }

        if(points.bottomRight)
        {
			if (me.rotatePoints) {
				points.bottomRight.x = (this.x2 - xm) * cos - (this.y2 - ym) * sin + xm;
				points.bottomRight.y = (this.x2 - xm) * sin + (this.y2 - ym) * cos + ym;
			} else {
				points.bottomRight.x = this.x2;
				points.bottomRight.y = this.y2;
			}			
        }

        if(points.angle)
        {
            var width = s_x2 - s_x1;
            var height = s_y2 - s_y1;
            var rad = width < height ? width * 1/4 : height * 1/4;

            points.angle.x = xm + Math.cos(this.angle) * rad;
            points.angle.y = ym + Math.sin(this.angle) * rad;

            if(points.angle.isActive && params.context)
            {

                params.context.beginPath();
                params.context.arc(xm, ym, rad, 0, 2 * Math.PI, false);         

	            params.context.lineWidth = 12 / params.scale;
                params.context.strokeStyle = "rgba(255,255,255,0.75)"; 
                params.context.stroke();

	            params.context.lineWidth = 2 / params.scale;
                params.context.strokeStyle = "rgba(50,50,50,1)"; 
                //params.context.strokeStyle = "gray"; 
                params.context.stroke();
                params.context.closePath();     
            }
        }

        updateZOrder();
        
        if(params.context)
        {
			box.size  = boxLineWidth / params.scale;  
            box.onDraw(params);
            for(var i in zorder)
            {
            	zorder[i].onDraw(params);
            }
            	          
        }
    };
    
    this.onPress = function(params)
    {
        if(this.visible)
        {
            if(params.pass == 0)
            {
                for(var i in points) 
                {
                    var capture = points[i].onPress(params); 
                    if(capture) return capture;
                }               
            }
            
            if((params.pass == 1) && this.hitTest)
            {
                if(this.hitTest(params))
                {
                    return points.middleMiddle.forceCapture(params);    
                }
            }           
        }        
        else if(this.hitTest && this.onSelect && (params.pass == 1))
        {
            if(this.hitTest(params))
            {
                this.onSelect();
                if(this.visible)
                {
                    this.onDraw(params);
                    return points.middleMiddle.forceCapture(params);    
                }
            }
        }
        return null;
    };

    this.onRelease = function(params)
    {
        if(!this.visible) return;

        for(var i in points)
        {
            var capture = points[i].onRelease(params); 
            if(capture) return capture;
        }
        return null;
    };

    this.onMove = function(params)
    {
        if(!this.visible) return;

        for(var i in points)
        {
            var capture = points[i].onMove(params); 
            if(capture) return capture;
        }
        return null;
    };
        
    //if(this.onChange) this.onChange(this, x1, y1, x2, y2);
}