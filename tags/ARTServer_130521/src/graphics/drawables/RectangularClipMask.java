package graphics.drawables;

import java.awt.Color;

import graphics.JavaCanvas;
import graphics.SceneContext;

public class RectangularClipMask implements IDrawable
{
	private final double _width; 
	private final double _height; 
	@Override
	public void onDraw(SceneContext params)
	{
        double hw = _width / 2;
        double hh = _height / 2;

        JavaCanvas ctx = params.context;
        
        ctx.beginPath();
		ctx.moveTo(-hw, -hh);
        ctx.lineTo(hw, -hh);
        ctx.lineTo(hw, hh);
        ctx.lineTo(-hw, hh);
		ctx.closePath();
        
        ctx.setFillStyle(params.palette[SceneContext.PALETTE_COLOR_PAPER]);
        ctx.fill();
        ctx.clip();
	}

	public RectangularClipMask(double width, double height)
	{
		_width = width;
		_height = height;
	}
}
