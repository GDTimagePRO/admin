import data.ResourceId;
import data.ResourceManager;


public class TestMain {

	/**
	 * @param args
	 */
	public static void main(String[] args)
	{
		//ResourceId rid = new ResourceId(ResourceManager.GROUP_SESSION, "9ec1c2c5dd5b87f7347c04730407f97c149cccbb/1367193325463.jpg", ResourceManager.TYPE_ORIGINAL);
		//ResourceId rid = new ResourceId(ResourceManager.GROUP_SESSION, "9ec1c2c5dd5b87f7347c04730407f97c149cccbb/1375186687598.jpg", ResourceManager.TYPE_ORIGINAL);
		//ResourceId rid = new ResourceId(ResourceManager.GROUP_SESSION, "9ec1c2c5dd5b87f7347c04730407f97c149cccbb/1379206404506.jpg", ResourceManager.TYPE_WEB);
		ResourceId rid = new ResourceId(ResourceManager.GROUP_SESSION, "9ec1c2c5dd5b87f7347c04730407f97c149cccbb/1367112889629.png", ResourceManager.TYPE_WEB);
		//rid.setParams(new String[]{ResourceId.PARAM_LINEAR_TINT + "70421440DA"});
		//rid.setParams(new String[]{ResourceId.PARAM_GRADIENT + "704214FFFFFF"});
		//rid.setParams(new String[]{ResourceId.PARAM_LINEAR_TINT + "70421440DA", ResourceId.PARAM_MIRROR_VERTICAL});
		rid.setParams(new String[]{ResourceId.PARAM_MONOCHROME, ResourceId.PARAM_MIRROR_VERTICAL});
		rid.update();
	}

}
