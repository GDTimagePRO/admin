package graphics.maps;

public interface IMap
{
	public double getRangeXStart();
	public double getRangeXEnd();
	Vector2D transform(double x, double y, Vector2D dest);
}
