package components;

import java.util.Locale;

import org.tepi.filtertable.FilterDecorator;
import org.tepi.filtertable.FilterTable;
import org.tepi.filtertable.numberfilter.NumberFilterPopupConfig;

import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.server.Resource;
import com.vaadin.shared.ui.datefield.Resolution;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.CustomTable;
import com.vaadin.ui.UI;

public class GenericTable extends FilterTable {

	public class TableFilterDecorator implements FilterDecorator {

	    @Override
	    public String getEnumFilterDisplayName(Object propertyId, Object value) {
	        // returning null will output default value
	        return null;
	    }

		@Override
		public Resource getEnumFilterIcon(Object propertyId, Object value) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getBooleanFilterDisplayName(Object propertyId,
				boolean value) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public Resource getBooleanFilterIcon(Object propertyId, boolean value) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public boolean isTextFilterImmediate(Object propertyId) {
			// TODO Auto-generated method stub
			return false;
		}

		@Override
		public int getTextChangeTimeout(Object propertyId) {
			// TODO Auto-generated method stub
			return 0;
		}

		@Override
		public String getFromCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getToCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getSetCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getClearCaption() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public Resolution getDateFieldResolution(Object propertyId) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getDateFormatPattern(Object propertyId) {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public Locale getLocale() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public String getAllItemsVisibleString() {
			// TODO Auto-generated method stub
			return null;
		}

		@Override
		public NumberFilterPopupConfig getNumberFilterPopupConfig() {
			// TODO Auto-generated method stub
			NumberFilterPopupConfig config = new NumberFilterPopupConfig();
			return config;
		}

		@Override
		public boolean usePopupForNumericProperty(Object propertyId) {
			// TODO Auto-generated method stub
			return true;
		}
	}

	
	private JPAContainer container;
	private ClickListener edit;
	
	public GenericTable(JPAContainer container, ClickListener edit)  {
		this.container = container;
		this.edit = edit;
		this.setContainerDataSource(container);
	}
	
	public void init() {
		this.addGeneratedColumn("Edit", new ColumnGenerator() {

			@Override
			public Object generateCell(CustomTable source, final Object itemId,
					Object columnId) {
				Button button = new Button("Edit");
				button.setHeight("24px");
				button.setData(itemId);
				button.setWidth("100%");
				button.addClickListener(edit);
				return button;
			}
		
		});
		
		this.addGeneratedColumn("Delete", new ColumnGenerator() {

			@Override
			public Object generateCell(CustomTable source, final Object itemId,
					Object columnId) {
				Button button = new Button("Delete");
				button.setHeight("24px");
				button.setWidth("100%");
				button.addClickListener(new ClickListener() {

					@Override
					public void buttonClick(ClickEvent event) {
						DeleteDialog dialog = new DeleteDialog(container, itemId);
						UI.getCurrent().addWindow(dialog);
					}
					
				});
				return button;
			}
		
		});
		this.setColumnHeader("Delete","");
		this.setColumnHeader("Edit","");
		this.setColumnWidth("Delete", 140);
		this.setColumnWidth("Edit", 140);
		this.setFilterBarVisible(true);
		//table.setFilterGenerator(new TableFilterGenerator());
		this.setFilterDecorator(new TableFilterDecorator());
		//this.setFilterOnDemand(true);
		this.setSizeFull();
	}
}
