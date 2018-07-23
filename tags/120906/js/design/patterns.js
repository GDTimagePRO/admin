//Make ribbon not use so many segments to paint straight lines
function PatternStripes()
{
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
    };

    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var height = 20;
        var width = 4;
        var spacing = 10;

        var scale = size / height;
        height = size;
        width = width * scale;
        spacing = spacing * scale * spacingScale;
        
        context.fillStyle = fgColor;
        
        var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
        spacing = (map.range[1] - map.range[0]) / segments;

        for(var i=0; i<segments; i++)
        {
            var v = map.transform(i * spacing, 0);
            
            context.save();
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);
            context.translate(-width/2, - height);
            context.fillRect(0,0,width, height);
            
            context.restore();
        }
    };
}

function PatternStripes2()
{
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
    };

    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var height = 20;
        var width = 4;
        var spacing = 10;
        
        var scale = size / height;
        height = size;
        width = width * scale;
        spacing = spacing * scale * spacingScale;
        
        context.fillStyle = fgColor;
        
        var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
        spacing = (map.range[1] - map.range[0]) / segments;
        
        for(var i=0; i<segments; i++)
        {
            var v = map.transform(i * spacing, 0);
            
            context.save();
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);
            context.translate(-width/2, -height);
            context.rotate( Math.PI * 1/5);
            context.fillRect(0,0,width, height);
            
            context.restore();
        }
    };
}

function PatternRibbon()
{
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var v1 = map.transform(0, size);
        var v2 = map.transform(size, size);
        var v3 = map.transform(size, 0);
        var v4 = map.transform(0, 0);
        
        context.fillStyle = fgColor;

        context.beginPath();
        
        context.moveTo(v1[0], v1[1]);
        context.lineTo(v2[0], v2[1]);
        context.lineTo(v3[0], v3[1]);
        context.lineTo(v4[0], v4[1]);
        
        context.closePath();
        context.fill();
    };
    
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var ribbonHeight = size;
        var spacingRibbon = 5
        var segmentsRibbon = Math.floor((map.range[1] - map.range[0]) / spacingRibbon);
        var spacingRibbon = (map.range[1] - map.range[0]) / segmentsRibbon;
        
        context.fillStyle = fgColor;
        
        context.beginPath();
        var v_first = map.transform(i * spacingRibbon, ribbonHeight);
        context.moveTo(v_first[0], v_first[1]);
        
        for(var i=0; i<= segmentsRibbon; i++)
        {
            var v = map.transform(i * spacingRibbon, ribbonHeight);
            context.lineTo(v[0], v[1]);
        }
        
        for(var i=segmentsRibbon; i>=0; i--)
        {
            var v = map.transform(i * spacingRibbon, 0);
            context.lineTo(v[0], v[1]);
        }
        
        context.lineTo(v_first[0], v_first[1]);
        context.closePath();
        context.fill();
    };
}

function PatternHighlight(paddingTop,paddingBottom)
{
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var v1 = map.transform(0, size + paddingTop);
        var v2 = map.transform(size + paddingTop, size + paddingTop);
        var v3 = map.transform(size + paddingTop, 0);
        var v4 = map.transform(0, 0);
        
        context.fillStyle = fgColor;

        context.beginPath();
        
        context.moveTo(v1[0], v1[1]);
        context.lineTo(v2[0], v2[1]);
        context.lineTo(v3[0], v3[1]);
        context.lineTo(v4[0], v4[1]);
        
        context.closePath();
        context.fill();
    };
    
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var ribbonHeight = size + paddingTop;
        var spacingRibbon = 5
        var segmentsRibbon = Math.floor((map.range[1] - map.range[0]) / spacingRibbon);
        var spacingRibbon = (map.range[1] - map.range[0]) / segmentsRibbon;
        
        context.fillStyle = fgColor;
        
        context.beginPath();
        var v_first = map.transform(i * spacingRibbon, ribbonHeight);
        context.moveTo(v_first[0], v_first[1]);
        
        for(var i=0; i<= segmentsRibbon; i++)
        {
            var v = map.transform(i * spacingRibbon, ribbonHeight);
            context.lineTo(v[0], v[1]);
        }
        
        for(var i=segmentsRibbon; i>=0; i--)
        {
            var v = map.transform(i * spacingRibbon, -paddingBottom);
            context.lineTo(v[0], v[1]);
        }
        
        context.lineTo(v_first[0], v_first[1]);
        context.closePath();
        context.fill();
    };
}

