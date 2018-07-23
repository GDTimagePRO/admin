package graphics;

import java.awt.Color;
import java.awt.geom.Rectangle2D;

public final class SceneContext
{
	public static final int PALETTE_COLOR_INK			= 0;
	public static final int PALETTE_COLOR_PAPER			= 1;
	public static final int PALETTE_COLOR_BACKGROUND	= 2;
	public static final int PALETTE_COLOR_HIGHLIGHT		= 3;
	
    public double scale = 1.0;
    public final Color[] palette = new Color[4];
    public final Rect2D viewRect;
    public final JavaCanvas context;
    
    
    public static int parseColorIndex(String colorName)
    {
    	if(colorName == null) return PALETTE_COLOR_INK;
    	if(colorName.equals("ink")) return PALETTE_COLOR_INK;
    	if(colorName.equals("paper")) return PALETTE_COLOR_PAPER;
    	if(colorName.equals("background")) return PALETTE_COLOR_BACKGROUND;
    	if(colorName.equals("highlight")) return PALETTE_COLOR_HIGHLIGHT;    	
    	return PALETTE_COLOR_INK;
    }
    
    public SceneContext(Rect2D viewRect, JavaCanvas context)
    {
    	palette[PALETTE_COLOR_INK] = Color.BLACK;
    	palette[PALETTE_COLOR_PAPER] = Color.WHITE;
    	palette[PALETTE_COLOR_BACKGROUND] = new Color(0x404040);
    	palette[PALETTE_COLOR_HIGHLIGHT] = new Color( 180f / 255f, 240f / 255f, 1.0f, 0.75f);
    	this.viewRect = viewRect;
    	this.context = context;
    }
}
