package graphics;

import java.awt.BasicStroke;
import java.awt.Color;
import java.awt.Font;
import java.awt.FontMetrics;
import java.awt.Graphics2D;
import java.awt.Rectangle;
import java.awt.RenderingHints;
import java.awt.Shape;
import java.awt.font.GlyphVector;
import java.awt.geom.AffineTransform;
import java.awt.geom.Path2D;
import java.awt.geom.Point2D;
import java.awt.geom.Rectangle2D;
import java.awt.image.BufferedImage;
import java.io.File;
import java.net.URL;
import java.util.Hashtable;
import java.util.LinkedHashMap;
import java.util.LinkedList;

import javax.imageio.ImageIO;

public final class JavaCanvas
{	
	public enum TextAlignment
	{
		LEFT,	//The text starts at the specified position
		RIGHT,	//The text ends at the specified position
		CENTER,	//The center of the text is placed at the specified position
	}

	public enum TextBaseline
	{
		ALPHABETIC,	//The text baseline is the normal alphabetic baseline
		TOP,		//The text baseline is the top of the em square
		MIDDLE,		//The text baseline is the middle of the em square
		BOTTOM		//The text baseline is the bottom of the bounding box
	}
	
	private static class SavedContext
	{
		public float fontSize; 
		public Graphics2D g2d;
		public Color fillColor;
		public Color strokeColor;
		public Color shadowColor;
		public Color clearColor;
		public TextAlignment textAlignment;
		public TextBaseline textBaseline;
	}
	
	public static final int MAX_IMAGE_CACHE_SIZE = 20;
	private static final Hashtable<String, BufferedImage> _imageCache = new Hashtable<>(); 
	private static final LinkedList<String> _cacheFIFO = new LinkedList<>(); 
	
	
	public static void clearCache()
	{
		synchronized (_imageCache)
		{
			_imageCache.clear();
			_cacheFIFO.clear();
		}
	}
	
	public static BufferedImage getImageFromCache(String id)
	{
		synchronized (_imageCache)
		{
			BufferedImage image = _imageCache.get(id); 
			//System.out.println("Cache " + ((image != null) ?"Hit " : "Miss ")+ id);				
			return image;
		}
	}
	
	public static void addImageToCache(String id, BufferedImage image)
	{
		synchronized (_imageCache)
		{
			if(_imageCache.put(id, image) == null)
			{
				if(!id.startsWith("web_"))
				{
					//System.out.println("Cache request denied " + id);									
					return;
				}
				
				//System.out.println("Adding " + id);				
				_cacheFIFO.addLast(id);

				if(_cacheFIFO.size() > MAX_IMAGE_CACHE_SIZE)
				{
					//System.out.println("Removing " + _cacheFIFO.getFirst());
					_imageCache.remove(_cacheFIFO.removeFirst());
				}				
			}			
		}
	}
	
	private final String _imageServiceURL;
	
	private final String _fontDirectory;
	private float _fontSize = 10;
	private Color _fillColor = Color.BLACK;
	private Color _strokeColor = Color.BLACK;
	private Color _shadowColor = Color.BLACK;
	private Color _clearColor = Color.WHITE;
	public TextAlignment _textAlignment = TextAlignment.LEFT;
	public TextBaseline _textBaseline = TextBaseline.ALPHABETIC;
	
	
	private final String _imgDomain;
	private Path2D.Double _path = null;
	private BufferedImage _image = null;
	public BufferedImage getImage() { return _image; }
	
	private Graphics2D _g2d = null;
	private Graphics2D _g2dFonts = null;
	public Graphics2D getG2D() { return _g2d; }
	
	private LinkedList<SavedContext> _contextStack = new LinkedList<JavaCanvas.SavedContext>();
	