function PatternStars()
{
    var patternRibbon = new PatternRibbon();
    var defaultHeight = 30;
    var defaultSpacing = 26;
    
    function makeStar(scale)
    {
        return [
            { x : -12.5 * scale, y :  -2.5 * scale },
            { x :  -4.5 * scale, y :  -2.5 * scale },
            { x :   0.5 * scale, y : -12.5 * scale },
            { x :   4.5 * scale, y :  -2.5 * scale },
            { x :  12.5 * scale, y :  -2.5 * scale },
            { x :   4.5 * scale, y :   3.5 * scale },
            { x :   8.5 * scale, y :  12.5 * scale },
            { x :   0.5 * scale, y :   6.5 * scale },
            { x :  -8.5 * scale, y :  12.5 * scale },
            { x :  -4.5 * scale, y :   3.5 * scale }
        ];
    }
    
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        patternRibbon.corner(map, context, size, spacingScale, fgColor, bgColor);

        context.fillStyle = bgColor;
        context.save();        
        
        var star = makeStar(size / defaultHeight);
        var v = map.transform(size * 2 / 5,  size * 2 / 5);
        
        context.translate(v[0], v[1]);
        context.rotate(v[2] + Math.PI / 4);
        
        context.beginPath();
        context.moveTo(star[0].x , star[0].y);
        
        for(var ii=1; ii<star.length; ii++)
        {
            v = star[ii];
            context.lineTo(v.x, v.y);
        }
        context.closePath();
        context.fill();
        
        context.restore();
    };
    
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var spacing = defaultSpacing;
        var scale = size / defaultHeight;
        var height = size;
        spacing = spacing * scale * spacingScale;
        
        patternRibbon.border(map, context, size, spacingScale, fgColor, bgColor);
        
        context.fillStyle = bgColor;
        
        var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
        spacing = (map.range[1] - map.range[0]) / segments;
        
        var offset = [ spacing / 2, size / 2];
        
        var star = makeStar(scale);
        
        for(var i=0; i<segments; i++)
        {
            var v = map.transform(i * spacing + offset[0],  + offset[1]);
            
            context.save();
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);
            
            context.beginPath();
            context.moveTo(star[0].x , star[0].y);
            
            for(var ii=1; ii<star.length; ii++)
            {
                v = star[ii];
                context.lineTo(v.x, v.y);
            }
            context.closePath();
            context.fill();
            
            context.restore();
        }
    };
}

function PatternRope()
{
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
    };
    
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var spacing = 10;
        var height = 15;
        
        var scale = size / height;
        height = size;
        spacing = spacing * scale * spacingScale;
        
		context.fillStyle = fgColor;

        var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
        spacing = (map.range[1] - map.range[0]) / segments;
        
        context.save();        
        
        context.lineWidth = 1.6;
        context.beginPath();
        
        var rv1 = {x: spacing * -1.46, y: height * 0.2};
        var rv2 = {x: spacing * -0.2, y: height * -0.3};
        var rv3 = {x: spacing * 0.2, y: height * 1.4};
        var rv4 = {x: spacing * 1.46, y: height * 0.8};
        
        for(var i=0; i<segments; i++)
        {
            var s = i * spacing;
            var v1 = map.transform(s + rv1.x, rv1.y);
            var v3 = map.transform(s + rv3.x, rv3.y);
            var v2 = map.transform(s + rv2.x, rv2.y);
            var v4 = map.transform(s + rv4.x, rv4.y);
            
            context.moveTo(v1[0], v1[1]);
            context.bezierCurveTo(
                v2[0], v2[1],
                v3[0], v3[1],
                v4[0], v4[1]
            );
        }
        context.stroke();
        context.restore();
    };
}
       
function PatternDotted()
{
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
    };
    
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var height = 5;
        var width = 4;
        var spacing = 7;
        
        var scale = size / height;
        height = size;
        width = width * scale;
        spacing = spacing * scale * spacingScale;
        
        context.fillStyle = fgColor;
        
        var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
        spacing = (map.range[1] - map.range[0]) / segments;
        
        var v1 = {x: -width / 2, y: -3 * scale};
        var v3 = {x: -width / 2, y: -6 * scale};
        var v4 = {x:  width / 2, y: -6 * scale};
        var v5 = {x:  width / 2, y: -3 * scale};
        var v7 = {x:  width / 2, y: 0};
        var v8 = {x: -width / 2, y: 0};
        
        for(var i=0; i<segments; i++)
        {
            var v = map.transform(i * spacing + spacing / 2, 0);
            
            context.save();
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);
            
            context.beginPath();
            
            context.moveTo(v1.x , v1.y);
            context.bezierCurveTo(
                v3.x, v3.y,
                v4.x, v4.y,
                v5.x, v5.y
            );
            
            context.bezierCurveTo(
                v7.x, v7.y,
                v8.x, v8.y,
                v1.x, v1.y
            );
            
            context.closePath();
            context.fill();
            
            context.restore();
        }
    };
}
            
