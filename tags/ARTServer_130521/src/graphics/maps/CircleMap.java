package graphics.maps;

public final class CircleMap extends BaseMap
{
	private final double _srcX;
	private final double _srcScale;
	private final double _radius;
	private final double _angleStart;
	private final double _centerX;
	private final double _centerY;
	
	@Override
	public Vector2D transform(double x, double y, Vector2D dest)
	{
		if(dest == null) dest = new Vector2D();

        double a = (x - _srcX) * _srcScale / _radius + _angleStart;
        double h = y + _radius;
        
        dest.x = h * Math.cos(a) + _centerX;
        dest.y = h * Math.sin(a) + _centerY;
        dest.angle =  a + Math.PI / 2;
        
		return dest;
	}
	
	public CircleMap(double centerX, double centerY, double angleStart, double angleEnd, double radius, double srcX, double srcScale)
	{
	    if(radius < 0.1) radius = 0.1;
		double len = Math.abs(angleEnd - angleStart) * radius / srcScale;
	    this._range[0] = srcX;
	    this._range[1] = srcX + len;
	    
		_srcX = srcX;
		_srcScale = srcScale;
		_radius = radius;
		_angleStart = angleStart;
		_centerX = centerX;
		_centerY = centerY;
	}
}
