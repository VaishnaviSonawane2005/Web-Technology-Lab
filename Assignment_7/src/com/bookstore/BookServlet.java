package com.bookstore;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.io.PrintWriter;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.sql.SQLException;
import java.util.List;
import java.util.Locale;

@WebServlet("/books")
public class BookServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    private final BookDAO bookDAO = new BookDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        String action = text(request.getParameter("action"));
        Book editBook = null;

        if ("edit".equalsIgnoreCase(action)) {
            try {
                editBook = bookDAO.findById(getServletContext(), parseBookId(request));
                if (editBook == null) {
                    response.sendRedirect("books?error=" + encode("Book record not found for editing."));
                    return;
                }
            } catch (SQLException e) {
                response.sendRedirect("books?error=" + encode("Unable to load book: " + e.getMessage()));
                return;
            } catch (ClassNotFoundException e) {
                renderFatalError(response, "Driver Error",
                        "MySQL JDBC driver not found. Add Connector/J to Tomcat or WEB-INF/lib.");
                return;
            } catch (IllegalArgumentException e) {
                response.sendRedirect("books?error=" + encode(e.getMessage()));
                return;
            }
        }

        renderDashboard(request, response, editBook);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        String action = text(request.getParameter("action"));

        try {
            if ("add".equalsIgnoreCase(action)) {
                bookDAO.insert(getServletContext(), parseBook(request, false));
                response.sendRedirect("books?message=" + encode("Book added successfully."));
                return;
            }
            if ("update".equalsIgnoreCase(action)) {
                boolean updated = bookDAO.update(getServletContext(), parseBook(request, true));
                response.sendRedirect("books?" + (updated ? "message=" + encode("Book updated successfully.")
                        : "error=" + encode("Book record not found for update.")));
                return;
            }
            if ("delete".equalsIgnoreCase(action)) {
                boolean deleted = bookDAO.delete(getServletContext(), parseBookId(request));
                response.sendRedirect("books?" + (deleted ? "message=" + encode("Book deleted successfully.")
                        : "error=" + encode("Book record not found for deletion.")));
                return;
            }
            response.sendRedirect("books?error=" + encode("Unsupported action requested."));
        } catch (IllegalArgumentException e) {
            response.sendRedirect("books?error=" + encode(e.getMessage()));
        } catch (SQLException e) {
            response.sendRedirect("books?error=" + encode("Database operation failed: " + e.getMessage()));
        } catch (ClassNotFoundException e) {
            response.sendRedirect("books?error="
                    + encode("MySQL JDBC driver not found. Add Connector/J to Tomcat or WEB-INF/lib."));
        }
    }

    private void renderDashboard(HttpServletRequest request, HttpServletResponse response, Book editBook)
            throws IOException {
        response.setContentType("text/html; charset=UTF-8");

        String keyword = text(request.getParameter("keyword"));
        String stock = text(request.getParameter("stock"));
        String message = text(request.getParameter("message"));
        String error = text(request.getParameter("error"));

        List<Book> books;
        InventoryStats stats;

        try {
            books = bookDAO.findBooks(getServletContext(), keyword, stock);
            stats = bookDAO.getStats(getServletContext());
        } catch (SQLException e) {
            renderFatalError(response, "Database Error", "Unable to read inventory: " + e.getMessage());
            return;
        } catch (ClassNotFoundException e) {
            renderFatalError(response, "Driver Error",
                    "MySQL JDBC driver not found. Add Connector/J to Tomcat or WEB-INF/lib.");
            return;
        }

        try (PrintWriter out = response.getWriter()) {

            out.println("<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>");
            out.println("<meta name='viewport' content='width=device-width, initial-scale=1.0'>");
            out.println("<title>Bookstore Inventory</title><style>");
            out.println("body{margin:0;font-family:'Segoe UI',Tahoma,sans-serif;background:linear-gradient(135deg,#f5efe2,#dbe7f0);color:#102a43}");
            out.println(".page{max-width:1180px;margin:0 auto;padding:28px 18px 42px}");
            out.println(".hero,.panel,.stat{background:#fff;border-radius:20px;box-shadow:0 12px 30px rgba(15,23,42,.08)}");
            out.println(".hero{padding:26px;background:linear-gradient(135deg,#143d59,#1c5d7a);color:#fff}");
            out.println(".hero a,.btn{display:inline-block;text-decoration:none;border:none;cursor:pointer;border-radius:999px;font-weight:700}");
            out.println(".hero a,.btn{padding:10px 16px}.btn-primary{background:#143d59;color:#fff}.btn-accent{background:#f4b942;color:#102a43}.btn-muted{background:#d9e2ec;color:#243b53}.btn-danger{background:#d64545;color:#fff}");
            out.println(".stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin:22px 0}.stat{padding:18px}.stat span{display:block;color:#52606d}.stat strong{font-size:1.7rem}");
            out.println(".layout{display:grid;grid-template-columns:360px 1fr;gap:22px}.panel{padding:22px}.field{margin-bottom:14px}label{display:block;margin-bottom:6px;font-weight:600;color:#334e68}");
            out.println("input,select{width:100%;padding:11px;border:1px solid #bcccdc;border-radius:12px;font-size:.98rem;box-sizing:border-box}");
            out.println(".toolbar{display:grid;grid-template-columns:1fr auto auto auto;gap:12px;margin-bottom:16px}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}");
            out.println(".msg{padding:14px 16px;border-radius:14px;margin:18px 0;font-weight:600}.ok{background:#e8f7ee;color:#1f7a42}.bad{background:#fdecec;color:#a61b1b}");
            out.println("table{width:100%;border-collapse:collapse}th,td{padding:14px 10px;border-bottom:1px solid #e4e7eb;text-align:left;vertical-align:top}th{color:#486581;font-size:.92rem;text-transform:uppercase}");
            out.println(".badge{display:inline-block;padding:6px 12px;border-radius:999px;font-weight:700;font-size:.85rem}.badge.ok{background:#e8f7ee;color:#1f7a42}.badge.low{background:#fff4de;color:#9a6700}");
            out.println(".helper{color:#52606d;line-height:1.5}.empty{background:#f8fafc;padding:22px;border-radius:16px;text-align:center;color:#52606d}.inline{display:inline}.table-actions{display:flex;gap:8px;flex-wrap:wrap}");
            out.println("@media (max-width:900px){.layout,.toolbar{grid-template-columns:1fr}}</style></head><body><div class='page'>");

            out.println("<section class='hero'><h1>Bookstore Inventory Manager</h1>");
            out.println("<p>This servlet app demonstrates MySQL connectivity with JDBC and performs display, insert, update, delete, and filtered search operations on the <strong>ebookshop</strong> table.</p>");
            out.println("<p><a class='btn-accent' href='books'>Refresh Inventory</a> <a class='btn-muted' href='index.html'>Home</a></p></section>");

            out.println("<section class='stats'>");
            stat(out, "Total Titles", Integer.toString(stats.getTotalTitles()));
            stat(out, "Total Quantity", Integer.toString(stats.getTotalStock()));
            stat(out, "Inventory Value", "Rs. " + String.format(Locale.US, "%.2f", stats.getTotalInventoryValue()));
            stat(out, "Low Stock Titles", Integer.toString(stats.getLowStockTitles()));
            out.println("</section>");

            if (!message.isEmpty()) {
                out.println("<div class='msg ok'>" + esc(message) + "</div>");
            }
            if (!error.isEmpty()) {
                out.println("<div class='msg bad'>" + esc(error) + "</div>");
            }

            out.println("<section class='layout'><div class='panel'>");
            renderForm(out, editBook);
            out.println("</div><div class='panel'>");
            out.println("<h2>Inventory Records</h2><p class='helper'>Use the filters below to run the servlet SELECT query and inspect current stock.</p>");
            out.println("<form method='get' action='books'><div class='toolbar'>");
            out.println("<input type='text' name='keyword' placeholder='Search by title or author' value='" + esc(keyword) + "'>");
            out.println("<select name='stock'><option value=''" + sel(stock.isEmpty()) + ">All Stock Levels</option>");
            out.println("<option value='available'" + sel("available".equalsIgnoreCase(stock)) + ">Available</option>");
            out.println("<option value='low'" + sel("low".equalsIgnoreCase(stock)) + ">Low Stock (&lt;= 5)</option></select>");
            out.println("<button class='btn btn-primary' type='submit'>Search</button><a class='btn btn-muted' href='books'>Reset</a></div></form>");

            if (books.isEmpty()) {
                out.println("<div class='empty'>No books matched the current filters. Add a new record or reset the search.</div>");
            } else {
                out.println("<table><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Price</th><th>Quantity</th><th>Status</th><th>Actions</th></tr></thead><tbody>");
                for (Book book : books) {
                    boolean lowStock = book.getQuantity() <= 5;
                    out.println("<tr><td>" + book.getBookId() + "</td><td>" + esc(book.getTitle()) + "</td><td>"
                            + esc(book.getAuthor()) + "</td><td>Rs. "
                            + String.format(Locale.US, "%.2f", book.getPrice()) + "</td><td>" + book.getQuantity()
                            + "</td><td><span class='badge " + (lowStock ? "low" : "ok") + "'>"
                            + (lowStock ? "Low Stock" : "In Stock") + "</span></td><td><div class='table-actions'>");
                    out.println("<a class='btn btn-accent' href='books?action=edit&bookId=" + book.getBookId() + "'>Edit</a>");
                    out.println("<form class='inline' method='post' action='books' onsubmit=\"return confirm('Delete this book record?');\">");
                    out.println("<input type='hidden' name='action' value='delete'><input type='hidden' name='bookId' value='" + book.getBookId() + "'>");
                    out.println("<button class='btn btn-danger' type='submit'>Delete</button></form></div></td></tr>");
                }
                out.println("</tbody></table>");
            }
            out.println("</div></section></div></body></html>");
        }
    }

    private void renderForm(PrintWriter out, Book editBook) {
        boolean editing = editBook != null;
        out.println("<h2>" + (editing ? "Edit Book" : "Add New Book") + "</h2>");
        out.println("<p class='helper'>This form shows servlet POST handling for INSERT and UPDATE operations.</p>");
        out.println("<form method='post' action='books'><input type='hidden' name='action' value='" + (editing ? "update" : "add") + "'>");
        if (editing) {
            out.println("<input type='hidden' name='bookId' value='" + editBook.getBookId() + "'>");
        }
        input(out, "Book Title", "bookTitle", editing ? editBook.getTitle() : "", "text", "");
        input(out, "Author", "bookAuthor", editing ? editBook.getAuthor() : "", "text", "");
        input(out, "Price", "bookPrice", editing ? String.format(Locale.US, "%.2f", editBook.getPrice()) : "",
                "number", "step='0.01' min='0'");
        input(out, "Quantity", "quantity", editing ? Integer.toString(editBook.getQuantity()) : "",
                "number", "min='0'");
        out.println("<div class='actions'><button class='btn btn-primary' type='submit'>" + (editing ? "Update Book" : "Add Book") + "</button>");
        if (editing) {
            out.println("<a class='btn btn-muted' href='books'>Cancel Edit</a>");
        }
        out.println("</div></form>");
    }

    private void input(PrintWriter out, String label, String name, String value, String type, String extraAttributes) {
        out.println("<div class='field'><label for='" + name + "'>" + label + "</label>");
        out.println("<input id='" + name + "' name='" + name + "' type='" + type + "' " + extraAttributes
                + " required value='" + esc(value) + "'></div>");
    }

    private Book parseBook(HttpServletRequest request, boolean requireId) {
        String title = text(request.getParameter("bookTitle"));
        String author = text(request.getParameter("bookAuthor"));
        String bookPrice = text(request.getParameter("bookPrice"));
        String quantityValue = text(request.getParameter("quantity"));

        if (title.isEmpty() || author.isEmpty() || bookPrice.isEmpty() || quantityValue.isEmpty()) {
            throw new IllegalArgumentException("All book fields are required.");
        }

        double price;
        int quantity;

        try {
            price = Double.parseDouble(bookPrice);
        } catch (NumberFormatException e) {
            throw new IllegalArgumentException("Book price must be a valid number.");
        }
        try {
            quantity = Integer.parseInt(quantityValue);
        } catch (NumberFormatException e) {
            throw new IllegalArgumentException("Quantity must be a valid whole number.");
        }

        if (price < 0 || quantity < 0) {
            throw new IllegalArgumentException("Price and quantity cannot be negative.");
        }

        Book book = new Book();
        if (requireId) {
            book.setBookId(parseBookId(request));
        }
        book.setTitle(title);
        book.setAuthor(author);
        book.setPrice(price);
        book.setQuantity(quantity);
        return book;
    }

    private int parseBookId(HttpServletRequest request) {
        String id = text(request.getParameter("bookId"));
        if (id.isEmpty()) {
            throw new IllegalArgumentException("Book id is required.");
        }
        try {
            return Integer.parseInt(id);
        } catch (NumberFormatException e) {
            throw new IllegalArgumentException("Book id must be numeric.");
        }
    }

    private void renderFatalError(HttpServletResponse response, String title, String message) throws IOException {
        response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        response.setContentType("text/html; charset=UTF-8");
        try (PrintWriter out = response.getWriter()) {
            out.println("<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>" + esc(title) + "</title>");
            out.println("<style>body{font-family:'Segoe UI',Tahoma,sans-serif;background:#f8fafc;padding:30px}.card{max-width:720px;margin:0 auto;background:#fff;border-radius:18px;padding:24px;box-shadow:0 8px 24px rgba(0,0,0,.06)}h1{color:#a61b1b}a{color:#143d59;font-weight:700;text-decoration:none}</style>");
            out.println("</head><body><div class='card'><h1>" + esc(title) + "</h1><p>" + esc(message)
                    + "</p><p><a href='books'>Return to inventory dashboard</a></p></div></body></html>");
        }
    }

    private void stat(PrintWriter out, String label, String value) {
        out.println("<div class='stat'><span>" + esc(label) + "</span><strong>" + esc(value) + "</strong></div>");
    }

    private String text(String value) {
        return value == null ? "" : value.trim();
    }

    private String encode(String value) {
        return URLEncoder.encode(value, StandardCharsets.UTF_8);
    }

    private String sel(boolean selected) {
        return selected ? " selected" : "";
    }

    private String esc(String value) {
        StringBuilder escaped = new StringBuilder();
        for (char ch : value.toCharArray()) {
            switch (ch) {
                case '&': escaped.append("&amp;"); break;
                case '<': escaped.append("&lt;"); break;
                case '>': escaped.append("&gt;"); break;
                case '"': escaped.append("&quot;"); break;
                case '\'': escaped.append("&#39;"); break;
                default: escaped.append(ch);
            }
        }
        return escaped.toString();
    }
}
