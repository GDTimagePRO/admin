package graphics.drawables;

import java.awt.geom.Ellipse2D;

import graphics.JavaCanvas;
import graphics.SceneContext;

public class CircularClipMask implements IDrawable
{
	private final double _radius;
	@Override
	public void onDraw(SceneContext params)
	{
        JavaCanvas ctx = params.context;
        
        Ellipse2D.Double circle = new Ellipse2D.Double(-_radius, -_radius, _radius * 2, _radius * 2);
        ctx.setFillStyle(params.palette[SceneContext.PALETTE_COLOR_PAPER]);
        ctx.fill(circle);
        ctx.getG2D().clip(circle);
	}
	
	public CircularClipMask(double width, double height)
	{
		_radius = Math.min(width, height) / 2;
	}
}
