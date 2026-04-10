<%@ page import="java.sql.*" %>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Student</title>

<style>
body {
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#0f172a,#1e293b);
    color:white;
}

.container {
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.card {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    padding:30px;
    border-radius:16px;
    width:350px;
    box-shadow:0 10px 40px rgba(0,0,0,0.5);
}

h2 { text-align:center; margin-bottom:20px; }

.input-group {
    margin-bottom:15px;
}

label {
    display:block;
    margin-bottom:5px;
    font-weight:500;
}

input {
    width:100%;
    padding:10px;
    border:none;
    border-radius:8px;
    outline:none;
    background: rgba(255,255,255,0.1);
    color:white;
}

input::placeholder { color: #cbd5f5; }

button {
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#f59e0b;
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover {
    transform:scale(1.05);
}

.back {
    display:block;
    text-align:center;
    margin-top:10px;
    color:#cbd5f5;
}
</style>

</head>

<body>

<div class="container">
<div class="card">

<h2>Edit Student</h2>

<%
String id = request.getParameter("id");

Class.forName(application.getInitParameter("driverClass"));
Connection con = DriverManager.getConnection(
    application.getInitParameter("jdbcUrl"),
    application.getInitParameter("dbUser"),
    application.getInitParameter("dbPassword")
);

PreparedStatement ps = con.prepareStatement("SELECT * FROM students_info WHERE stud_id=?");
ps.setInt(1, Integer.parseInt(id));
ResultSet rs = ps.executeQuery();
rs.next();
%>

<form method="post">
    <div class="input-group">
        <label for="id">Student ID</label>
        <input type="text" id="id" name="id" value="<%=rs.getInt("stud_id")%>" readonly>
    </div>

    <div class="input-group">
        <label for="name">Student Name</label>
        <input type="text" id="name" name="name" value="<%=rs.getString("stud_name")%>" required>
    </div>

    <div class="input-group">
        <label for="class">Class</label>
        <input type="text" id="class" name="class" value="<%=rs.getString("class")%>">
    </div>

    <div class="input-group">
        <label for="division">Division</label>
        <input type="text" id="division" name="division" value="<%=rs.getString("division")%>">
    </div>

    <div class="input-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<%=rs.getString("city")%>">
    </div>

    <button type="submit">Update Student</button>
</form>

<a href="index.jsp" class="back">← Back</a>

<%
if(request.getMethod().equalsIgnoreCase("POST")){
    PreparedStatement update = con.prepareStatement(
        "UPDATE students_info SET stud_name=?, class=?, division=?, city=? WHERE stud_id=?"
    );

    update.setString(1, request.getParameter("name"));
    update.setString(2, request.getParameter("class"));
    update.setString(3, request.getParameter("division"));
    update.setString(4, request.getParameter("city"));
    update.setInt(5, Integer.parseInt(request.getParameter("id")));

    update.executeUpdate();

    response.sendRedirect("index.jsp");
}
%>

</div>
</div>

</body>
</html>