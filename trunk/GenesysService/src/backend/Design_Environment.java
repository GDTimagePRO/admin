package backend;

import java.io.InputStreamReader;
import java.io.StringWriter;
import java.net.URL;
import java.util.ArrayList;

import backend.Order.*;
import backend.Settings.Result;
import backend.Resource_Manager.*;
import backend.db_design.Design;

public class Design_Environment {
	class PaletteColor
	{
		public static final String COLOR_INK		= "ink";
	
		public String name;
		public String value;
	
		public PaletteColor(String name, String value)
		{
			name = this.name;
			value = this.value;
		}
	}
	
	static class ActiveDesign
	{
		public static final int EDITOR_SIMPLE		= 0;
		public static final int EDITOR_ADVANCED	= 1;
		
		/**
		 * @var Design
		 */
		public Design design = null;
		public String previewImageId = new String();
		public PaletteColor [] colorPalettes = new PaletteColor[]{};
		
		public defaultValues = NULL;
		
		
		/**
		 * @param ConfigItem configItem
		 * @return ActiveDesign
		 */
		public static ActiveDesign createFromBarcodeConfigItem(ConfigItem configItem)
		{
			ActiveDesign activeDesign = new ActiveDesign();
			
			Startup system = Startup.getInstance();
			designDB = system.db.design;
			orderDB = system.db.order;
			
			Integer product = orderDB.getProductById(configItem.productId);
			if(product == null) return null;
			
			Integer templateId = configItem.templateId;
			if(templateId == null)
			{
				templateId = designDB.getDefualtDesignTemplateId(product.productTypeId);
				if(templateId < 0) return null;
			}
				
			Integer template = designDB.getDesignTemplateById(templateId);
			if(template == null) return null;
			
			Design design = new Design();
			design.setConfigItem(ConfigItem.merge(configItem, template.getConfigItem()));
			design.orderItemId = -1;
			design.productId = product.id;
			design.productTypeId = product.productTypeId;
			design.designJSON = template.designJSON;
			design.state = Design.STATE_PENDING_SCL_RENDERING;

			return activeDesign;
		}
		
		public static void load(String orderId)
		{
		}
		
		public Boolean save()
		{
			
			Design designDB = Startup.getInstance().db.design;
			if(design.id == -1)
			{
				if(!designDB.createDesign(design)) return false;

				Result response = Settings.resourceOpCopy(previewImageId,
						design.getPreviewImageId());
				return response.errorCode == Result.CODE_OK;
			}
			else
			{
				if(!designDB.updateDesign(design)) return false;
			}
			
			return true;
		}
		
	}


	class DesignEnvironment
	{
		public static final int MODE_NEW_ORDER = 1;
		public static final int MODE_EDIT_TEMPLATE = 20;
		
		public int mode = MODE_NEW_ORDER;
		public theme = NULL;

		public batchImportQueueItemId = NULL;
		
		/**
		 * @var OrderItem
		 */
		public OrderItem orderItem;
		public ArrayList<ActiveDesign> activeDesigns;
		
		/**
		 * @return DesignEnvironment
		 */
		public DesignEnvironment createFromTemplate(int templateId, int sessionId, int productId)
		{
			DesignEnvironment designEnvironment = new DesignEnvironment();
			designEnvironment.mode = DesignEnvironment.MODE_EDIT_TEMPLATE;
				
			OrderItem orderItem = designEnvironment.orderItem;
			Config config = new Config();
			config.uiMode = Config.UI_MODE_NORMAL;
			orderItem.setConfig(config);
			
			ConfigItem configItem = new ConfigItem();
			configItem.productId = productId;
			configItem.templateId = templateId;
			configItem.templateCategoryId = ConfigItem.TEMPLATE_CATEGORY_ID_WILDCARD;
			ActiveDesign activeDesign = ActiveDesign.createFromBarcodeConfigItem(configItem);

			
			activeDesign.colorPalettes =  new PaletteColor[]{
// 					new PaletteColor('Fire Brick',		'B22222'),
// 					new PaletteColor('Royal Blue',		'4169E1'),
// 					new PaletteColor('Crimson',			'DC143C'),
// 					new PaletteColor('Pale Violet Red',	'DB7093'),
// 					new PaletteColor('Lime Green',		'32CD32'),
// 					new PaletteColor('Dodger Blue',		'1E90FF'),
// 					new PaletteColor('Sienna',			'A0522D'),
// 					new PaletteColor('Slate Blue',		'6A5ACD'),
// 					new PaletteColor('Black',			'000000')
//					new PaletteColor("Blissful-Burgundy",		"A60F42"),
					new PaletteColor("Blueberry",				"235DA7"),
					new PaletteColor("Candy-Apple-Red",			"E61938"),
					new PaletteColor("Electrifyingly-Pink",		"ED628B"),
					new PaletteColor("Go Green",				"1DA038"),
					new PaletteColor("Mediterranean-Blue",		"2DA5BD"),
					new PaletteColor("Mocha-Brown",				"52240A"),
					new PaletteColor("Purple-Rain",				"5A3F82"),
					new PaletteColor("Midnight-Black",			"000000")};
			
			activeDesign.previewImageId = ResourceManager.getId(
					ResourceManager.GROUP_SESSION,
					sessionId + "_item0_prev.png"
				);
			
			designEnvironment.activeDesigns.add(activeDesign);
				
			return designEnvironment;
		}
		
		
		/**
		 * @param Barcode barcode
		 * @return DesignEnvironment
		 */
		public DesignEnvironment createFromBarcode(Barcode barcode, String sessionId)
		{
			DesignEnvironment designEnvironment = new DesignEnvironment();
			designEnvironment.mode = DesignEnvironment.MODE_NEW_ORDER;
			
			OrderItem orderItem = designEnvironment.orderItem;
			orderItem.externalUserId = -1;
			orderItem.externalOrderId = -1;
			orderItem.externalOrderStatus = 0;
			orderItem.externalSystemName = "";
			orderItem.barcode = barcode.barcode;
			orderItem.customerId = barcode.customerId;
			orderItem.processingStagesId = ProcessingStage.STAGE_PENDING_CART_ORDER;
						
			config = barcode.getConfig();
			configItems = config.items;
			designEnvironment.theme = config.theme;
					
			if(configItems==null) return null;
			
			config.items = null;
			orderItem.setConfig(config);
			PaletteColor[] colorPalettes = new PaletteColor[]{};
			if(configItems.length > 1)
			{
				
				colorPalettes = new PaletteColor[]{
//					new PaletteColor("Blissful-Burgundy",		"A60F42"),
					new PaletteColor("Blueberry",				"235DA7"),
					new PaletteColor("Candy-Apple-Red",			"E61938"),
					new PaletteColor("Electrifyingly-Pink",		"ED628B"),
					new PaletteColor("Go Green",				"1DA038"),
					new PaletteColor("Mediterranean-Blue",		"2DA5BD"),
					new PaletteColor("Mocha-Brown",				"52240A"),
					new PaletteColor("Purple-Rain",				"5A3F82"),
					new PaletteColor("Midnight-Black",			"000000")};
		    }
			else
			{
				colorPalettes = new PaletteColor[]{
						new PaletteColor("Midnight-Black",		"000000")
					};
			}
			
			int itemCount = 0;
			for(int item = 0; configItems.length > item; item ++)
			{
				/* @var item ConfigItem */
				ActiveDesign activeDesign = ActiveDesign.createFromBarcodeConfigItem(configItems[item]);
				if(activeDesign == null) return null;
				
				activeDesign.colorPalettes = colorPalettes;
				activeDesign.previewImageId = ResourceManager.getId(
						ResourceManager.GROUP_SESSION, 
						sessionId + "_item" + (itemCount) + "_prev.png");
				designEnvironment.activeDesigns[] = activeDesign;
				itemCount++;
			}
			
			return designEnvironment;
		}
		
