package backend;
import backend.ResourceManager.*;
public class Design_Elements {
	static class DesignTextElement
	{
		public static void alphaSort(String[] data){
			int j;
            boolean flag = true;  // will determine when the sort is finished
            String temp;

            while (flag)
            {
                  flag = false;
                  for ( j = 0;  j < data.length - 1;  j++ )
                  {
                          if (data[ j ].compareToIgnoreCase(data[j+1]) > 0)
                          {                                             // ascending sort
                                      temp = data[j];
                                      data[j] = data[j+1];     // swapping
                                      data[j+1] = temp; 
                                      flag = true;
                           } 
                   } 
            } 
		}
		public static void writeJS(String customerId, String customerName)
		{
			System.out.print("	TextElement.FONTS = [" + "\n");
				
			ResourceId temp = new ResourceId();
			temp.type = ResourceManager.GROUP_LEGACY_FONTS;
			String [] legacyFonts = temp.getFileList(true);
			alphaSort(legacyFonts);
				
			Boolean isFirst = true;
			for(int fontId = 0; fontId < legacyFonts.length; fontId ++)
			{
				String fileName = legacyFonts[fontId].substring(legacyFonts[fontId].lastIndexOf('/') + 1);
				if((fileName.length() > 4) && (fileName.toLowerCase().endsWith("ttf")))
				{
					if(!isFirst) System.out.print(",");
					isFirst = false;
						
					String filePath = legacyFonts[fontId].substring(legacyFonts[fontId].indexOf('/') + 1);
					String legacyId = fileName.substring(0, fileName.length() - 4);
					System.out.print("{ name:" + fileName.toLowerCase().substring(0, 1) + "* : " + filePath.replace("\"", "\\\"").replace("\\", "\\\\") 
							+ ", id:" + legacyFonts[fontId].replace("\"", "\\\"").replace("\\", "\\\\") + ", legacyId:" 
							+ legacyId.replace("\"", "\\\"").replace("\\", "\\\\") + "}\n");
					
				}
			}
			
			temp.type = ResourceManager.GROUP_CUSTOMER;
			temp.group = (customerId + "/" + ResourceManager.DIR_CUSTOMER_FONTS);
			String [] customerFonts = temp.getFileList(true);
			alphaSort(customerFonts);
			for(int fontId = 0; fontId < customerFonts.length; fontId ++)
			{
				String fileName = customerFonts[fontId].substring(customerFonts[fontId].lastIndexOf('/') + 1);
				if((fileName.length() > 4) && (fileName.toLowerCase().endsWith("ttf")))
				{
					if(!isFirst) System.out.print(",");
					isFirst = false;
						
					String filePath = customerFonts[fontId].substring(customerFonts[fontId].indexOf('/') + 1).substring(customerFonts[fontId].indexOf('/') + 1); //Bypasses the first / and goes to the second /
					String legacyId = filePath.substring(ResourceManager.DIR_CUSTOMER_FONTS.length() + 1);
					System.out.print("{ name:" + fileName.toLowerCase().substring(0, 1) + " [" + filePath.replace("\"", "\\\"").replace("\\", "\\\\") 
							+ "] : " + customerName.replace("\"", "\\\"").replace("\\", "\\\\") + ", id:" 
							+ customerFonts[fontId].replace("\"", "\\\"").replace("\\", "\\\\") + "}\n");
					
				}
			}
			
					
			System.out.print("	];\n");			
		}
	}
	
	static class DesignElements
	{
		public static void writeJS(String customerId)
		{
			Startup system = Startup.getInstance();
			Customer customer = system.db.order.getCustomerById(customerId);
						
			DesignTextElement.writeJS(customerId, customer.description);
		}
	}
}
