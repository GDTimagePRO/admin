package com.instamp.workstation.data;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;

import com.vaadin.data.util.sqlcontainer.connection.JDBCConnectionPool;


public class SimpleJ2EEConnectionPool implements JDBCConnectionPool {

	private static final long serialVersionUID = -6043287486896707785L;

	private String dataSourceJndiName;

    private DataSource dataSource = null;

    public SimpleJ2EEConnectionPool(DataSource dataSource) {
        this.dataSource = dataSource;
    }

    public SimpleJ2EEConnectionPool(String dataSourceJndiName) {
        this.dataSourceJndiName = dataSourceJndiName;
    }

    public Connection reserveConnection() throws SQLException {
        Connection conn = getDataSource().getConnection();
        conn.setAutoCommit(false);
        try {
            Statement s = conn.createStatement();
            s.execute("SET SESSION sql_mode = 'ANSI'");
            s.close();
        } catch (Exception e) {
            // Failed to set ansi mode; continue
        }
        return conn;
    }

    private DataSource getDataSource() throws SQLException {
        if (dataSource == null) {
            dataSource = lookupDataSource();
        }
        return dataSource;
    }

    private DataSource lookupDataSource() throws SQLException {
        try {
            InitialContext ic = new InitialContext();
            return (DataSource) ic.lookup(dataSourceJndiName);
        } catch (NamingException e) {
            throw new SQLException(
                    "NamingException - Cannot connect to the database. Cause: "
                            + e.getMessage());
        }
    }

    public void releaseConnection(Connection conn) {
        try {
            conn.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void destroy() {
        dataSource = null;
    }
    
}
