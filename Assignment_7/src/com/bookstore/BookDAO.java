package com.bookstore;

import jakarta.servlet.ServletContext;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

/**
 * Handles all database operations for the ebookshop table.
 */
public class BookDAO {

    private static final String BASE_SELECT =
            "SELECT book_id, book_title, book_author, book_price, quantity FROM ebookshop_db";

    public List<Book> findBooks(ServletContext context, String keyword, String stockFilter)
            throws SQLException, ClassNotFoundException {

        List<Book> books = new ArrayList<>();
        List<Object> parameters = new ArrayList<>();

        StringBuilder sql = new StringBuilder(BASE_SELECT);
        boolean hasWhere = false;

        if (keyword != null && !keyword.trim().isEmpty()) {
            sql.append(" WHERE (LOWER(book_title) LIKE ? OR LOWER(book_author) LIKE ?)");
            String pattern = "%" + keyword.trim().toLowerCase() + "%";
            parameters.add(pattern);
            parameters.add(pattern);
            hasWhere = true;
        }

        if ("low".equalsIgnoreCase(stockFilter)) {
            sql.append(hasWhere ? " AND" : " WHERE");
            sql.append(" quantity <= ?");
            parameters.add(Integer.valueOf(5));
        } else if ("available".equalsIgnoreCase(stockFilter)) {
            sql.append(hasWhere ? " AND" : " WHERE");
            sql.append(" quantity > ?");
            parameters.add(Integer.valueOf(0));
        }

        sql.append(" ORDER BY book_id");

        try (Connection connection = DBConnection.getConnection(context);
             PreparedStatement statement = connection.prepareStatement(sql.toString())) {

            bindParameters(statement, parameters);

            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    books.add(mapBook(resultSet));
                }
            }
        }

        return books;
    }

    public Book findById(ServletContext context, int bookId) throws SQLException, ClassNotFoundException {
        String sql = BASE_SELECT + " WHERE book_id = ?";

        try (Connection connection = DBConnection.getConnection(context);
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setInt(1, bookId);

            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return mapBook(resultSet);
                }
            }
        }

        return null;
    }

    public void insert(ServletContext context, Book book) throws SQLException, ClassNotFoundException {
        String sql = "INSERT INTO ebookshop_db (book_title, book_author, book_price, quantity) VALUES (?, ?, ?, ?)";

        try (Connection connection = DBConnection.getConnection(context);
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setString(1, book.getTitle());
            statement.setString(2, book.getAuthor());
            statement.setDouble(3, book.getPrice());
            statement.setInt(4, book.getQuantity());
            statement.executeUpdate();
        }
    }

    public boolean update(ServletContext context, Book book) throws SQLException, ClassNotFoundException {
        String sql =
                "UPDATE ebookshop_db SET book_title = ?, book_author = ?, book_price = ?, quantity = ? WHERE book_id = ?";

        try (Connection connection = DBConnection.getConnection(context);
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setString(1, book.getTitle());
            statement.setString(2, book.getAuthor());
            statement.setDouble(3, book.getPrice());
            statement.setInt(4, book.getQuantity());
            statement.setInt(5, book.getBookId());
            return statement.executeUpdate() > 0;
        }
    }

    public boolean delete(ServletContext context, int bookId) throws SQLException, ClassNotFoundException {
        String sql = "DELETE FROM ebookshop_db WHERE book_id = ?";

        try (Connection connection = DBConnection.getConnection(context);
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setInt(1, bookId);
            return statement.executeUpdate() > 0;
        }
    }

    public InventoryStats getStats(ServletContext context) throws SQLException, ClassNotFoundException {
        String sql =
                "SELECT COUNT(*) AS total_titles, "
                        + "COALESCE(SUM(quantity), 0) AS total_stock, "
                        + "COALESCE(SUM(book_price * quantity), 0) AS total_value, "
                        + "COALESCE(SUM(CASE WHEN quantity <= 5 THEN 1 ELSE 0 END), 0) AS low_stock_titles "
                        + "FROM ebookshop_db";

        try (Connection connection = DBConnection.getConnection(context);
             Statement statement = connection.createStatement();
             ResultSet resultSet = statement.executeQuery(sql)) {

            if (resultSet.next()) {
                return new InventoryStats(
                        resultSet.getInt("total_titles"),
                        resultSet.getInt("total_stock"),
                        resultSet.getDouble("total_value"),
                        resultSet.getInt("low_stock_titles"));
            }
        }

        return new InventoryStats(0, 0, 0.0, 0);
    }

    private Book mapBook(ResultSet resultSet) throws SQLException {
        return new Book(
                resultSet.getInt("book_id"),
                resultSet.getString("book_title"),
                resultSet.getString("book_author"),
                resultSet.getDouble("book_price"),
                resultSet.getInt("quantity"));
    }

    private void bindParameters(PreparedStatement statement, List<Object> parameters) throws SQLException {
        for (int index = 0; index < parameters.size(); index++) {
            Object parameter = parameters.get(index);
            int jdbcIndex = index + 1;

            if (parameter instanceof String) {
                statement.setString(jdbcIndex, (String) parameter);
            } else if (parameter instanceof Integer) {
                statement.setInt(jdbcIndex, ((Integer) parameter).intValue());
            } else {
                statement.setObject(jdbcIndex, parameter);
            }
        }
    }
}
