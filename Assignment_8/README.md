# JSP Student Information Portal

This project demonstrates the use of JSP with JDBC to fetch and display all records from a database table named `students_info`.

## Files

- `index.jsp`: Connects to the database and displays student details using a SQL `SELECT` query.
- `WEB-INF/web.xml`: Stores JDBC driver, database URL, username, and password.
- `sql/students_info.sql`: Creates the database, table, and sample records.
- `WEB-INF/lib/README.txt`: Explains where to place the MySQL JDBC driver JAR.

## How to Run

1. Import and execute `sql/students_info.sql` in MySQL.
2. Update the database credentials inside `WEB-INF/web.xml` if required.
3. Copy the MySQL Connector/J JAR into `WEB-INF/lib`.
4. Deploy this folder to a JSP/Servlet container such as Apache Tomcat.
5. Open the application in a browser. The welcome page is `index.jsp`.

## Example URL

If deployed in Tomcat with context name `Assignment_8`:

`http://localhost:8080/Assignment_8/`
