package graphics.patterns;

import java.awt.Color;

import graphics.JavaCanvas;
import graphics.maps.IMap;

public interface IPattern
{
    void drawCorner(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor);
    void drawBorder(IMap map, JavaCanvas context, double size, double spacingScale, Color fgColor, Color bgColor);
}
