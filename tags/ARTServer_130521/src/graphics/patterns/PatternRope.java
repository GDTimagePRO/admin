package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class PatternRope implements IPattern
{

	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
	}

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        double spacing = 10;
        double height = 15;
        
        double scale = size / height;
        height = size;
        spacing = spacing * scale * spacingScale;
        
		context.setFillStyle(fgColor);
		context.setStrokeStyle(fgColor);

		double rs = map.getRangeXStart();
		double re = map.getRangeXEnd();
		
        int segments = (int)((re - rs) / spacing);
        spacing = (re - rs) / segments;
        
        context.save();        
        
        context.setLineWidth(1.6);
        context.beginPath();
        
        double rv1X = spacing * -1.46;
        double rv1Y = height * 0.2;
        		
        double rv2X = spacing * -0.2;
        double rv2Y = height * -0.3;

        double rv3X = spacing * 0.2;
        double rv3Y = height * 1.4;

        double rv4X = spacing * 1.46;
        double rv4Y = height * 0.8;
        

        Vector2D v1 = new Vector2D();
        Vector2D v3 = new Vector2D();
        Vector2D v2 = new Vector2D();
        Vector2D v4 = new Vector2D();

        for(int i=0; i<segments; i++)
        {
            double s = i * spacing;
            map.transform(s + rv1X, rv1Y, v1);
            map.transform(s + rv3X, rv3Y, v3);
            map.transform(s + rv2X, rv2Y, v2);
            map.transform(s + rv4X, rv4Y, v4);
            
            context.moveTo(v1.x, v1.y);
            context.bezierCurveTo(
                v2.x, v2.y,
                v3.x, v3.y,
                v4.x, v4.y
            );
        }
        context.stroke();
        context.restore();
	}
}
