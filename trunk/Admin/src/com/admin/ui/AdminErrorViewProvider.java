package com.admin.ui;

import views.ErrorView;

import com.vaadin.navigator.View;
import com.vaadin.navigator.ViewProvider;

public class AdminErrorViewProvider implements ViewProvider {

	@Override
	public String getViewName(String viewAndParameters) {
		return viewAndParameters;
	}

	@Override
	public View getView(String viewName) {
		return new ErrorView();
	}
}