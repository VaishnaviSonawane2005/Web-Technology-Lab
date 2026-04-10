<%@ page contentType="text/html; charset=UTF-8" language="java" import="java.sql.*" %>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Portal</title>

<style>
:root {
    --primary: #4f46e5;
    --accent: #22c55e;
    --danger: #ef4444;
    --bg: #0f172a;
    --card: rgba(255,255,255,0.08);
    --glass: blur(12px);
}

/* GLOBAL */
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
}

/* CONTAINER */
.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.title {
    font-size: 28px;
    font-weight: bold;
}

.btn-add {
    background: var(--accent);
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    color: white;
    font-weight: bold;
    transition: 0.3s;
}
.btn-add:hover {
    transform: scale(1.05);
}

/* SEARCH */
.search-box {
    margin: 20px 0;
}
.search-box input {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: none;
    outline: none;
}

/* GRID */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}

/* CARD */
.card {
    background: var(--card);
    backdrop-filter: var(--glass);
    padding: 18px;
    border-radius: 16px;
    transition: 0.3s;
    border: 1px solid rgba(255,255,255,0.1);
}

.card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 40px rgba(0,0,0,0.5);
}

/* STUDENT NAME */
.name {
    font-size: 20px;
    font-weight: bold;
}

/* DETAILS */
.details {
    margin: 10px 0;
    font-size: 14px;
    color: #cbd5f5;
}

/* ACTIONS */
.actions {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.btn {
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 12px;
    text-decoration: none;
    color: white;
}

.edit { background: #f59e0b; }
.delete { background: var(--danger); }

.btn:hover {
    opacity: 0.8;
}

/* STATUS */
.status {
    margin: 15px 0;
    padding: 10px;
    border-radius: 8px;
    background: #16a34a;
}

.error {
    background: #dc2626;
}

/* EMPTY */
.empty {
    text-align: center;
    margin-top: 30px;
    opacity: 0.7;
}
</style>

<script>
function searchStudents() {
    let input = document.getElementById("search").value.toLowerCase();
    let cards = document.getElementsByClassName("card");

    for (let i = 0; i < cards.length; i++) {
        let text = cards[i].innerText.toLowerCase();
        cards[i].style.display = text.includes(input) ? "block" : "none";
    }
}
</script>

</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="title">🎓 Student Dashboard</div>
        <a href="addStudent.jsp" class="btn-add">+ Add</a>
    </div>

    <!-- SEARCH -->
    <div class="search-box">
        <input type="text" id="search" onkeyup="searchStudents()" placeholder="Search students...">
    </div>

<%
    try {
        Class.forName(application.getInitParameter("driverClass"));
        Connection con = DriverManager.getConnection(
            application.getInitParameter("jdbcUrl"),
            application.getInitParameter("dbUser"),
            application.getInitParameter("dbPassword")
        );

        PreparedStatement ps = con.prepareStatement("SELECT * FROM students_info");
        ResultSet rs = ps.executeQuery();

        boolean hasData = false;
%>

<div class="status">Connected successfully</div>

<div class="grid">

<%
    while(rs.next()){
        hasData = true;
%>

    <div class="card">
        <div class="name"><%= rs.getString("stud_name") %></div>

        <div class="details">
            ID: <%= rs.getInt("stud_id") %><br>
            Class: <%= rs.getString("class") %><br>
            Division: <%= rs.getString("division") %><br>
            City: <%= rs.getString("city") %>
        </div>

        <div class="actions">
            <a href="editStudent.jsp?id=<%= rs.getInt("stud_id") %>" class="btn edit">Edit</a>

            <a href="deleteStudent.jsp?id=<%= rs.getInt("stud_id") %>"
               class="btn delete"
               onclick="return confirm('Delete student?')">
               Delete
            </a>
        </div>
    </div>

<%
    }

    if(!hasData){
%>
    <div class="empty">No students found</div>
<%
    }

    con.close();

    } catch(Exception e){
%>

<div class="status error">
    Error: <%= e.getMessage() %>
</div>

<%
    }
%>

</div>

</div>

</body>
</html>