function PatternHash()
{
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
    };
    
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var height = 12;
        var width = 4;
        var spacing = 7;
        
        var scale = size / height;
        height = size;
        width = width * scale;
        spacing = spacing * scale * spacingScale;
        
        context.fillStyle = fgColor;
        
        var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
        spacing = (map.range[1] - map.range[0]) / segments;
        
        var v1 = {x: -width / 2, y: -3 * scale};
        var v2 = {x: -width / 2, y: -9.5 * scale};
        var v3 = {x: -width / 2, y: -12.5 * scale};
        var v4 = {x:  width / 2, y: -12.5 * scale};
        var v5 = {x:  width / 2, y: -9.5 * scale};
        var v6 = {x:  width / 2, y: -3 * scale};
        var v7 = {x:  width / 2, y: 0};
        var v8 = {x: -width / 2, y: 0};
        
        for(var i=0; i<segments; i++)
        {
            var v = map.transform(i * spacing + spacing / 2, 0);
            
            context.save();
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);
            
            context.beginPath();
            
            context.moveTo(v1.x , v1.y);
            context.lineTo(v2.x, v2.y);
            context.bezierCurveTo(
                v3.x, v3.y,
                v4.x, v4.y,
                v5.x, v5.y
            );
            
            context.lineTo(v6.x, v6.y);
            context.bezierCurveTo(
                v7.x, v7.y,
                v8.x, v8.y,
                v1.x, v1.y
            );
            
            context.closePath();
            context.fill();
            
            context.restore();
        }
    };
}
      
function TextPattern(text, font, invert, bold, italic)
{
    var fontPre = (bold ? "bold " : "") + (italic ? "italic " : ""); 
    var fontPost = 'px "'  + font + '"';
    
    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
    };
        
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {       
        context.fillStyle = fgColor;
        
        context.save();          
        context.font = fontPre + Math.floor(size) + fontPost;
          
        var spacing = spacingScale * size - size;
        if(spacing < 0) spacing = 0;
    
        var clippedText = text;
        var segments = 0; 
        var width = 0;
        var offset = 0;  
          
        while(true)
        {
            segments = clippedText.length;
            width = context.measureText(clippedText).width + (segments - 1) * spacing;
            offset = (map.range[1] - map.range[0] - width) / 2;
            if(offset < spacing / 2)
            {
                clippedText = clippedText.slice(0, -1);
            }
            else break;
        }
        
        if(invert)
        {   
            var yOffset = size * 2/3;
            var lastChar = clippedText.length - 1;
            for(var i=0; i<segments; i++)
            {
                var c = clippedText.charAt(lastChar - i);            
                var cWidth = context.measureText(c).width;
                
                var v = map.transform(offset + (cWidth + spacing) / 2, yOffset);
                
                context.save();          
        
                context.translate(v[0], v[1]);
                context.rotate(v[2] + Math.PI);
                context.fillText(c, -cWidth / 2, 0);
                
                context.restore();
                offset = offset + cWidth + spacing;
            }           
        }
        else
        {
            for(var i=0; i<segments; i++)
            {
                var c = clippedText.charAt(i);            
                var cWidth = context.measureText(c).width;
                
                var v = map.transform(offset + (cWidth + spacing) / 2, 0);
                
                context.save();          
        
                context.translate(v[0], v[1]);
                context.rotate(v[2]);
                context.fillText(c, -cWidth / 2, 0);
                
                context.restore();
                offset = offset + cWidth + spacing;
            }           
        }
        context.restore();
    };        
}

/* lineSpecs[] : {
 *  distance : how far away from the border it is
 *  size : how think it is
 *  corner : "indent", "edge", "none"
 *  radius : radius of the corner, must be <= distance
 * }
 */
