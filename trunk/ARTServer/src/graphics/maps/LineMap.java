package graphics.maps;

public final class LineMap extends BaseMap 
{
	private final double _ca; 
	private final double _sa; 
	private final double _destX1; 
	private final double _destY1; 
	private final double _angle; 
	private final double _srcScale;
	
	@Override
	public Vector2D transform(double x, double y, Vector2D dest)
	{
		if(dest == null) dest = new Vector2D();

		x = (x - _range[0]) * _srcScale;
		
		dest.x = x * _ca + y * _sa + _destX1;  
		dest.y = x * _sa - y * _ca + _destY1;
		dest.angle = _angle;
        
		return dest;
	}
	
	public LineMap(double destX1, double destY1, double destX2, double destY2, double srcX, double srcScale)
	{
	    double width = destX2 - destX1;
	    double height = destY2 - destY1;
	    double len = Math.sqrt( width * width + height * height );
	    double angle = Math.atan2( height, width );
	    
	    _ca = Math.cos(angle);
	    _sa = Math.sin(angle);
	    
	    this._range[0] = srcX;
	    this._range[1] = srcX + len / srcScale;
	    
		_destX1 = destX1; 
		_destY1 = destY1; 
		_angle = angle; 
		_srcScale = srcScale;
	}
}
