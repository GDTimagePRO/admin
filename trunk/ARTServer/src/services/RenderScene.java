package services;

import graphics.JavaCanvas;
import graphics.Scene;
import graphics.SceneContext;
import graphics.drawables.CircularClipMask;
import graphics.drawables.GridDrawable;
import graphics.drawables.IDrawable;
import graphics.drawables.ImageDrawable;
import graphics.drawables.LineDrawable;
import graphics.drawables.PatternMapDrawable;
import graphics.drawables.RectDrawable;
import graphics.drawables.RectangleWidget;
import graphics.drawables.RectangularClipMask;
import graphics.drawables.TouchWidget;
import graphics.maps.CircleMap;
import graphics.maps.CompositeMap;
import graphics.maps.EllipseMap;
import graphics.maps.IMap;
import graphics.maps.LineMap;
import graphics.maps.MapCollection;
import graphics.patterns.IPattern;
import graphics.patterns.PatternDotted;
import graphics.patterns.PatternFill;
import graphics.patterns.PatternHash;
import graphics.patterns.PatternHighlight;
import graphics.patterns.PatternLine;
import graphics.patterns.PatternRibbon;
import graphics.patterns.PatternRope;
import graphics.patterns.PatternStars;
import graphics.patterns.PatternStripes;
import graphics.patterns.PatternStripes2;
import graphics.patterns.TextPattern;

import java.awt.Color;
import java.awt.Graphics2D;
import java.awt.font.FontRenderContext;
import java.awt.font.LineBreakMeasurer;
import java.awt.font.TextAttribute;
import java.awt.font.TextLayout;
import java.awt.image.BufferedImage;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.text.AttributedCharacterIterator;
import java.text.AttributedString;
import java.util.ArrayList;
import java.util.Base64;
import java.util.Hashtable;
import java.util.Map;
import java.util.Map.Entry;
import java.util.logging.Logger;

import javax.imageio.ImageIO;
import javax.servlet.ServletConfig;
import javax.servlet.ServletContext;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.io.IOUtils;

import com.google.gson.Gson;

import data.ImageResourceManager;
import data.ResourceId;

/**
 * Servlet implementation class RenderScene
 */
@WebServlet("/RenderScene")
public final class RenderScene extends HttpServlet
{
	private static class DrawableParams
	{
		public String n;
		public Object[] p;
	}
	
	private static class LayerParams
	{
		public String n;
		public DrawableParams cm;
		public DrawableParams[] d;
	}

	public static class PaletteColorParams
	{
		public String name;
		public String value;
	}

	private static class SceneParams
	{
		public double s;
		public double w;
		public double h;
		public Map<String,PaletteColorParams> colors;
		
		public LayerParams[] l;
	}
	
	
	private static IPattern createPattern(ArrayList<Object> params)
	{
		IPattern pattern = null;
		String patternId = (String)params.get(0);

		if(patternId.equals("Txt"))
		{
			pattern = new TextPattern(
					(String)params.get(1), //text 
					(String)params.get(2), //font 
					(Boolean)params.get(3), //scaleToFit 
					(Boolean)params.get(4), //invert 
					(Boolean)params.get(5), //bold 
					(Boolean)params.get(6), //italic 
					(Double)params.get(7), //minSize 
					((Double)params.get(8)).intValue(), //alignment 
					((Double)params.get(9)).intValue() //verticalAlignment
				);
		}
		else if(patternId.equals("Dot"))
		{
			pattern = new PatternDotted();
		}
		else if(patternId.equals("Fill"))
		{
			pattern = new PatternFill();
		}
		else if(patternId.equals("Hash"))
		{
			pattern = new PatternHash();
		}
		else if(patternId.equals("HL"))
		{
			pattern = new PatternHighlight((Double)params.get(1), (Double)params.get(2));
		}
		else if(patternId.equals("Lines"))
		{
			ArrayList<Object> lineSpecStates = (ArrayList<Object>)params.get(1);
			PatternLine.LineSpec[] lineSpecs = new PatternLine.LineSpec[lineSpecStates.size()];
			for(int i=0; i<lineSpecStates.size(); i++)
			{
				ArrayList<Object>  p = (ArrayList<Object>)lineSpecStates.get(i);				
				lineSpecs[i] = new PatternLine.LineSpec(
						(Double)p.get(0), //radius 
						(Double)p.get(1), //size 
						(Double)p.get(2), //distance 
						(String)p.get(3)  //corner
					);
			}
			
			pattern = new PatternLine(lineSpecs);
		}
		else if(patternId.equals("Rib"))
		{
			pattern = new PatternRibbon();
		}
		else if(patternId.equals("Rope"))
		{
			pattern = new PatternRope();
		}
		else if(patternId.equals("Star"))
		{
			pattern = new PatternStars();
		}
		else if(patternId.equals("Stripe"))
		{
			pattern = new PatternStripes();
		}
		else if(patternId.equals("Stripe2"))
		{
			pattern = new PatternStripes2();
		}
		
		return pattern;
		
	}
	
