package com.example.chatapp;

import javax.servlet.ServletContext;
import javax.servlet.annotation.WebServlet;

import com.example.chatapp.ChatChannel.ChatMessage;
import com.google.gwt.safehtml.shared.SafeHtmlUtils;
import com.vaadin.annotations.PreserveOnRefresh;
import com.vaadin.annotations.Push;
import com.vaadin.annotations.Theme;
import com.vaadin.annotations.VaadinServletConfiguration;
import com.vaadin.event.ShortcutAction.KeyCode;
import com.vaadin.server.VaadinRequest;
import com.vaadin.server.VaadinServlet;
import com.vaadin.shared.ui.label.ContentMode;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Label;
import com.vaadin.ui.Panel;
import com.vaadin.ui.TextField;
import com.vaadin.ui.UI;
import com.vaadin.ui.VerticalLayout;

@Push
@Theme("chatapp")
@SuppressWarnings("serial")
@PreserveOnRefresh
public class ChatAppUI extends UI implements ChatChannel.MessageListener
{
	/* The default  

	 * 
	 */

	//@WebServlet(value = "/*", asyncSupported = true)
	@WebServlet(value = {"/ui/*", "/VAADIN/*"}, asyncSupported = true)
	@VaadinServletConfiguration(productionMode = false, ui = ChatAppUI.class)
	public static class Servlet extends VaadinServlet { }

	final VerticalLayout _messageLog = new VerticalLayout();	
	private String _userName = "";
	
	@Override
	public void onMessage(final ChatMessage message)
	{
		ChatAppUI.this.access(new Runnable()
		{					
			@Override
			public void run()
			{
				String html = SafeHtmlUtils.htmlEscape(message.sender) + " : " + SafeHtmlUtils.htmlEscape(message.text);
				html = html.replace(":)", "<img src=\"../img/happy.jpg\">");
				_messageLog.addComponent(new Label(html,ContentMode.HTML));
			}
		});
	}

	@Override
	public void detach()
	{
		ServletContext context =  VaadinServlet.getCurrent().getServletContext();				
		ChatChannel chatChannel = (ChatChannel)context.getAttribute(ChatChannel.ATTRIBUTE_NAME);
		chatChannel.unregister(this, _userName);

		super.detach();
	}

	@Override
	protected void init(VaadinRequest request)
	{		
		final VerticalLayout rootLayout = new VerticalLayout();
		rootLayout.setMargin(true);
		setContent(rootLayout);
		
		final Panel messageLogPanel = new Panel(_messageLog);
		messageLogPanel.setHeight("500px");
		
		ServletContext context =  VaadinServlet.getCurrent().getServletContext();				
		ChatChannel chatChannel = (ChatChannel)context.getAttribute(ChatChannel.ATTRIBUTE_NAME);
		_userName = chatChannel.register(this);

		rootLayout.addComponent(messageLogPanel);
		
		final TextField chatInput = new TextField();
		chatInput.setWidth("300px");
		
		final Button sendButton = new Button("Send");
		sendButton.setClickShortcut(KeyCode.ENTER);
		sendButton.addClickListener(new Button.ClickListener()
		{
			public void buttonClick(ClickEvent event)
			{
				if(chatInput.getValue().isEmpty()) return;
				ServletContext context =  VaadinServlet.getCurrent().getServletContext();				
				ChatChannel chatChannel = (ChatChannel)context.getAttribute(ChatChannel.ATTRIBUTE_NAME);
				chatChannel.broadcast(new ChatMessage(_userName, chatInput.getValue()));
				chatInput.setValue("");
			}
		});		
		
		final Button logoutButton = new Button("Logout");
		logoutButton.addClickListener(new Button.ClickListener()
		{
			public void buttonClick(ClickEvent event)
			{
				getUI().getSession().close();
			}
		});
		
		rootLayout.addComponent(new HorizontalLayout(chatInput,sendButton, logoutButton));
	}
}