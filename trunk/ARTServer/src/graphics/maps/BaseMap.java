package graphics.maps;

public abstract class BaseMap implements IMap
{
	protected double[] _range = new double[2];
	
	@Override
	public final double getRangeXStart() { return _range[0]; }

	@Override
	public final double getRangeXEnd() { return _range[1]; }

}
