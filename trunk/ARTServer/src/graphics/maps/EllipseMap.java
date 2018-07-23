package graphics.maps;

public final class EllipseMap extends BaseMap
{
	private static final int STEPS = 300;
	private static final double dPI = Math.PI * 2.0;
	private static final double hPI = Math.PI / 2.0;
	
	private final double[] _mapD;
	private final double[] _mapT;
	private final double[] _mapTD;
	private final int _mapSize; 

	private final double _srcScale;
	private final double _wh;
	private final double _negRangeA;
	private final double _negRangeB;
	private final double _hW;
	private final double _hH;
	private final double _offsetX;
	private final double _offsetY;

	private int _selected = 0;
	
	@Override
	public Vector2D transform(double x, double y, Vector2D dest)
	{
		if(dest == null) dest = new Vector2D();

    	while(x < 0) x += this._range[1];
        while(x > this._range[1]) x -= this._range[1];
        
    	x *= _srcScale;
        
        if((_mapD[_selected] > x) || (_mapD[_selected + 1] <= x))
        {
            int a = 0;
            int b = _mapSize - 2;
                        
            while(true)
            {
                _selected = (b + a) >> 1;
                     
                if(_mapD[_selected] > x)
                {
                    if(_selected == a) break;
                    b = _selected - 1;                 
                }
                else if(_mapD[_selected + 1] <= x)
                {
                    if(_selected == b) break;
                    a = _selected + 1;                             
                }
                else break;
            }
        };
                
        double t = (x - _mapD[_selected]) * _mapTD[_selected] + _mapT[_selected];
        if(t > dPI) t -= dPI;        
        if(t < 0) t += dPI;                
        double a = Math.atan(_wh * Math.tan(t));
        if((t > _negRangeA) && (t <= _negRangeB)) a += Math.PI;

        
        dest.x = _hW * Math.cos(t) + _offsetX + y * Math.cos(a);
        dest.y = _hH * Math.sin(t) + _offsetY + y * Math.sin(a); 
        dest.angle = a + hPI;
		
		return dest;
	}
	
	public EllipseMap(double offsetX, double offsetY, double width, double height, double angleStart, double angleEnd, double srcScale)
	{
	    _mapD = new double [STEPS + 5];
	    _mapT = new double [STEPS + 5];
	    _mapTD = new double [STEPS + 5];
	    
	    int mapSize = 0;
	    
	    _hW = width / 2;
	    _hH = (height == 0) ? 0.1 : height / 2;
	    _wh = _hW / _hH;   
	    _negRangeA = hPI;
	    _negRangeB = 3 * hPI;    
	    
	    double angleRange = Math.abs(angleEnd - angleStart);
	    
	    while(angleStart < 0) angleStart += dPI;
	    while(angleStart > dPI) angleStart -= dPI;
	    double offsetT = angleStart;
	    
	    double stepSize = angleRange / (STEPS + 1); 

	    double len = 0;
	    double lenSum = 0;
	    double oldX = _hW * Math.cos(offsetT);
	    double oldY = _hH * Math.sin(offsetT);
	    double newX = 0;
	    double newY = 0;
	    double t = 0;

	    _mapD[mapSize] = 0;
	    _mapT[mapSize] = offsetT;
	    mapSize++;
	    
	    offsetX += _hW;
	    offsetY += _hH;

	    for(int i=0; i <= STEPS; i++)
	    {
	        t = stepSize * i + offsetT;
	        newX = _hW * Math.cos(t);
	        newY = _hH * Math.sin(t);
	        len = Math.pow(newX - oldX, 2) + Math.pow(newY - oldY, 2);
	        if(len > 0)
	        {
	            lenSum += Math.sqrt(len);
	            _mapD[mapSize] = lenSum;
	            _mapT[mapSize] = t;
	            mapSize++;
	        }
	        oldX = newX;
	        oldY = newY;
	    }
	    
	    t = angleRange + offsetT;
	    newX = _hW * Math.cos(t);
	    newY = _hH * Math.sin(t);
	    len = Math.pow(newX - oldX, 2) + Math.pow(newY - oldY, 2);
	    if(len > 0)
	    {
	        lenSum += Math.sqrt(len);
	        _mapD[mapSize] = lenSum;
	        _mapT[mapSize] = t;
	        mapSize++;
	    }
	    
	    _mapD[mapSize] = lenSum + 0.01;
	    _mapT[mapSize] = t + 0.01;
	    mapSize++;
	    
	    for(int i=0; i< mapSize - 1; i++)
	    {       
	        _mapTD[i] = (_mapT[i + 1] - _mapT[i]) / (_mapD[i + 1] - _mapD[i]);
	    }

	    this._range[0] = 0;
	    this._range[1] = lenSum / srcScale;
	    
	    _mapSize = mapSize;
		_srcScale = srcScale;
		_offsetX = offsetX;
		_offsetY = offsetY;
	}

}