	private Hashtable<String, Font> _loadedFonts = new Hashtable<String, Font>();
	private Font getFont(String name)
	{
		try
		{
			Font result = _loadedFonts.get(name);
			if(result == null)
			{
				result = Font.createFont(Font.TRUETYPE_FONT, new File(_fontDirectory + File.separatorChar + name +".ttf"));
				_loadedFonts.put(name, result);
			}
			return result;
		}
		catch(Exception e) { throw new RuntimeException("Error loading font : " + name); }
	}
	
	
	private float getTextShape_CachedOffsetY = 0;
	private Font getTextShape_CachedFont = null;
	
	
//	private BasicStroke _textStroke = new BasicStroke((float)0.3); 
	private Shape getTextShape(String text, double x, double y)
	{
		//Font f = getFont("arial");
		//_g2d.setFont(f.deriveFont(300));


		FontMetrics fm = _g2dFonts.getFontMetrics();
		
//		GlyphVector B = _g2d.getFont().createGlyphVector(
//				fm.getFontRenderContext(), 
//				//"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
//				"ATSDMyQpgj"
//			);d
		
		GlyphVector gv = _g2dFonts.getFont().createGlyphVector(
				fm.getFontRenderContext(), 
				text
			);
		
		
		String s = "";
		float offsetY = 0;
		switch(_textBaseline)
		{
		case ALPHABETIC:
			offsetY = 0;
			break;
		
		case MIDDLE:
			{			
				if(getTextShape_CachedFont == _g2dFonts.getFont())
				{
					offsetY = getTextShape_CachedOffsetY;
				}
				else
				{
					getTextShape_CachedFont = _g2dFonts.getFont();
					GlyphVector A = getTextShape_CachedFont.createGlyphVector(fm.getFontRenderContext(),"tl");
					Rectangle2D b = A.getVisualBounds();
					offsetY = getTextShape_CachedOffsetY = (float)(A.getVisualBounds().getHeight() / 2.0);					
				}
			}			
			break;
			
		default:
			throw new RuntimeException("Only ALPHABETIC and MIDDLE baselines are currently supported.");
		}
		
		return gv.getOutline((float)x, (float)y + offsetY);
	}
	
	public static Color parseColor(String value)
	{
		if((value.length() != 7) || (value.charAt(0) != '#'))
		{
			//throw new RuntimeException( value + " is not a valid color.");
			return Color.black;
		}
		
		return new Color(Integer.parseInt(value.substring(1), 16));
	}
		
	private void createPath(boolean useExisting)
	{
		if(useExisting && (_path != null)) return;
		
		if(_path != null) closePath();
		_path = new Path2D.Double();
	}
	
	public static final class MethodNotSupported extends RuntimeException
	{
		private static final long serialVersionUID = 1L;
		public MethodNotSupported() { super("This method is not supported"); }
	}
	
	public void setFillStyle(String value) { _fillColor = parseColor(value); }	
	public void setFillStyle(Color value) { _fillColor = value; }	

	public void setStrokeStyle(String value) { _strokeColor = parseColor(value); }	
	public void setStrokeStyle(Color value) { _strokeColor = value; }	

	public void setShadowColor(String value) { _shadowColor = parseColor(value); }
	public void setShadowBlur(String value) { throw new MethodNotSupported(); }
	public void setShadowOffsetX(String value) { throw new MethodNotSupported(); }
	public void setShadowOffsetY(String value) { throw new MethodNotSupported(); }

	public void createLinearGradient() { throw new MethodNotSupported(); }
	public void createPattern() { throw new MethodNotSupported(); }
	public void createRadialGradient() { throw new MethodNotSupported(); }
	public void addColorStop() { throw new MethodNotSupported(); }
	
	public void setLineCap(String value) { throw new MethodNotSupported(); }
	public void setLineJoin(String value) { throw new MethodNotSupported(); }
	public void setLineWidth(double value)
	{
		_g2d.setStroke(new BasicStroke((float)value));
	}
	public void setMiterLimit(String value) { throw new MethodNotSupported(); }
	
	public void rect(double x, double y, double width, double height)
	{
		createPath(true);
		Point2D p = _path.getCurrentPoint();
		_path.moveTo(x, y);
		_path.lineTo(x + width, y);
		_path.lineTo(x + width, y + height);
		_path.lineTo(x, y + height);
		_path.lineTo(x, y);
		_path.moveTo(p.getX(), p.getY());
	}

	public void fillRect(double x, double y, double width, double height)
	{
		_g2d.setColor(_fillColor);
		_g2d.fill(new Rectangle.Double(x, y, width, height));
	}
	
	public void strokeRect(double x, double y, double width, double height)
	{
		_g2d.setColor(_strokeColor);
		_g2d.draw(new Rectangle.Double(x, y, width, height));
	}

	public void clearRect(double x, double y, double width, double height)
	{
		_g2d.setColor(_clearColor);
		_g2d.fill(new Rectangle.Double(x, y, width, height));
	}

