<%@ page import="java.sql.*" %>

<%
String id = request.getParameter("id");

try{
    Class.forName(application.getInitParameter("driverClass"));
    Connection con = DriverManager.getConnection(
        application.getInitParameter("jdbcUrl"),
        application.getInitParameter("dbUser"),
        application.getInitParameter("dbPassword")
    );

    PreparedStatement ps = con.prepareStatement(
        "DELETE FROM students_info WHERE stud_id=?"
    );

    ps.setInt(1, Integer.parseInt(id));
    ps.executeUpdate();

    response.sendRedirect("index.jsp");

} catch(Exception e){
    out.println(e);
}
%>