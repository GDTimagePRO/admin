package graphics.drawables;

import java.awt.Color;

import graphics.JavaCanvas;
import graphics.SceneContext;

public final class RectDrawable implements IDrawable
{	
	private final double _x;
	private final double _y;
	private final double _width;
	private final double _height;
	
	private final Color _fgColor;
	private final int _fgColorIndex;
	
	@Override
	public void onDraw(SceneContext params)
	{
		params.context.setFillStyle((_fgColor!=null) ? _fgColor : params.palette[_fgColorIndex]);   
        params.context.fillRect(_x, _y, _width, _height);
	}
	
	public RectDrawable(double x, double y, double width, double height, Color fgColor)
	{
		_x = x;
		_y = y;
		_width = width;
		_height = height;
		_fgColor = fgColor;
		_fgColorIndex = -1;
	}

	public RectDrawable(double x, double y, double width, double height, int fgColorIndex)
	{
		_x = x;
		_y = y;
		_width = width;
		_height = height;
		_fgColor = null;
		_fgColorIndex = fgColorIndex;
	}
	
	public RectDrawable(double x, double y, double width, double height, String fgColor, SceneContext ctx)
	{
		_x = x;
		_y = y;
		_width = width;
		_height = height;
		
		if((fgColor != null) && (fgColor.length() > 0))
		{
			if(fgColor.charAt(0) == '$')
			{
				_fgColor = null;
				_fgColorIndex = ctx.parseColorIndex(fgColor.substring(1));
			}
			else
			{
				_fgColor = JavaCanvas.parseColor(fgColor);
				_fgColorIndex = SceneContext.PALETTE_COLOR_INK;				
			}
		}
		else
		{
			_fgColor = null;
			_fgColorIndex = SceneContext.PALETTE_COLOR_INK;
		}
	}
	
}