	public void fill()
	{
		if(_path == null) return;
		_g2d.setColor(_fillColor);
		_g2d.fill(_path);
	}
	
	public void stroke()
	{
		if(_path == null) return;
		_g2d.setColor(_strokeColor);
		_g2d.draw(_path);
	}
	
	public void beginPath()
	{
		_path = new Path2D.Double();
	}
	
	public void moveTo(double x, double y)
	{
		createPath(true);
		_path.moveTo(x, y);
	}
	
	public void closePath()
	{
		if(_path == null) return;
		_path.closePath();
	}
	
	public void lineTo(double x, double y)
	{
		createPath(true);
		_path.lineTo(x, y);
	}
	
	public void clip()
	{
		_g2d.clip(_path);
	}
		
	public void quadraticCurveTo(double cpx, double cpy, double x, double y)	
	{
		createPath(true);
		_path.quadTo(cpx, cpy, x, y);
	}
	
	public void bezierCurveTo(double cp1x, double cp1y, double cp2x, double cp2y, double x, double y)
	{
		createPath(true);
		_path.curveTo(cp1x, cp1y, cp2x, cp2y, x, y);
	}
	
	public void arc(double x, double y, double r, double sAngle, double eAngle, boolean counterclockwise)
	{
		System.out.println("Warning JavaCanvas.arc is not supported");
		//throw new MethodNotSupported();
	}	
	
	public void arcTo(double x1, double y1, double x2, double y2, double r)
	{
		System.out.println("Warning JavaCanvas.arcTo is not supported");
		//throw new MethodNotSupported();
	}	
	public void isPointInPath()
	{
		System.out.println("Warning JavaCanvas.isPointInPath is not supported");
		//throw new MethodNotSupported();
	}	
	
	public void scale(double scalewidth, double scaleheight)
	{
		_g2d.scale(scalewidth, scaleheight);
	}
	
	public void rotate(double angle)
	{
		_g2d.rotate(angle);
	}
	
	public void translate(double x, double y)
	{
		_g2d.translate(x, y);
	}
	
	public void transform(double a, double b, double c, double d, double e, double f)
	{
		throw new MethodNotSupported();
	}	
	
	public void setTransform(double a, double b, double c, double d, double e, double f)
	{
		throw new MethodNotSupported();
	}
	
	public void setFont(String value)
	{
		value = value.trim().toLowerCase();
		int nameStart = value.indexOf('"');
		if(nameStart < 0) nameStart = value.lastIndexOf(' ');
		if(nameStart < 0) nameStart = 0;
		String name =  value.substring(nameStart).replace("\"", "").trim();
		
		boolean bold = false;
		boolean italic = false;
		String[] props = value.substring(0, nameStart).split(" +");
		for(int i=0; i<props.length; i++)
		{
			if(props[i].equals("bold")) bold = true;
			else if(props[i].equals("italic")) italic = true;
			else if(props[i].endsWith("px"))
			{
				_fontSize = (int)Double.parseDouble(props[i].substring(0, props[i].length() - 2));			
			}
			else throw new RuntimeException("invalid font value : " + value);
		}
		
		_g2dFonts.setFont(getFont(name).deriveFont(
				(bold ? Font.BOLD : 0) | (italic ? Font.ITALIC : 0),
				_fontSize
			));		
	}
	
	public void setTextAlign(String value) { throw new MethodNotSupported(); }
	public void setTextBaseline(TextBaseline value) { _textBaseline = value; }
	public void setTextBaseline(String value)
	{
		value = value.trim().toLowerCase();
		if(value.equals("alphabetic")) _textBaseline = TextBaseline.ALPHABETIC;
		else if(value.equals("top")) _textBaseline = TextBaseline.TOP;
		else if(value.equals("middle")) _textBaseline = TextBaseline.MIDDLE;
		else if(value.equals("bottom")) _textBaseline = TextBaseline.BOTTOM;
		else throw new RuntimeException("Invalid baseline value : " + value);
	}
	
	public void fillText(String text, double x, double y)
	{
		_g2d.setColor(_fillColor);
		Shape s = getTextShape(text, x, y);
		_g2d.fill(s);
		
		
//		_g2d.setColor(Color.red);
//		_g2d.drawLine(0, 0, 1000, 0);
//		_g2d.setColor(Color.black);
	}
	
