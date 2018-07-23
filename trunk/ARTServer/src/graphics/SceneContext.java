package graphics;

import java.awt.Color;
import java.util.Hashtable;

public final class SceneContext
{
	public static final int PALETTE_COLOR_INK			= 0;
	public static final int PALETTE_COLOR_PAPER			= 1;
	public static final int PALETTE_COLOR_BACKGROUND	= 2;
	public static final int PALETTE_COLOR_HIGHLIGHT		= 3;
	
    public double scale = 1.0;
    public final Color[] palette = new Color[255];
    public final Rect2D viewRect;
    public final JavaCanvas context;
    private final Hashtable<String, Integer> _colorIds = new Hashtable<>();
    
    public void setColor(String id, Color value)
    {
    	Integer i = _colorIds.get(id);
    	if(i == null)
    	{
    		i = _colorIds.size();    		
    		_colorIds.put(id, i);
    	}
    	palette[i] = value;    	
    }
    
    public int parseColorIndex(String colorName)
    {
    	if(colorName == null) return PALETTE_COLOR_INK;
    	Integer result = _colorIds.get(colorName);
    	if(result != null) return result;
    	return PALETTE_COLOR_INK;
    }
    
    public SceneContext(Rect2D viewRect, JavaCanvas context)
    {    	
    	palette[PALETTE_COLOR_INK] = Color.BLACK;
    	_colorIds.put("ink", PALETTE_COLOR_INK);
    	
    	palette[PALETTE_COLOR_PAPER] = Color.WHITE;
    	_colorIds.put("paper", PALETTE_COLOR_PAPER);

    	palette[PALETTE_COLOR_BACKGROUND] = new Color(0x404040);
    	_colorIds.put("background", PALETTE_COLOR_BACKGROUND);

    	palette[PALETTE_COLOR_HIGHLIGHT] = new Color( 180f / 255f, 240f / 255f, 1.0f, 0.75f);
    	_colorIds.put("highlight", PALETTE_COLOR_HIGHLIGHT);

    	this.viewRect = viewRect;
    	this.context = context;
    }
}
