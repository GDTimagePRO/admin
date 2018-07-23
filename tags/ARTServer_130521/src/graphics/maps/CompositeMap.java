package graphics.maps;

import java.util.ArrayList;

public final class CompositeMap extends BaseMap
{
	private IMap _selected = null;
	private ArrayList<IMap> _maps = new ArrayList<IMap>();
	
	public void addMap(IMap map)
	{
        _maps.add(map);          
        this._range[1] = map.getRangeXEnd(); 
        if(_selected == null) _selected = map;		
	}
	
	@Override
	public Vector2D transform(double x, double y, Vector2D dest)
	{
        x = x - Math.floor(x / this._range[1]) * this._range[1];
        if((_selected.getRangeXStart() > x) || (_selected.getRangeXEnd() <= x))
        {
            int a = 0; 
            int b = _maps.size() - 1;
                        
            while(true)
            {
                int c = (b + a) >> 1;
                _selected = _maps.get(c);
                     
                if(_selected.getRangeXStart() > x)
                {
                    if(c == a) break;
                    b = c - 1;                 
                }
                else if(_selected.getRangeXEnd() <= x)
                {
                    if(c == b) break;
                    a = c + 1;                                 
                }
                else break;
            }             
        }
        return _selected.transform(x ,y, dest);                
	}
	
	public static CompositeMap createRoundedRectangleMap(double x1, double y1, double x2, double y2, double radius, double srcScale)
	{
	    if(radius < 0) radius = 0;
	    if(x1 > x2) { double tmp = x2; x2 = x1; x1 = tmp; }
	    if(y1 > y2) { double tmp = y2; y2 = y1; y1 = tmp; }
	    
	    double width = x2 - x1; 
	    double height = y2 - y1;        
	    double diameter = radius * 2;
	    
	    if(width < diameter) diameter = width;
	    if(height < diameter) diameter = height;
	    
	    width = width - diameter;
	    height = height - diameter;
	    
	    radius = diameter / 2;
	    	    
	    CompositeMap map = new CompositeMap();

	    //top line segment
	    if(width > 0)
	    {
	        map.addMap(new LineMap(
	            x1 + radius, y1,
	            x2 - radius, y1, 
	            map.getRangeXEnd(), 
	            srcScale
	        ));
	    }     

	    //top right corner
	    if(radius > 0)
	    {
	        map.addMap(new CircleMap(
	            x2 - radius, 
	            y1 + radius,
	            Math.PI * 3/2, 2 * Math.PI,
	            radius,
	            map.getRangeXEnd(),
	            srcScale
	        ));
	    }

	    //right line segment
	    if(height > 0)
	    {
	        map.addMap(new LineMap(
	            x2, y1 + radius, 
	            x2, y2 - radius, 
	            map.getRangeXEnd(),
	            srcScale
	        ));
	    }        

	    //bottom right corner
	    if(radius > 0)
	    {
	        map.addMap(new CircleMap(
	            x2 - radius, 
	            y2 - radius,
	            0, Math.PI * 1/2,
	            radius,
	            map.getRangeXEnd(),
	            srcScale
	        ));
	    }   

	    //bottom line segment
	    if(width > 0)
	    {
	        map.addMap(new LineMap(
	            x2 - radius, y2, 
	            x1 + radius, y2,
	            map.getRangeXEnd(),
	            srcScale
	        ));
	    }

	    //bottom left corner
	    if(radius > 0)
	    {
	        map.addMap(new CircleMap(
	            x1 + radius, 
	            y2 - radius,
	            Math.PI *  1/2, Math.PI,
	            radius,
	            map.getRangeXEnd(),
	            srcScale
	        ));
	    }

	    //left line segment
	    if(height > 0)
	    {
	        map.addMap(new LineMap(
	            x1, y2 - radius, 
	            x1, y1 + radius, 
	            map.getRangeXEnd(),
	            srcScale
	        ));
	    }
	    
	    //top left corner
	    if(radius > 0)
	    {
	        map.addMap(new CircleMap(
	            x1 + radius,
	            y1 + radius,
	            Math.PI, Math.PI * 3/2,
	            radius,
	            map.getRangeXEnd(),
	            srcScale
	        ));
	    }
	    
	    return map;
	}
}
