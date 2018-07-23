package workstation.processors;


import java.util.ArrayList;

import com.vaadin.server.StreamResource;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.ComponentContainer;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Label;
import com.vaadin.ui.Link;
import com.vaadin.ui.Panel;
import com.vaadin.ui.ProgressBar;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.themes.BaseTheme;

import concurrency.JobManager.FinishedEvent;
import concurrency.JobManager.IObserverListener;
import concurrency.JobManager.IOnFinishedListener;
import concurrency.JobManager.Observer;

public class DefaultObserverUI extends CustomComponent implements IObserverListener
{
	private static final long serialVersionUID = -1948756342065420019L;

	private final VerticalLayout _rootLayout = new VerticalLayout();
	private final ProgressBar _progressBar = new ProgressBar(0);
	private final Label _progressMessage = new Label();
	private final HorizontalLayout _bottomLayout = new HorizontalLayout();
	private final Link _downloadLink = new Link();
	private final Button _clear = new Button("Clear");
	private final ArrayList<IOnFinishedListener> listeners = new ArrayList<IOnFinishedListener>();

	public DefaultObserverUI(DesignProcessor owner)
	{
		_rootLayout.setWidth("98%");
		_rootLayout.addComponent(new Label(owner.getName()));
		_progressBar.setWidthUndefined();
		_rootLayout.addComponent(_progressBar);
		_progressMessage.setSizeFull();
		_rootLayout.addComponent(_progressMessage);
		_rootLayout.addComponent(_bottomLayout);
		_bottomLayout.addComponent(_downloadLink);
		_bottomLayout.addComponent(_clear);
		_bottomLayout.setWidth("20%");
		
		_downloadLink.setVisible(false);
		_downloadLink.setCaption("Download");
		_downloadLink.setTargetName("_blank");
		_downloadLink.setStyleName(BaseTheme.BUTTON_LINK);
		_clear.setVisible(false);
		_clear.setStyleName(BaseTheme.BUTTON_LINK);
		_clear.addClickListener(new Button.ClickListener() {
		    public void buttonClick(ClickEvent event) {
		    	removeSelf();
		    }
		});
		
		setCompositionRoot(new Panel(_rootLayout));
	}
	
	public void removeSelf() {
		ComponentContainer parent = (ComponentContainer) this.getParent();
    	parent.removeComponent(this);
	}
	

	@Override
	public void onChange(Observer observer)
	{
		_progressBar.setValue(observer.getProgress());
		_progressMessage.setValue(observer.getLastStateMessage());
		if (observer.getProgress() == 1.0f || observer.getErrorState()) {
			FinishedEvent e = new FinishedEvent(observer.getErrorState(), observer.getLastStateMessage());
			for (IOnFinishedListener l : listeners) {
				l.finished(e);
			}
			_clear.setVisible(true);
		}
		StreamResource resource = observer.getResult();
		if (resource != null) {
			_downloadLink.setResource(resource);
			_downloadLink.setVisible(true);
			
		}
	}

	@Override
	public void addOnFinishedListener(IOnFinishedListener listener) {
		listeners.add(listener);
	}
	
	@Override
	public void removeOnFinishedListener(IOnFinishedListener listener) {
		listeners.remove(listener);
	}
}