	private static IMap createMap(ArrayList<Object> params)
	{
		IMap map = null;
		String mapId = (String)params.get(0);
		if(mapId.equals("Line"))
		{
			map = new LineMap(
					(Double)params.get(1), //destX1 
					(Double)params.get(2), //destY1 
					(Double)params.get(3), //destX2 
					(Double)params.get(4), //destY2 
					(Double)params.get(5),	//srcX 
					(Double)params.get(6)//srcScale
				);
		}
		else if(mapId.equals("Circle"))
		{
			map = new CircleMap(
					(Double)params.get(1), 	//centerX
					(Double)params.get(2), 	//centerY
					(Double)params.get(3),	//angleStart 
					(Double)params.get(4),	//angleEnd 
					(Double)params.get(5),	//radius 
					(Double)params.get(6),	//srcX 
					(Double)params.get(7)	//srcScale
				);
		}		
		else if(mapId.equals("Ellipse"))
		{
			map = new EllipseMap(
					(Double)params.get(1),	//offsetX 
					(Double)params.get(2),	//offsetY 
					(Double)params.get(3),	//width 
					(Double)params.get(4),	//height 
					(Double)params.get(5),	//angleStart 
					(Double)params.get(6),	//angleEnd 
					(Double)params.get(7)	//srcScale
				);
		}
		else if(mapId.equals("Comp"))
		{
			CompositeMap compMap = new CompositeMap();
			for(int i=1; i<params.size(); i++)
			{
				compMap.addMap(createMap((ArrayList<Object>)params.get(i)));
			}
			map = compMap;
		}
		else if(mapId.equals("MC"))
		{
			MapCollection mc = new MapCollection();
			for(int i=1; i<params.size(); i++)
			{
				mc.addMap(createMap((ArrayList<Object>)params.get(i)));
				mc.addCorner();
			}
			map = mc;
		}
		
		return map;
	}
	
	
	private static IDrawable createDrawable(DrawableParams dp, SceneContext ctx)
	{
		if(dp.n.equals("I"))
		{
			return new ImageDrawable(
					(String)dp.p[0], 	//descriptor
					(Double)dp.p[1], 	//x
					(Double)dp.p[2], 	//y
					(Double)dp.p[3], 	//width
					(Double)dp.p[4],	//height
					(Double)dp.p[5],	//angle
					(Double)dp.p[6]		//type
				);
		}
		else if(dp.n.equals("L"))
		{
			return new LineDrawable(
					(Double)dp.p[0],	//x1
					(Double)dp.p[1],	//y1
					(Double)dp.p[2],	//x2
					(Double)dp.p[3],	//y2
					(Double)dp.p[4],	//lineWidth
					(String)dp.p[5],		//fgColor
					ctx
				);
		}
		else if(dp.n.equals("R"))
		{
			return new RectDrawable(
					(Double)dp.p[0],	//x
					(Double)dp.p[1], 	//y
					(Double)dp.p[2], 	//width
					(Double)dp.p[3],	//height
					(String)dp.p[4],	//fgColor
					ctx
				);
		}
		else if(dp.n.equals("P"))
		{
			return new PatternMapDrawable(
					createMap((ArrayList<Object>)dp.p[0]),		//map
					createPattern((ArrayList<Object>)dp.p[1]),	//pattern
					(Double)dp.p[2], 					//size
					(Double)dp.p[3], 					//spacingScale
					(String)dp.p[4], 					//fgColor
					(String)dp.p[5],					//bgColor
					ctx
				);
		}
		else if(dp.n.equals("RCM"))
		{
			return new RectangularClipMask(
					(Double)dp.p[0],	//width 
					(Double)dp.p[1]		//height
				);
		}
		else if(dp.n.equals("CCM"))
		{
			return new CircularClipMask(
					(Double)dp.p[0],	//width 
					(Double)dp.p[1]		//height
				);
		}
		else if(dp.n.equals("TW"))
		{
			return new TouchWidget(
					(Double)dp.p[0],	//x 
					(Double)dp.p[1],	//y 
					(Boolean)dp.p[2],	//isActive 
					(Boolean)dp.p[3]	//editAllowMove
				);
		}
		else if(dp.n.equals("RW"))
		{
			return new RectangleWidget(
					(Double)dp.p[0],	//x1 
					(Double)dp.p[1],	//y1 
					(Double)dp.p[2],	//x2 
					(Double)dp.p[3],	//y2 
					(Double)dp.p[4]		//angle
				);
		}
		return null;
	}
	
