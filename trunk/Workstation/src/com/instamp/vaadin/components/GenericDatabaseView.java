package com.instamp.vaadin.components;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.sql.SQLException;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Iterator;
import java.util.Set;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;

import com.instamp.vaadin.utils.GenericFreeformStatementDelegate;
import com.instamp.workstation.Application;
import com.instamp.workstation.data.GenesysDB;
import com.instamp.workstation.data.GenesysDB.DesignJson;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.Item;
import com.vaadin.data.Property;
import com.vaadin.data.Property.ValueChangeEvent;
import com.vaadin.data.util.sqlcontainer.RowId;
import com.vaadin.data.util.sqlcontainer.SQLContainer;
import com.vaadin.data.util.sqlcontainer.connection.JDBCConnectionPool;
import com.vaadin.data.util.sqlcontainer.query.FreeformQuery;
import com.vaadin.data.util.sqlcontainer.query.OrderBy;
import com.vaadin.event.ShortcutAction.KeyCode;
import com.vaadin.event.ShortcutListener;
import com.vaadin.ui.CheckBox;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.Link;
import com.vaadin.ui.Table;
import com.vaadin.ui.Table.ColumnGenerator;
import com.vaadin.server.ExternalResource;

public final class GenericDatabaseView extends CustomComponent
{
	public static final String COLUMN_ID_SELECTED = "selected";
	public static final String COLUMN_ID_VIEWIMAGE = "viewimage";
	public static final String COLUMN_ID_VIEWSVG = "viewsvg";
	private static final long serialVersionUID = 4721801540000059431L;

	private final Table _table = new Table("Formatted Table") {
	    @Override
	    protected String formatPropertyValue(Object rowId,
	            Object colId, Property property) {
	        // Format by property type
	        if (property.getType() == Long.class && colId == "oi_external_order_id") {
	            String num = String.format("%d", property.getValue());
	            if (num.length() >= 4) {
	            	num = num.substring(0, num.length() - 3) + "-" + num.substring(num.length() - 3, num.length());
	            }
	            return num;
	        }

	        return super.formatPropertyValue(rowId, colId, property);
	    }
	};
	public Table getTable() { return _table; }
	
	private final SQLContainer _sqlContainer;
	private final FreeformQuery _freeformQuery;
	
	private final Set<Object> _selectedIds = new HashSet<Object>();
	
	public Object[][] getSelected()
	{
		//Object[] _temp = new Object[_selectedIds.size()];
		Object[][] result = new Object[_selectedIds.size()][];
		Iterator<Object> itr = _selectedIds.iterator();
		int i = 0;
		
		_table.getItemIds();
		while(itr.hasNext()) 
			result[i++] = ((RowId)itr.next()).getId();

		return result;
	}
	
	public void clearSelected()
	{
		
		_selectedIds.clear();
		_table.refreshRowCache();
	}
	
	public void selectAll()
	{
		_selectedIds.addAll(_table.getItemIds());
		_table.refreshRowCache();
	}
	
	public void setFilters(Collection<Filter> filters)
	{
		_sqlContainer.removeAllContainerFilters();
		if(filters == null) return;
		for(Filter f : filters)
		{
			_sqlContainer.addContainerFilter(f);			
		}
		//_table.refreshRowCache();
	}

	public void setFilters(Filter[] filters)
	{
		_sqlContainer.removeAllContainerFilters();
		if(filters == null) return;
		for(int i=0; i< filters.length; i++)
		{
			_sqlContainer.addContainerFilter(filters[i]);			
		}
		//_table.refreshRowCache();
	}

	public GenericDatabaseView(JDBCConnectionPool connectionPool, String[] fieldIds, String[] fieldDeclarations, String[] keyFields, String fromSQL, String whereSQL) throws SQLException
	{
		String temp = null;
		String temp2 = null;
		final String genesysURL;
		final String apiURL;
		setCompositionRoot(_table);		
		_table.setSelectable(true);
		_table.setSizeFull();
		
		_freeformQuery = new FreeformQuery("SELECT * FROM workstation_table", connectionPool, keyFields);
		_freeformQuery.setDelegate(new  GenericFreeformStatementDelegate());
		_sqlContainer = new SQLContainer(_freeformQuery);
		_sqlContainer.setPageLength(500);
		
		_table.setContainerDataSource(_sqlContainer);
		_table.sort();
		
		
		
		
		Context context;
		try {
			context = new InitialContext();
			temp = (String) context.lookup(Application.GenesysURL);
			temp2 = (String) context.lookup(Application.APIURL);
		} catch (NamingException e) {
			e.printStackTrace();
		}
		genesysURL = temp;
		apiURL = temp2;
		
		
		
		_table.addGeneratedColumn(COLUMN_ID_SELECTED, new ColumnGenerator() {

			private static final long serialVersionUID = -113947286191575492L;

			public Object generateCell(Table source, final Object itemId, Object columnId)
			{
				boolean selected = _selectedIds.contains(itemId);
				final CheckBox checkBox = new CheckBox("", selected);
				
				checkBox.setImmediate(true);
				
				checkBox.addValueChangeListener(new Property.ValueChangeListener() {						

					private static final long serialVersionUID = -5538217005789049929L;

					public void valueChange(ValueChangeEvent event)
					{
						if(checkBox.getValue())
						{
							_selectedIds.add(itemId);
						}
						else
						{
							_selectedIds.remove(itemId);
						}
					}
				});
				
				return checkBox;
			}
		});
		
		_table.addGeneratedColumn(COLUMN_ID_VIEWIMAGE, new ColumnGenerator() {
			public Object generateCell(Table source, Object itemId,
					Object columnId) {
				final Link link = new Link("Get Image", new ExternalResource(genesysURL + "GetImage?id=designs/" + itemId + "_hd.png"));
				link.setTargetName("_blank");
				return link;
			}
		});
		
		_table.addGeneratedColumn(COLUMN_ID_VIEWSVG, new ColumnGenerator() {
			public Object generateCell(Table source, Object itemId,
					Object columnId) {
				Link link = new Link("Get SVG", new ExternalResource(apiURL + "render_image.php?designId=" + itemId + "&type=svg&dpi=300&name=" + itemId + ".svg"));
				link.setTargetName("_blank");
				return link;
			}
		});
		
		_table.addShortcutListener(new ShortcutListener("Checkboxes", KeyCode.SPACEBAR,
                new int[0]) {
            @Override
            public void handleAction(Object sender, Object target) {
            	if (_selectedIds.contains(_table.getValue())) {
					_selectedIds.remove(_table.getValue());
				} else {
					_selectedIds.add(_table.getValue());
				}
				
				_table.refreshRowCache();
            }
        });
		
	}
}
