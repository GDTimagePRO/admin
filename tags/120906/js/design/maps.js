//TODO: Elipse map gets into an infinite loop when width = height = 0

function LineMap( destX1, destY1, destX2, destY2, srcX )
{
    var width = destX2 - destX1;
    var height = destY2 - destY1;
    var len = Math.sqrt( width * width + height * height );
    var angle = Math.atan2( height, width );
    
    var ca = Math.cos(angle);
    var sa = Math.sin(angle);
    
    this.range = [srcX, srcX + len]; 
            
    this.transform = function(x ,y)
    {
        x = x - srcX;
                
        return [
            x * ca + y * sa + destX1 ,  
            x * sa - y * ca + destY1 ,
            angle
        ];
    };
}

function CornerMap(mapA, mapB)
{
    var v1 = mapB.transform(mapB.range[0], 0);        
    var v2 = mapB.transform(mapB.range[0], 100);
    var angle = Math.atan2( v2[1] - v1[1], v2[0] - v1[0] );
        
    var ca = Math.cos(angle);
    var sa = Math.sin(angle);
        
    this.range = [0, 0]; 
                
    this.transform = function(x ,y)
    {
        return [
            x * ca + y * sa + v1[0] ,  
            x * sa - y * ca + v1[1] ,
            angle
          ];
    };
}
      
function CircleMap( centerX, centerY, angleStart, angleEnd, radius, srcX, srcScale )
{
    var len = Math.abs(angleEnd - angleStart) * radius / srcScale;
    this.range = [srcX, srcX + len];
        
    this.transform = function( x, y )
    {
        var a = (x - srcX) * srcScale / radius + angleStart;
        var h = y + radius;
        return [
            h * Math.cos(a) + centerX ,
            h * Math.sin(a) + centerY ,
            a + Math.PI / 2
        ];
    };      
}

function EllipseMap( offsetX, offsetY, width, height, angle, srcScale )
{
    var mapD = [];
    var mapT = [];
    var mapTD = [];
    
    var hW = width / 2;
    var hH = height / 2;
    var wh = hW / hH;   
    var hPI = Math.PI / 2.0;
    var dPI = Math.PI * 2.0;

    var steps = 300.0;
    var stepSize = dPI / steps; 
     
    var offsetD = 0;        
        
    var len = 0;
    var lenSum = 0;
    var oldX = hW * Math.cos(0);
    var oldY = hH * Math.sin(0);
    var newX = 0;
    var newY = 0;
    var t = 0;

    mapD.push(0);
    mapT.push(0);

    offsetX += width / 2;
    offsetY += height / 2;

    for(var i=1; i <= steps; i++)
    {
        t = stepSize * i;
        newX = hW * Math.cos(t);
        newY = hH * Math.sin(t);
        len = Math.pow(newX - oldX, 2) + Math.pow(newY - oldY, 2);
        if(len > 0)
        {
            lenSum += Math.sqrt(len);
            mapD.push(lenSum);
            mapT.push(t);           
        }
        oldX = newX;
        oldY = newY;
    }
    
    mapD.push(lenSum + 0.01);
    mapT.push(stepSize * steps + 0.01);     

    while(angle < 0) angle += dPI;
    while(angle > dPI) angle -= dPI;
    var offsetT = Math.atan(Math.tan(angle) * (hW / hH));
    if((angle > hPI) && (angle <= 3 * hPI)) offsetT += Math.PI; 
    while(offsetT < 0) offsetT += dPI;
    while(offsetT > dPI) offsetT -= dPI;
    
    for(var i=0; i<mapD.length-2; i++)
    {       
        mapTD.push((mapT[i + 1] - mapT[i]) / (mapD[i + 1] - mapD[i]));
        if((offsetT >= mapT[i]) && (offsetT < mapT[i + 1]))
        {
            offsetD = (offsetT - mapT[i]) / (mapT[i + 1] - mapT[i]) * (mapD[i + 1] - mapD[i]) + mapD[i];  
        } 
    }


    var negRangeA = hPI;
    var negRangeB = 3 * hPI;    
    var selected = 0;    
    this.range = [0, lenSum / srcScale];

    this.transform = function( x, y )
    {       
        x += offsetD;
        while(x < 0) x += this.range[1];
        while(x >= this.range[1]) x -= this.range[1];
        x *= srcScale;
        
        if(x < 0) x += this.range[1];
        if(x >= this.range[1]) x -= this.range[1];
        
        
        if((mapD[selected] > x) || (mapD[selected + 1] <= x))
        {
            var a = 0;
            var b = mapD.length - 2;
                        
            while(true)
            {
                selected = (b + a) >> 1;
                     
                if(mapD[selected] > x)
                {
                    if(selected == a) break;
                    b = selected - 1;                 
                }
                else if(mapD[selected + 1] <= x)
                {
                    if(selected == b) break;
                    a = selected + 1;                             
                }
                else break;
            }
        };
                
        var t = (x - mapD[selected]) * mapTD[selected] + mapT[selected];        
        var a = Math.atan(wh * Math.tan(t));
        if((t > negRangeA) && (t <= negRangeB)) a += Math.PI;
        
        return [
            hW * Math.cos(t) + offsetX + y * Math.cos(a),
            hH * Math.sin(t) + offsetY + y * Math.sin(a), 
            a + hPI
        ];
    };      
}