	private static final long serialVersionUID = 1L;
       
    /**
     * @see HttpServlet#HttpServlet()
     */
    public RenderScene() 
    {
        super();
    }

    @Override
	public void init(ServletConfig config) throws ServletException
	{
		super.init(config);
		ServletContext context = getServletContext();
	}
    
    public static JavaCanvas getCanvas(String sceneJSON, int imgFrameWidth, int imgFrameHeight, String imgDomain, String fillColor, String destId) {
    	return getCanvas(sceneJSON, imgFrameWidth, imgFrameHeight, imgDomain, fillColor, destId, false);
    }
    
    public static JavaCanvas getCanvas(String sceneJSON, int imgFrameWidth, int imgFrameHeight, String imgDomain, String fillColor, String destId, boolean drawGrid) {
    	Gson gson = new Gson();
		SceneParams sceneParams = gson.fromJson(sceneJSON, SceneParams.class);
		
		
		JavaCanvas canvas = new JavaCanvas(imgFrameWidth, imgFrameHeight, imgDomain, (destId != null) && (fillColor == null));
		if(fillColor != null)
		{
			canvas.setFillStyle(fillColor);
			canvas.fillRect(0, 0, canvas.getWidth(), canvas.getHeight());
		}

		
		
		Scene scene = new Scene(sceneParams.w, sceneParams.h, sceneParams.s, canvas);
		
		for(Entry<String, PaletteColorParams> i : sceneParams.colors.entrySet())
		{
			scene.params.setColor(i.getKey(), JavaCanvas.parseColor(i.getValue().value));
		}
		
		double width = 0;
		double height = 0;
		
		for(int iLayer=0; iLayer<sceneParams.l.length; iLayer++)
		{
			LayerParams lp = sceneParams.l[iLayer];
			Scene.Layer layer = scene.addLayer(lp.n);

			if(lp.cm != null)
			{
				layer.clipMask = createDrawable(lp.cm, scene.params);
				if (lp.n.equals("f")) {
					width = (double)lp.cm.p[0];
					height = (double)lp.cm.p[1];
					
				}
			}
			

			for(int iDrawable=0; iDrawable<lp.d.length; iDrawable++)
			{
				layer.add(createDrawable(lp.d[iDrawable], scene.params));
			}
		}
		
		if (drawGrid) {
			scene.getLayer(Scene.LAYER_NAME_OVERLAY).add(new GridDrawable(1, Color.BLACK, width, height));
		}
		
		scene.draw();
		return canvas;
    }

