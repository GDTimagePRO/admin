package workstation.processors;

import java.util.Arrays;
import java.util.Comparator;
import java.util.Hashtable;

import workstation.processors.DesignProcessorFactory.DesignProcessorType;

public class DesignProcessorFactoryForRedemption {
public static String INSTANCE_NAME = "Admin_DesignProcessorFactory";
	
	public enum DesignProcessorType {
		PackingSlipProcessor
	}
	
	
	private static Hashtable<DesignProcessorType, Class<?>> _processors;
	
	static {
		_processors = new Hashtable<DesignProcessorType, Class<?>>();
		_processors.put(DesignProcessorType.PackingSlipProcessor, PackingSlipProcessor.class);
	}
	
	/*private static final Class<?> _processors[] = new Class<?>[] {
		PrintProcessor.class
	};*/
	
	public DesignProcessor[] getProcessors()
	{
		try
		{
			DesignProcessor[] result = new DesignProcessor[_processors.size()];
			Class<?> values[] =  _processors.values().toArray(new Class<?>[_processors.size()]);
			for(int i=0; i<result.length; i++)
			{
				result[i] = (DesignProcessor)values[i].newInstance(); 
			}
			Arrays.sort(result, new Comparator<DesignProcessor>() {
				@Override
				public int compare(DesignProcessor arg0, DesignProcessor arg1) {
						return arg0.getName().compareTo(arg1.getName());
				}
			});
			return result;
		}
		catch(Exception e) { throw new RuntimeException(e); }
	}

	public DesignProcessor getProcessor(DesignProcessorType type) {
		try
		{
			return (DesignProcessor) _processors.get(type).newInstance();
		}
		catch(Exception e) { throw new RuntimeException(e); }
	}
}