	public void strokeText(String text, double x, double y)
	{
		_g2d.setColor(_fillColor);
		_g2d.draw(getTextShape(text, x, y));
	}
	
	public void drawImage(String descriptor, double x, double y, double width, double height)
	{
		if(descriptor.length() == 0) return;
		String[] dp = descriptor.split(" +");

		try
		{
			BufferedImage image = getImageFromCache(_imgDomain + dp[1]);
			if(image == null)
			{
				URL url = new URL(_imageServiceURL + _imgDomain + dp[1]);			
				image = ImageIO.read(url);
				addImageToCache(_imgDomain + dp[1], image);
			}
			
			AffineTransform t = new AffineTransform();
	        t.translate(x, y);	        
	        t.scale(width / (double)image.getWidth(), height / (double)image.getHeight()); 
	        _g2d.drawImage(image, t, null);
		}
		catch(Exception e)
		{
			throw new RuntimeException("Error drawing image id " + dp[1] + " : " + e.getMessage());
		}
	}
	
	public void setWidth(String value) { throw new MethodNotSupported(); }	
	public void setHeight(String value) { throw new MethodNotSupported(); }
	
	public void putImageData() { throw new MethodNotSupported(); }

	public void setGlobalAlpha(String value) { throw new MethodNotSupported(); }
	public void setGlobalCompositeOperation(String value) { throw new MethodNotSupported(); }
	
	public void save()
	{
		SavedContext sc = new SavedContext();
		
		sc.g2d = _g2d;		
		_g2d = (Graphics2D)_g2d.create();
		
		sc.fontSize = _fontSize;
		sc.fillColor = _fillColor;
		sc.strokeColor = _strokeColor;
		sc.shadowColor = _shadowColor;
		sc.clearColor = _clearColor;
		sc.textAlignment = _textAlignment;
		sc.textBaseline = _textBaseline;
		
		_contextStack.push(sc);
	}
	
	public void restore()
	{
		if(_contextStack.isEmpty()) return;		
		SavedContext sc = _contextStack.pop(); 
		
		_g2d.dispose();
		_g2d = sc.g2d;
		
		_fontSize = sc.fontSize;
		_fillColor = sc.fillColor;
		_strokeColor = sc.strokeColor;
		_shadowColor = sc.shadowColor;
		_clearColor = sc.clearColor;
		_textAlignment = sc.textAlignment;
		_textBaseline = sc.textBaseline;
	}
	
	public int measureTextWidth(String text)
	{		
		return _g2dFonts.getFontMetrics().stringWidth(text);
	}
	
	public double measureTextWidth(Character c)
	{
		return _g2dFonts.getFontMetrics().charWidth(c);
	}

	public int measureTextHeight()
	{
		return _g2dFonts.getFontMetrics().getHeight();
	}
	
	public int getWidth() { return _image.getWidth(); }
	public int getHeight() { return _image.getHeight(); }

	public void init(double width, double height, boolean alphaChannel)
	{
		int nWidth = (int)Math.round(width);
		int nHeight = (int)Math.round(height);
		_image = new BufferedImage(nWidth, nHeight, alphaChannel ? BufferedImage.TYPE_4BYTE_ABGR : BufferedImage.TYPE_3BYTE_BGR); 
        _g2d = (Graphics2D)_image.getGraphics();
        
        _g2d.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
        _g2d.setRenderingHint(RenderingHints.KEY_DITHERING, RenderingHints.VALUE_DITHER_ENABLE);
        _g2d.setRenderingHint(RenderingHints.KEY_INTERPOLATION, RenderingHints.VALUE_INTERPOLATION_BILINEAR);
        _g2d.setRenderingHint(RenderingHints.KEY_TEXT_ANTIALIASING, RenderingHints.VALUE_TEXT_ANTIALIAS_ON);
        
        _g2dFonts = (Graphics2D)_image.getGraphics();
        _g2dFonts.scale(40, 40);
        
	}
	
	public JavaCanvas(int width, int height, String fontDirectory, String imageDomain, String imageServiceURL, boolean alphaChannel)
	{
		_fontDirectory = fontDirectory;
		init(width, height, alphaChannel);
		_imgDomain = imageDomain;
		_imageServiceURL = imageServiceURL;
	}
	
	
	
}