	/**
	 * @see HttpServlet#doGet(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException
	{
		try
		{
			
			response.setHeader("Access-Control-Allow-Origin", "*");			
			response.setHeader("Pragma-directive", "no-cache");
			response.setHeader("Cache-directive", "no-cache");
			response.setHeader("Cache-control", "no-cache");
			response.setHeader("Pragma", "no-cache");
			response.setDateHeader("Expires", 0);
			
			
			//long startTime = System.currentTimeMillis();

			
			if("true".equals(request.getParameter("clearCache")))
			{
				JavaCanvas.clearCache();
			}
			
			String sceneJSON = request.getParameter("sceneJSON");			
			int imgWidth = (int)Double.parseDouble(request.getParameter("imgWidth"));
			int imgHeight = (int)Double.parseDouble(request.getParameter("imgHeight"));
			int imgFrameWidth = imgWidth; 
			int imgFrameHeight = imgHeight;
			String fillColor = request.getParameter("fillColor");
			
			String paperColor = request.getParameter("paperColor");
			
			String tmpVal = request.getParameter("imgFrameWidth");
			if(tmpVal != null) imgFrameWidth = Integer.parseInt(tmpVal);
			
			tmpVal = request.getParameter("imgFrameHeight");
			if(tmpVal != null) imgFrameHeight = Integer.parseInt(tmpVal);

			
			String imgDomain = request.getParameter("imgDomain");
			String destId = request.getParameter("destId");
			boolean drawGrid = Boolean.parseBoolean(request.getParameter("drawGrid"));
			
			
			JavaCanvas canvas = getCanvas(sceneJSON, imgFrameWidth, imgFrameHeight, imgDomain, fillColor, destId, drawGrid);

			
/*			
			if((imgWidth < imgFrameWidth) && (imgHeight < imgFrameHeight))
			{
				double paddingSize = Math.min(
						imgFrameWidth - imgWidth, 
						imgFrameHeight - imgHeight 
					) / 2 ;

					double lineWidth = Math.min(10, paddingSize);
					canvas.setLineWidth(1);
					canvas.setFillStyle(Color.BLACK);
					canvas.fillRect( 0, 0, imgFrameWidth, lineWidth );
					canvas.fillRect( 0, 0, lineWidth, imgFrameHeight );
					canvas.fillRect( imgFrameWidth - lineWidth, 0, imgFrameWidth, imgFrameHeight );
					canvas.fillRect( 0, imgFrameHeight - lineWidth, imgFrameWidth, imgFrameHeight );
			}
//*/			
			if(destId == null)
			{
				//response.setHeader("Content-Type", "image/png");			
				//ImageIO.write(canvas.getImage(), "png", response.getOutputStream());

				//response.setHeader("Content-Type", "image/gif");			
				//ImageIO.write(canvas.getImage(), "gif", response.getOutputStream());

				response.setHeader("Content-Type", "image/jpeg");		
				ImageIO.write(canvas.getImage(), "jpeg", response.getOutputStream());
			}
			else
			{
				ResourceId destRID = ResourceId.fromId(destId);
				OutputStream destOS = destRID.getOutputStream();
				ImageIO.write(canvas.getImage(), "png", destOS);
				destOS.close();
				
				response.setHeader("Content-Type", "text/plain");			
				response.getWriter().print("true");
			}
			
			//long endTime = System.currentTimeMillis();
						
			//System.out.println("Done ------------- " + (endTime - startTime) + "ms" );

		}
		catch(Exception e)
		{
			e.printStackTrace();
			
			if(request.getParameter("destId") != null)
			{
				response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, e.getMessage() + "\n" + e.getStackTrace());
			}
			else
			{
				int imgWidth = (int)Double.parseDouble(request.getParameter("imgWidth"));
				int imgHeight = (int)Double.parseDouble(request.getParameter("imgHeight"));

				BufferedImage img = ImageResourceManager.createImage(imgWidth, imgHeight, false);
				Graphics2D g2d = img.createGraphics();
				g2d.setColor(Color.WHITE);
				g2d.fillRect(0, 0, imgWidth, imgHeight);
				
				g2d.setColor(Color.BLACK);
				
				String msg = e.getMessage() + " -- ";
				StackTraceElement[] st = e.getStackTrace();
				for(int i = 0; i<st.length; i++)
				{
					msg += st[i].toString()  + " -- "; 
				}
				
			    Hashtable<TextAttribute, Object> map = new Hashtable<TextAttribute, Object>();
			    map.put(TextAttribute.FAMILY, "Serif");
		        map.put(TextAttribute.SIZE, new Float(18.0));			    
			  
				AttributedString text = new AttributedString(msg, map);				
				AttributedCharacterIterator paragraph = text.getIterator();
			    int paragraphStart = paragraph.getBeginIndex();
			    int paragraphEnd = paragraph.getEndIndex();
			    FontRenderContext frc = g2d.getFontRenderContext();
			    LineBreakMeasurer lineMeasurer = new LineBreakMeasurer(paragraph, frc);

			    float breakWidth = img.getWidth();
		        float drawPosY = 0;
		        lineMeasurer.setPosition(paragraphStart);
		 
		        while (lineMeasurer.getPosition() < paragraphEnd)
		        {
		            TextLayout layout = lineMeasurer.nextLayout(breakWidth);		 
		            float drawPosX = layout.isLeftToRight() ? 0 : breakWidth - layout.getAdvance();
		            drawPosY += layout.getAscent();
		            layout.draw(g2d, drawPosX, drawPosY);
		            drawPosY += layout.getDescent() + layout.getLeading();
		        }
			    
				
//				Font font = g2d.getFont();
//				FontRenderContext frc = g2d.getFontRenderContext();
//				TextLayout layout = new TextLayout(e.getMessage() + "\n", font, frc );
//				layout.draw(g2d, 0, 0);

				response.setHeader("Content-Type", "image/jpeg");			
				ImageIO.write(img, "jpeg", response.getOutputStream());
				//Rectangle2D bounds = layout.getBounds();				
			}
			
		}
	}

	/**
	 * @see HttpServlet#doPost(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException
	{
	}

}
