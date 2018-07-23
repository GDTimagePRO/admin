package com.instamp.workstation;

import javax.servlet.annotation.WebServlet;

import com.vaadin.annotations.Theme;
import com.vaadin.annotations.VaadinServletConfiguration;
import com.vaadin.server.VaadinRequest;
import com.vaadin.server.VaadinServlet;
import com.vaadin.ui.Label;
import com.vaadin.ui.UI;

@Theme("workstation")
public class VaadinContentServlet extends UI
{
	
	@WebServlet(value = "/VAADIN/*", asyncSupported = true)
	@VaadinServletConfiguration(productionMode = true, ui = VaadinContentServlet.class)
	public static class Servlet extends VaadinServlet
	{
		private static final long serialVersionUID = 256702570235481534L;
	}

	@Override
	protected void init(VaadinRequest request)
	{
		setContent(new Label("Root"));
	}
	

	
}
