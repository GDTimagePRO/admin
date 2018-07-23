package graphics.drawables;

import graphics.SceneContext;

public interface IDrawableListener
{
	public void onBeforeDraw(IDrawable sender, SceneContext params);
}
