package graphics.drawables;

import graphics.SceneContext;

public final class ImageDrawable implements IDrawable
{
	public static final int TYPE_STRETCH = 0;
	public static final int TYPE_CENTER = 1;
	
	public IDrawableListener listener = null;
	private final double _x;
	private final double _y;
	private final double _width;
	private final double _height;
	private final double _angle;
	private final String _descriptor;
	private final int _type;
	
	
	@Override
	public void onDraw(SceneContext params)
	{
        if(listener != null) listener.onBeforeDraw(this, params);
    	
        params.context.save();
        params.context.translate(_x + _width / 2.0, _y + _height / 2.0);
        params.context.rotate(_angle);
        params.context.drawImage(
    			_descriptor, 
    			_width, _height,
    			_type
            );
        params.context.restore();
    }
	
	public ImageDrawable(String descriptor, double x, double y, double width, double height, double angle, double type)
	{
		this._x = x;
		this._y = y;
		this._width = width;
		this._height = height;
		this._descriptor = descriptor;
		this._angle = angle;
		this._type = (int)type;
	}
}
