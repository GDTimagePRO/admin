package com.example.chatapp;

import java.io.Serializable;
import java.util.LinkedList;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import javax.servlet.ServletContextEvent;
import javax.servlet.ServletContextListener;
import javax.servlet.annotation.WebListener;

@SuppressWarnings("serial")
public class ChatChannel implements Serializable
{
	public static final String ATTRIBUTE_NAME = "ChatChannel"; 
	static final ExecutorService executorService = Executors.newSingleThreadExecutor();

	@WebListener
	public static class ChatChannelWebListener implements ServletContextListener
	{

		@Override
		public void contextInitialized(ServletContextEvent sce)
		{
			sce.getServletContext().setAttribute(ATTRIBUTE_NAME, new ChatChannel());
		}

		@Override
		public void contextDestroyed(ServletContextEvent arg0)
		{
		}
	}

	public static final class ChatMessage implements Serializable
	{

		public final String sender;
		public final String text;

		public ChatMessage(String sender, String text)
		{
			this.sender = sender;
			this.text = text;
		}
	}

	public interface MessageListener
	{
		void onMessage(ChatMessage message);
	}

	private int _subscriberIdCount = 0;
	private final LinkedList<MessageListener> _subscribers = new LinkedList<MessageListener>();

	public synchronized String register(MessageListener listener)
	{
		String userName = "User_" + _subscriberIdCount++; 
		broadcast(new ChatMessage("SYSTEM", userName + " has joined."));
		_subscribers.add(listener);
		return userName;
	}

	public synchronized void unregister(MessageListener listener, String userName)
	{
		_subscribers.remove(listener);
		this.broadcast(new ChatMessage("SYSTEM", userName + " has left"));
	}

	public synchronized void broadcast(final ChatMessage message)
	{
		for (final MessageListener subscriber : _subscribers)
		{
			executorService.execute(new Runnable()
			{
				@Override
				public void run()
				{
					subscriber.onMessage(message);
				}
			});
		}
	}
}