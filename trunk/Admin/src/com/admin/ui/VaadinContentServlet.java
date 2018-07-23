package com.admin.ui;

import javax.servlet.annotation.WebServlet;

import com.vaadin.annotations.Theme;
import com.vaadin.annotations.VaadinServletConfiguration;
import com.vaadin.server.VaadinRequest;
import com.vaadin.server.VaadinServlet;
import com.vaadin.ui.Label;
import com.vaadin.ui.UI;

@Theme("admin")
public class VaadinContentServlet extends UI
{
	private static final long serialVersionUID = -8721816880511173256L;

	@WebServlet(value = "/VAADIN/*", asyncSupported = true)
	@VaadinServletConfiguration(productionMode = true, ui = VaadinContentServlet.class)
	public static class Servlet extends VaadinServlet
	{
		private static final long serialVersionUID = -4935471458477757215L;
	}

	@Override
	protected void init(VaadinRequest request)
	{
		setContent(new Label("Root"));
	}
	

	
}