function CompositeMap()
{
    this.range = [0, 0];

    var maps = [];
    var selected = null;
        
    this.addMap = function(map)
    {
        maps.push(map);          
        this.range[1] = map.range[1] 
        if(selected == null) selected = map;
    };

    this.transform = function(x, y)
    {
        var x = x - Math.floor(x / this.range[1]) * this.range[1];
        if((selected.range[0] > x) || (selected.range[1] <= x))
        {
            var a = 0; 
            var b = maps.length - 1;
                        
            while(true)
            {
                var c = (b + a) >> 1;
                selected = maps[c];
                     
                if(selected.range[0] > x)
                {
                    if(c == a) break;
                    b = c - 1;                 
                }
                else if(selected.range[1] <= x)
                {
                    if(c == b) break;
                    a = c + 1;                                 
                }
                else break;
            }             
        }
        return selected.transform(x ,y);                
    };
}

function MapCollection()
{
    var canAddCorner = false;
    var cornerUpdateNeeded = false;
    
    this.range = [0, 0];
    this.maps = [];
    this.corners = [];
                        
    this.addMap = function(map)
    {
        if(cornerUpdateNeeded)
        {
            this.corners[this.corners.length - 1] = new CornerMap(
                this.maps[this.maps.length - 1], map
            );
            cornerUpdateNeeded = false;
        }
        this.maps.push(map);
        canAddCorner = true;
    };
        
    this.addCorner = function()
    {
        if(!canAddCorner) return;          
        this.corners.push( new CornerMap(
            this.maps[this.maps.length - 1], this.maps[0]
        ));
        cornerUpdateNeeded = true;
        canAddCorner = false;
    };
}
      
function roundedRectangleMap(x1, y1, x2, y2, radius, srcScale)
{
    if(radius < 0) radius = 0;
    if(x1 > x2) { var tmp = x2; x2 = x1; x1 = tmp; }
    if(y1 > y2) { var tmp = y2; y2 = y1; y1 = tmp; }
    
    var width = x2 - x1; 
    var height = y2 - y1;        
    var diameter = radius * 2;
    
    if(width < diameter) diameter = width;
    if(height < diameter) diameter = height;
    
    width = width - diameter;
    height = height - diameter;
    
    radius = diameter / 2;
    
    var map = (radius > 0) ? new CompositeMap() : new MapCollection();
        
    //top line segment
    if(width > 0)
    {
        map.addMap(new LineMap(
            x1 + radius, y1,
            x2 - radius, y1, 
            map.range[1]
        ));
    }     

    //top right corner
    if(radius > 0)
    {
        map.addMap(new CircleMap(
            x2 - radius, 
            y1 + radius,
            Math.PI * 3/2, 2 * Math.PI,
            radius,
            map.range[1],
            srcScale
        ));
    }
    else map.addCorner();

    //right line segment
    if(height > 0)
    {
        map.addMap(new LineMap(
            x2, y1 + radius, 
            x2, y2 - radius, 
            map.range[1]
        ));
    }        

    //bottom right corner
    if(radius > 0)
    {
        map.addMap(new CircleMap(
            x2 - radius, 
            y2 - radius,
            0, Math.PI * 1/2,
            radius,
            map.range[1],
            srcScale
        ));
    }   
    else map.addCorner(); 

    //bottom line segment
    if(width > 0)
    {
        map.addMap(new LineMap(
            x2 - radius, y2, 
            x1 + radius, y2,
            map.range[1]
        ));
    }

    //bottom left corner
    if(radius > 0)
    {
        map.addMap(new CircleMap(
            x1 + radius, 
            y2 - radius,
            Math.PI *  1/2, Math.PI,
            radius,
            map.range[1],
            srcScale
        ));
    }
    else map.addCorner();

    //left line segment
    if(height > 0)
    {
        map.addMap(new LineMap(
            x1, y2 - radius, 
            x1, y1 + radius, 
            map.range[1]
        ));
    }
    
    //top left corner
    if(radius > 0)
    {
        map.addMap(new CircleMap(
            x1 + radius,
            y1 + radius,
            Math.PI, Math.PI * 3/2,
            radius,
            map.range[1],
            srcScale
        ));
    }
    else map.addCorner();    
    
    return map;
}
      
