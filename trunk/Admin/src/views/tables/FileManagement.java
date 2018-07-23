package views.tables;

import java.io.File;
import java.util.ArrayList;
import java.util.Arrays;

import com.vaadin.server.Page;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.GridLayout;
import com.vaadin.ui.Panel;

public class FileManagement extends CustomComponent {

	ArrayList<File> files;
	GridLayout layout;
	//FlexibleOptionGroup fop;
	
	public FileManagement() {
		initObjects();
		initLayout();
	}
	
	private void initObjects() {
		File f = new File("C:\\_genesys_data_\\original\\legacy\\images");
		files = new ArrayList<File>(Arrays.asList(f.listFiles()));
		int pageWidth = Page.getCurrent().getBrowserWindowWidth();
		int numColumns = pageWidth / 200;
		int numRows = (int) Math.ceil(files.size() / (double)numColumns);
		//fop = new FlexibleOptionGroup();
		for (File file : files) {
			//fop.addItem(file);
		}
		layout = new GridLayout(numColumns, numRows);
	}
	
	private void initLayout() {
		Panel p = new Panel();
		/*for (Iterator<FlexibleOptionGroupItemComponent> iter = fop.getItemComponentIterator(); iter.hasNext();) {
			FlexibleOptionGroupItemComponent comp = iter.next();
			Label captionLabel = new Label();
			captionLabel.setIcon(new FileResource((File)comp.getItemId()));
			captionLabel.setCaption(((File)comp.getItemId()).getName());
			captionLabel.setWidth(null);
			captionLabel.setData(comp);
			layout.addComponent(comp);
			layout.addComponent(captionLabel);
		}*/
		layout.setSpacing(true);
		p.setSizeFull();
		p.setContent(layout);
		setCompositionRoot(p);
		setSizeFull();
	}
}
