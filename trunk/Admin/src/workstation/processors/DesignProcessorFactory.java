package workstation.processors;


import java.util.Arrays;
import java.util.Comparator;
import java.util.Hashtable;

public class DesignProcessorFactory
{
	public static String INSTANCE_NAME = "Admin_DesignProcessorFactory";
	
	public enum DesignProcessorType {
		DynamicLaser,
		DynamicLaser2,
		DynamicLaserBlack,
		DynamicLaserIndex,
		Summary,
		//MRCANTrio,
		//RGCIndex,
		//RGCLabel,
		Trio,
		TrioIndex,
		Polymer,
		PolymerIndex,
		Embosser,
		EmbosserBlack,
		EmbosserIndex,
		DynamicFlash,
		PhoneCover,
		ShippingLabels,
		ChangeStatus,
		XLSShippingProcessor,
		FedexShippingProcessor,
		UPSShippingProcessor,
		PackingSlipProcessor,
		ProductionSheet,
		BatchInput,
		BatchInput2,
		JaneBatchInput,
		JaneBatchInput2,
		ZulilyXSL,
		ZulilyXSL2
	}
	
	
	private static Hashtable<DesignProcessorType, Class<?>> _processors;
	
	static {
		_processors = new Hashtable<DesignProcessorType, Class<?>>();
		_processors.put(DesignProcessorType.DynamicLaser, DynamicLaserProcessor.class);
		_processors.put(DesignProcessorType.DynamicLaser2, DynamicLaserProcessor2.class);
		_processors.put(DesignProcessorType.DynamicLaserBlack, DynamicLaserBlack.class);
		_processors.put(DesignProcessorType.DynamicLaserIndex, DynamicLaserIndexProcessor.class);
		_processors.put(DesignProcessorType.Summary, SummaryProcessor.class);
		//_processors.put(DesignProcessorType.MRCANTrio, MRCanTrioProcessor.class);
		//_processors.put(DesignProcessorType.RGCLabel, RGCLabelProcessor.class);
		//_processors.put(DesignProcessorType.RGCIndex, RGCIndexProcessor.class);
		_processors.put(DesignProcessorType.Trio, TrioProcessor.class);
		_processors.put(DesignProcessorType.TrioIndex, TrioIndexProcessor.class);
		_processors.put(DesignProcessorType.Polymer, PolymerProcessor.class);
		_processors.put(DesignProcessorType.PolymerIndex, PolymerIndexProcessor.class);
		_processors.put(DesignProcessorType.Embosser, EmbosserProcessor.class);
		_processors.put(DesignProcessorType.EmbosserBlack, EmbosserBlack.class);
		_processors.put(DesignProcessorType.EmbosserIndex, EmbosserIndexProcessor.class);
		_processors.put(DesignProcessorType.DynamicFlash, DynamicFlashProcessor.class);
		_processors.put(DesignProcessorType.PhoneCover, PhoneCoverProcessor.class);
		_processors.put(DesignProcessorType.ShippingLabels, ShippingLabelProcessor.class);
		_processors.put(DesignProcessorType.ChangeStatus, OrderStatusProcessor.class);
		_processors.put(DesignProcessorType.XLSShippingProcessor, XLSShippingProcessor.class);
		_processors.put(DesignProcessorType.FedexShippingProcessor, FedexShippingProcessor.class);
		_processors.put(DesignProcessorType.UPSShippingProcessor, UPSShippingProcessor.class);
		_processors.put(DesignProcessorType.PackingSlipProcessor, PackingSlipProcessor.class);
		_processors.put(DesignProcessorType.ProductionSheet, ProductionSheetProcessor.class);
		_processors.put(DesignProcessorType.BatchInput, BatchInputProcessor.class);
		_processors.put(DesignProcessorType.BatchInput2, BatchInputProcessor2.class);
		_processors.put(DesignProcessorType.JaneBatchInput, JaneBatchInputProcessor.class);
		_processors.put(DesignProcessorType.JaneBatchInput2, JaneBatchInputProcessor2.class);
		_processors.put(DesignProcessorType.ZulilyXSL, ZulilyXSLProcessor.class);
		_processors.put(DesignProcessorType.ZulilyXSL2, ZulilyXSLProcessor2.class);
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
 