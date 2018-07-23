package graphics;

import graphics.drawables.IDrawable;
import graphics.drawables.ImageDrawable;

import java.awt.Color;
import java.util.Hashtable;
import java.util.LinkedList;


public final class Scene
{
	public static final String LAYER_NAME_BACKGROUND	= "b";
	public static final String LAYER_NAME_FOREGROUND	= "f";
	public static final String LAYER_NAME_OVERLAY		= "o";
	public static final String LAYER_NAME_WIDGETS		= "w";
	
	private boolean _isDrawing = false;
	private final double _width;
	public double getWidth() { return _width; }
	
	private final double _height;
	public double getHeight() { return _height; }

	private final double _scale;
	
	public final class Layer
	{
		public IDrawable clipMask = null;
		private LinkedList<IDrawable> _drawables = new LinkedList<IDrawable>();
		public boolean add(IDrawable drawable)
		{
			_drawables.add(drawable);
			return true;
		}
		
		public boolean remove(IDrawable drawable)
		{
			return _drawables.remove(drawable);
		}
		
		void onDraw(SceneContext params)
		{
	        params.context.save();
	        if(this.clipMask != null) this.clipMask.onDraw(params);

	        for(IDrawable drawable : _drawables)
	        {
	        	if(drawable instanceof ImageDrawable)
	        	{
	        		drawable.onDraw(params);
	        	}
	        }

	        for(IDrawable drawable : _drawables)
	        {
	        	if(!(drawable instanceof ImageDrawable))
	        	{
	        		drawable.onDraw(params);
	        	}
	        }
	        params.context.restore();
		}
	}
	
	private Hashtable<String, Layer> _layers = new Hashtable<String, Layer>();
	private LinkedList<Layer> _zorder = new LinkedList<Layer>();
	
	public Layer addLayer(String name)
	{
        Layer newLayer = new Layer(); 
        _zorder.addLast(newLayer);
        _layers.put(name, newLayer);
        return  newLayer;
	}
	
	public Layer getLayer(String name)
	{
		return _layers.get(name);
	}
	
	public void drawTo(JavaCanvas canvas, Color inkColor)
	{
		if(_isDrawing) return;

		_isDrawing = true;
		double offsetX = _width / 2.0;
		double offsetY = _width / 2.0;

		SceneContext params = new SceneContext(
				new Rect2D( -offsetX, -offsetY, offsetX, offsetY ), 
    			canvas
    		);
		params.palette[SceneContext.PALETTE_COLOR_INK] = inkColor;
		
        params.context.save();

		try
    	{
	        
			params.context.translate(canvas.getWidth() / 2.0, canvas.getHeight() / 2.0);
					
			params.context.scale(_scale, _scale);
			
	        for(Layer layer : _zorder)
	        {
	        	layer.onDraw(params);
	        }
    	}
    	finally
    	{
        	params.context.restore();
        	_isDrawing = false;
    	}
	}
	
	public Scene(double width, double height, double scale)
	{
		_width = width;
		_height = height;
		_scale = scale;
	}
}