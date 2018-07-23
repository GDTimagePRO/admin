package components;

import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.Image;
import com.vaadin.ui.Panel;

public class ImageComponent extends CustomComponent {

	private Image image;
	private boolean selected = false;
	private String caption = null;
	Panel panel = new Panel();
	
	public boolean isSelect() {
		return selected;
	}
	
	public void setSelected() {
		setSelected(true);
	}
	
	public void setSelected(boolean state) {
		selected = state;
	}
	
	public ImageComponent(Image i) {
		this.image = i;
		i.setCaption(null);
		createLayout();
	}
	
	public ImageComponent(Image i, String caption) {
		this.image = i;
		i.setCaption(null);
		this.caption = caption;
		createLayout();
	}
	
	private void createLayout() {
		
		panel.setSizeUndefined();
		setCompositionRoot(panel);
	}
	
	
}
