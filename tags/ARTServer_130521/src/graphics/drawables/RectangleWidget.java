package graphics.drawables;

import java.awt.Color;

import graphics.SceneContext;
import graphics.maps.LineMap;
import graphics.maps.MapCollection;
import graphics.patterns.PatternDotted;

public final class RectangleWidget implements IDrawable
{
	
//	private static final Color COLOR_1 = new Color(1f, 1f, 1f, 0.75f);
//	private static final Color COLOR_2 = new Color(50, 50, 50);
//	private static final Color COLOR_3 = new Color(0f, 0f, 0f, 0.25f);
//	
//	private final double _x1;
//	private final double _y1;
//	private final double _x2;
//	private final double _y2;
//	private final double _angle;
	
	@Override
	public void onDraw(SceneContext params)
	{
//        double s_x1 = _x1 < _x2 ? _x1 : _x2;
//        double s_y1 = _y1 < _y2 ? _y1 : _y2;
//        double s_x2 = _x1 > _x2 ? _x1 : _x2;
//        double s_y2 = _y1 > _y2 ? _y1 : _y2;
//        
//
//        double bo = 1.5;  
//        
//        MapCollection mc = new MapCollection();
//        mc = new MapCollection();
//        mc.addMap(new LineMap( s_x1 + bo, s_y1 + bo, s_x2 - bo, s_y1 + bo, 0, 1));
//        mc.addCorner();
//        mc.addMap(new LineMap( s_x2 - bo, s_y1 + bo, s_x2 - bo, s_y2 - bo, 0, 1));
//        mc.addCorner();
//        mc.addMap(new LineMap( s_x2 - bo, s_y2 - bo, s_x1 + bo, s_y2 - bo, 0, 1));
//        mc.addCorner();
//        mc.addMap(new LineMap( s_x1 + bo, s_y2 - bo, s_x1 + bo, s_y1 + bo, 0, 1));
//        mc.addCorner();
//
//        double xm = (_x1 + _x2) / 2.0;
//        double ym = (_y1 + _y2) / 2.0;
//
//        TouchWidget topLeft			= new TouchWidget(_x1, _y1, false, true);
//        TouchWidget topMiddle		= new TouchWidget( xm, _y1, false, true);
//        TouchWidget topRight		= new TouchWidget(_x2, _y1, false, true);
//
//        TouchWidget middleLeft		= new TouchWidget(_x1,  ym, false, true);
//        TouchWidget middleMiddle	= new TouchWidget( xm,  ym, false, true);
//        TouchWidget middleRight		= new TouchWidget(_x2,  ym, false, true);
//        
//        TouchWidget bottomLeft		= new TouchWidget(_x1, _y2, false, true);
//        TouchWidget bottomMiddle	= new TouchWidget( xm, _y2, false, true);
//        TouchWidget bottomRight		= new TouchWidget(_x2, _y2, false, true);
//
//        
//        double width = s_x2 - s_x1;
//        double height = s_y2 - s_y1;
//        double rad = width < height ? width * 1.0/4.0 : height * 1.0/4.0;
//
//        TouchWidget angleWidget = new TouchWidget(
//        		xm + Math.cos(_angle) * rad, 
//        		ym + Math.sin(_angle) * rad, 
//        		false, 
//        		true
//        	);
//        
//        params.context.beginPath();
//        params.context.arc(xm, ym, rad, 0, 2 * Math.PI, false);         
//
//        params.context.setLineWidth(12.0 / params.scale);
//        params.context.setStrokeStyle(COLOR_1); 
//        params.context.stroke();
//
//        params.context.setLineWidth(2.0 / params.scale);
//        params.context.setStrokeStyle(COLOR_2); 
//        params.context.stroke();
//        params.context.closePath();     
//
//
//        PatternMapDrawable box = new PatternMapDrawable(
//        		mc, 
//        		new PatternDotted(), 
//        		3.0 / params.scale, 
//        		1.2, 
//        		COLOR_3, 
//        		null
//        	);
//        
//        box.onDraw(params);
//        topLeft.onDraw(params);
//        topMiddle.onDraw(params);
//        topRight.onDraw(params);
//        middleLeft.onDraw(params);
//        middleMiddle.onDraw(params);
//        middleRight.onDraw(params);        
//        bottomLeft.onDraw(params);
//        bottomMiddle.onDraw(params);
//        bottomRight.onDraw(params);
//        angleWidget.onDraw(params);
//		
	}
	
	public RectangleWidget(double x1, double y1, double x2, double y2, double angle)
	{
//		_x1 = x1;
//		_y1 = y1;
//		_x2 = x2;
//		_y2 = y2;
//		_angle = angle;
	}
}
