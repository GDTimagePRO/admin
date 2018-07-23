package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class PatternStars implements IPattern
{
	private static final PatternRibbon PATTERN_RIBBON = new PatternRibbon();  
	private static final double DEFAULT_HEIGHT = 30;
	private static final double DEFAULT_SPACING = 26;

    private double[] makeStar(double scale)
    {
        return new double[]{
        		-12.5 * scale,	 -2.5 * scale,
        		 -3.5 * scale,	 -2.5 * scale,
        		  0.0 * scale,	-12.0 * scale,
        		  3.5 * scale,	 -2.5 * scale,
        		 12.5 * scale,	 -2.5 * scale,
        		  4.5 * scale,	  3.5 * scale,
        		  8.5 * scale,	 12.5 * scale,
        		  0.0 * scale,	  6.5 * scale,
        		 -8.5 * scale,	 12.5 * scale,
        		 -4.5 * scale,	  3.5 * scale
        	};
    }

    @Override
	public void begin(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void end(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }
    
	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        PATTERN_RIBBON.drawCorner(map, context, size, spacingScale, fgColor, bgColor);

        context.setFillStyle(bgColor);
        context.save();        
        
        double[] star = makeStar(size / DEFAULT_HEIGHT);
        Vector2D v = map.transform(size * 2.0 / 5.0,  size * 2.0 / 5.0, null);
        
        context.translate(v.x, v.y);
        context.rotate(v.angle + Math.PI / 4);
        
        context.beginPath();
        context.moveTo(star[0] , star[1]);
        
        int pointCount = star.length / 2;
        for(int ii=1; ii<pointCount; ii++)
        {
            int iStar = ii << 1;
            context.lineTo(star[iStar], star[iStar + 1]);
        }
        context.closePath();
        context.fill();
        
        context.restore();
	}

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        double spacing = DEFAULT_SPACING;
        double scale = size / DEFAULT_HEIGHT;
        double rs = map.getRangeXStart();
        double re = map.getRangeXEnd();
        spacing = spacing * scale * spacingScale;
        
        PATTERN_RIBBON.drawBorder(map, context, size, spacingScale, fgColor, bgColor);
        
        context.setFillStyle(bgColor);
        
        int segments = (int)((re - rs) / spacing);
        spacing = (re - rs) / segments;
        
        double offsetX = spacing / 2.0;
        double offsetY = size / 2.0;
        
        double[] star = makeStar(scale);
        Vector2D v = new Vector2D();
        
        for(int i=0; i<segments; i++)
        {
            map.transform(i * spacing + offsetX,  + offsetY, v);
            
            context.save();
            
            context.translate(v.x, v.y);
            context.rotate(v.angle);
            
            context.beginPath();
            context.moveTo(star[0] , star[1]);
            
            int pointCount = star.length / 2;
            for(int ii=1; ii<pointCount; ii++)
            {
                int iStar = ii << 1;
                context.lineTo(star[iStar], star[iStar + 1]);
            }
            context.closePath();
            context.fill();
            
            context.restore();
        }
	}
}
