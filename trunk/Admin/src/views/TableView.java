package views;

import javax.inject.Inject;

import model.User;
import views.tables.BarcodeTable;
import views.tables.ProductCategoryTable;
import views.tables.ProductTable;
import views.tables.TemplateCategoryTable;
import views.tables.TemplateTable;
import views.tables.UserTable;
import views.tables.WrkstTable;

import com.admin.ui.AdminUI;
import com.admin.ui.CurrentUser;
import com.vaadin.cdi.CDIView;
import com.vaadin.navigator.View;
import com.vaadin.navigator.ViewChangeListener.ViewChangeEvent;
import com.vaadin.ui.Alignment;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Label;
import com.vaadin.ui.TabSheet;
import com.vaadin.ui.UI;
import com.vaadin.ui.VerticalLayout;

@CDIView(TableView.NAME)
public class TableView extends CustomComponent implements View {

	public static final String NAME = "tables";
	@Inject private CurrentUser currentUser;
	
	@Override
	public void enter(ViewChangeEvent event) {
		if (CurrentUser.isUserSignedIn()) {
			initObjects();
			initLayout();
		} else {
			currentUser.logoutUser();
		}
	}
	
	private void initObjects() {

	}
	
	private void initLayout() {
		final VerticalLayout _rootLayout = new VerticalLayout();
		
		HorizontalLayout buttons = new HorizontalLayout();
		Label userName = new Label("Welcome " + currentUser.getUserName());
		userName.setStyleName("welcome-label");
		Button logout = new Button("Logout");
		logout.setHeight("27px");
		logout.addClickListener(new ClickListener() {

			@Override
			public void buttonClick(ClickEvent event) {
				currentUser.logoutUser();
			}
			
		});
		buttons.addComponent(userName);
		buttons.addComponent(logout);
		buttons.setComponentAlignment(logout, Alignment.MIDDLE_RIGHT);
		buttons.setStyleName("buttons-right");
		TabSheet tabSheet = new TabSheet();
		setCompositionRoot(_rootLayout);
		_rootLayout.setSizeFull();
		_rootLayout.addComponent(buttons);
		_rootLayout.addComponent(tabSheet);
		tabSheet.setSizeFull();
		_rootLayout.setExpandRatio(tabSheet, 1f);
		this.setSizeFull();
		
		if (currentUser.hasPermission(ProductTable.PERMISSION_ACCESS)) {
			tabSheet.addTab(new ProductTable(currentUser), "Products");
		}
		if (currentUser.hasPermission(TemplateTable.PERMISSION_ACCESS)) {
			tabSheet.addTab(new TemplateTable(currentUser), "Templates");
		}
		if (currentUser.hasPermission(BarcodeTable.PERMISSION_ACCESS)) {
			tabSheet.addTab(new BarcodeTable(currentUser), "Barcodes");
		}
		if (currentUser.hasPermission(TemplateCategoryTable.PERMISSION_ACCESS)) {
			tabSheet.addTab(new TemplateCategoryTable(currentUser), "Template Categories");
		}
		if (currentUser.hasPermission(ProductCategoryTable.PERMISSION_ACCESS)) {
			tabSheet.addTab(new ProductCategoryTable(), "Product Categories");
		}
		if (currentUser.hasPermission(WrkstTable.PERMISSION_ACCESS)) {
			tabSheet.addTab(new WrkstTable(currentUser), "Workstation");
		}
		//tabSheet.addTab(new FileManagement(), "Image Management");
		if (currentUser.hasPermission(UserTable.PERMISSION_ACCESS)) {
			tabSheet.addTab(new UserTable(), "Users");
		}
	}	


}
