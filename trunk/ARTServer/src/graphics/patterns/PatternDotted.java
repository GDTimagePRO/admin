package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class PatternDotted implements IPattern
{
	@Override
	public void begin(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void end(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        double height = 5;
        double width = 4;
        double spacing = 7;
        
        double rs = map.getRangeXStart();
        double re = map.getRangeXEnd();
        double scale = size / height;
        height = size;
        width = width * scale;
        spacing = spacing * scale * spacingScale;
        
        context.setFillStyle(fgColor);
        
        int segments = (int)((re - rs) / spacing);
        spacing = (re - rs) / segments;
        
        double v1X = -width / 2, y;
        double v1Y = -3 * scale;

        double v3X = -width / 2;
        double v3Y = -6 * scale;

        double v4X = width / 2;
        double v4Y = -6 * scale;

        double v5X = width / 2;
        double v5Y = -3 * scale;

        double v7X = width / 2;
        double v7Y = 0;

        double v8X = -width / 2;
        double v8Y = 0;
        
        Vector2D v = new Vector2D();
        
        for(int i=0; i<segments; i++)
        {
            map.transform(i * spacing + spacing / 2, 0, v);
            
            context.save();
            
            context.translate(v.x, v.y);
            context.rotate(v.angle);
            
            context.beginPath();
            
            context.moveTo(v1X , v1Y);
            context.bezierCurveTo(
                v3X, v3Y,
                v4X, v4Y,
                v5X, v5Y
            );
            
            context.bezierCurveTo(
                v7X, v7Y,
                v8X, v8Y,
                v1X, v1Y
            );
            
            context.closePath();
            context.fill();
            
            context.restore();
        }
	}
}
