import java.io.InputStream;
import java.io.InputStreamReader;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.util.Hashtable;

import com.google.gson.Gson;

/**
 * SCLRenderer : Simple Canvas Log Renderer
 */
public final class SCLRenderer
{
	private final static class Command
	{
		public String n;
		public Object[] p;
	}
	
	Hashtable<String, Method> _binding = new Hashtable<String, Method>(); 
	
	public JavaCanvas render(InputStream in)
	{
		JavaCanvas canvas = new JavaCanvas();
		
		
		Gson gson = new Gson();
		Command[] cmdList = gson.fromJson(new InputStreamReader(in), Command[].class);
		for(int i=0; i<cmdList.length; i++)
		{
			Command cmd = cmdList[i];
			Method m = _binding.get(cmd.n);
			if(m == null) throw new RuntimeException("No binding found for : " + cmd.n);
			try
			{
				m.invoke(canvas, cmd.p);
			}
			catch(IllegalAccessException e) { e.printStackTrace(); }
			catch(InvocationTargetException e) {
				e.getTargetException().printStackTrace();				
				//e.printStackTrace(); 
			}
			catch(IllegalArgumentException e)
			{
				throw new RuntimeException("Invalid parameters for cmd[" + i + "] = " + cmd.n);
			}
		}		
//		int one = gson.fromJson("1", int.class);
//		Integer one = gson.fromJson("1", Integer.class);
//		Long one = gson.fromJson("1", Long.class);
//		Boolean false = gson.fromJson("false", Boolean.class);
//		String str = gson.fromJson("\"abc\"", String.class);
//		String anotherStr = gson.fromJson("[\"abc\"]", String.class);


		
		return canvas;
	}


	private void addMethod(String name, String alias)
	{
		//Note: This method does not support overloaded methods
		Method[] methods = JavaCanvas.class.getMethods();
		for(int i=0; i<methods.length; i++)
		{
			if(methods[i].getName().equals(name))
			{
				_binding.put(alias, methods[i]);
				return;
			}
		}
		throw new RuntimeException("Method not found : " + name);
	}
	
	private void addMethod(String name) { addMethod(name, name); }

	private void addParam(String name, String alias)
	{
		name = "set" + name.substring(0, 1).toUpperCase() + name.substring(1); 
		addMethod(name, alias);	
	}

	private void addParam(String name) { addParam(name, name); }

	public SCLRenderer()
	{
		//Colors, Styles, and Shadows	
		addParam("fillStyle");
		addParam("strokeStyle");
		
		//Line Styles
		addParam("lineWidth");
		
		//Rectangles
		addMethod("rect");
		addMethod("fillRect");
		addMethod("strokeRect");
		addMethod("clearRect");
		
		//Paths
		addMethod("fill","f");
		addMethod("stroke","s");
		addMethod("beginPath","bp");
		addMethod("moveTo","m");
		addMethod("closePath","cp");
		addMethod("lineTo","l");
		addMethod("clip");
		addMethod("quadraticCurveTo");
		addMethod("bezierCurveTo");
		addMethod("arc");
		addMethod("arcTo");
		
		//Transformations
		addMethod("scale");
		addMethod("rotate","tr");
		addMethod("translate","tl");
		addMethod("transform","tf");
		addMethod("setTransform");
		
		//Text
		addParam("font");
		addParam("textAlign");
		addParam("textBaseline");

		addMethod("fillText","ft");
		addMethod("strokeText","st");
		
		//Image Drawing
		addMethod("drawImage");
		
		//Compositing
		addParam("globalAlpha");
		addParam("globalCompositeOperation");
		
		//Other
		addMethod("save", "sv");
		addMethod("restore", "r");
		
		addMethod("init");
	}
}
