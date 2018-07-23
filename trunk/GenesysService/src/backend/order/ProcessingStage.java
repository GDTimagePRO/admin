package backend.order;

public class ProcessingStage
{
    public static final int STAGE_PENDING_CANCELED        = 100;
    public static final int STAGE_PENDING_CONFIRMATION    = 200;
    public static final int STAGE_PENDING_CART_ORDER      = 300;
    public static final int STAGE_PENDING_RENDERING       = 350;
    public static final int STAGE_READY                   = 400;
    public static final int STAGE_PRINTED                 = 425;
    public static final int STAGE_SHIPPED                 = 450;
    public static final int STAGE_ARCHIVED                = 500;

    public int id = -1;
    public String keyName;
    public String name;
    public String shortName;
}