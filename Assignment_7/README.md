# Bookstore Servlet Application

Java Servlet project for a bookstore inventory system using MySQL and JDBC.

## What It Demonstrates

- Servlet-based request handling with `doGet()` and `doPost()`
- MySQL connectivity through JDBC
- SQL operations on the `ebookshop` table
- CRUD functionality from a browser
- Dynamic HTML generation from servlet output

## Features

- View all books
- Search by title or author
- Filter low-stock and available books
- Add a book
- Edit a book
- Delete a book
- Show inventory statistics such as total titles and inventory value

## Project Structure

```text
Assignment_7/
|-- src/com/bookstore/
|   |-- Book.java
|   |-- BookDAO.java
|   |-- BookServlet.java
|   |-- DBConnection.java
|   `-- InventoryStats.java
|-- WebContent/
|   |-- index.html
|   `-- WEB-INF/
|       `-- web.xml
|-- sql/
|   `-- create_database.sql
|-- build.bat
|-- build.sh
|-- QUICKSTART.txt
`-- README.md
```

## Database Schema

Database: `ebookshop`

Table: `ebookshop`

| Column | Type |
| --- | --- |
| `book_id` | `INT AUTO_INCREMENT PRIMARY KEY` |
| `book_title` | `VARCHAR(255)` |
| `book_author` | `VARCHAR(255)` |
| `book_price` | `DECIMAL(10,2)` |
| `quantity` | `INT` |

## Setup

### 1. Create the MySQL database

Run:

```bash
mysql -u root -p < sql/create_database.sql
```

### 2. Add MySQL Connector/J

Place `mysql-connector-j-x.x.x.jar` in one of these:

- `CATALINA_HOME/lib`
- `WebContent/WEB-INF/lib`

### 3. Configure DB credentials

Edit `WebContent/WEB-INF/web.xml` if your database values are different:

```xml
<context-param>
    <param-name>dbUrl</param-name>
    <param-value>jdbc:mysql://localhost:3306/ebookshop?useSSL=false&amp;allowPublicKeyRetrieval=true&amp;serverTimezone=Asia/Kolkata</param-value>
</context-param>
<context-param>
    <param-name>dbUser</param-name>
    <param-value>root</param-value>
</context-param>
<context-param>
    <param-name>dbPassword</param-name>
    <param-value>root</param-value>
</context-param>
```

### 4. Build

Windows:

```bat
build.bat
```

Linux/Mac:

```bash
./build.sh
```

### 5. Deploy to Tomcat

Copy `build/bookstore.war` to Tomcat's `webapps` folder, then start Tomcat.

### 6. Open the app

```text
http://localhost:8080/bookstore/
```

## Servlet Endpoints

- `/` or `/index.html` - landing page
- `/books` - inventory dashboard

## SQL Operations Covered

- `SELECT` for listing and filtering books
- `INSERT` for adding books
- `UPDATE` for editing books
- `DELETE` for removing books

## Notes

- The servlet uses prepared statements for form-based database operations.
- Database configuration is read from `web.xml` context parameters instead of being hardcoded in Java.
- This is a classroom/demo application, so authentication and connection pooling are intentionally omitted.
