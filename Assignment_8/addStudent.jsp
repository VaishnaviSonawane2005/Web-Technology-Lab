<%@ page contentType="text/html; charset=UTF-8" language="java" import="java.sql.*" %>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Student</title>

<style>
body {
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#0f172a,#1e293b);
    color:white;
}

/* CENTER CARD */
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

/* TITLE */
h2 {
    margin-bottom:20px;
    text-align:center;
}

/* INPUT */
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

/* BUTTON */
button {
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#22c55e;
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover {
    transform:scale(1.05);
}

/* BACK */
.back {
    display:block;
    margin-top:15px;
    text-align:center;
    color:#cbd5f5;
    text-decoration:none;
}
</style>

</head>

<body>

<div class="container">
<div class="card">

<h2>Add Student</h2>

<form method="post">
    <div class="input-group">
        <label for="id">Student ID</label>
        <input type="number" id="id" name="id" placeholder="Enter Student ID" required>
    </div>

    <div class="input-group">
        <label for="name">Student Name</label>
        <input type="text" id="name" name="name" placeholder="Enter Student Name" required>
    </div>

    <div class="input-group">
        <label for="class">Class</label>
        <input type="text" id="class" name="class" placeholder="Enter Class">
    </div>

    <div class="input-group">
        <label for="division">Division</label>
        <input type="text" id="division" name="division" placeholder="Enter Division">
    </div>

    <div class="input-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" placeholder="Enter City">
    </div>

    <button type="submit">Add Student</button>
</form>

<a href="index.jsp" class="back">← Back to Dashboard</a>

<%
if(request.getMethod().equalsIgnoreCase("POST")){
    try{
        Class.forName(application.getInitParameter("driverClass"));
        Connection con = DriverManager.getConnection(
            application.getInitParameter("jdbcUrl"),
            application.getInitParameter("dbUser"),
            application.getInitParameter("dbPassword")
        );

        PreparedStatement ps = con.prepareStatement(
            "INSERT INTO students_info VALUES (?, ?, ?, ?, ?)"
        );

        ps.setInt(1, Integer.parseInt(request.getParameter("id")));
        ps.setString(2, request.getParameter("name"));
        ps.setString(3, request.getParameter("class"));
        ps.setString(4, request.getParameter("division"));
        ps.setString(5, request.getParameter("city"));

        ps.executeUpdate();

        response.sendRedirect("index.jsp");

    } catch(Exception e){
        out.println("<p style='color:red;'>"+e.getMessage()+"</p>");
    }
}
%>

</div>
</div>

</body>
</html>