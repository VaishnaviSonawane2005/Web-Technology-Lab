# E-Bookshop Servlet Application - Assignment 7

## Project Structure
```
Assignment 7/
├── BookServlet.java      # Main servlet for displaying books
├── index.html            # Welcome page
├── WEB-INF/
│   ├── web.xml          # Deployment descriptor
│   └── classes/         # Compiled classes go here
└── README.md            # This file
```

## Prerequisites
1. **Apache Tomcat** (8.x or 9.x)
2. **MySQL Server** with database created
3. **MySQL JDBC Driver** (mysql-connector-java-8.x.jar)
4. **JDK 8 or higher**

## Database Setup (Already Done)
```sql
    CREATE DATABASE ebookshop;

    USE ebookshop;

    CREATE TABLE books (
        book_id INT PRIMARY KEY AUTO_INCREMENT,
        book_title VARCHAR(100),
        book_author VARCHAR(100),
        book_price DOUBLE,
        quantity INT
    );

INSERT INTO books (book_title, book_author, book_price, quantity) VALUES
('Java Basics', 'James Gosling', 500, 10),
('Servlet Guide', 'Sun Microsystems', 300, 5);
```

## Configuration
Edit `BookServlet.java` and update database credentials:
```java
private static final String DB_URL = "jdbc:mysql://localhost:3306/ebookshop";
private static final String DB_USER = "root";
private static final String DB_PASSWORD = "your_password";  // Update this!
```

## Deployment Steps

### Step 1: Compile the Servlet
```cmd
cd "E:\TY LABS SCOE\WTL\Assignment 7"

# Set classpath to include servlet-api.jar from Tomcat
set CLASSPATH=%CATALINA_HOME%\lib\servlet-api.jar;.

# Compile
javac -d WEB-INF/classes BookServlet.java
```

### Step 2: Add MySQL JDBC Driver
Copy `mysql-connector-java-8.x.jar` to:
- `WEB-INF/lib/` folder (create if doesn't exist), OR
- `%CATALINA_HOME%/lib/` folder

### Step 3: Deploy to Tomcat
**Option A:** Copy entire "Assignment 7" folder to `%CATALINA_HOME%/webapps/ebookshop`

**Option B:** Create WAR file and deploy

### Step 4: Start Tomcat
```cmd
%CATALINA_HOME%\bin\startup.bat
```

### Step 5: Access Application
- **Homepage:** http://localhost:8080/ebookshop/
- **Book Management:** http://localhost:8080/ebookshop/books
- **Add Book:** http://localhost:8080/ebookshop/books?action=add
- **View Books:** http://localhost:8080/ebookshop/books?action=list

## Expected Output
The servlet now provides a complete **Book Management System** with the following features:

### 📋 View All Books
- Displays all books in a styled table
- Shows Book ID, Title, Author, Price, and Quantity
- Includes action buttons for Edit and Delete operations

### ➕ Add New Book
- Form to add new books to the database
- Fields: Title, Author, Price, Quantity
- Input validation and success/error messages

### ✏️ Edit Book
- Edit existing book details
- Pre-populated form with current values
- Update functionality with confirmation

### 🗑️ Delete Book
- Delete confirmation dialog
- Safe deletion with user confirmation
- Success/error feedback

### 🎨 Enhanced UI Features
- Modern, responsive design
- Action buttons with icons
- Success/error message display
- Navigation between different operations
- Styled forms and tables

## Troubleshooting
1. **ClassNotFoundException for MySQL Driver**: Ensure mysql-connector-java.jar is in WEB-INF/lib or Tomcat's lib
2. **SQLException**: Check database credentials and ensure MySQL is running
3. **404 Error**: Verify deployment path and URL mapping
