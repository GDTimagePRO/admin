package graphics.maps;

import java.util.ArrayList;

public final class MapCollection extends BaseMap
{
    private boolean _canAddCorner = false;
    private boolean _cornerUpdateNeeded = false;
    public final ArrayList<IMap> maps = new ArrayList<IMap>();
    public final ArrayList<IMap> corners = new ArrayList<IMap>();
    
	public void addMap(IMap map)
	{
        if(_cornerUpdateNeeded)
        {
            corners.set(
            		corners.size() - 1, 
            		new CornerMap(maps.get(maps.size() - 1), map)
            	);
            _cornerUpdateNeeded = false;
        }
        maps.add(map);
        _canAddCorner = true;
	}
	
	public void addCorner()
	{
        if(!_canAddCorner) return;          
        corners.add(new CornerMap(
            maps.get(maps.size() - 1), maps.get(0)
        ));
        _cornerUpdateNeeded = true;
        _canAddCorner = false;
	}

	@Override
	public Vector2D transform(double x, double y, Vector2D dest)
	{
		if(dest == null) dest = new Vector2D();
		return dest;
	}
	
	public static MapCollection createRectangleMap(double x1, double y1, double x2, double y2, double srcScale)
	{
	    if(x1 > x2) { double tmp = x2; x2 = x1; x1 = tmp; }
	    if(y1 > y2) { double tmp = y2; y2 = y1; y1 = tmp; }
	    
	    double width = x2 - x1; 
	    double height = y2 - y1;        
	    
	    MapCollection map = new MapCollection();
	        
	    //top line segment
	    if(width > 0)
	    {
	        map.addMap(new LineMap(
	            x1, y1,
	            x2, y1, 
	            map.getRangeXEnd(), 
	            srcScale
	        ));
	    }     

	    //top right corner
	    else map.addCorner();

	    //right line segment
	    if(height > 0)
	    {
	        map.addMap(new LineMap(
	            x2, y1, 
	            x2, y2, 
	            map.getRangeXEnd(), 
	            srcScale
	        ));
	    }        

	    //bottom right corner
	    else map.addCorner(); 

	    //bottom line segment
	    if(width > 0)
	    {
	        map.addMap(new LineMap(
	            x2, y2, 
	            x1, y2,
	            map.getRangeXEnd(), 
	            srcScale
	        ));
	    }

	    //bottom left corner
	    else map.addCorner();

	    //left line segment
	    if(height > 0)
	    {
	        map.addMap(new LineMap(
	            x1, y2, 
	            x1, y1, 
	            map.getRangeXEnd(), 
	            srcScale
	        ));
	    }
	    
	    //top left corner
	    map.addCorner();    
	    
	    return map;
	}
}
