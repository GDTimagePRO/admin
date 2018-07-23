package backend.order;

public class Product
{
	public static final int TYPE_ID_CIRCLE                = 1;
	public static final int TYPE_ID_RECTANGLE             = 2;

	public static final String COLOR_MODEL_1_BIT          = "1_BIT";
	public static final String COLOR_MODEL_24_BIT         = "24_BIT";

    public int id = -1;
    public String code = null;
    public Integer width = null;
    public Integer height = null;
    public String longName = null;
    public Integer categoryId = null;
    public Boolean allowGraphics = null;
    public Integer shapeId = null;
    public Integer frameWidth = null;
    public Integer frameHeight = null;
    public Integer productTypeId = null;
    public String colorModel = null;
    public String configJSON = null;
}