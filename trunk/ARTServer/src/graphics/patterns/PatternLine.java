package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class PatternLine implements IPattern
{
	public static final int LSCORNER_NONE = 0;
	public static final int LSCORNER_EDGE = 1;
	public static final int LSCORNER_INDENT = 2;

	public static final class LineSpec
	{
		public final double radius;
		public final double size;
		public final double distance;
		public final int corner;
		
		public LineSpec(double radius, double size, double distance, int corner)
		{
			this.radius = radius;
			this.size = size;
			this.distance = distance;
			this.corner = corner;
		}
		
		public LineSpec(double radius, double size, double distance, String corner)
		{
			this.radius = radius;
			this.size = size;
			this.distance = distance;
			if(corner.equals("indent"))
			{
				this.corner = LSCORNER_INDENT;
			}
			else if(corner.equals("edge"))				
			{
				this.corner = LSCORNER_EDGE;
			}
			else
			{
				this.corner = LSCORNER_NONE;				
			}
		}
	}
	
	private final LineSpec[] _lineSpecs;
	private final double _defaultHeight;
	

	@Override
	public void begin(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void end(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        double scale = size / _defaultHeight;
        double indent = 0.9;
        
        context.setFillStyle(fgColor);        
        
        Vector2D v = new Vector2D();
        Vector2D v_a = new Vector2D(); 
        Vector2D v_b = new Vector2D(); 
        Vector2D v_c = new Vector2D(); 
        
        for(int iLineSpec=0; iLineSpec <_lineSpecs.length; iLineSpec++)
        {
            double lineBottom = _lineSpecs[iLineSpec].distance * scale;
            double lineTop = lineBottom + _lineSpecs[iLineSpec].size * scale;
            int type = _lineSpecs[iLineSpec].corner;
            
            if(type == LSCORNER_EDGE)
            {
                context.beginPath();
                
                map.transform(0, lineTop, v);
                context.moveTo(v.x, v.y);
                
                map.transform(lineTop, lineTop, v);
                context.lineTo(v.x, v.y);

                map.transform(lineTop, 0, v);
                context.lineTo(v.x, v.y);

                map.transform(lineBottom, 0, v);
                context.lineTo(v.x, v.y);

                map.transform(lineBottom, lineBottom, v);
                context.lineTo(v.x, v.y);

                map.transform(0, lineBottom, v);
                context.lineTo(v.x, v.y);
                
                context.closePath();
                context.fill();
            }
            else if(type == LSCORNER_INDENT)
            {
                double radius = _lineSpecs[iLineSpec].radius * scale;
                
                context.beginPath();
                
                if((radius > 0 ) && (radius <= lineBottom))
                {
                    map.transform(0, lineTop, v_a);
                    context.moveTo(v_a.x, v_a.y);
                    
                    if(lineBottom > radius)
                    {
                        map.transform(lineTop - radius, lineTop, v_a);
                        context.lineTo(v_a.x, v_a.y);
                    }
                    
                    map.transform(lineTop - radius * indent, lineTop - radius * indent, v_b);
                    map.transform(lineTop, lineTop - radius, v_c);
                    
                    context.quadraticCurveTo(v_b.x, v_b.y, v_c.x, v_c.y);
                    
                    if(lineBottom > radius)
                    {
                        map.transform(lineTop, 0, v_c);
                        context.lineTo(v_c.x, v_c.y);
                        
                        map.transform(lineBottom, 0, v_c);
                        context.lineTo(v_c.x, v_c.y);
                        
                        map.transform(lineBottom, lineBottom - radius, v_c);
                        context.lineTo(v_c.x, v_c.y);
                    }
                    else
                    {
                        map.transform(lineBottom, 0, v_c);
                        context.lineTo(v_c.x, v_c.y);
                    }
                    
                    map.transform(lineBottom - radius * indent, lineBottom - radius * indent, v_b);
                    map.transform(lineBottom - radius, lineBottom, v_a);
                    
                    context.quadraticCurveTo(v_b.x, v_b.y, v_a.x, v_a.y);
                    
                    if(lineBottom > radius)
                    {
                        map.transform(0, lineBottom, v_a);
                        context.lineTo(v_a.x, v_a.y);
                    }
                    
                    context.closePath();
                    context.fill();
                }
            }
        }
	}

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        double scale = size / _defaultHeight;
        double spacing = 5;
        double rs = map.getRangeXStart();
        double re = map.getRangeXEnd();
        int segments = (int)((re - rs) / spacing);
        spacing = (re - rs) / segments;
        double offsetX = rs;
        
        context.setFillStyle(fgColor);
        
        Vector2D v = new Vector2D();
        for(int iLineSpec=0; iLineSpec <_lineSpecs.length; iLineSpec++)
        {
            double lineBottom = _lineSpecs[iLineSpec].distance * scale;
            double lineTop = lineBottom + _lineSpecs[iLineSpec].size * scale;
            
            context.beginPath();
            map.transform(offsetX, lineTop, v);
            context.moveTo(v.x, v.y);
            
            for(int i=0; i<= segments; i++)
            {
                map.transform(i * spacing + offsetX, lineTop, v);
                context.lineTo(v.x, v.y);
            }
            
            for(int i=segments; i>=0; i--)
            {
                map.transform(i * spacing + offsetX, lineBottom, v);
                context.lineTo(v.x, v.y);
            }
            
            context.closePath();
            context.fill();
        }		
	}
	
	public PatternLine(LineSpec[] lineSpecs)
	{
	    double defaultHeight = 0;
	    for(int i=0; i <lineSpecs.length; i++)
	    {
	        if(lineSpecs[i].distance + lineSpecs[i].size > defaultHeight)
	        {
	            defaultHeight = lineSpecs[i].distance + lineSpecs[i].size;
	        }
	    }
	    _lineSpecs = lineSpecs;
	    _defaultHeight = defaultHeight;
	}
	
}
