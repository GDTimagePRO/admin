package backend.order;

import backend.Order.Config;

public class Barcode
{
    public String barcode = null;
    public Integer customerId = null;
    public Integer dateCreated = null;
    public String configJSON = null;
    public String master = null;
    public Integer dateUsed = 0;

    /**
     * @return Config
     */
    public Config getConfig()
    {
        return Config.fromJSON(configJSON);
    }

    public void setConfig(Config value)
    {
        configJSON = value.toJSON();
    }

    public boolean isMaster()
    {
        return master == "Y";
    }

    public boolean isUsed()
    {
        return (!isMaster()) && (dateUsed != 0);
    }
}