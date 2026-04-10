import java.io.*;
import java.sql.*;
import jakarta.servlet.*;
import jakarta.servlet.http.*;
import jakarta.servlet.annotation.WebServlet;

@WebServlet("/books")
public class BookServlet extends HttpServlet {
    
    // Database connection parameters
    private static final String DB_URL = "jdbc:mysql://localhost:3306/ebookshop";
    private static final String DB_USER = "root";
    private static final String DB_PASSWORD = ""; // Change this to your MySQL password
    
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        response.setContentType("text/html");
        PrintWriter out = response.getWriter();
        
        // HTML page header
        out.println("<!DOCTYPE html>");
        out.println("<html>");
        out.println("<head>");
        out.println("<title>E-Bookshop - Book List</title>");
        out.println("<style>");
        out.println("body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }");
        out.println("h1 { color: #333; text-align: center; }");
        out.println("table { width: 100%; border-collapse: collapse; background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }");
        out.println("th { background-color: #4CAF50; color: white; padding: 15px; text-align: left; }");
        out.println("td { padding: 12px 15px; border-bottom: 1px solid #ddd; }");
        out.println("tr:hover { background-color: #f1f1f1; }");
        out.println(".container { max-width: 1200px; margin: 0 auto; }");
        out.println(".actions { text-align: center; margin: 20px 0; }");
        out.println(".btn { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; text-decoration: none; display: inline-block; }");
        out.println(".btn:hover { background-color: #45a049; }");
        out.println(".btn-danger { background-color: #f44336; }");
        out.println(".btn-danger:hover { background-color: #da190b; }");
        out.println(".btn-warning { background-color: #ff9800; }");
        out.println(".btn-warning:hover { background-color: #e68900; }");
        out.println(".form-container { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }");
        out.println("input[type='text'], input[type='number'] { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }");
        out.println("label { display: block; margin-top: 10px; font-weight: bold; }");
        out.println(".message { padding: 10px; margin: 10px 0; border-radius: 4px; }");
        out.println(".success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }");
        out.println(".error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }");
        out.println("</style>");
        out.println("</head>");
        out.println("<body>");
        out.println("<div class='container'>");
        out.println("<h1>📚 E-Bookshop Management System</h1>");
        
        // Check for action parameter
        String action = request.getParameter("action");
        String message = request.getParameter("message");
        String messageType = request.getParameter("type");
        
        // Display message if any
        if (message != null && !message.isEmpty()) {
            String cssClass = "success".equals(messageType) ? "success" : "error";
            out.println("<div class='message " + cssClass + "'>" + message + "</div>");
        }
        
        // Action buttons
        out.println("<div class='actions'>");
        out.println("<a href='?action=add' class='btn'>➕ Add New Book</a>");
        out.println("<a href='?action=list' class='btn'>📋 View All Books</a>");
        out.println("<a href='/' class='btn btn-warning'>🏠 Home</a>");
        out.println("</div>");
        
        // Handle different actions
        if ("add".equals(action)) {
            showAddForm(out);
        } else if ("edit".equals(action)) {
            String bookId = request.getParameter("id");
            showEditForm(out, bookId);
        } else if ("delete".equals(action)) {
            String bookId = request.getParameter("id");
            showDeleteConfirmation(out, bookId);
        } else {
            // Default: show book list
            showBookList(out);
        }
            
