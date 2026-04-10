package com.bookstore;

import jakarta.servlet.ServletContext;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Database utility that reads MySQL configuration from web.xml context params.
 */
public final class DBConnection {

    private static final String DEFAULT_DB_URL =
            "jdbc:mysql://localhost:3306/ebookshop_db?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=Asia/Kolkata";
    private static final String DEFAULT_DB_USER = "root";
    private static final String DEFAULT_DB_PASSWORD = "";
    private static final String DEFAULT_DRIVER = "com.mysql.cj.jdbc.Driver";

    private static volatile boolean driverLoaded;

    private DBConnection() {
    }

    public static Connection getConnection(ServletContext context) throws SQLException, ClassNotFoundException {
        String driver = getConfig(context, "dbDriver", DEFAULT_DRIVER);
        String url = getConfig(context, "dbUrl", DEFAULT_DB_URL);
        String user = getConfig(context, "dbUser", DEFAULT_DB_USER);
        String password = getConfig(context, "dbPassword", DEFAULT_DB_PASSWORD);

        loadDriver(driver);
        return DriverManager.getConnection(url, user, password);
    }

    private static String getConfig(ServletContext context, String paramName, String defaultValue) {
        if (context == null) {
            return defaultValue;
        }

        String value = context.getInitParameter(paramName);
        if (value == null || value.trim().isEmpty()) {
            return defaultValue;
        }

        return value.trim();
    }

    private static synchronized void loadDriver(String driver) throws ClassNotFoundException {
        if (!driverLoaded) {
            Class.forName(driver);
            driverLoaded = true;
        }
    }
}
