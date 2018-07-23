package components;

import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Label;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;

public class DeleteDialog extends Window {

	public DeleteDialog(final JPAContainer container, final Object itemId) {
		setModal(true);
		setSizeUndefined();
		VerticalLayout root = new VerticalLayout();
		root.setSizeUndefined();
		root.setSpacing(true);
		root.setMargin(true);
		HorizontalLayout top = new HorizontalLayout();
		HorizontalLayout bottom = new HorizontalLayout();
		//bottom.setSpacing(true);
		bottom.setSizeFull();
		
		Label message = new Label("Are you sure you want to delete this item?");
		Button yes = new Button("Yes");
		Button no = new Button("No");
		Label spacer = new Label();
		top.addComponent(message);
		bottom.addComponent(spacer);
		bottom.addComponent(yes);
		bottom.addComponent(no);
		bottom.setExpandRatio(spacer, 1f);
		root.addComponent(top);
		root.addComponent(bottom);
		setContent(root);
		center();
		setClosable(false);
		setResizable(false);
		
		yes.addClickListener(new ClickListener() {

			@Override
			public void buttonClick(ClickEvent event) {
				container.removeItem(itemId);
				container.commit();
				close();
			}
			
		});
		
		no.addClickListener(new ClickListener() {

			@Override
			public void buttonClick(ClickEvent event) {
				close();
			}
			
		});
	}
}
