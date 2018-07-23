package graphics.drawables;

import graphics.SceneContext;

public final class ImageDrawable implements IDrawable
{
	public IDrawableListener listener = null;
	private final double _x;
	private final double _y;
	private final double _width;
	private final double _height;
	private final String _descriptor;
	
	@Override
	public void onDraw(SceneContext params)
	{
        if(listener != null) listener.onBeforeDraw(this, params);
    	
    	params.context.drawImage(
    			_descriptor, 
    			_x, _y, 
    			_width, _height
            );
    }
	
	public ImageDrawable(String descriptor, double x, double y, double width, double height)
	{
		this._x = x;
		this._y = y;
		this._width = width;
		this._height = height;
		this._descriptor = descriptor;
	}
}
