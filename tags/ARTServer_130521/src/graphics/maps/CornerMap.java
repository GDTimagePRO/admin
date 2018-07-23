package graphics.maps;

public final class CornerMap extends BaseMap
{

	private final double _ca;
	private final double _sa;
	private final double _angle;
	private final Vector2D _v1;
	
	@Override
	public Vector2D transform(double x, double y, Vector2D dest)
	{
		if(dest == null) dest = new Vector2D();

		dest.x = x * _ca + y * _sa + _v1.x;  
		dest.y = x * _sa - y * _ca + _v1.y;
		dest.angle =_angle;

		return dest;

	}
	
	public CornerMap(IMap mapA, IMap mapB)
	{
	    _v1 = mapB.transform(mapB.getRangeXStart(), 0, null);        
	    Vector2D v2 = mapB.transform(mapB.getRangeXStart(), 100, null);
	    _angle = Math.atan2( v2.y - _v1.y, v2.x - _v1.x );
	        
	    _ca = Math.cos(_angle);
	    _sa = Math.sin(_angle);
	        
	}

}
