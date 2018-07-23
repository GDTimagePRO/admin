package graphics.drawables;

import java.awt.Color;

import graphics.SceneContext;

public class GridDrawable implements IDrawable {

	private final Color color;
	private final int colorIndex;
	private final double lineWidth;
	private final double width;
	private final double height;
	
	public GridDrawable(double lineWidth, Color color, double width, double height) {
		this.lineWidth = lineWidth;
		this.color = color;
		this.colorIndex = -1;
		this.width = width;
		this.height = height;
	}
	
	public GridDrawable(double lineWidth, int colorIndex, double width, double height) {
		this.lineWidth = lineWidth;
		this.colorIndex = colorIndex;
		this.color = null;
		this.width = width;
		this.height = height;
	}
	
	@Override
	public void onDraw(SceneContext params) {
		params.context.save();
		params.context.setLineWidth(lineWidth);
		params.context.setStrokeStyle((color != null) ? color : params.palette[colorIndex]);
		
		double spacing = width / 10;
		
		double right = Math.floor((width / 2) / spacing) * spacing;
		double bottom =  Math.ceil((height / 2) / spacing) * spacing;
		double left = -right;
		double top = -bottom;
		
        params.context.beginPath();
        for (double x = 0; x <= right; x += spacing) {
        	params.context.moveTo(x, 0);
	        params.context.lineTo(x, bottom);
	        params.context.lineTo(x, top);
	        params.context.moveTo(-x, 0);
	        params.context.lineTo(-x, bottom);
	        params.context.lineTo(-x, top);
        }
        
        for (double y = 0; y <= bottom; y += spacing) {
        	 params.context.moveTo(0, y);
 	        params.context.lineTo(right, y);
 	        params.context.lineTo(left, y);
 	       params.context.moveTo(0, -y);
	        params.context.lineTo(right, -y);
	        params.context.lineTo(left, -y);
        }
        params.context.closePath();
        params.context.stroke();
        params.context.restore();
	}

}
