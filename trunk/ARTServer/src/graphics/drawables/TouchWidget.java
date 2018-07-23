package graphics.drawables;

import graphics.SceneContext;

public final class TouchWidget implements IDrawable
{
//    private static final Color COLOR_1 = new Color(0x8ED6FF);
//    private static final Color COLOR_2 = new Color(0.58823529411f, 0f, 0f, 0.75f);
//    private static final Color COLOR_3 = new Color(0.58823529411f, 0f, 0f, 0.25f);
//
//    private final double _x;
//	private final double _y;
//	private final boolean _isActive;
//	private final boolean _editAllowMove;
	
	@Override
	public void onDraw(SceneContext params)
	{
//		JavaCanvas  context = params.context;
//
//        context.beginPath();
//        if(_isActive)
//        {
//            context.arc(_x, _y, 6.0 / params.scale, 0, 2.0 * Math.PI, false);
//            context.setFillStyle(COLOR_1);
//            context.fill();         
//            context.setLineWidth(3.0 / params.scale);
//            context.setStrokeStyle(Color.BLACK); 
//            context.stroke();
//        }
//        else
//        {
//            context.arc(_x, _y, 6.0 / params.scale, 0, 2.0 * Math.PI, false);            
//			context.setFillStyle(_editAllowMove ? COLOR_2 : COLOR_3);
//            context.fill();
//            context.setLineWidth(3.0 / params.scale);
//            context.setStrokeStyle(params.palette[SceneContext.PALETTE_COLOR_PAPER]); 
//            context.stroke();
//        }
//        context.closePath();        
	}
	
	public TouchWidget(double x, double y, boolean isActive, boolean editAllowMove)
	{
//		_x = x;
//		_y = y;
//		_isActive = isActive;
//		_editAllowMove = editAllowMove;
	}
}