		/**
		 * @return DesignEnvironment
		 */
		public void load(String orderId)
		{
		}


		public Boolean save()
		{
			if(mode == DesignEnvironment.MODE_NEW_ORDER)
			{
				return saveAsOrder();
			}
			else if(mode == DesignEnvironment.MODE_EDIT_TEMPLATE)
			{
				return saveAsTemplate();
			}
			
			return false;
		}

		public Boolean saveAsTemplate()
		{
			designDB = Startup.getInstance().db.design;
			
			/* @var activeDesign ActiveDesign */
			ActiveDesign activeDesign = activeDesigns.get(0);
			
			templateId = activeDesign.design.getConfigItem().templateId;
			template = designDB.getDesignTemplateById(templateId);
			if(template == null) return false; 
			
			template.designJSON = activeDesign.design.designJSON;
			if(!designDB.updateDesignTemplate(template)) return false;
			

			Result response = Settings.resourceOpCopy(
					activeDesign.previewImageId,
					template.getPreviewImageId()
			);
			return response.errorCode == Result.CODE_OK;
		}
		
		public Boolean saveAsOrder()		
		{
			orderDB = Startup.getInstance().db.order;				
			if(orderItem.id == -1)
			{
				if(!orderDB.createOrderItem(this.orderItem)) return false;
				
				for(int i = 0; i < activeDesigns.size(); i++)
				{
					/* @var activeDesign ActiveDesign */
					activeDesigns.get(i).design.orderItemId = orderItem.id;
				}
			}
			else
			{
				if(!orderDB.updateOrderItem(orderItem)) return false;
			}

			String srcIds = null;
			int size;
			if(activeDesigns.size() < 3)
			{
				
				for(int i = 0; i < activeDesigns.size(); i++)
				{
					/* @var activeDesign ActiveDesign */
					if(!activeDesigns.get(i).save()) return false;
				
					if(srcIds == null)
					{
						srcIds = activeDesigns.get(i).design.getPreviewImageId();
					}
					else
					{
						srcIds.concat("," + activeDesigns.get(i).design.getPreviewImageId());
					}
				}
				size = 250;				
			}
			else
			{
				for(int i = 0; i < activeDesigns.size(); i++)
				{
					/* @var activeDesign ActiveDesign */
					if(!activeDesigns.get(i).save()) return false;
				
					ResourceId rid = ResourceId.fromId(activeDesigns.get(i).design.getPreviewImageId());
					rid.type = ResourceManager.TYPE_THUMBNAIL;
						
					if(srcIds == null)
					{
						srcIds = rid.getId();
					}
					else
					{
						srcIds.concat("," + rid.getId());
					}
				}
				size = 250;				
			}
			URL search = new URL(Settings.SERVICE_CREATE_COLLAGE +
					"?srcIds="  + srcIds +
					"&destId=" + orderItem.getPreviewImageId() +
					"&width=" + size + "&height=" + size);
			InputStreamReader intput = new InputStreamReader(search.openStream());	
			StringWriter output = new StringWriter(); 
			if (output == null){
				return false;
			}
			
			return true; 
		}
	}
}
