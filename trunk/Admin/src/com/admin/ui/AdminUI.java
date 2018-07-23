package com.admin.ui;

import javax.enterprise.event.Observes;
import javax.inject.Inject;
import javax.persistence.PersistenceContext;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;

import model.User;
import views.LoginView;
import views.LoginView.LoginEvent;
import views.TableView;

import com.vaadin.annotations.Push;
import com.vaadin.annotations.Theme;
import com.vaadin.annotations.Title;
import com.vaadin.annotations.VaadinServletConfiguration;
import com.vaadin.cdi.CDIUI;
import com.vaadin.cdi.CDIViewProvider;
import com.vaadin.cdi.access.JaasAccessControl;
import com.vaadin.cdi.server.VaadinCDIServlet;
import com.vaadin.navigator.Navigator;
import com.vaadin.server.SessionDestroyEvent;
import com.vaadin.server.VaadinRequest;
import com.vaadin.shared.communication.PushMode;
import com.vaadin.shared.ui.ui.Transport;
import com.vaadin.ui.Notification;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.UI;

@Theme("admin")
@Title("Genesys - Admin")
@CDIUI("")
//@Push(value = PushMode.MANUAL, transport = Transport.WEBSOCKET)
public class AdminUI extends UI {
	private static final long serialVersionUID = -7624450649290259969L;

	//TODO: Take more advantage of cdi injection
	
	@WebServlet(value = "/*", asyncSupported = true)
	@VaadinServletConfiguration(productionMode = true, ui = AdminUI.class, widgetset = "com.admin.ui.widgetset.AdminWidgetset")
	@PersistenceContext(name = "persistence/em", unitName = "genesys_core")
	public static class Servlet extends VaadinCDIServlet {
		private static final long serialVersionUID = -458403711627178137L;
	}

	@Inject
	private CDIViewProvider viewProvider;
	private Navigator navigator;
	private CurrentUser user;

	@Override
	protected void init(VaadinRequest request) {
		setSizeFull();
		AdminViewDisplay viewDisplay = new AdminViewDisplay();
		viewDisplay.setSizeFull();
		setContent(viewDisplay);
		navigator = new Navigator(this, this);
		navigator.setErrorProvider(new AdminErrorViewProvider());
		navigator.addProvider(viewProvider);
		if (CurrentUser.isUserSignedIn()) {
			navigator.navigateTo(TableView.NAME);
		} else {
			navigator.navigateTo(LoginView.NAME);
		}
	}

	protected void onLogin(@Observes LoginEvent loginEvent) {
		if (CurrentUser.isUserSignedIn()) {
			navigator.navigateTo(TableView.NAME);
		} else {
			try {
				JaasAccessControl.login(loginEvent.getUsername(), loginEvent.getPassword());
				navigator.navigateTo(TableView.NAME);
			} catch (ServletException e) {
				Notification.show("Error logging in", Type.ERROR_MESSAGE);
			}
		}
	}

	public void navigateTo(String viewName) {
		navigator.navigateTo(viewName);
	}
	
	public CurrentUser getCurrentUser() {
		return user;
	}

}
