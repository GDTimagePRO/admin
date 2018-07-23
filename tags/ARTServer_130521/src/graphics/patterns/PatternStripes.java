package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class PatternStripes  implements IPattern
{

	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
	}

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        double height = 20;
        double width = 4;
        double spacing = 10;

        double scale = size / height;
        height = size;
        width = width * scale;
        spacing = spacing * scale * spacingScale;
        
        context.setFillStyle(fgColor);
        
        double segments = Math.floor((map.getRangeXEnd() - map.getRangeXStart()) / spacing);
        spacing = (map.getRangeXEnd() - map.getRangeXStart()) / segments;

        Vector2D v = new Vector2D();
        for(int i=0; i<segments; i++)
        {
            map.transform(i * spacing, 0, v);
            
            context.save();
            
            context.translate(v.x, v.y);
            context.rotate(v.angle);
            context.translate(-width/2, - height);
            context.fillRect(0,0,width, height);
            
            context.restore();
        }
	}
}
