package com.instamp.vaadin.utils;

import java.sql.Connection;
import java.sql.SQLException;
import java.util.List;

import com.vaadin.addon.sqlcontainer.Util;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.sqlcontainer.RowItem;
import com.vaadin.data.util.sqlcontainer.query.FreeformStatementDelegate;
import com.vaadin.data.util.sqlcontainer.query.OrderBy;
import com.vaadin.data.util.sqlcontainer.query.generator.StatementHelper;
import com.vaadin.data.util.sqlcontainer.query.generator.filter.QueryBuilder;

public class GenericFreeformStatementDelegate implements FreeformStatementDelegate
{

	private static final long serialVersionUID = 7951281373634247469L;

	private List<Filter> _filters = null;
	private List<OrderBy> _orderBys = null;

	//-----------------------------------------------------------------------------------------------------------------------------------

	@Override
	public String getQueryString(int offset, int limit) throws UnsupportedOperationException { throw new UnsupportedOperationException(); }

	@Override
	public String getCountQuery() throws UnsupportedOperationException  { throw new UnsupportedOperationException(); }

	@Override
	public String getContainsRowQueryString(Object... keys) throws UnsupportedOperationException { throw new UnsupportedOperationException(); }

	//-----------------------------------------------------------------------------------------------------------------------------------

	@Override
	public void setFilters(List<Filter> filters) throws UnsupportedOperationException
	{
		_filters = filters;
	}

	@Override
	public void setOrderBy(List<OrderBy> orderBys) throws UnsupportedOperationException
	{
		_orderBys = orderBys;
	}

	//-----------------------------------------------------------------------------------------------------------------------------------

	@Override
	public int storeRow(Connection conn, RowItem row) throws UnsupportedOperationException, SQLException
	{
		throw new UnsupportedOperationException();
	}

	@Override
	public boolean removeRow(Connection conn, RowItem row) throws UnsupportedOperationException, SQLException
	{
		throw new UnsupportedOperationException();
	}

	//-----------------------------------------------------------------------------------------------------------------------------------

	@Override
	public StatementHelper getQueryStatement(int offset, int limit) throws UnsupportedOperationException
	{
		StatementHelper sh = new StatementHelper();
        StringBuffer query = new StringBuffer("SELECT * from workstation_table ");
        if (_filters != null) {
            query.append(QueryBuilder.getWhereStringForFilters(
                    _filters, sh));
        }
        query.append(getOrderByString());
        if (offset != 0 || limit != 0) {
            query.append(" LIMIT ").append(limit);
            query.append(" OFFSET ").append(offset);
        }
        sh.setQueryString(query.toString());
        return sh;		
	}
	
	private String getOrderByString() {
        StringBuffer orderBuffer = new StringBuffer("");
        if (_orderBys != null && !_orderBys.isEmpty()) {
            orderBuffer.append(" ORDER BY ");
            OrderBy lastOrderBy = _orderBys.get(_orderBys.size() - 1);
            for (OrderBy orderBy : _orderBys) {
                orderBuffer.append(Util.escapeSQL(orderBy.getColumn()));
                if (orderBy.isAscending()) {
                    orderBuffer.append(" ASC");
                } else {
                    orderBuffer.append(" DESC");
                }
                if (orderBy != lastOrderBy) {
                    orderBuffer.append(", ");
                }
            }
        }
        return orderBuffer.toString();
    }
	
	@Override
	public StatementHelper getCountStatement() throws UnsupportedOperationException
	{		
		StatementHelper sh = new StatementHelper();
        StringBuffer query = new StringBuffer("SELECT COUNT(*) FROM workstation_table ");
        if (_filters != null) {
            query.append(QueryBuilder.getWhereStringForFilters(
                    _filters, sh));
        }
        sh.setQueryString(query.toString());
        return sh;
	}

	@Override
	public StatementHelper getContainsRowQueryStatement(Object... keys) throws UnsupportedOperationException
	{
		StatementHelper sh = new StatementHelper();
        StringBuffer query = new StringBuffer(
                "SELECT * FROM workstation_table WHERE d_id = ?");
        sh.addParameterValue(keys[0]);
        sh.setQueryString(query.toString());
        return sh;
	}

}




























