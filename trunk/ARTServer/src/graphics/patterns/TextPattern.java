package graphics.patterns;

import graphics.JavaCanvas;
import graphics.maps.IMap;
import graphics.maps.Vector2D;

import java.awt.Color;

public final class TextPattern implements IPattern
{
	private final String _fontPre;	
	private final String _fontPost;
	
	private final int _alignment;
	private final int _verticalAlignment;
	private final boolean _scaleToFit;
	private final boolean _invert;
	
	private double _oldWidth;
	private double _oldSize;
	private double _oldMeasure;
	private double _actSize;
	private double _minSize;
	
	private final String _text;

	@Override
	public void begin(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void end(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }
	
	@Override
	public void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor) { }

	@Override
	public void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor)
	{
        context.setFillStyle(fgColor);
        
        context.save();
        context.setFont(_fontPre + (int)size + _fontPost);
        double rs = map.getRangeXStart();
        double re = map.getRangeXEnd();
        double maxWidth = re - rs;
        
        //TODO: make this not suck!
        if(_scaleToFit)
        {
        	double newMeasure =  context.measureTextWidth(_text); //In case the font finished loading
	        if ( (_oldWidth != maxWidth) || (_oldSize != size) || (_oldMeasure != newMeasure) ) 
	        {
	        	_oldWidth = maxWidth;
	        	_actSize = _oldSize = size;
	        	_oldMeasure = newMeasure; 
	        	
		        for (; _actSize > _minSize; _actSize--)
		    	{
		    		context.setFont(_fontPre + _actSize + _fontPost);
		    		double measured = context.measureTextWidth(_text);
		    		
		    		if ( measured <= maxWidth ) 
		    		{ 
		    			break; 
					}
		    	}
	        }
	        context.setFont(_fontPre + (int)_actSize + _fontPost);
        }
        else
        {
	        context.setFont(_fontPre + (int)size + _fontPost);
	        _actSize = size;
        }
        
  
        double spacing = spacingScale * size - size;
        if(spacing < 0) spacing = 0;
    
        String clippedText = _text;
        double segments = 0; 
        double width = 0;
        double offset = 0;
        
        while(true)
        {
            segments = clippedText.length();
            width = context.measureTextWidth(clippedText) + (segments - 1) * spacing;
        	offset = (re - rs - width) / 2;
            if(offset < spacing / 2)
            {
                clippedText = clippedText.substring(0, clippedText.length() - 1);
            }
            else break;
        }
        
        
        if (_alignment == 1) 
    	{
    		offset = 2;
    	}
    	else if (_alignment == 2) 
    	{
    		offset = re - rs - width + 2;
    	}
       	else
    	{
        	offset = (re - rs - width) / 2;
    	}
 
        
    	double yOffset;
        if(_invert)
        {   
        	if(_verticalAlignment < 0)
        	{
                context.setTextBaseline(JavaCanvas.TextBaseline.ALPHABETIC);                
                yOffset = _actSize;                
        	}
        	else if(_verticalAlignment > 0)
        	{
                context.setTextBaseline(JavaCanvas.TextBaseline.ALPHABETIC);                
                yOffset = size;
        	}
        	else
        	{
                context.setTextBaseline(JavaCanvas.TextBaseline.MIDDLE);        		
        		yOffset = size / 2;
        	}

            Vector2D v = new Vector2D();
            int lastChar = clippedText.length() - 1;
            for(int i=0; i<segments; i++)
            {
                char c = clippedText.charAt(lastChar - i);            
                double cWidth = context.measureTextWidth(c);
                
                map.transform(offset + (cWidth + spacing) / 2, yOffset, v);
                
                context.save();          
        
                context.translate(v.x, v.y);
                context.rotate(v.angle + Math.PI);
                context.fillText(Character.toString(c), -cWidth / 2, 0);
                
                context.restore();
                offset = offset + cWidth + spacing;
            }           
        }
        else
        {
        	if(_verticalAlignment < 0)
        	{
                context.setTextBaseline(JavaCanvas.TextBaseline.ALPHABETIC);                
                yOffset = size - _actSize;
        	}
        	else if(_verticalAlignment > 0)
        	{
                context.setTextBaseline(JavaCanvas.TextBaseline.ALPHABETIC);                
                yOffset = 0;                
        	}
        	else
        	{
                context.setTextBaseline(JavaCanvas.TextBaseline.MIDDLE);                
        		yOffset = size / 2;
        	}
            
        	Vector2D v = new Vector2D();
            for(int i=0; i<segments; i++)
            {
                char c = clippedText.charAt(i);            
                double cWidth = context.measureTextWidth(c);
                
               	map.transform(offset + (cWidth + spacing) / 2, yOffset, v);

                context.save();          
        
                context.translate(v.x, v.y);
                context.rotate(v.angle);
                context.fillText(Character.toString(c), -cWidth / 2, 0);
                
                context.restore();
                offset = offset + cWidth + spacing;
            }           
        }
        context.restore();
	}
	
	public TextPattern(String text,String font, boolean scaleToFit, boolean invert, boolean bold, boolean italic, double minSize, int alignment, int verticalAlignment)
	{
		_text = text;
		_fontPre = (bold ? "bold " : "") + (italic ? "italic " : ""); 
	    _fontPost = "px \""  + font + "\"";
	    _scaleToFit = scaleToFit;
	    _actSize = 0;
	    _oldSize = 0;
	    _oldWidth = 0;
	    _oldMeasure = 0;
	    _invert = invert;
	    _minSize = (minSize <= 0) ? 8 : minSize; 	    
	    _alignment = alignment; 
	    _verticalAlignment = verticalAlignment;
	}	
}