        out.println("</div>");
        out.println("</body>");
        out.println("</html>");
    }
    
    private void showBookList(PrintWriter out) {
        Connection conn = null;
        Statement stmt = null;
        ResultSet rs = null;
        
        try {
            // Load MySQL JDBC Driver
            Class.forName("com.mysql.cj.jdbc.Driver");
            
            // Establish connection
            conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD);
            
            // Create statement and execute query
            stmt = conn.createStatement();
            String sql = "SELECT book_id, book_title, book_author, book_price, quantity FROM books";
            rs = stmt.executeQuery(sql);
            
            // Create HTML table
            out.println("<table>");
            out.println("<tr>");
            out.println("<th>Book ID</th>");
            out.println("<th>Title</th>");
            out.println("<th>Author</th>");
            out.println("<th>Price (₹)</th>");
            out.println("<th>Quantity</th>");
            out.println("<th>Actions</th>");
            out.println("</tr>");
            
            // Display each book record
            while (rs.next()) {
                int bookId = rs.getInt("book_id");
                String title = rs.getString("book_title");
                String author = rs.getString("book_author");
                double price = rs.getDouble("book_price");
                int quantity = rs.getInt("quantity");
                
                out.println("<tr>");
                out.println("<td>" + bookId + "</td>");
                out.println("<td>" + title + "</td>");
                out.println("<td>" + author + "</td>");
                out.println("<td>₹" + price + "</td>");
                out.println("<td>" + quantity + "</td>");
                out.println("<td>");
                out.println("<a href='?action=edit&id=" + bookId + "' class='btn btn-warning'>✏️ Edit</a>");
                out.println("<a href='?action=delete&id=" + bookId + "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this book?\")'>🗑️ Delete</a>");
                out.println("</td>");
                out.println("</tr>");
            }
            
            out.println("</table>");
            
        } catch (ClassNotFoundException e) {
            out.println("<div class='message error'>Error: MySQL JDBC Driver not found!</div>");
            out.println("<p>" + e.getMessage() + "</p>");
        } catch (SQLException e) {
            out.println("<div class='message error'>Database Error!</div>");
            out.println("<p>" + e.getMessage() + "</p>");
        } finally {
            // Close resources
            try {
                if (rs != null) rs.close();
                if (stmt != null) stmt.close();
                if (conn != null) conn.close();
            } catch (SQLException e) {
                out.println("<div class='message error'>Error closing connection: " + e.getMessage() + "</div>");
            }
        }
    }
    
    private void showAddForm(PrintWriter out) {
        out.println("<div class='form-container'>");
        out.println("<h2>➕ Add New Book</h2>");
        out.println("<form method='post' action='books'>");
        out.println("<input type='hidden' name='action' value='add'>");
        out.println("<label for='title'>Book Title:</label>");
        out.println("<input type='text' id='title' name='title' required>");
        out.println("<label for='author'>Author:</label>");
        out.println("<input type='text' id='author' name='author' required>");
        out.println("<label for='price'>Price (₹):</label>");
        out.println("<input type='number' id='price' name='price' step='0.01' min='0' required>");
        out.println("<label for='quantity'>Quantity:</label>");
        out.println("<input type='number' id='quantity' name='quantity' min='0' required>");
        out.println("<br><br>");
        out.println("<button type='submit' class='btn'>Add Book</button>");
        out.println("<a href='?action=list' class='btn btn-warning'>Cancel</a>");
        out.println("</form>");
        out.println("</div>");
    }
    
    private void showEditForm(PrintWriter out, String bookId) {
        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet rs = null;
        
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD);
            
            String sql = "SELECT * FROM books WHERE book_id = ?";
            stmt = conn.prepareStatement(sql);
            stmt.setInt(1, Integer.parseInt(bookId));
            rs = stmt.executeQuery();
            
            if (rs.next()) {
                out.println("<div class='form-container'>");
                out.println("<h2>✏️ Edit Book</h2>");
                out.println("<form method='post' action='books'>");
                out.println("<input type='hidden' name='action' value='update'>");
                out.println("<input type='hidden' name='id' value='" + rs.getInt("book_id") + "'>");
                out.println("<label for='title'>Book Title:</label>");
                out.println("<input type='text' id='title' name='title' value='" + rs.getString("book_title") + "' required>");
                out.println("<label for='author'>Author:</label>");
                out.println("<input type='text' id='author' name='author' value='" + rs.getString("book_author") + "' required>");
                out.println("<label for='price'>Price (₹):</label>");
                out.println("<input type='number' id='price' name='price' step='0.01' min='0' value='" + rs.getDouble("book_price") + "' required>");
                out.println("<label for='quantity'>Quantity:</label>");
                out.println("<input type='number' id='quantity' name='quantity' min='0' value='" + rs.getInt("quantity") + "' required>");
                out.println("<br><br>");
                out.println("<button type='submit' class='btn'>Update Book</button>");
                out.println("<a href='?action=list' class='btn btn-warning'>Cancel</a>");
                out.println("</form>");
                out.println("</div>");
            } else {
                out.println("<div class='message error'>Book not found!</div>");
            }
            
        } catch (Exception e) {
            out.println("<div class='message error'>Error: " + e.getMessage() + "</div>");
        } finally {
            try {
                if (rs != null) rs.close();
                if (stmt != null) stmt.close();
                if (conn != null) conn.close();
            } catch (SQLException e) {}
        }
    }
    
    private void showDeleteConfirmation(PrintWriter out, String bookId) {
        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet rs = null;
        
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD);
            
            String sql = "SELECT * FROM books WHERE book_id = ?";
            stmt = conn.prepareStatement(sql);
            stmt.setInt(1, Integer.parseInt(bookId));
            rs = stmt.executeQuery();
            
            if (rs.next()) {
                out.println("<div class='form-container'>");
                out.println("<h2>🗑️ Delete Book</h2>");
                out.println("<p>Are you sure you want to delete this book?</p>");
                out.println("<table style='margin: 20px 0;'>");
                out.println("<tr><td><strong>Title:</strong></td><td>" + rs.getString("book_title") + "</td></tr>");
                out.println("<tr><td><strong>Author:</strong></td><td>" + rs.getString("book_author") + "</td></tr>");
                out.println("<tr><td><strong>Price:</strong></td><td>₹" + rs.getDouble("book_price") + "</td></tr>");
                out.println("<tr><td><strong>Quantity:</strong></td><td>" + rs.getInt("quantity") + "</td></tr>");
                out.println("</table>");
                out.println("<form method='post' action='books' style='display: inline;'>");
                out.println("<input type='hidden' name='action' value='delete'>");
                out.println("<input type='hidden' name='id' value='" + rs.getInt("book_id") + "'>");
                out.println("<button type='submit' class='btn btn-danger'>Yes, Delete</button>");
                out.println("</form>");
                out.println("<a href='?action=list' class='btn'>Cancel</a>");
                out.println("</div>");
            } else {
                out.println("<div class='message error'>Book not found!</div>");
            }
            
        } catch (Exception e) {
            out.println("<div class='message error'>Error: " + e.getMessage() + "</div>");
        } finally {
            try {
                if (rs != null) rs.close();
                if (stmt != null) stmt.close();
                if (conn != null) conn.close();
            } catch (SQLException e) {}
        }
    }
    
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        String action = request.getParameter("action");
        String message = "";
        String messageType = "success";
        
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            
            if ("add".equals(action)) {
                message = addBook(request);
            } else if ("update".equals(action)) {
                message = updateBook(request);
            } else if ("delete".equals(action)) {
                message = deleteBook(request);
            } else {
                message = "Invalid action!";
                messageType = "error";
            }
            
        } catch (Exception e) {
            message = "Error: " + e.getMessage();
            messageType = "error";
        }
        
        // Redirect back with message
        response.sendRedirect("books?action=list&message=" + java.net.URLEncoder.encode(message, "UTF-8") + "&type=" + messageType);
    }
    
    private String addBook(HttpServletRequest request) throws SQLException {
        Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD);
        PreparedStatement stmt = null;
        
        try {
            String sql = "INSERT INTO books (book_title, book_author, book_price, quantity) VALUES (?, ?, ?, ?)";
            stmt = conn.prepareStatement(sql);
            stmt.setString(1, request.getParameter("title"));
            stmt.setString(2, request.getParameter("author"));
            stmt.setDouble(3, Double.parseDouble(request.getParameter("price")));
            stmt.setInt(4, Integer.parseInt(request.getParameter("quantity")));
            
            int rowsAffected = stmt.executeUpdate();
            return rowsAffected > 0 ? "Book added successfully!" : "Failed to add book.";
            
        } finally {
            if (stmt != null) stmt.close();
            if (conn != null) conn.close();
        }
    }
    
    private String updateBook(HttpServletRequest request) throws SQLException {
        Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD);
        PreparedStatement stmt = null;
        
        try {
            String sql = "UPDATE books SET book_title=?, book_author=?, book_price=?, quantity=? WHERE book_id=?";
            stmt = conn.prepareStatement(sql);
            stmt.setString(1, request.getParameter("title"));
            stmt.setString(2, request.getParameter("author"));
            stmt.setDouble(3, Double.parseDouble(request.getParameter("price")));
            stmt.setInt(4, Integer.parseInt(request.getParameter("quantity")));
            stmt.setInt(5, Integer.parseInt(request.getParameter("id")));
            
            int rowsAffected = stmt.executeUpdate();
            return rowsAffected > 0 ? "Book updated successfully!" : "Book not found or no changes made.";
            
        } finally {
            if (stmt != null) stmt.close();
            if (conn != null) conn.close();
        }
    }
    
    private String deleteBook(HttpServletRequest request) throws SQLException {
        Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD);
        PreparedStatement stmt = null;
        
        try {
            String sql = "DELETE FROM books WHERE book_id=?";
            stmt = conn.prepareStatement(sql);
            stmt.setInt(1, Integer.parseInt(request.getParameter("id")));
            
            int rowsAffected = stmt.executeUpdate();
            return rowsAffected > 0 ? "Book deleted successfully!" : "Book not found.";
            
        } finally {
            if (stmt != null) stmt.close();
            if (conn != null) conn.close();
        }
    }
}
