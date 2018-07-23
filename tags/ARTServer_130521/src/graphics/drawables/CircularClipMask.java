package graphics.drawables;

import graphics.JavaCanvas;
import graphics.SceneContext;

public class CircularClipMask implements IDrawable
{
	private final double _radius;
	@Override
	public void onDraw(SceneContext params)
	{
        JavaCanvas ctx = params.context;
        ctx.beginPath();
        ctx.arc(0,0,_radius,0,Math.PI*2,false);
        ctx.setFillStyle(params.palette[SceneContext.PALETTE_COLOR_PAPER]);
        ctx.fill();
        ctx.clip();
	}
	
	public CircularClipMask(double width, double height)
	{
		_radius = Math.min(width, height) / 2;
	}
}
