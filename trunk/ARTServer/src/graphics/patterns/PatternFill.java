package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class PatternFill implements IPattern
{
	private static final int SEGMENT_COUNT = 200;
	
	@Override
	public void begin(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        context.save();
        context.setFillStyle(fgColor);
        context.beginPath();
        Vector2D v = new Vector2D();
		map.transform(0, 0, v);        
		context.moveTo(v.x, v.y);        
	}

	@Override
	public void end(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        context.closePath();
        context.fill();
        context.restore();        
	}

	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
		double tempX;
		double tempY;
		
		double rs = map.getRangeXStart();
        double re = map.getRangeXEnd();

        double oldX = Double.MIN_VALUE;
        double oldY = Double.MIN_VALUE;
        Vector2D v = new Vector2D();
		double stride = (re - rs) / (double)SEGMENT_COUNT;
        
		for(int i=1; i<=SEGMENT_COUNT; i++)
        {
        	map.transform(rs + i * stride, 0, v);        	
    		tempX = v.x - oldX;
    		tempY = v.y - oldY;    		
    		tempX *= tempX;
    		tempY *= tempY;
    		
    		if(tempX + tempY < 9.0) continue; 
    		
    		oldX = v.x;
    		oldY = v.y;
            context.lineTo(v.x, v.y);
        }
		
		tempX = v.x - oldX;
		tempY = v.y - oldY;    		
		tempX *= tempX;
		tempY *= tempY;
		
		if(tempX + tempY > 0.5)
		{
			map.transform(rs + SEGMENT_COUNT * stride, 0, v);        	
			context.lineTo(v.x, v.y);
		}		
	}
}
