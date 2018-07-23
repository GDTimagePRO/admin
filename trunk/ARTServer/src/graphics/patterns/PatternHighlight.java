package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class PatternHighlight  implements IPattern
{
	private final double _paddingTop;

	@Override
	public void begin(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void end(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        context.setFillStyle(fgColor);
        context.beginPath();
        
        Vector2D v = new Vector2D();
        
        map.transform(0, size + _paddingTop, v);
        context.moveTo(v.x, v.y);

        map.transform(size + _paddingTop, size + _paddingTop, v);
        context.lineTo(v.x, v.y);

        map.transform(size + _paddingTop, 0, v);
        context.lineTo(v.x, v.y);

        map.transform(0, 0, v);
        context.lineTo(v.x, v.y);
        
        context.closePath();
        context.fill();
	}

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        double ribbonHeight = size + _paddingTop;
        double spacingRibbon = 5;
        double rs = map.getRangeXStart();
        double re = map.getRangeXEnd();
        int segmentsRibbon = (int)((re - rs) / spacingRibbon);
        spacingRibbon = (re - rs) / segmentsRibbon;
        
        context.setFillStyle(fgColor);
        
        context.beginPath();
        Vector2D v_first = map.transform(0, ribbonHeight, null);
        context.moveTo(v_first.x, v_first.y);
        
        Vector2D v = new Vector2D();
        
        for(int i=0; i< segmentsRibbon; i++)
        {
            map.transform(i * spacingRibbon + rs, ribbonHeight, v);
            context.lineTo(v.x, v.y);
        }

        map.transform(re, ribbonHeight, v);
        context.lineTo(v.x, v.y);

        map.transform(re, 0, v);
        context.lineTo(v.x, v.y);
        
        for(int i=segmentsRibbon-1; i>=0; i--)
        {
            map.transform(i * spacingRibbon + rs, 0, v);
            context.lineTo(v.x, v.y);
        }
        
        context.lineTo(v_first.x, v_first.y);
        context.closePath();
        context.fill();
	}

	
	public PatternHighlight(double paddingTop, double paddingBottom)
	{
		_paddingTop = paddingTop;
	}
}
