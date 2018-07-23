package graphics.drawables;

import java.awt.Color;

import graphics.JavaCanvas;
import graphics.SceneContext;

public class LineDrawable implements IDrawable
{
	private final double _x1;
	private final double _y1;
	private final double _x2;
	private final double _y2;
	private final double _lineWidth;
	private final Color _fgColor;
	private final int _fgColorIndex;
	
	@Override
	public void onDraw(SceneContext params)
	{
		params.context.setLineWidth(_lineWidth);
		params.context.setStrokeStyle((_fgColor != null) ? _fgColor : params.palette[_fgColorIndex]);

        params.context.beginPath();
        params.context.moveTo(_x1, _y1);
        params.context.lineTo(_x2, _y2);
        params.context.closePath();
        params.context.stroke();
	}
	
	public LineDrawable(double x1, double y1, double x2, double y2, double lineWidth, Color fgColor)
	{
		_x1 = x1;
		_y1 = y1;
		_x2 = x2;
		_y2 = y2;
		_lineWidth = lineWidth;
		_fgColor = fgColor;
		_fgColorIndex = -1;
	}

	public LineDrawable(double x1, double y1, double x2, double y2, double lineWidth, int fgColorIndex)
	{
		_x1 = x1;
		_y1 = y1;
		_x2 = x2;
		_y2 = y2;
		_lineWidth = lineWidth;
		_fgColor = null;
		_fgColorIndex = fgColorIndex;
	}
	
	public LineDrawable(double x1, double y1, double x2, double y2, double lineWidth, String fgColor, SceneContext ctx)
	{
		_x1 = x1;
		_y1 = y1;
		_x2 = x2;
		_y2 = y2;
		_lineWidth = lineWidth;
		
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
