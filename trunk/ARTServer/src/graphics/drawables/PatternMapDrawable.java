package graphics.drawables;

import java.awt.Color;

import graphics.JavaCanvas;
import graphics.SceneContext;
import graphics.maps.IMap;
import graphics.maps.MapCollection;
import graphics.patterns.IPattern;

public final class PatternMapDrawable implements IDrawable
{
	private final Color _fgColor;
	private final Color _bgColor;
	private final int _fgColorIndex;
	private final int _bgColorIndex;

	private final IMap _map;
	private final IPattern _pattern;
	private final double _size;
	private final double _spacingScale;
	
	@Override
	public void onDraw(SceneContext params)
	{
        Color fg = (_fgColor != null) ? _fgColor : params.palette[_fgColorIndex]; 
        Color bg = (_bgColor != null) ? _bgColor : params.palette[_bgColorIndex]; 
		
		if(_map instanceof MapCollection)
        {
        	MapCollection mc = (MapCollection)_map;  

            if(!mc.maps.isEmpty())
            {
	        	_pattern.begin(
	                mc.maps.get(0),
	                params.context,
	                _size,
	                _spacingScale,
	                fg,
	                bg
	            );
            }
        	
        	for(int i = 0; i<mc.maps.size(); i++)
            {
                _pattern.drawBorder(
                    mc.maps.get(i),
                    params.context,
                    _size,
                    _spacingScale,
                    fg,
                    bg
                );
            }
            
            for(int i=0; i < mc.corners.size(); i++)
            {
                _pattern.drawCorner(
                    mc.corners.get(i),
                    params.context,
                    _size,
                    _spacingScale,
                    fg,
                    bg
                );  
            }
            
            if(!mc.maps.isEmpty())
            {
	            _pattern.end(
	            	mc.maps.get(mc.maps.size() - 1),
	                params.context,
	                _size,
	                _spacingScale,
	                fg,
	                bg
	            );
            }
        }
        else
        {
        	_pattern.begin(
                _map, 
                params.context,
                _size,
                _spacingScale,
                fg,
                bg
            );
            
        	_pattern.drawBorder(
                _map, 
                params.context,
                _size,
                _spacingScale,
                fg,
                bg
            );
        	
        	_pattern.end(
                _map, 
                params.context,
                _size,
                _spacingScale,
                fg,
                bg
            );
        }           
    }

	public PatternMapDrawable(IMap map, IPattern pattern, double size, double spacingScale, Color fgColor, Color bgColor, int fgColorIndex, int bgColorIndex)
	{
		_fgColor = fgColor;
		_bgColor = bgColor;
		_fgColorIndex = fgColorIndex;
		_bgColorIndex = bgColorIndex;

		_map = map;
		_pattern = pattern;
		_size = size;
		_spacingScale = spacingScale;
	}
	
	public PatternMapDrawable(IMap map, IPattern pattern, double size, double spacingScale, Color fgColor, Color bgColor)
	{
		this(
				map, 
				pattern, 
				size, 
				spacingScale, 
				fgColor, 
				bgColor, 
				(fgColor != null) ? -1 : SceneContext.PALETTE_COLOR_INK, 
				(bgColor != null) ? -1 : SceneContext.PALETTE_COLOR_PAPER 
			);
	}
	
	public PatternMapDrawable(IMap map, IPattern pattern, double size, double spacingScale, int fgColorIndex, int bgColorIndex)
	{
		this(map, pattern, size, spacingScale, null, null, fgColorIndex, bgColorIndex);
	}

	public PatternMapDrawable(IMap map, IPattern pattern, double size, double spacingScale, String fgColor, String bgColor, SceneContext ctx)
	{
		_map = map;
		_pattern = pattern;
		_size = size;
		_spacingScale = spacingScale;

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

		if((bgColor != null) && (bgColor.length() > 0))
		{
			if(bgColor.charAt(0) == '$')
			{
				_bgColor = null;
				_bgColorIndex = ctx.parseColorIndex(bgColor.substring(1));
			}
			else
			{
				_bgColor = JavaCanvas.parseColor(bgColor);
				_bgColorIndex = SceneContext.PALETTE_COLOR_INK;				
			}
		}
		else
		{
			_bgColor = null;
			_bgColorIndex = SceneContext.PALETTE_COLOR_PAPER;
		}
	}
	
	
}
