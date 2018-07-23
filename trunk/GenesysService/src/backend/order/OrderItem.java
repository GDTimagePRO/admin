package backend.order;

import backend.Order.Config;
import backend.ResourceManager;

public class OrderItem
{
    public int id = -1;
    public Integer customerId = null;
    public String barcode = null;
    public Integer processingStagesId = null;
    public String creationDate = null;
    public String configJSON = null;
    public Integer externalOrderId = null;
    public Integer externalOrderStatus = null;
    public Integer externalUserId = null;
    public String externalSystemName = null;


    public static String previewImageId(int orderItemId)
    {
        return ResourceManager.GROUP_ORDER_ITEMS + "/" + orderItemId + "_prev.png";
    }

    public String getPreviewImageId() { return previewImageId(id); }


    public Config getConfig()
    {
        return Config.fromJSON(configJSON);
    }

    public void setConfig(Config value)
    {
        configJSON = value.toJSON();
    }
}