function PatternLines(lineSpecs)
{
    var defaultHeight = 0;
    for(var i=0; i <lineSpecs.length; i++)
    {
        if(lineSpecs[i].distance + lineSpecs[i].size > defaultHeight)
        {
            defaultHeight = lineSpecs[i].distance + lineSpecs[i].size;
        }
    }

    this.corner = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var scale = size / defaultHeight;
        var indent = 0.9;
        
        context.fillStyle = fgColor;        
        
        for(var iLineSpec=0; iLineSpec <lineSpecs.length; iLineSpec++)
        {
            var lineBottom = lineSpecs[iLineSpec].distance * scale;
            var lineTop = lineBottom + lineSpecs[iLineSpec].size * scale;
            var type = lineSpecs[iLineSpec].corner;
            
            if(type == "edge")
            {
                var v1 = map.transform(0, lineTop);
                var v2 = map.transform(lineTop, lineTop);
                var v3 = map.transform(lineTop, 0);
                var v4 = map.transform(lineBottom, 0);
                var v5 = map.transform(lineBottom, lineBottom);
                var v6 = map.transform(0, lineBottom);
                
                context.beginPath();
                
                context.moveTo(v1[0], v1[1]);
                context.lineTo(v2[0], v2[1]);
                context.lineTo(v3[0], v3[1]);
                context.lineTo(v4[0], v4[1]);
                context.lineTo(v5[0], v5[1]);
                context.lineTo(v6[0], v6[1]);
                
                context.closePath();
                context.fill();
            }
            else if(type == "indent")
            {
                var radius = lineSpecs[iLineSpec].radius * scale;
                
                context.beginPath();
                
                if((radius > 0 ) && (radius <= lineBottom))
                {
                    var v_a = map.transform(0, lineTop);
                    context.moveTo(v_a[0], v_a[1]);
                    
                    if(lineBottom > radius)
                    {
                        v_a = map.transform(lineTop - radius, lineTop);
                        context.lineTo(v_a[0], v_a[1]);
                    }
                    
                    var v_b = map.transform(lineTop - radius * indent, lineTop - radius * indent);
                    var v_c = map.transform(lineTop, lineTop - radius);
                    
                    context.quadraticCurveTo(v_b[0], v_b[1],v_c[0], v_c[1]);
                    
                    if(lineBottom > radius)
                    {
                        v_c = map.transform(lineTop, 0);
                        context.lineTo(v_c[0], v_c[1]);
                        
                        v_c = map.transform(lineBottom, 0);
                        context.lineTo(v_c[0], v_c[1]);
                        
                        v_c = map.transform(lineBottom, lineBottom - radius);
                        context.lineTo(v_c[0], v_c[1]);
                    }
                    else
                    {
                        v_c = map.transform(lineBottom, 0);
                        context.lineTo(v_c[0], v_c[1]);
                    }
                    
                    var v_b = map.transform(lineBottom - radius * indent, lineBottom - radius * indent);
                    var v_a = map.transform(lineBottom - radius, lineBottom);
                    
                    context.quadraticCurveTo(v_b[0], v_b[1],v_a[0], v_a[1]);
                    
                    if(lineBottom > radius)
                    {
                        v_a = map.transform(0, lineBottom);
                        context.lineTo(v_a[0], v_a[1]);
                    }
                    
                    context.closePath();
                    context.fill();
                }
            }
        }
    };
    
    this.border = function(map, context, size, spacingScale, fgColor, bgColor)
    {
        var scale = size / defaultHeight;
        var spacing = 5
        var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
        var spacing = (map.range[1] - map.range[0]) / segments;
        var offsetX = map.range[0];
        
        context.fillStyle = fgColor;
        
        for(var iLineSpec=0; iLineSpec <lineSpecs.length; iLineSpec++)
        {
            var lineBottom = lineSpecs[iLineSpec].distance * scale;
            var lineTop = lineBottom + lineSpecs[iLineSpec].size * scale;
            
            context.beginPath();
            var v = map.transform(offsetX, lineTop);
            context.moveTo(v[0], v[1]);
            
            for(var i=0; i<= segments; i++)
            {
                var v = map.transform(i * spacing + offsetX, lineTop);
                context.lineTo(v[0], v[1]);
            }
            
            for(var i=segments; i>=0; i--)
            {
                var v = map.transform(i * spacing + offsetX, lineBottom);
                context.lineTo(v[0], v[1]);
            }
            
            context.closePath();
            context.fill();
        }
    };
}