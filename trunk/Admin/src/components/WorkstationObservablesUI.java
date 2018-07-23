package components;

import com.vaadin.ui.Component;
import com.vaadin.ui.Label;
import com.vaadin.ui.Panel;
import com.vaadin.ui.UI;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.themes.ValoTheme;

public class WorkstationObservablesUI extends Window {
	
	public WorkstationObservablesUI() {
		init();
	}
	
	private VerticalLayout observables;
	
	private void init() {
		this.setModal(true);
		this.center();
		this.setWidth("95%");
		this.setHeight("95%");
		this.setContent(createLayout());
		this.addCloseListener(new CloseListener() {

			@Override
			public void windowClose(CloseEvent e) {
				UI.getCurrent().setPollInterval(-1);
			}
			
		});
	}
	
	protected Component createLayout() {
		VerticalLayout root = new VerticalLayout();
		observables = new VerticalLayout();
		observables.setWidth("100%");
		observables.setHeightUndefined();
		Panel observablePanel = new Panel();
		observablePanel.setWidth("100%");
		observablePanel.setHeight("100%");
		observablePanel.setContent(observables);
		Label title = new Label("Jobs");
		title.setStyleName(ValoTheme.LABEL_H1);
		root.addComponent(title);
		root.addComponent(observables);
		root.setExpandRatio(observables, 1.0f);
		root.setSpacing(true);
		root.setWidth("100%");
		root.setHeight("100%");
		return root;
	}
	
	public void addObservable(Component c) {
		observables.addComponent(c);
	}
	
	public void show() {
		UI.getCurrent().setPollInterval(2000);
		UI.getCurrent().addWindow(this);
	}
}
