package com.admin.ui;

import java.security.Principal;
import java.util.List;

import javax.annotation.PostConstruct;
import javax.enterprise.inject.Any;
import javax.inject.Inject;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.persistence.EntityManager;
import javax.persistence.TypedQuery;
import javax.servlet.ServletException;

import views.LoginView;
import model.Customer;
import model.Permission;
import model.User;

import com.vaadin.addon.jpacontainer.provider.jndijta.JndiAddresses;
import com.vaadin.cdi.access.JaasAccessControl;
import com.vaadin.navigator.Navigator;
import com.vaadin.server.VaadinService;
import com.vaadin.ui.UI;

public class CurrentUser {

	private User user;
	
	public CurrentUser() {
		
	}
	
	private CurrentUser(User user) {
		this.user = user;
	}
	
	private static User getUser() {
		try {
            InitialContext initialContext = new InitialContext();
            EntityManager lookup = (EntityManager) initialContext.lookup(JndiAddresses.DEFAULTS.getEntityManagerName());
            TypedQuery<User> query = lookup.createNamedQuery("User.GetUser", User.class);
            User u = query.setParameter("username", getPrincipalName()).getSingleResult();
            return u;
        } catch (NamingException ex) {
            throw new RuntimeException(ex);
        }
	}
	
	@PostConstruct
	private void init() {
		this.user = getUser();
	}
	
	public static CurrentUser getCurrentUser() {
		return new CurrentUser(getUser());
	}
	
	public boolean hasPermission(String permissionName) {
		if (isUserInRole("admin")) {
			return true;
		}
		for (Permission p :  user.getAllPermissions()) {
			if (p.getName().equals(permissionName)) {
				return true;
			}
		}
		return false;
	}
	
	protected static String getPrincipalName() {
		Principal principal = JaasAccessControl.getCurrentRequest().getUserPrincipal();
		if (principal != null) {
			return principal.getName();
		}
		return null;
	}
	
	public String getUserName() {
		return user.getUsername();
	}
	
	public boolean isUserInRole(String role) {
		return JaasAccessControl.getCurrentRequest().isUserInRole(role);
	}

	public static boolean isUserSignedIn() {
		Principal principal = JaasAccessControl.getCurrentRequest().getUserPrincipal();
		return principal != null;
	}
	
	public List<Customer> getUserCustomers() {
		return user.getCustomers();
	}
	
	
	public void logoutUser() {
		try {
			JaasAccessControl.getCurrentRequest().logout();
			UI.getCurrent().getSession().close();
			VaadinService.getCurrentRequest().getWrappedSession().invalidate();
			UI.getCurrent().getPage().setLocation("");
		} catch (ServletException e) {
			e.printStackTrace();
		}
	}
}
